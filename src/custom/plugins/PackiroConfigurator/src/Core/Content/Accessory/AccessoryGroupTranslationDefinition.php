<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\Collection\FieldCollectionAccessory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AccessoryGroupTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'pc_accessory_group_translation';

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

    protected function getParentDefinitionClass(): string
    {
        return AccessoryGroupDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(FieldCollectionAccessory::getTranslatedFieldItems());
    }
}
