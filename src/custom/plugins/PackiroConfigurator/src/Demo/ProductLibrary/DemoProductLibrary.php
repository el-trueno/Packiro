<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Demo\ProductLibrary;

use Kuniva\PackiroConfigurator\PackiroConfigurator;
use MoorlFoundation\Core\System\DataExtension;
use MoorlFoundation\Core\System\DataInterface;

class DemoProductLibrary extends DataExtension implements DataInterface
{
    public function customerRequired(): bool
    {
        return true;
    }

    public function getTables(): ?array
    {
        return array_merge(
            PackiroConfigurator::PLUGIN_TABLES,
            PackiroConfigurator::SHOPWARE_TABLES
        );
    }

    public function getShopwareTables(): ?array
    {
        return PackiroConfigurator::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return PackiroConfigurator::PLUGIN_TABLES;
    }

    public function getName(): string
    {
        return 'product-library';
    }

    public function getType(): string
    {
        return 'demo';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function getPluginName(): string
    {
        return PackiroConfigurator::NAME;
    }

    public function getCreatedAt(): string
    {
        return PackiroConfigurator::DATA_CREATED_AT;
    }
}
