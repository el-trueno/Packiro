<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\Collection\FieldCollectionAccessory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tag\TagDefinition;

class AccessoryGroupDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_accessory_group';
    public const ENUM_TYPE = [
        'product', // Product exists
        'pseudo-product', // Product non-exists
        'texture',
        'artwork', // Options are generated by quantity
    ];

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AccessoryGroupEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AccessoryGroupCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'multipleSelection' => false,
            'scalingStack' => false,
            'active' => false,
            'disabled' => false,
            'type' => self::ENUM_TYPE[0],
            'priority' => 0,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        $collection = [
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),

            (new BoolField('multiple_selection', 'multipleSelection'))->addFlags(new ApiAware()),
            (new BoolField('scaling_stack', 'scalingStack'))->addFlags(new ApiAware()),
            (new BoolField('active_pro_lib', 'activeProductLib'))->addFlags(new ApiAware()),

            new ManyToManyAssociationField('tags', TagDefinition::class, AccessoryGroupTagDefinition::class, 'pc_accessory_group_id', 'tag_id'),

            (new TranslationsAssociationField(AccessoryGroupTranslationDefinition::class, 'pc_accessory_group_id')),

            (new OneToManyAssociationField('accessoryOptions', AccessoryOptionDefinition::class, 'pc_accessory_group_id'))->addFlags(new ApiAware()),
        ];

        $collection = array_merge(
            $collection,
            FieldCollectionAccessory::getFieldItems()
        );

        return new FieldCollection($collection);
    }
}
