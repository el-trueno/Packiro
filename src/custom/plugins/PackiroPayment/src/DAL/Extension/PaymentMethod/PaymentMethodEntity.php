<?php

declare(strict_types=1);

namespace Packiro\Payment\DAL\Extension\PaymentMethod;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity as ParentPaymentMethodEntity;

class PaymentMethodEntity extends Entity
{
    use EntityIdTrait;

    protected ?int $commission = null;
    protected string $paymentId;

    protected ?ParentPaymentMethodEntity $paymentMethod;

    public function getCommission(): ?int
    {
        return $this->commission;
    }

    public function setCommission(?int $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getPaymentMethod(): ?ParentPaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?ParentPaymentMethodEntity $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId(string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }
}
