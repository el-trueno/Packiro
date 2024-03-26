<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1685543941RemoveReopenOrderStatusFromStateMachine extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1685543941;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            DELETE smt.* FROM state_machine_transition smt 
            join state_machine sm ON sm.id = smt.state_machine_id JOIN 
            state_machine_state sms1 ON sms1.id = smt.from_state_id JOIN 
            state_machine_state sms2 ON sms2.id = smt.to_state_id 
            WHERE sm.technical_name = 'order.state' AND sms1.technical_name = 'cancelled' 
            AND sms2.technical_name = 'open';
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
