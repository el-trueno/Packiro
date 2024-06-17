<?php

declare(strict_types=1);

namespace Packiro\Payment\DAL\Extension\PaymentMethod;

use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PaymentMethodExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return PaymentMethodDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                PaymentMethodExtensionDefinition::EXTENSION_PROPERTY_NAME,
                'id',
                'payment_method_id',
                PaymentMethodExtensionDefinition::class,
                true,
            ))->addFlags(new ApiAware()),
        );
    }
}
