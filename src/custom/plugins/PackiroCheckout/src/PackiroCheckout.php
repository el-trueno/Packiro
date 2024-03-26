<?php

declare(strict_types=1);

namespace Packiro\Checkout;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class PackiroCheckout extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->deleteTables();
    }

    private function deleteTables(): void
    {
        $connection = $this->container->get(Connection::class);

        $sql = 'DROP TABLE `pc_order`;
            DROP TABLE `pc_cart`;
            DELETE from state_machine_transition WHERE action_name LIKE \'%_cancel\';
            DELETE from state_machine_transition WHERE action_name = \'preprocessing_rejected\';';

        $connection->executeStatement($sql);
    }
}
