<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1679501004CartParent extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1679501004;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `pc_cart` (
                `cart_token` VARCHAR(50) NOT NULL,
                `parent_token` VARCHAR(50) NULL,
                `product_variant_id` BINARY(16) NULL,
                `accessory_option_id` BINARY(16) NULL,
                PRIMARY KEY (`cart_token`),
                UNIQUE `unique.pc_cart` (`parent_token`, `product_variant_id`, `accessory_option_id`),
                CONSTRAINT `fk.pc_cart.product_variant_id` 
                    FOREIGN KEY (`product_variant_id`)
                    REFERENCES `product` (`id`) 
                    ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.pc_cart.accessory_option_id` 
                    FOREIGN KEY (`accessory_option_id`)
                    REFERENCES `pc_accessory_option` (`id`) 
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
