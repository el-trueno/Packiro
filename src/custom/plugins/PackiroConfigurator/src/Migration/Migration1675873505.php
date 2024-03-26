<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1675873505 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1675873505;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `pc_pro_lib_item`
ADD `customer_id` BINARY(16) AFTER `product_version_id`,
ADD CONSTRAINT `fk.pc_pro_lib_item.customer_id` 
    FOREIGN KEY (`customer_id`)
    REFERENCES `customer` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
