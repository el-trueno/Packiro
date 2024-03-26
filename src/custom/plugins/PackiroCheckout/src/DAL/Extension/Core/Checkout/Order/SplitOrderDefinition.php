<?php

declare(strict_types=1);

namespace Packiro\Checkout\DAL\Extension\Core\Checkout\Order;

use Packiro\Core\DAL\Field\EnumField;
use Shopware\Core\Checkout\Order\OrderDefinition ;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class SplitOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_order';
    public const EXTENSION_PROPERTY_NAME = 'splitOrder';
    public const NORMAL_ORDER_TYPE = 'NORMAL';
    public const SERVICE_ORDER_TYPE = 'SERVICES';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SplitOrderEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware()),
            (new StringField('checkout_id', 'checkoutId', 50))->addFlags(new ApiAware()),
            (new EnumField(
                'order_type',
                'orderType',
                [self::NORMAL_ORDER_TYPE, self::SERVICE_ORDER_TYPE]
            ))->addFlags(new ApiAware()),

            new OneToOneAssociationField('order', 'order_id', 'id', OrderDefinition::class, false),
            new ReferenceVersionField(OrderDefinition::class, 'order_version_id'),
        ]);
    }
}
