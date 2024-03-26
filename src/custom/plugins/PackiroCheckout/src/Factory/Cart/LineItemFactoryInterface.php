<?php

declare(strict_types=1);

namespace Packiro\Checkout\Factory\Cart;

use Packiro\Checkout\Struct\Accessory\AccessoryStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface LineItemFactoryInterface
{
    /**
     * @return array<LineItem>
     */
    public function createDiscountLineItems(
        string $originalCode,
        PromotionEntity $promotionEntity,
        Cart $cart,
        SalesChannelContext $salesChannelContext,
    ): array;

    public function createAccessoryLineItem(AccessoryStruct $accessoryStruct): LineItem;
}
