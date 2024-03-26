<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\EntityAccessoryTrait;
use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\EntityAccessoryTranslationTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AccessoryGroupEntity extends Entity
{
    use EntityIdTrait;
    use EntityAccessoryTrait;
    use EntityAccessoryTranslationTrait;

    protected bool $multipleSelection = false;
    protected bool $scalingStack = false;
    protected bool $activeProductLib = false;
    protected ?AccessoryOptionCollection $accessoryOptions = null;

    /**
     * @return bool
     */
    public function isActiveProductLib(): bool
    {
        return $this->activeProductLib;
    }

    /**
     * @param bool $activeProductLib
     */
    public function setActiveProductLib(bool $activeProductLib): void
    {
        $this->activeProductLib = $activeProductLib;
    }

    /**
     * @return AccessoryOptionCollection|null
     */
    public function getAccessoryOptions(): ?AccessoryOptionCollection
    {
        return $this->accessoryOptions;
    }

    /**
     * @param AccessoryOptionCollection|null $accessoryOptions
     */
    public function setAccessoryOptions(?AccessoryOptionCollection $accessoryOptions): void
    {
        $this->accessoryOptions = $accessoryOptions;
    }

    /**
     * @return bool
     */
    public function isMultipleSelection(): bool
    {
        return $this->multipleSelection;
    }

    /**
     * @param bool $multipleSelection
     */
    public function setMultipleSelection(bool $multipleSelection): void
    {
        $this->multipleSelection = $multipleSelection;
    }

    /**
     * @return bool
     */
    public function isScalingStack(): bool
    {
        return $this->scalingStack;
    }

    /**
     * @param bool $scalingStack
     */
    public function setScalingStack(bool $scalingStack): void
    {
        $this->scalingStack = $scalingStack;
    }
}
