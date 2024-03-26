<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1687269486AddMinMaxDeliveryDaysIntoAccessoryOption extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1687269486;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `pc_accessory_option` ADD `min_delivery_days` INT(11) AFTER `delivery_days`,
    ADD `max_delivery_days` INT(11) AFTER `min_delivery_days`;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
