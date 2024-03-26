<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1693987364AddCancellationToEachOrderStatus extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1693987364;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            SET @state_machine_idd = (select id from state_machine sm where technical_name = 'order.state');
            SET @state_cancelled = (SELECT id from state_machine_state sms where state_machine_id = @state_machine_idd 
                AND technical_name = 'cancelled');
            INSERT INTO state_machine_transition (id, action_name, state_machine_id, from_state_id, to_state_id, created_at)
            SELECT unhex(REPLACE (UUID(),'-','')) as id, CONCAT(technical_name, '_cancel') as action_name, @state_machine_idd as state_machine_id, id as from_state_id, @state_cancelled as to_state_id, NOW() as created_at FROM state_machine_state sm
            WHERE state_machine_id = @state_machine_idd AND id <> @state_cancelled;      
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
