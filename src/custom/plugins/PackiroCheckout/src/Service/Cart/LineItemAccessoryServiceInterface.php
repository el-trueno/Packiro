<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Cart;

use Packiro\Checkout\Struct\Accessory\AccessoryStructCollection;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface LineItemAccessoryServiceInterface
{
    public function addAccessoriesToLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart;

    public function updateAccessoriesInLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart;

    public function deleteAccessoriesFromLineItem(
        string $lineItemId,
        Cart $mainCart,
        AccessoryStructCollection $accessoryStructCollection,
        SalesChannelContext $salesChannelContext
    ): Cart;
}
