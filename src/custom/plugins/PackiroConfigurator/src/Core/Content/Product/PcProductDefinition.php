<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PcProductDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_product';
    public const EXTENSION_NAME = 'pcProduct';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PcProductEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),

            (new StringField('type', 'type')),
            (new StringField('technical_name', 'technicalName')),
            new JsonField('pc_accessory_not_available', 'notAvailable'),
            new JsonField('pc_accessory_not_combinable', 'notCombinable'),

            new OneToOneAssociationField('product', 'product_id', 'id', ProductDefinition::class, false),
        ]);
    }
}
