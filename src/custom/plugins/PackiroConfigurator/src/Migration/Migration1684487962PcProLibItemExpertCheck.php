<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1684487962PcProLibItemExpertCheck extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1684487962;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `pc_pro_lib_item`
            ADD `artwork_status` varchar(64) AFTER `payload`,
            ADD `expert_check_approved` DATETIME(3) AFTER `artwork_status`;
        SQL;
        try {
            $connection->executeStatement($sql);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
