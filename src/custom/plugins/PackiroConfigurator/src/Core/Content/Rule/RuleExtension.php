<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Rule;

use Kuniva\PackiroConfigurator\Core\Content\Country\CountryTaxFreeRuleDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\CountryDefinition;

class RuleExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'taxFreeCountries',
                CountryDefinition::class,
                CountryTaxFreeRuleDefinition::class,
                'rule_id',
                'country_id'
            ))->addFlags(new Inherited())
        );
    }
}
