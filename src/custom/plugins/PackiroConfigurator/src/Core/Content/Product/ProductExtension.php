<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Product;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionDefinition;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionProductDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Extension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                PcProductDefinition::EXTENSION_NAME,
                'id',
                'product_id',
                PcProductDefinition::class,
                true
            ))->addFlags(new Extension(), new CascadeDelete())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                AccessoryOptionDefinition::COLLECTION_PROPERTY_NAME,
                AccessoryOptionDefinition::class,
                AccessoryOptionProductDefinition::class,
                'product_id',
                'pc_accessory_option_id'
            ))->addFlags(new Inherited())
        );
    }
}
