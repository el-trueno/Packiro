<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1675694995 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1675694995;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `pc_pro_lib_group`
ADD `last_order_item_id` binary(16) AFTER `customer_id`,
ADD `last_version_item_id` binary(16) AFTER `last_order_item_id`,
ADD `last_item_order_id` binary(16) AFTER `last_version_item_id`,
ADD CONSTRAINT `fk.pc_pro_lib_group.last_order_item_id` 
    FOREIGN KEY (`last_order_item_id`)
    REFERENCES `pc_pro_lib_item` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk.pc_pro_lib_group.last_version_item_id` 
    FOREIGN KEY (`last_version_item_id`)
    REFERENCES `pc_pro_lib_item` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk.pc_pro_lib_group.last_item_order_id` 
    FOREIGN KEY (`last_item_order_id`)
    REFERENCES `pc_pro_lib_item_order` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
