<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProLibItemOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_pro_lib_item_order';
    public const COLLECTION_PROPERTY_NAME = 'proLibItemOrders';
    public const PROPERTY_NAME = 'proLibItemOrder';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProLibItemOrderCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProLibItemOrderEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new ApiAware(), new Required()),
            (new StringField('cart_token', 'cartToken'))->addFlags(new ApiAware()),
            (new IdField('cart_line_item_id', 'cartLineItemId'))->addFlags(new ApiAware()),
            (new DateTimeField('updated_delivery_date', 'updatedDeliveryDate'))->addFlags(new ApiAware()),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('order_line_item_id', 'orderLineItemId', OrderLineItemDefinition::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(OrderLineItemDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new FkField('pc_pro_lib_item_id', 'proLibItemId', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new IntField('quantity', 'quantity'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('proLibItem', 'pc_pro_lib_item_id', ProLibItemDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('orderLineItem', 'order_line_item_id', OrderLineItemDefinition::class))->addFlags(new ApiAware()),
        ]);
    }
}
