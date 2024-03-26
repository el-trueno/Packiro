<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProLibItemEntity extends Entity
{
    use EntityIdTrait;

    protected string $proLibGroupId;
    protected string $customerId;
    protected ?string $productId = null;
    protected ?string $artworkId = null;
    protected ?string $artworkState = null;
    protected ?array $artworkAccess = null;
    protected bool $packshotCreated = false;
    protected bool $packshotPurchased = false;
    protected ?string $name = null;
    protected ?string $note = null;
    protected ?int $version = null;
    protected ?string $artworkStatus = null;
    protected ?\DateTimeImmutable $expertCheckApproved = null;
    protected ?\DateTimeImmutable $lastOrderAt = null;
    protected ?array $payload = null;
    protected ?CustomerEntity $customer = null;
    protected ?ProLibGroupEntity $proLibGroup = null;
    protected ?ProductEntity $product = null;
    protected ?AccessoryOptionCollection $accessoryOptions = null;
    protected ?ProLibItemOrderCollection $proLibItemOrders = null;

    /**
     * @return ProLibItemOrderCollection|null
     */
    public function getProLibItemOrders(): ?ProLibItemOrderCollection
    {
        return $this->proLibItemOrders;
    }

    /**
     * @param ProLibItemOrderCollection|null $proLibItemOrders
     */
    public function setProLibItemOrders(?ProLibItemOrderCollection $proLibItemOrders): void
    {
        $this->proLibItemOrders = $proLibItemOrders;
    }

    /**
     * @return string
     */
    public function getProLibGroupId(): string
    {
        return $this->proLibGroupId;
    }

    /**
     * @param string $proLibGroupId
     */
    public function setProLibGroupId(string $proLibGroupId): void
    {
        $this->proLibGroupId = $proLibGroupId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * @param string|null $productId
     */
    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return string|null
     */
    public function getArtworkId(): ?string
    {
        return $this->artworkId;
    }

    /**
     * @param string|null $artworkId
     */
    public function setArtworkId(?string $artworkId): void
    {
        $this->artworkId = $artworkId;
    }

    /**
     * @return string|null
     */
    public function getArtworkState(): ?string
    {
        return $this->artworkState;
    }

    /**
     * @param string|null $artworkState
     */
    public function setArtworkState(?string $artworkState): void
    {
        $this->artworkState = $artworkState;
    }

    /**
     * @return array|null
     */
    public function getArtworkAccess(): ?array
    {
        return $this->artworkAccess;
    }

    /**
     * @param array|null $artworkAccess
     */
    public function setArtworkAccess(?array $artworkAccess): void
    {
        $this->artworkAccess = $artworkAccess;
    }

    /**
     * @return bool
     */
    public function isPackshotCreated(): bool
    {
        return $this->packshotCreated;
    }

    /**
     * @param bool $packshotCreated
     */
    public function setPackshotCreated(bool $packshotCreated): void
    {
        $this->packshotCreated = $packshotCreated;
    }

    /**
     * @return bool
     */
    public function isPackshotPurchased(): bool
    {
        return $this->packshotPurchased;
    }

    /**
     * @param bool $packshotPurchased
     */
    public function setPackshotPurchased(bool $packshotPurchased): void
    {
        $this->packshotPurchased = $packshotPurchased;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     */
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    /**
     * @return int|null
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * @param int|null $version
     */
    public function setVersion(?int $version): void
    {
        $this->version = $version;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastOrderAt(): ?\DateTimeImmutable
    {
        return $this->lastOrderAt;
    }

    /**
     * @param \DateTimeImmutable|null $lastOrderAt
     */
    public function setLastOrderAt(?\DateTimeImmutable $lastOrderAt): void
    {
        $this->lastOrderAt = $lastOrderAt;
    }

    /**
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array|null $payload
     */
    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity|null $customer
     */
    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return ProLibGroupEntity|null
     */
    public function getProLibGroup(): ?ProLibGroupEntity
    {
        return $this->proLibGroup;
    }

    /**
     * @param ProLibGroupEntity|null $proLibGroup
     */
    public function setProLibGroup(?ProLibGroupEntity $proLibGroup): void
    {
        $this->proLibGroup = $proLibGroup;
    }

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
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

    public function setArtworkStatus(string $artworkStatus): self
    {
        $this->artworkStatus = $artworkStatus;

        return $this;
    }

    public function getArtworkStatus(): ?string
    {
        return $this->artworkStatus;
    }

    public function setExpertCheckApproved(\DateTimeImmutable $expertCheckApproved): self
    {
        $this->expertCheckApproved = $expertCheckApproved;

        return $this;
    }

    public function getExpertCheckApproved(): ?\DateTimeImmutable
    {
        return $this->expertCheckApproved;
    }
}
