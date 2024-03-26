<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\System\Tag\TagCollection;

trait EntityAccessoryTrait
{
    protected bool $active = false;
    protected bool $disabled = false;
    protected int $priority = 0;
    protected ?string $internalNote = null;
    protected ?string $technicalName = null;
    protected ?string $type = null;
    protected ?string $mediaId = null;
    protected ?MediaEntity $media = null;
    protected ?TagCollection $tags = null;

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @return TagCollection|null
     */
    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    /**
     * @param TagCollection|null $tags
     */
    public function setTags(?TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return string|null
     */
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    /**
     * @param string|null $mediaId
     */
    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    /**
     * @param MediaEntity|null $media
     */
    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return string|null
     */
    public function getInternalNote(): ?string
    {
        return $this->internalNote;
    }

    /**
     * @param string|null $internalNote
     */
    public function setInternalNote(?string $internalNote): void
    {
        $this->internalNote = $internalNote;
    }

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
}
