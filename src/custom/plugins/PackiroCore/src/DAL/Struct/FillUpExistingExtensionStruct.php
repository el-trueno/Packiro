<?php

declare(strict_types=1);

namespace Packiro\Core\DAL\Struct;

use Shopware\Core\Framework\Struct\Struct;

class FillUpExistingExtensionStruct extends Struct
{
    private string $idName;

    private string $idValue;

    private array $updatedValues = [];

    public function getIdName(): string
    {
        return $this->idName;
    }

    public function setIdName(string $idName): self
    {
        $this->idName = $idName;

        return $this;
    }

    public function getIdValue(): string
    {
        return $this->idValue;
    }

    public function setIdValue(string $idValue): self
    {
        $this->idValue = $idValue;

        return $this;
    }

    /**
     * @return array
     */
    public function getUpdatedValues(): array
    {
        return $this->updatedValues;
    }

    /**
     * @param array $updatedValues
     */
    public function setUpdatedValues(array $updatedValues): self
    {
        $this->updatedValues = $updatedValues;

        return $this;
    }

    public function addUpdatedValue(array $newValue): self
    {
        if (!array_key_exists(array_key_first($newValue), $this->updatedValues)) {
            $this->updatedValues = array_merge($this->updatedValues, $newValue);
        }

        return $this;
    }

    public function merge(array $oldValueArray): array
    {
        return array_merge($oldValueArray, $this->updatedValues);
    }

    public function hasKey(string $key): bool
    {
        if (array_key_exists($key, $this->getUpdatedValues())) {
            return true;
        }

        return false;
    }
}
