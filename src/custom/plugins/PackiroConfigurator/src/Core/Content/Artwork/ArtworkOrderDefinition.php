<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ArtworkOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_artwork_order';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ArtworkCollection::class;
    }

    public function getEntityClass(): string
    {
        return ArtworkEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(),
            (new FkField('order_line_item_id', 'orderLineItemId', OrderLineItemDefinition::class))->addFlags(),
            (new FkField('pc_artwork_id', 'artworkId', ArtworkDefinition::class))->addFlags(),
            (new IntField('quantity', 'quantity'))->addFlags(),
            (new ManyToOneAssociationField('artwork', 'pc_artwork_id', ArtworkDefinition::class)),
            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class)),
            (new ManyToOneAssociationField('orderLineItem', 'order_line_item_id', OrderLineItemDefinition::class)),
        ]);
    }
}
