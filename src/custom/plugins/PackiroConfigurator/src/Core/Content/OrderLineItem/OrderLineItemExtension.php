<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\OrderLineItem;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderLineItemExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderLineItemDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
                ProLibItemOrderDefinition::class,
                'order_line_item_id'
            ))->addFlags(new ApiAware())
        );
    }
}
