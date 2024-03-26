<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart;

use Exception;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryGroupCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryGroupEntity;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\SalesChannelAccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Core\Content\Product\PcProductDefinition;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderCollection;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Kuniva\PackiroConfigurator\Core\Service\ProLibItemOrderService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Exception\MissingLineItemPriceException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\Cart\ProductGatewayInterface;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

use function count;

class PouchBundleCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    public const PURCHASE_STEP = 500;
    public const TYPE = 'pc_pouch_bundle';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SIDE_DESIGN = 'SIDES';
    public const TYPE_ARTWORK = 'artwork';
    public const TYPE_DELIVERY_TIME = 'delivery';
    public const TYPE_PSEUDO_PRODUCT = 'pseudo-product';
    public const DATA_KEY = 'pc_accessory-option-';
    public const DATA_KEY_PRODUCT = 'product-';
    public const DATA_KEY_PARENT_PRODUCT = 'parent-product-';

    public const ACCESSORY_DATA = 'accessoryData';
    public const GROUP = 'group';
    public const OPTION = 'option';
    public const PC_OPTIONS_KEY = 'pcOptions';
    private const TYPE_KEY = 'TYPE';
    private const PRODUCT_NUMBER = 'productNumber';
    private const TECHNICAL_NAME = 'technicalName';
    private const TYPE_EXTRA = 'EXTRAS';
    private const DELIVERY_TIME_KEY = 'deliveryTime';
    private const OPTION_DATA = 'data';

    public function __construct(
        private readonly ProductGatewayInterface $productGateway,
        private readonly QuantityPriceCalculator $calculator,
        private readonly ProLibItemOrderService $proLibItemOrderService
    ) {
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        Profiler::trace(
            'cart::pc-pouch-bundle::collect',
            function () use ($data, $original, $context, $behavior): void {
                $parentLineItems = $original->getLineItems()->filterType(self::TYPE);
                if (count($parentLineItems) === 0) {
                    return;
                }

                /** @var LineItem $parentLineItem */
                foreach ($parentLineItems as $parentLineItem) {
                    $childrenLineItems = $parentLineItem->getChildren();
                    if (!$childrenLineItems) {
                        continue;
                    }

                    /* Enrich main product */
                    $productLineItem = $this->getProductLineItem($childrenLineItems);
                    $this->enrichMainProductLI($productLineItem, $data, $context);


                    $product = $this->getProductEntity($productLineItem, $data, $context);
                    /** @var AccessoryOptionCollection $accessoryOptions */
                    $accessoryOptions = $product->getExtension('accessoryOptions');
                    /** @var AccessoryGroupCollection $accessoryGroups */
                    $accessoryGroups = $product->getExtension('accessoryGroups');
                    if ($accessoryOptions->count() && $accessoryGroups) {
                        foreach ($accessoryOptions as $accessoryOption) {
                            /* The accessory groups are removed to prevent a recursion, for the cart processor they are enriched again */
                            $accessoryGroup = $accessoryGroups->get($accessoryOption->getAccessoryGroupId());
                            if (!$accessoryGroup) {
                                throw new ProductNotFoundException($productLineItem->getReferencedId());
                            }
                            $accessoryOption->setAccessoryGroup($accessoryGroup);

                            /* Store all possible accessory options from main product */
                            $data->set(self::DATA_KEY . $accessoryOption->getId(), $accessoryOption);
                        }
                    } else {
                        throw new ProductNotFoundException($productLineItem->getReferencedId());
                    }

                    /* Enrich parent line item by product */
                    $parentLineItemPayload = $parentLineItem->getPayload() ?: [];
                    $parentLineItemPayload = array_merge(
                        $parentLineItemPayload,
                        $productLineItem->getPayload()
                    );
                    $parentLineItem->setPayload($parentLineItemPayload);

                    $parentLineItem->setRemovable(true);
                    $parentLineItem->setStackable(false);
                    $parentLineItem->setLabel($product->getTranslation('name'));
                    //$parentLineItem->setQuantityInformation($productLineItem->getQuantityInformation());
                    if ($product->getCover()) {
                        $parentLineItem->setCover($product->getCover()->getMedia());
                    }
                    $parentLineItem->setReferencedId($productLineItem->getReferencedId());

                    /* Enrich ProLibItemOrderCollection "Artwork slots" from cart */
                    if ($original->hasExtension(ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME)) {
                        /** @var ProLibItemOrderCollection $proLibItemOrders */
                        $proLibItemOrders = $original->getExtension(
                            ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME
                        );

                        $parentLineItem->addExtension(
                            ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
                            $proLibItemOrders->filterByCartLineItem($parentLineItem->getId())
                        );
                    }

                    /* Enrich accessories */
                    $accessoryOptionLineItems = $this->filterNotType(
                        $childrenLineItems,
                        LineItem::PRODUCT_LINE_ITEM_TYPE
                    );

                    foreach ($accessoryOptionLineItems as $accessoryOptionLineItem) {
                        $this->enrichAccessoryOption(
                            $context,
                            $productLineItem,
                            $accessoryOptionLineItem,
                            $data,
                            $behavior
                        );
                    }
                }
            },
            'cart'
        );
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        Profiler::trace(
            'cart::pc-pouch-bundle::process',
            function () use ($data, $original, $toCalculate, $context): void {
                $parentLineItems = $original->getLineItems()->filterType(self::TYPE);
                if (count($parentLineItems) === 0) {
                    return;
                }

                /* Is mandatory to enrich cart from product library items instantly */
                $this->proLibItemOrderService->enrichCart($toCalculate, $parentLineItems, $context);

                foreach ($parentLineItems as $parentLineItem) {
                    $childrenLineItems = $parentLineItem->getChildren();
                    if (!$childrenLineItems) {
                        continue;
                    }

                    /* Calculate main product - done by ProductCartProcessor */
                    $productLineItem = $this->getProductLineItem($childrenLineItems);

                    /* If custom price, modify price definition of product */
                    if ($parentLineItem->getPayloadValue('hasCustomPrice')) {
                        /** @var QuantityPriceDefinition $priceDefinition */
                        $priceDefinition = $productLineItem->getPriceDefinition();
                        $priceDefinition = new QuantityPriceDefinition(
                            $parentLineItem->getPayloadValue('customUnitPrice'),
                            $priceDefinition->getTaxRules(),
                            $priceDefinition->getQuantity()
                        );
                        $productLineItem->setPriceDefinition($priceDefinition);

                        $productLineItem->setPrice(
                            $this->calculator->calculate($priceDefinition, $context)
                        );

                        $priceDefinition->setIsCalculated(true);
                    }

                    /* Calculate accessories */
                    $accessoryOptionLineItems = $this->filterNotType(
                        $childrenLineItems,
                        LineItem::PRODUCT_LINE_ITEM_TYPE
                    );
                    foreach ($accessoryOptionLineItems as $accessoryOptionLineItem) {
                        $this->calculateChildLineItemAccessoryOption(
                            $data,
                            $toCalculate,
                            $productLineItem,
                            $accessoryOptionLineItem,
                            $context
                        );
                    }

                    $this->setBundlePrice($parentLineItem, $childrenLineItems, $productLineItem->getQuantity());
                    $this->enrichProLibItemOrders(
                        $toCalculate,
                        $parentLineItem
                    );

                    $toCalculate->add($parentLineItem);
                }
            },
            'cart'
        );
    }

    public static function setBundlePrice(LineItem $parentLineItem, LineItemCollection $childrenLineItems, int $quantity): void
    {
        $calculatedPrice = $childrenLineItems->getPrices()->sum();
        $parentLineItem->setPrice(
            new CalculatedPrice(
                $calculatedPrice->getTotalPrice() / $quantity,
                $calculatedPrice->getTotalPrice(),
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules(),
            ),
        );
    }

    private function getProductEntity(
        LineItem $productLineItem,
        CartDataCollection $data,
        SalesChannelContext $context
    ): SalesChannelProductEntity {
        /* Core product collector with priority 5000 should have stored product */
        /** @var SalesChannelProductEntity $product */
        $product = $data->get('product-' . $productLineItem->getReferencedId());
        if (!$product) {
            throw new ProductNotFoundException($productLineItem->getReferencedId());
        }

        if (
            $product->getParentId()
            && !$product->hasExtension(PcProductDefinition::EXTENSION_NAME)
        ) {
            /** @var SalesChannelProductEntity $parentProduct */
            if (!$data->has(self::DATA_KEY_PARENT_PRODUCT . $product->getParentId())) {
                $parentProduct = $this->productGateway->get([$product->getParentId()], $context)->first(); //can be null
                $data->set(self::DATA_KEY_PARENT_PRODUCT . $parentProduct->getId(), $parentProduct);
            } else {
                $parentProduct = $data->get(self::DATA_KEY_PARENT_PRODUCT . $product->getParentId());
            }
            if (!$parentProduct) {
                throw new ProductNotFoundException($product->getParentId());
            }
            $product->addExtension(//like it is inherited
                PcProductDefinition::EXTENSION_NAME,
                $parentProduct->getExtension(PcProductDefinition::EXTENSION_NAME)
            );
        }

        return $product;
    }

    private function enrichMainProductLI(
        LineItem $productLineItem,
        CartDataCollection $data,
        SalesChannelContext $context
    ): void {
        $productLineItem->setStackable(false);
        $productLineItem->setRemovable(false);
        $productLineItem->setLabel(
            sprintf("%dx%s", $productLineItem->getQuantity(), $productLineItem->getLabel())
        );
        $product = $this->getProductEntity($productLineItem, $data, $context);
        $optionsArray[self::TYPE_KEY] = $product->getExtension(
            PcProductDefinition::EXTENSION_NAME
        )?->getTechnicalName();
        $optionsArray[self::PRODUCT_NUMBER] = $product->getProductNumber();
        /** @var PropertyGroupOptionEntity $option */
        foreach ($product->getOptions()->getElements() as $option) {
            $optionsArray[$option->getGroup()->getCustomFields()[self::TECHNICAL_NAME] ?? '--no-technical-name--']
                = $option->getCustomFields()[self::TECHNICAL_NAME] ?? '--no-technical-name--';
        }
        $productLineItem->setPayloadValue(self::PC_OPTIONS_KEY, $optionsArray);
    }

    private function enrichProLibItemOrders(
        Cart $cart,
        LineItem $parentLineItem
    ): void {
        if ($parentLineItem->hasExtension(ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME)) {
            return;
        }

        /* Enrich ProLibItemOrderCollection "Artwork slots" from cart to line item */
        if ($cart->hasExtension(ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME)) {
            /** @var ProLibItemOrderCollection $proLibItemOrders */
            $proLibItemOrders = $cart->getExtension(ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME);

            $parentLineItem->addExtension(
                ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
                $proLibItemOrders->filterByCartLineItem($parentLineItem->getId())
            );
        }
    }

    private function getProductLineItem(LineItemCollection $lineItems): LineItem
    {
        $productLineItems = $lineItems->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        if ($productLineItems->count() !== 1) {
            throw new Exception("Exactly one product is expected");
        }
        return $productLineItems->first();
    }

    private function filterNotType(LineItemCollection $lineItems, string $type): LineItemCollection
    {
        return $lineItems->filter(
            function (LineItem $lineItem) use ($type) {
                return $lineItem->getType() !== $type;
            }
        );
    }

    private function enrichAccessoryOption(
        SalesChannelContext $context,
        LineItem $parentLineItem,
        LineItem $lineItem,
        CartDataCollection $data,
        CartBehavior $behavior
    ): void {
        $lineItem->setStackable(true);
        $lineItem->setRemovable(false);

        $id = $lineItem->getReferencedId();
        /** @var AccessoryOptionEntity $accessoryOption */
        $accessoryOption = $data->get(self::DATA_KEY . $id);
        if (!$accessoryOption instanceof SalesChannelAccessoryOptionEntity) {
            return;
        }
        $accessoryGroup = $accessoryOption->getAccessoryGroup();
        if (!$accessoryGroup instanceof AccessoryGroupEntity) {
            return;
        }

        if ($accessoryGroup->isScalingStack()) {
            $lineItem->setQuantity($parentLineItem->getQuantity());
        }

        if ($accessoryGroup->getType() === self::TYPE_ARTWORK) {
            $quantityInformation = new QuantityInformation();
            $quantityInformation->setMinPurchase(1);
            $quantityInformation->setMaxPurchase((int)ceil($parentLineItem->getQuantity() / self::PURCHASE_STEP));
            $lineItem->setQuantityInformation($quantityInformation);
            $lineItem->setStackable(false);
        }

        /* Different ProductStackProcessor rule by selected delivery time */
        if ($accessoryGroup->getType() === self::TYPE_DELIVERY_TIME) {
            $parentLineItem->setPayloadValue(self::TYPE_DELIVERY_TIME, $id);
            $lineItem->setStackable(false);
        }

        /* Different ProductStackProcessor rule by selected delivery time */
        if ($accessoryGroup->getType() === self::TYPE_EXTRA) {
            $parentLineItem->setPayloadValue(self::DELIVERY_TIME_KEY, $accessoryOption->getMaxDeliveryDays());
        }

        /* Shopware do not support stackable or removable for child line items */
        //$lineItem->setRemovable($accessoryGroup->isMultipleSelection());

        $lineItem->setLabel(
            sprintf("%dx%s", $lineItem->getQuantity(), $accessoryOption->getTranslation('name'))
        );

        if ($accessoryOption->getMedia()) {
            $lineItem->setCover($accessoryOption->getMedia());
        }

        $lineItem->setPriceDefinition(
            $this->getPriceDefinition($accessoryOption, $context, $lineItem->getQuantity())
        );

        $this->setLIAccessoryPayload($lineItem, $accessoryGroup, $accessoryOption);
    }

    private function calculateChildLineItemAccessoryOption(
        CartDataCollection $data,
        Cart $toCalculate,
        LineItem $parentLineItem,
        LineItem $lineItem,
        SalesChannelContext $context
    ): void {
        /** @var SalesChannelAccessoryOptionEntity $accessoryOption */
        $accessoryOption = $data->get(self::DATA_KEY . $lineItem->getReferencedId());
        if (!$accessoryOption) {
            return;
        }

        $accessoryGroup = $accessoryOption->getAccessoryGroup();
        if (!$accessoryGroup instanceof AccessoryGroupEntity) {
            return;
        }

        $definition = $lineItem->getPriceDefinition();
        if (!$definition instanceof QuantityPriceDefinition) {
            throw new MissingLineItemPriceException($lineItem->getId());
        }

        if ($accessoryGroup->getType() === self::TYPE_ARTWORK) {
            $definition = new QuantityPriceDefinition(
                $accessoryOption->getCalculatedPrice()->getUnitPrice(),
                $definition->getTaxRules(),
                $lineItem->getQuantity() - 1
            );
        } else {
            $definition = new QuantityPriceDefinition(
                $accessoryOption->getCalculatedPrice()->getUnitPrice(),
                $definition->getTaxRules(),
                $definition->getQuantity()
            );
        }

        $lineItem->setPrice(
            $this->calculator->calculate($definition, $context)
        );
    }

    private function getPriceDefinition(
        SalesChannelProductEntity|SalesChannelAccessoryOptionEntity $product,
        SalesChannelContext $context,
        int $quantity
    ): QuantityPriceDefinition {
        if ($product->getCalculatedPrices()->count() === 0) {
            return $this->buildPriceDefinition($product->getCalculatedPrice(), $quantity);
        }

        // keep loop reference to $price variable to get last quantity price in case of "null"
        $price = $product->getCalculatedPrice();
        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $this->buildPriceDefinition($price, $quantity);
    }

    private function buildPriceDefinition(CalculatedPrice $price, int $quantity): QuantityPriceDefinition
    {
        $definition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $quantity);
        if ($price->getListPrice() !== null) {
            $definition->setListPrice($price->getListPrice()->getPrice());
        }

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        return $definition;
    }

    private function setLIAccessoryPayload(
        LineItem $lineItem,
        AccessoryGroupEntity $accessoryGroup,
        AccessoryOptionEntity $accessoryOption
    ): void {
        //Only one option per group can be set, another option will be in a new ine item
        $lineItem->setPayloadValue(
            self::ACCESSORY_DATA,
            [
                self::GROUP => $accessoryGroup->getTechnicalName(),
                self::OPTION => $accessoryOption->getTechnicalName(),
                self::OPTION_DATA => $accessoryOption->toExportArray(),
            ]
        );
    }
}
