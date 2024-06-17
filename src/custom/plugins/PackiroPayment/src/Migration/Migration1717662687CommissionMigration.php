<?php

declare(strict_types=1);

namespace Packiro\Payment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1717662687CommissionMigration extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1717662687;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'CREATE TABLE IF NOT EXISTS pc_payment_method(
                `id` BINARY(16) NOT NULL,
                 `payment_method_id` binary(16) DEFAULT NULL, 
                 `commission` INT DEFAULT NULL, PRIMARY KEY (`id`),
                 `created_at` DATETIME(3) NOT NULL,
                 `updated_at` DATETIME(3) NULL,
                 KEY `fk.payment_method_extension.payment_method_id` (`payment_method_id`),
                CONSTRAINT `fk.payment_method_extension.payment_method_id` 
                FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE)
                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
