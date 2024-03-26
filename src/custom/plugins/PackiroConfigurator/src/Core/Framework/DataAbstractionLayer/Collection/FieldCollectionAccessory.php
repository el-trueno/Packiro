<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldCollectionAccessory extends FieldCollection
{
    public static function getFieldItems(): array
    {
        return [
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new BoolField('disabled', 'disabled'))->addFlags(new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new ApiAware()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware()),
            (new LongTextField('internal_note', 'internalNote'))->addFlags(new ApiAware()),
            (new TranslatedField('name'))->addFlags(new Required(), new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('shortDescription'))->addFlags(new ApiAware()),
            (new TranslatedField('helpText'))->addFlags(new ApiAware()),
            new FkField('media_id', 'mediaId', MediaDefinition::class),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class))->addFlags(new ApiAware()),
        ];
    }

    public static function getTranslatedFieldItems(): array
    {
        return [
            (new StringField('name', 'name'))->addFlags(new Required())->addFlags(new ApiAware()),
            (new LongTextField('short_description', 'shortDescription'))->addFlags(new AllowHtml(), new ApiAware()),
            (new LongTextField('help_text', 'helpText'))->addFlags(new ApiAware()),
        ];
    }
}
