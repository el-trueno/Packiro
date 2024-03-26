<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1702641061AddRegexpForVatIds extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1702641061;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            UPDATE `country` SET `vat_id_pattern` = '(IE)?\\\d\\\w[\\\d]{5}[a-zA-Z]{1,2}' WHERE iso3 = 'IRL';
            UPDATE `country` SET `vat_id_pattern` = '(BE)?(0|1)\\\d{9}' WHERE iso3 = 'BEL';
            UPDATE `country` SET `vat_id_pattern` = '(HR)?\\\d{11}' WHERE iso3 = 'HRV';
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
