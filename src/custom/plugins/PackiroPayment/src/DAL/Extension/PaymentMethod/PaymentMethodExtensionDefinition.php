<?php

declare(strict_types=1);

namespace Packiro\Payment\DAL\Extension\PaymentMethod;

use Shopware\Core\Checkout\Payment\PaymentMethodDefinition as ParentPaymentDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PaymentMethodExtensionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_payment_method';
    public const EXTENSION_PROPERTY_NAME = 'paymentMethodExtension';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PaymentMethodEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new FkField('payment_method_id', 'paymentMethodId', ParentPaymentDefinition::class),
            (new OneToOneAssociationField(
                'paymentMethod',
                'payment_method_id',
                'id',
                ParentPaymentDefinition::class,
                false
            )),
            (new IntField('commission', 'commission', 0))->addFlags(new ApiAware()),
        ]);
    }
}
