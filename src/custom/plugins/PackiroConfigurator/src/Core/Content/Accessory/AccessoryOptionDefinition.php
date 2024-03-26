<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemAccessoryOptionDefinition;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemDefinition;
use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\Collection\FieldCollectionAccessory;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tag\TagDefinition;
use Shopware\Core\System\Tax\TaxDefinition;

class AccessoryOptionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_accessory_option';
    public const COLLECTION_PROPERTY_NAME = 'accessoryOptions';
    public const PROPERTY_NAME = 'accessoryOption';

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

    public function getDefaults(): array
    {
        return [
            'price' => $this->getDefaultPrice(),
            'active' => false,
            'disabled' => false,
        ];
    }

    private function getDefaultPrice(): array
    {
        return [
            sprintf("c%s", Defaults::CURRENCY) => [
                'net' => 0,
                'gross' => 0,
                'linked' => true,
                'currencyId' => Defaults::CURRENCY,
            ],
        ];
    }

    protected function defineFields(): FieldCollection
    {
        $collection = [
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),
            (new FkField('pc_accessory_group_id', 'accessoryGroupId', AccessoryGroupDefinition::class))->addFlags(new Required()),

            (new FkField('tax_id', 'taxId', TaxDefinition::class))->addFlags(),

            (new PriceField('price', 'price'))->addFlags(new ApiAware()),

            (new IntField('min_quantity', 'minQuantity'))->addFlags(new ApiAware()), // Minimum quantity to unleash this option
            (new IntField('max_quantity', 'maxQuantity'))->addFlags(new ApiAware()), // Maximum quantity to unleash this option
            (new IntField('delivery_days', 'deliveryDays'))->addFlags(new ApiAware()), // Days to delivery
            (new IntField('min_delivery_days', 'minDeliveryDays'))->addFlags(new ApiAware()), // Minimal days to delivery
            (new IntField('max_delivery_days', 'maxDeliveryDays'))->addFlags(new ApiAware()), // Maxiimal days to delivery

            (new BoolField('pre_selected', 'preSelected'))->addFlags(new ApiAware()), // Select with the highest priority

            (new JsonField('pc_accessory_provided', 'provided'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(AccessoryOptionTranslationDefinition::class, 'pc_accessory_option_id'))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField('accessoryGroup', 'pc_accessory_group_id', AccessoryGroupDefinition::class)),
            (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class, 'id', true)),

            new ManyToManyAssociationField('tags', TagDefinition::class, AccessoryOptionTagDefinition::class, 'pc_accessory_option_id', 'tag_id'),

            (new ManyToManyAssociationField(
                'mainProducts',
                ProductDefinition::class,
                AccessoryOptionProductDefinition::class,
                'pc_accessory_option_id',
                'product_id'
            ))->addFlags(new ReverseInherited('accessoryOptions')),

            (new ManyToManyAssociationField(
                'proLibItems',
                ProLibItemDefinition::class,
                ProLibItemAccessoryOptionDefinition::class,
                'pc_accessory_option_id',
                'pc_pro_lib_item_id',
            )),
        ];

        $collection = array_merge(
            $collection,
            FieldCollectionAccessory::getFieldItems()
        );

        return new FieldCollection($collection);
    }
}
