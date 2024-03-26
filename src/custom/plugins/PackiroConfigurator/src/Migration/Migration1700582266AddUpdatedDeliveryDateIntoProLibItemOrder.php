<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700582266AddUpdatedDeliveryDateIntoProLibItemOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700582266;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `pc_pro_lib_item_order` ADD `updated_delivery_date` DATETIME AFTER `updated_at`;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
