<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Cart;

use Packiro\Checkout\Exception\UnsupportedLineItemException;
use Packiro\Checkout\Exception\UsedCartException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CartServiceInterface
{
    /**
     * @throws CartTokenNotFoundException
     * @throws UsedCartException
     */
    public function switchCartInContext(?string $cartToSwitchToken, SalesChannelContext $salesChannelContext): ?string;

    /**
     * @throws UnsupportedLineItemException
     */
    public function getSplitCart(LineItem $lineItem, SalesChannelContext $salesChannelContext): Cart;

    /**
     * @return array<LineItem>
     */
    public function getSplitDiscountLineItems(
        CartDataCollection $cartDataCollection,
        Cart $originalCart,
        SalesChannelContext $salesChannelContext
    ): array;

    public function recalculateSplitCarts(string $parentToken, SalesChannelContext $salesChannelContext): void;

    public function removeDuplicatedDeliveryCosts(Cart $cart, SalesChannelContext $salesChannelContext): Cart;

    public function prepareShippingCostsWithSplitCart(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context
    ): void;
}
