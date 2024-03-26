<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer;

trait EntityAccessoryTranslationTrait
{
    protected string $name = "";
    protected ?string $shortDescription = null;
    protected ?string $helpText = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    /**
     * @param string|null $helpText
     */
    public function setHelpText(?string $helpText): void
    {
        $this->helpText = $helpText;
    }
}
