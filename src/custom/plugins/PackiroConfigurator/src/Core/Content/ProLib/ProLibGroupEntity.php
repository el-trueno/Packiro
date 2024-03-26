<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProLibGroupEntity extends Entity
{
    use EntityIdTrait;

    protected string $customerId;
    protected ?string $lastOrderItemId = null;
    protected ?string $lastVersionItemId = null;
    protected ?string $lastItemOrderId = null;
    protected ?string $name = null;
    protected ?string $customerReference = null;
    protected int $versionCount = 0;
    protected ?CustomerEntity $customer = null;
    protected ?ProLibItemEntity $lastOrderItem = null;
    protected ?ProLibItemEntity $lastVersionItem = null;
    protected ?ProLibItemOrderEntity $lastItemOrder = null;
    protected ?ProLibItemCollection $proLibItems = null;

    /**
     * @return string|null
     */
    public function getLastItemOrderId(): ?string
    {
        return $this->lastItemOrderId;
    }

    /**
     * @param string|null $lastItemOrderId
     */
    public function setLastItemOrderId(?string $lastItemOrderId): void
    {
        $this->lastItemOrderId = $lastItemOrderId;
    }

    /**
     * @return ProLibItemOrderEntity|null
     */
    public function getLastItemOrder(): ?ProLibItemOrderEntity
    {
        return $this->lastItemOrder;
    }

    /**
     * @param ProLibItemOrderEntity|null $lastItemOrder
     */
    public function setLastItemOrder(?ProLibItemOrderEntity $lastItemOrder): void
    {
        $this->lastItemOrder = $lastItemOrder;
    }

    /**
     * @return string|null
     */
    public function getLastOrderItemId(): ?string
    {
        return $this->lastOrderItemId;
    }

    /**
     * @param string|null $lastOrderItemId
     */
    public function setLastOrderItemId(?string $lastOrderItemId): void
    {
        $this->lastOrderItemId = $lastOrderItemId;
    }

    /**
     * @return string|null
     */
    public function getLastVersionItemId(): ?string
    {
        return $this->lastVersionItemId;
    }

    /**
     * @param string|null $lastVersionItemId
     */
    public function setLastVersionItemId(?string $lastVersionItemId): void
    {
        $this->lastVersionItemId = $lastVersionItemId;
    }

    /**
     * @return ProLibItemEntity|null
     */
    public function getLastOrderItem(): ?ProLibItemEntity
    {
        return $this->lastOrderItem;
    }

    /**
     * @param ProLibItemEntity|null $lastOrderItem
     */
    public function setLastOrderItem(?ProLibItemEntity $lastOrderItem): void
    {
        $this->lastOrderItem = $lastOrderItem;
    }

    /**
     * @return ProLibItemEntity|null
     */
    public function getLastVersionItem(): ?ProLibItemEntity
    {
        return $this->lastVersionItem;
    }

    /**
     * @param ProLibItemEntity|null $lastVersionItem
     */
    public function setLastVersionItem(?ProLibItemEntity $lastVersionItem): void
    {
        $this->lastVersionItem = $lastVersionItem;
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
    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    /**
     * @param string|null $customerReference
     */
    public function setCustomerReference(?string $customerReference): void
    {
        $this->customerReference = $customerReference;
    }

    /**
     * @return int
     */
    public function getVersionCount(): int
    {
        return $this->versionCount;
    }

    /**
     * @param int $versionCount
     */
    public function setVersionCount(int $versionCount): void
    {
        $this->versionCount = $versionCount;
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
     * @return ProLibItemCollection|null
     */
    public function getProLibItems(): ?ProLibItemCollection
    {
        return $this->proLibItems;
    }

    /**
     * @param ProLibItemCollection|null $proLibItems
     */
    public function setProLibItems(?ProLibItemCollection $proLibItems): void
    {
        $this->proLibItems = $proLibItems;
    }
}
