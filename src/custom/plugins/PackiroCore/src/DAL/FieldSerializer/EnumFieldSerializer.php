<?php

declare(strict_types=1);

namespace Packiro\Core\DAL\FieldSerializer;

use http\Exception\UnexpectedValueException;
use Packiro\Core\DAL\Field\EnumField;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidSerializerFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Util\HtmlSanitizer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnumFieldSerializer extends AbstractFieldSerializer
{
    private HtmlSanitizer $sanitizer;

    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry,
        HtmlSanitizer $sanitizer
    ) {
        parent::__construct($validator, $definitionRegistry);

        $this->sanitizer = $sanitizer;
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof EnumField) {
            throw new InvalidSerializerFieldException(EnumField::class, $field);
        }

        if (!in_array(trim($data->getValue()), $field->getAvailableValues())) {
            throw new UnexpectedValueException('Value is not available');
        }

        $data->setValue($this->sanitize($this->sanitizer, $data, $field, $existence));

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        yield $field->getStorageName() => $data->getValue() !== null ? (string) $data->getValue() : null;
    }

    public function decode(Field $field, $value): ?string
    {
        if ($value === null) {
            return $value;
        }

        return (string) $value;
    }

    /**
     * @param EnumField $field
     *
     * @return Constraint[]
     */
    protected function getConstraints(Field $field): array
    {
        $constraints = [
            new Type('string'),
            new Choice($field->getAvailableValues()),
            new NotBlank(),
        ];

        return $constraints;
    }
}
