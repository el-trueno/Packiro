<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Cart;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\CartCollection;
use Packiro\Checkout\Exception\AccessoriesAlreadyExistsException;
use Packiro\Checkout\Exception\AccessoryNotFoundException;
use Packiro\Checkout\Factory\Cart\LineItemFactoryInterface;
use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Packiro\Checkout\Struct\Accessory\AccessoryStruct;
use Packiro\Checkout\Struct\Accessory\AccessoryStructCollection;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemAccessoryService implements LineItemAccessoryServiceInterface
{
    public const PAYLOAD_KEY_ACCESSORIES = 'accessoryOptions';

    public function __construct(
        private readonly AbstractCartPersister $cartPersister,
        private readonly CartCalculator $cartCalculator,
        private readonly CartReaderInterface $cartReader,
        private readonly CartServiceInterface $cartService,
        private readonly LineItemFactoryInterface $lineItemFactory,
    ) {
    }

    public function addAccessoriesToLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart {
        $parentLineItem = $this->retrieveLineItemFromCart($lineItemId, $mainCart);
        $splitCart = $this->cartService->getSplitCart($parentLineItem, $salesChannelContext);
        $splitCartLineItem = $this->retrieveLineItemFromCart($lineItemId, $splitCart);

        $accessoryIds = $accessoryStructCollection->getIds();
        $accessoryFilterClosure = fn(LineItem $lineItem) => in_array($lineItem->getReferencedId(), $accessoryIds);

        $accessoryLineItems = $parentLineItem->getChildren()->filter($accessoryFilterClosure);
        $accessorySplitCartLineItems = $splitCartLineItem->getChildren()->filter($accessoryFilterClosure);
        if ($accessoryLineItems->count() > 0 || $accessorySplitCartLineItems->count() > 0) {
            throw new AccessoriesAlreadyExistsException();
        }

        foreach ($accessoryStructCollection as $accessoryStruct) {
            $accessoryLineItem = $this->lineItemFactory->createAccessoryLineItem($accessoryStruct);
            $accessoriesPayload = $parentLineItem->getPayloadValue(self::PAYLOAD_KEY_ACCESSORIES);
            $accessoriesPayload[$accessoryStruct->getId()] = $accessoryStruct->toArray();

            $parentLineItem->addChild($accessoryLineItem);
            $parentLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);

            $splitCartLineItem->addChild($accessoryLineItem);
            $splitCartLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);
        }

        $mainCart = $this->recalculateCarts($mainCart, $splitCart, $salesChannelContext);

        return $this->addExtensionsToCart($mainCart);
    }

    public function updateAccessoriesInLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart {
        $parentLineItem = $this->retrieveLineItemFromCart($lineItemId, $mainCart);

        $splitCart = $this->cartService->getSplitCart($parentLineItem, $salesChannelContext);
        $splitCartLineItem = $this->retrieveLineItemFromCart($lineItemId, $splitCart);

        foreach ($accessoryStructCollection as $accessoryStruct) {
            $accessoryLineItem = $this->retrieveAccessoryFromLineItem($parentLineItem, $accessoryStruct);
            $accessorySplitCartLineItem = $this->retrieveAccessoryFromLineItem($splitCartLineItem, $accessoryStruct);

            if (!$accessoryLineItem || !$accessorySplitCartLineItem) {
                throw new AccessoryNotFoundException($accessoryStruct->getId());
            }

            $accessoryLineItem->setQuantity($accessoryStruct->getQuantity());
            $accessorySplitCartLineItem->setQuantity($accessoryStruct->getQuantity());

            $accessoriesPayload = $parentLineItem->getPayloadValue(self::PAYLOAD_KEY_ACCESSORIES);
            if (isset($accessoriesPayload[$accessoryStruct->getId()])) {
                $accessoriesPayload[$accessoryStruct->getId()]['quantity'] = $accessoryStruct->getQuantity();

                $parentLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);
                $splitCartLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);
            }
        }

        $mainCart = $this->recalculateCarts($mainCart, $splitCart, $salesChannelContext);

        return $this->addExtensionsToCart($mainCart);
    }

    public function deleteAccessoriesFromLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart {
        $parentLineItem = $this->retrieveLineItemFromCart($lineItemId, $mainCart);

        $splitCart = $this->cartService->getSplitCart($parentLineItem, $salesChannelContext);
        $splitCartLineItem = $this->retrieveLineItemFromCart($lineItemId, $splitCart);

        foreach ($accessoryStructCollection as $accessoryStruct) {
            $accessoryLineItem = $this->retrieveAccessoryFromLineItem($parentLineItem, $accessoryStruct);
            $accessorySplitCartLineItem = $this->retrieveAccessoryFromLineItem($splitCartLineItem, $accessoryStruct);

            if (!$accessoryLineItem || !$accessorySplitCartLineItem) {
                throw new AccessoryNotFoundException($accessoryStruct->getId());
            }

            $parentLineItem->getChildren()->remove($accessoryLineItem->getId());
            $splitCartLineItem->getChildren()->remove($accessoryLineItem->getId());

            $accessoriesPayload = $parentLineItem->getPayloadValue(self::PAYLOAD_KEY_ACCESSORIES);
            if (isset($accessoriesPayload[$accessoryStruct->getId()])) {
                unset($accessoriesPayload[$accessoryStruct->getId()]);

                $parentLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);
                $splitCartLineItem->setPayloadValue(self::PAYLOAD_KEY_ACCESSORIES, $accessoriesPayload);
            }
        }

        $mainCart = $this->recalculateCarts($mainCart, $splitCart, $salesChannelContext);

        return $this->addExtensionsToCart($mainCart);
    }

    private function addExtensionsToCart(Cart $cart): Cart
    {
        $cart->addExtension(
            CartCollection::EXTENSION_PROPERTY_NAME,
            $this->cartReader->findSplitCarts($cart->getToken())
        );

        return $cart;
    }

    private function retrieveAccessoryFromLineItem(LineItem $lineItem, AccessoryStruct $accessoryStruct): ?LineItem
    {
        return $lineItem->getChildren()
            ->filter(fn(LineItem $childLineItem) => $childLineItem->getReferencedId() === $accessoryStruct->getId())
            ->first();
    }

    private function recalculateCarts(Cart $mainCart, Cart $splitCart, SalesChannelContext $salesChannelContext): Cart
    {
        $mainCart = $this->cartCalculator->calculate($mainCart, $salesChannelContext);
        $this->cartPersister->save($mainCart, $salesChannelContext);

        $splitCart = $this->cartCalculator->calculate($splitCart, $salesChannelContext);
        $this->cartPersister->save($splitCart, $salesChannelContext);

        return $mainCart;
    }

    private function retrieveLineItemFromCart(string $lineItemId, Cart $cart): LineItem
    {
        $lineItem = $cart->getLineItems()->get($lineItemId);
        if (!$lineItem) {
            throw CartException::lineItemNotFound($lineItemId);
        }

        return $lineItem;
    }
}
