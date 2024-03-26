<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Order;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface OrderExtraFieldServiceInterface
{
    public function saveOrderType(OrderEntity $order, Context $context): void;
}
