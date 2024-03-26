<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ProLibItemOrderServiceInterface
{
    public function patchProLibItemOrders(Cart $cart, OrderEntity $order, Context $context): void;

    public function createProLibItemSlots(LineItem $parentLineItem, string $cartToken, Context $context): void;

    public function enrichCart(Cart $cart, LineItemCollection $lineItems, SalesChannelContext $salesChannelContext): void;
}
