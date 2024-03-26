<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1679389915OrderParent extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1679389915;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `pc_order` (
                `id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NOT NULL,
                `checkout_id` VARCHAR(50) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.pc_order.order_id` 
                    FOREIGN KEY (`order_id`)
                    REFERENCES `order` (`id`) 
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
