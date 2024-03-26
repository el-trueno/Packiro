<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1688639298StateMachineHistoryIndexes extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1688639298;
    }

    public function update(Connection $connection): void
    {
        //used in export service
        $connection->executeStatement(
            <<<Query
                ALTER TABLE `state_machine_history`
                  ADD INDEX `idx.state_machine_history.entity_name_action_name` (`entity_name`, `action_name`),
                  ADD INDEX `idx.state_machine_history.created_at` (`created_at`)
            Query
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
