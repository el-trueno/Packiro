<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Country;

use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\CountryDefinition;

class CountryExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CountryDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'taxFreeRules',
                RuleDefinition::class,
                CountryTaxFreeRuleDefinition::class,
                'country_id',
                'rule_id'
            ))->addFlags(new Inherited())
        );
    }
}
