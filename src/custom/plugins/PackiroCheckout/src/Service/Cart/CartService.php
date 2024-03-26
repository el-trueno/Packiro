<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Cart;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Packiro\Checkout\Exception\UnsupportedLineItemException;
use Packiro\Checkout\Exception\UsedCartException;
use Packiro\Checkout\Factory\Cart\LineItemFactoryInterface;
use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Packiro\Checkout\Reader\Promotion\PromotionReaderInterface;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Promotion\Cart\PromotionCodeTuple;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartService implements CartServiceInterface
{
    private const SPLIT_CART_DELIVERIES_KEY = 'split-cart-deliveries';

    public function __construct(
        private AbstractCartPersister $cartPersister,
        private SalesChannelContextPersister $salesChannelContextPersister,
        private CartReaderInterface $cartReader,
        private PromotionReaderInterface $promotionReader,
        private LineItemFactoryInterface $lineItemFactory,
        private CartCalculator $cartCalculator,
        private QuantityPriceCalculator $quantityPriceCalculator,
        private DeliveryCalculator $deliveryCalculator
    ) {
    }

    /**
     * @throws CartTokenNotFoundException
     * @throws UsedCartException
     */
    public function switchCartInContext(?string $cartToSwitchToken, SalesChannelContext $salesChannelContext): ?string
    {
        if ($cartToSwitchToken) {
            try {
                $cartToSwitch = $this->cartPersister->load($cartToSwitchToken, $salesChannelContext);
                $this->validateCartToSwitch($cartToSwitchToken, $salesChannelContext);
            } catch (CartTokenNotFoundException $exception) {
            }
        }

        try {
            $currentCart = $this->cartPersister->load($salesChannelContext->getToken(), $salesChannelContext);
            $currentCart = $this->changeCurrentCartToken($currentCart, $salesChannelContext);
        } catch (CartTokenNotFoundException $exception) {
            $currentCart = null;
        }

        if (!empty($cartToSwitch)) {
            $this->cartPersister->replace($cartToSwitch->getToken(), $salesChannelContext->getToken(), $salesChannelContext);
        }

        return $currentCart?->getToken();
    }

    public function getSplitCart(LineItem $lineItem, SalesChannelContext $salesChannelContext): Cart
    {
        if ($lineItem->getType() !== PouchBundleCartProcessor::TYPE) {
            throw new UnsupportedLineItemException(sprintf('Unsupported line item type: %s', $lineItem->getType()));
        }

        $deliveryTypeId = $this->getDeliveryTypeId($lineItem);

        $pcCart = $this->cartReader->findPcCart(
            $salesChannelContext->getToken(),
            $lineItem->getReferencedId(),
            $deliveryTypeId,
        );

        if (!$pcCart) {
            $pcCart = new SplitCartExtension(
                Random::getAlphanumericString(32),
                $salesChannelContext->getToken(),
                $lineItem->getReferencedId(),
                $deliveryTypeId,
            );

            $cart = new Cart('split-cart', $pcCart->getCartToken());
            $cart->addExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME, $pcCart);

            return $cart;
        }

        return $this->cartPersister->load($pcCart->getCartToken(), $salesChannelContext);
    }

    public function getSplitDiscountLineItems(
        CartDataCollection $cartDataCollection,
        Cart $originalCart,
        SalesChannelContext $salesChannelContext
    ): array {
        if (!$originalCart->hasExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME)) {
            return [];
        }

        /** @var SplitCartExtension $splitCartExtension */
        $splitCartExtension = $originalCart->getExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME);

        try {
            $parentCart = $this->cartPersister->load($splitCartExtension->getParentToken(), $salesChannelContext);
        } catch (CartTokenNotFoundException $exception) {
            // If no parent fetching existing promotions
            return $originalCart->getLineItems()->filterType(PromotionProcessor::LINE_ITEM_TYPE)->getElements();
        }

        $parentCartCodes = $parentCart->getLineItems()->filterType(PromotionProcessor::LINE_ITEM_TYPE)->getReferenceIds();
        $allParentPromotions = $this->promotionReader->findPromotionsByCodes($cartDataCollection, $parentCartCodes, $salesChannelContext);

        $discountLineItems = [];
        /** @var PromotionCodeTuple $tuple */
        foreach ($allParentPromotions->getPromotionCodeTuples() as $tuple) {
            $lineItems = $this->lineItemFactory->createDiscountLineItems(
                $tuple->getCode(),
                $tuple->getPromotion(),
                $originalCart,
                $salesChannelContext,
            );

            /** @var LineItem $nested */
            foreach ($lineItems as $nested) {
                $discountLineItems[] = $nested;
            }
        }

        return $discountLineItems;
    }

    public function recalculateSplitCarts(string $parentToken, SalesChannelContext $salesChannelContext): void
    {
        $splitCarts = $this->cartReader->findSplitCarts($parentToken);

        foreach ($splitCarts as $splitCart) {
            $splitCart = $this->cartCalculator->calculate($splitCart, $salesChannelContext);
            $this->cartPersister->save($splitCart, $salesChannelContext);
        }
    }

    public function removeDuplicatedDeliveryCosts(Cart $cart, SalesChannelContext $salesChannelContext): Cart
    {
        $appliedDeliveryPrices = [];
        foreach ($cart->getLineItems() as $parentLineItem) {
            if ($parentLineItem->getType() !== PouchBundleCartProcessor::TYPE) {
                continue;
            }

            /** @var LineItem $deliveryLineItem */
            $deliveryLineItem = $parentLineItem
                ->getChildren()
                ->filterType(PouchBundleCartProcessor::TYPE_DELIVERY_TIME)
                ->first();
            if (!$deliveryLineItem) {
                continue;
            }

            $batchItemHash = $this->getBatchItemHash($parentLineItem, $deliveryLineItem);
            if (!isset($appliedDeliveryPrices[$batchItemHash])) {
                $appliedDeliveryPrices[$batchItemHash] = true;

                continue;
            }

            $priceDefinition = $this->getZeroPriceDefinition($deliveryLineItem->getPriceDefinition());

            $deliveryLineItem
                ->setPriceDefinition($priceDefinition)
                ->setPrice(
                    $this->quantityPriceCalculator->calculate($priceDefinition, $salesChannelContext),
                );

            /** @var LineItem $productLineItem */
            $productLineItem =  $parentLineItem->getChildren()
                ->filterType(PouchBundleCartProcessor::TYPE_PRODUCT)
                ->first();
            PouchBundleCartProcessor::setBundlePrice(
                $parentLineItem,
                $parentLineItem->getChildren(),
                $productLineItem ? $productLineItem->getQuantity() : 1,
            );
        }

        return $cart;
    }

    public function collectSplitCartDeliveries(
        CartDataCollection $data,
        Cart $original,
    ): void {
        $splitCarts = $this->cartReader->findSplitCarts($original->getToken());
        $deliveryCollection = new DeliveryCollection();

        foreach ($splitCarts as $splitCart) {
            $deliveryCollection->add($splitCart->getDeliveries()->first());
        }

        $data->set(self::SPLIT_CART_DELIVERIES_KEY, $deliveryCollection);
    }

    public function prepareShippingCostsWithSplitCart(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context
    ): void {
        $deliveryCollection = $data->get(self::SPLIT_CART_DELIVERIES_KEY);

        if ($deliveryCollection->count() !== 0) {
            $toCalculate->setDeliveries($deliveryCollection);
            $this->deliveryCalculator->calculate($data, $toCalculate, $deliveryCollection, $context);
        }
    }

    private function changeCurrentCartToken(Cart $currentCart, SalesChannelContext $salesChannelContext): Cart
    {
        $newToken = Random::getAlphanumericString(32);

        $this->cartPersister->replace($currentCart->getToken(), $newToken, $salesChannelContext);

        $currentCart->setToken($newToken);

        return $currentCart;
    }

    /**
     * @throws UsedCartException
     */
    private function validateCartToSwitch(string $cartToken, SalesChannelContext $salesChannelContext): void
    {
        if (!empty($this->salesChannelContextPersister->load($cartToken, $salesChannelContext->getSalesChannelId()))) {
            throw new UsedCartException(sprintf('The cart with token %s is already used', $cartToken));
        }
    }

    private function getDeliveryTypeId(LineItem $lineItem): string
    {
        $accessoryOptions = $lineItem->getPayloadValue('accessoryOptions');

        foreach ($accessoryOptions as $accessoryOption) {
            if ($accessoryOption['type'] === PouchBundleCartProcessor::TYPE_DELIVERY_TIME) {
                return $accessoryOption['id'];
            }
        }

        throw new \Exception('Delivery type not found'); // TODO add appropriate exception or handle this situation differently
    }

    private function getBatchItemHash(LineItem $parentLineItem, LineItem $deliveryLineItem): string
    {
        return $parentLineItem->getReferencedId() . $deliveryLineItem->getReferencedId();
    }

    private function getZeroPriceDefinition(?PriceDefinitionInterface $originalPriceDefinition = null): QuantityPriceDefinition
    {
        if ($originalPriceDefinition instanceof QuantityPriceDefinition) {
            $taxRules = $originalPriceDefinition->getTaxRules();
        } else {
            $taxRules = new TaxRuleCollection();
        }

        return new QuantityPriceDefinition(0.0, $taxRules, 1);
    }
}
