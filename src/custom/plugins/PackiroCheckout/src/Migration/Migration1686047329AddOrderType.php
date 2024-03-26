<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1686047329AddOrderType extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1686047329;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `pc_order`
            ADD `order_type` varchar(64) AFTER `checkout_id`,
            MODIFY `checkout_id` varchar(50) NULL;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
