<?php

declare(strict_types=1);

namespace Packiro\Checkout\DAL\Extension\Core\Checkout\Order;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class SplitOrderEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $orderId = null;
    protected ?string $checkoutId = null;
    protected ?string $orderType = null;
    protected ?string $orderVersionId = null;

    protected ?OrderEntity $order = null;

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function setCheckoutId(?string $checkoutId): self
    {
        $this->checkoutId = $checkoutId;

        return $this;
    }

    public function getOrderType(): ?string
    {
        return $this->orderType;
    }

    public function setOrderType(?string $orderType): self
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getOrderVersionId(): ?string
    {
        return $this->orderVersionId;
    }

    public function setOrderVersionId(?string $orderVersionId): self
    {
        $this->orderVersionId = $orderVersionId;

        return $this;
    }
}
