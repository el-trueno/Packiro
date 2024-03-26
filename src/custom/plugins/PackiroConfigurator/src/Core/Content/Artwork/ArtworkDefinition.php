<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

class ArtworkDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pc_artwork';
    /* There is no service to get the initial state in admin */
    public const INITIAL_STATE_ID = '53640413f2a2d0984daac8d1c6e2a543';

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
        return [
            'payload' => [],
            'stateId' => self::INITIAL_STATE_ID,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new StateMachineStateField('state_id', 'stateId', ArtworkStates::STATE_MACHINE))->addFlags(new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(),
            (new StringField('artwork_number', 'artworkNumber')),
            (new StringField('name', 'name')),
            (new JsonField('payload', 'payload'))->addFlags(),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class)),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class)),
            (new ManyToOneAssociationField('stateMachineState', 'state_id', StateMachineStateDefinition::class, 'id', true))->addFlags(new ApiAware()),
        ]);
    }
}
