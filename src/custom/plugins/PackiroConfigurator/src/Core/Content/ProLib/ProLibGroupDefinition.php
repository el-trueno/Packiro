<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProLibGroupDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_pro_lib_group';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProLibGroupCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProLibGroupEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new FkField('last_order_item_id', 'lastOrderItemId', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new FkField('last_version_item_id', 'lastVersionItemId', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new FkField('last_item_order_id', 'lastItemOrderId', ProLibItemOrderDefinition::class))->addFlags(new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new ApiAware()),
            (new StringField('customer_reference', 'customerReference'))->addFlags(new ApiAware()),
            (new IntField('version_count', 'versionCount'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('lastOrderItem', 'last_order_item_id', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('lastVersionItem', 'last_version_item_id', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('lastItemOrder', 'last_item_order_id', ProLibItemOrderDefinition::class))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('proLibItems', ProLibItemDefinition::class, 'pc_pro_lib_group_id')),
        ]);
    }
}
