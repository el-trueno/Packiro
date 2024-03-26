<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Product;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class PcProductEntity extends Entity
{
    protected ?string $type = null;
    protected ?string $technicalName = null;
    protected ?array $notAvailable = null;
    protected ?array $notCombinable = null;

    /**
     * @return string|null
     */
    public function getTechnicalName(): ?string
    {
        return $this->technicalName;
    }

    /**
     * @param string|null $technicalName
     */
    public function setTechnicalName(?string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array|null
     */
    public function getNotAvailable(): ?array
    {
        return $this->notAvailable;
    }

    /**
     * @param array|null $notAvailable
     */
    public function setNotAvailable(?array $notAvailable): void
    {
        $this->notAvailable = $notAvailable;
    }

    /**
     * @return array|null
     */
    public function getNotCombinable(): ?array
    {
        return $this->notCombinable;
    }

    /**
     * @param array|null $notCombinable
     */
    public function setNotCombinable(?array $notCombinable): void
    {
        $this->notCombinable = $notCombinable;
    }
}
