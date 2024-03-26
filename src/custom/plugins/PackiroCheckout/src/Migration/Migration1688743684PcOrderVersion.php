<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1688743684PcOrderVersion extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1688743684;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE pc_order DROP FOREIGN KEY `fk.pc_order.order_id`;
            ALTER TABLE pc_order
                ADD `order_version_id` BINARY(16) DEFAULT NULL;
            
            SET foreign_key_checks = 0;
            ALTER TABLE pc_order
                ADD CONSTRAINT `fk.pc_order.order_id.order_version_id`
                FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`)
                    ON DELETE CASCADE ON UPDATE CASCADE;                
            SET foreign_key_checks = 1;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
