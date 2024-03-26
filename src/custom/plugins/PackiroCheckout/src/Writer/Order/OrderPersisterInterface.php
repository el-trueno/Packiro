<?php

declare(strict_types=1);

namespace Packiro\Checkout\Writer\Order;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface OrderPersisterInterface
{
    public function addCreatedByIdIntoOrder(OrderEntity $order, string $userId, Context $context): void;
}
