<?php

declare(strict_types=1);

namespace Packiro\Checkout\DAL\Extension\Core\Checkout\Order;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                SplitOrderDefinition::EXTENSION_PROPERTY_NAME,
                'id',
                'order_id',
                SplitOrderDefinition::class,
                true,
            ))->addFlags(new ApiAware()),
        );
    }
}
