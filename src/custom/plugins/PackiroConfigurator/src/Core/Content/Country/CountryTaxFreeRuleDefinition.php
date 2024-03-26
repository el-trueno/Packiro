<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Country;

use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\Country\CountryDefinition;

class CountryTaxFreeRuleDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'pc_country_rule_tax_free';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('country_id', 'countryId', CountryDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('rule_id', 'ruleId', RuleDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('country', 'country_id', CountryDefinition::class),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class),
            new CreatedAtField(),
        ]);
    }
}
