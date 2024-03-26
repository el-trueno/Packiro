<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator;

use Doctrine\DBAL\Connection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionDefinition;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class PackiroConfigurator extends Plugin
{
    use InheritanceUpdaterTrait;

    public const NAME = 'PackiroConfigurator';
    public const DATA_CREATED_AT = '2000-10-18 00:00:00.00';
    public const SHOPWARE_TABLES = [
        'category',
        'cms_page',
        'product',
        'property_group',
        'number_range_type',
        'state_machine',
    ];
    public const PLUGIN_TABLES = [
        'pc_pro_lib_item_order',
        'pc_pro_lib_group',
        'pc_pro_lib_item',
        'pc_pro_lib_item_accessory_option',
        'pc_accessory_group',
        'pc_accessory_group_translation',
        'pc_accessory_group_tag',
        'pc_accessory_option',
        'pc_accessory_option_translation',
        'pc_accessory_option_product',
        'pc_accessory_option_tag',
        'pc_product',
        'pc_country_rule_tax_free',
    ];
    public const INHERITANCES = [
        'product' => [
            AccessoryOptionDefinition::COLLECTION_PROPERTY_NAME,
        ],
        'order_line_item' => [
            ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
        ],
        'order' => [
            ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
        ],
        'customer' => ['artworks'],
        'country' => ['taxFreeRules'],
    ];
    public const VAT_VALIDATOR_URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $this->updateInheritances();
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->updateInheritances();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->updateTables();
    }

    private function updateInheritances(): void
    {
        $connection = $this->container->get(Connection::class);

        foreach (self::INHERITANCES as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                try {
                    $this->updateInheritance($connection, $table, $propertyName);
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
    }

    private function updateTables(): void
    {
        $connection = $this->container->get(Connection::class);

        foreach (self::PLUGIN_TABLES as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }
        $sql1 = 'ALTER TABLE `state_machine_history` DROP INDEX `idx.state_machine_history.entity_name_action_name`;';
        $sql2 = 'ALTER TABLE `state_machine_history` DROP INDEX `idx.state_machine_history.created_at`;';
        $connection->executeStatement($sql1 . $sql2);
    }
}
