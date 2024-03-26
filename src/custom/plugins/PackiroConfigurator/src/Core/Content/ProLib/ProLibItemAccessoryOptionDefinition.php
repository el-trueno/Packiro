<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class ProLibItemAccessoryOptionDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'pc_pro_lib_item_accessory_option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('pc_accessory_option_id', 'accessoryOptionId', AccessoryOptionDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('pc_pro_lib_item_id', 'proLibItemId', ProLibItemDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('accessoryOption', 'pc_accessory_option_id', AccessoryOptionDefinition::class, 'id', false),
            new ManyToOneAssociationField('proLibItem', 'pc_pro_lib_item_id', ProLibItemDefinition::class, 'id', false),
        ]);
    }
}
