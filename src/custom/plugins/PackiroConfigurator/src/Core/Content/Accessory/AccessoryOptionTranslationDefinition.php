<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\Collection\FieldCollectionAccessory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class AccessoryOptionTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'pc_accessory_option_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AccessoryOptionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AccessoryOptionCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return AccessoryOptionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(FieldCollectionAccessory::getTranslatedFieldItems());
    }
}
