<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Order;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface OrderServiceInterface
{
    public function executePostSplitOrderCreationProcess(
        string $cartToken,
        OrderEntity $splitOrder,
        string $checkoutId,
        SalesChannelContext $salesChannelContext,
    ): void;
}
