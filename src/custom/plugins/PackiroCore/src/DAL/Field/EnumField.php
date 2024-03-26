<?php

declare(strict_types=1);

namespace Packiro\Core\DAL\Field;

use Packiro\Core\DAL\FieldSerializer\EnumFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;

class EnumField extends Field implements StorageAware
{
    private string $storageName;

    private array $availableValues;

    public function __construct(string $storageName, string $propertyName, array $availableValues = [])
    {
        parent::__construct($propertyName);

        $this->storageName = $storageName;
        $this->availableValues = $availableValues;
    }

    protected function getSerializerClass(): string
    {
        return EnumFieldSerializer::class;
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function getAvailableValues(): array
    {
        return $this->availableValues;
    }
}
