<?php

declare(strict_types=1);

namespace Packiro\Checkout\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1694509245AddTransitionStateFromPreprocessingToRejected extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1694509245;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            SET @state_machine_idd = (SELECT id FROM state_machine sm WHERE technical_name = 'order.state');
            SET @state_preprocessing = (SELECT id FROM state_machine_state sms WHERE state_machine_id = @state_machine_idd 
                AND technical_name = 'preprocessing');
            SET @state_rejected = (SELECT id FROM state_machine_state sms WHERE state_machine_id = @state_machine_idd 
                AND technical_name = 'rejected');
            INSERT INTO state_machine_transition (id, action_name, state_machine_id, from_state_id, to_state_id, created_at)  
            SELECT unhex(REPLACE (UUID(),'-','')) , 'preprocessing_rejected', @state_machine_idd, @state_preprocessing, @state_rejected, NOW()
            FROM DUAL WHERE @state_preprocessing IS NOT NULL AND @state_rejected IS NOT NULL;    
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
