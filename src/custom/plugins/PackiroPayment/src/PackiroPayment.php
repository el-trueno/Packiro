<?php

declare(strict_types=1);

namespace Packiro\Payment;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class PackiroPayment extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        $this->deleteTables();
    }

    private function deleteTables(): void
    {
        $connection = $this->container->get(Connection::class);

        $sql = <<<SQL
            DROP TABLE `pc_payment_method`;
        SQL;

        $connection->executeStatement($sql);
    }
}
