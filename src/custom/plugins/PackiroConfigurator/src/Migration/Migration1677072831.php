<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1677072831 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1677072831;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `pc_pro_lib_item_order`
ADD `cart_token` VARCHAR(255) NULL AFTER `id`,
ADD `cart_line_item_id` binary(16) NULL AFTER `cart_token`,
CHANGE `order_id` `order_id` binary(16) NULL AFTER `pc_pro_lib_item_id`,
CHANGE `order_version_id` `order_version_id` binary(16) NULL AFTER `order_id`,
CHANGE `order_line_item_id` `order_line_item_id` binary(16) NULL AFTER `order_version_id`,
CHANGE `order_line_item_version_id` `order_line_item_version_id` binary(16) NULL AFTER `order_line_item_id`,
CHANGE `quantity` `quantity` int(11) NOT NULL AFTER `product_version_id`;
SQL;
        try {
            $connection->executeStatement($sql);
        } catch (\Exception) {
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
