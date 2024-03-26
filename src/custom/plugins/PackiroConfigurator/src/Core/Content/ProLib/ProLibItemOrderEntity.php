<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use DateTimeImmutable;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProLibItemOrderEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $cartToken = null;
    protected ?string $cartLineItemId = null;
    protected ?string $orderId = null;
    protected string $productId;
    protected ?string $orderLineItemId = null;
    protected ?string $proLibItemId = null;
    protected int $quantity = 0;
    protected ?ProLibItemEntity $proLibItem = null;
    protected ?OrderEntity $order = null;
    protected ?ProductEntity $product = null;
    protected ?OrderLineItemEntity $orderLineItem = null;
    protected ?DateTimeImmutable $updatedDeliveryDate = null;

    /**
     * @return string|null
     */
    public function getCartToken(): ?string
    {
        return $this->cartToken;
    }

    /**
     * @param string|null $cartToken
     */
    public function setCartToken(?string $cartToken): void
    {
        $this->cartToken = $cartToken;
    }

    /**
     * @return string|null
     */
    public function getCartLineItemId(): ?string
    {
        return $this->cartLineItemId;
    }

    /**
     * @param string|null $cartLineItemId
     */
    public function setCartLineItemId(?string $cartLineItemId): void
    {
        $this->cartLineItemId = $cartLineItemId;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @param string|null $orderId
     */
    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return string|null
     */
    public function getOrderLineItemId(): ?string
    {
        return $this->orderLineItemId;
    }

    /**
     * @param string|null $orderLineItemId
     */
    public function setOrderLineItemId(?string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    /**
     * @return string|null
     */
    public function getProLibItemId(): ?string
    {
        return $this->proLibItemId;
    }

    /**
     * @param string|null $proLibItemId
     */
    public function setProLibItemId(?string $proLibItemId): void
    {
        $this->proLibItemId = $proLibItemId;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return ProLibItemEntity|null
     */
    public function getProLibItem(): ?ProLibItemEntity
    {
        return $this->proLibItem;
    }

    /**
     * @param ProLibItemEntity|null $proLibItem
     */
    public function setProLibItem(?ProLibItemEntity $proLibItem): void
    {
        $this->proLibItem = $proLibItem;
    }

    /**
     * @return OrderEntity|null
     */
    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity|null $order
     */
    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
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
     * @return OrderLineItemEntity|null
     */
    public function getOrderLineItem(): ?OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    /**
     * @param OrderLineItemEntity|null $orderLineItem
     */
    public function setOrderLineItem(?OrderLineItemEntity $orderLineItem): void
    {
        $this->orderLineItem = $orderLineItem;
    }

    public function getUpdatedDeliveryDate(): ?DateTimeImmutable
    {
        return $this->updatedDeliveryDate;
    }

    public function setUpdatedDeliveryDate(?DateTimeImmutable $updatedDeliveryDate): self
    {
        $this->updatedDeliveryDate = $updatedDeliveryDate;

        return $this;
    }
}
