<?php

declare(strict_types=1);

namespace Packiro\Checkout\Writer\Order;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderPersister implements OrderPersisterInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function addCreatedByIdIntoOrder(OrderEntity $order, string $userId, Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function () use ($order, $userId): void {
            $this->connection->executeUpdate(
                'UPDATE `order` SET `created_by_id` = :createdById WHERE `id` = :id',
                ['createdById' => Uuid::fromHexToBytes($userId), 'id' => Uuid::fromHexToBytes($order->getId())]
            );
        });
    }
}
