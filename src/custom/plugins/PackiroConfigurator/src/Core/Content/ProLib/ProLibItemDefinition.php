<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionDefinition;
use Packiro\Core\DAL\Field\EnumField;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LockedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProLibItemDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_pro_lib_item';

    public const ARTWORK_STATUSES = [
        'expert_approved',
        'expert_check_requested',
        'rejected',
        'basic_approved',
        'basic_requested',
    ];

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProLibItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProLibItemEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'version' => null,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('pc_pro_lib_group_id', 'proLibGroupId', ProLibGroupDefinition::class))->addFlags(new ApiAware()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new StringField('artwork_id', 'artworkId'))->addFlags(new ApiAware()),
            (new StringField('artwork_state', 'artworkState'))->addFlags(new ApiAware()),
            (new JsonField('artwork_access', 'artworkAccess'))->addFlags(new ApiAware()),
            (new BoolField('packshot_created', 'packshotCreated'))->addFlags(new ApiAware()),
            (new BoolField('packshot_purchased', 'packshotPurchased'))->addFlags(new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new ApiAware()),
            (new StringField('note', 'note'))->addFlags(new ApiAware()),
            (new IntField('version', 'version'))->addFlags(new ApiAware()),
            (new DateTimeField('last_order_at', 'lastOrderAt'))->addFlags(new ApiAware()),
            (new JsonField('payload', 'payload'))->addFlags(new ApiAware()),
            (new EnumField(
                'artwork_status',
                'artworkStatus',
                self::ARTWORK_STATUSES
            ))->addFlags(new ApiAware()),
            (new DateTimeField('expert_check_approved', 'expertCheckApproved'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('proLibGroup', 'pc_pro_lib_group_id', ProLibGroupDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField(
                AccessoryOptionDefinition::COLLECTION_PROPERTY_NAME,
                AccessoryOptionDefinition::class,
                ProLibItemAccessoryOptionDefinition::class,
                'pc_pro_lib_item_id',
                'pc_accessory_option_id'
            ))->addFlags(new ApiAware()),
            (new OneToManyAssociationField(
                ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
                ProLibItemOrderDefinition::class,
                'pc_pro_lib_item_id'
            ))->addFlags(new ApiAware()),
            (new LockedField()),
        ]);
    }
}
