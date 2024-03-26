<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\EntityAccessoryTrait;
use Kuniva\PackiroConfigurator\Core\Framework\DataAbstractionLayer\EntityAccessoryTranslationTrait;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Tax\TaxEntity;

class AccessoryOptionEntity extends Entity
{
    use EntityIdTrait;
    use EntityAccessoryTrait;
    use EntityAccessoryTranslationTrait;

    protected int $minQuantity = 0;
    protected int $maxQuantity = 0;
    protected int $deliveryDays = 0;
    protected int $minDeliveryDays = 0;
    protected int $maxDeliveryDays = 0;
    protected ?PriceCollection $price = null;
    protected ?string $taxId = null;
    protected ?TaxEntity $tax = null;
    protected ?string $accessoryGroupId = null;
    protected ?AccessoryGroupEntity $accessoryGroup = null;
    protected bool $preSelected = false;
    protected ?array $provided = null;

    protected CalculatedPrice $calculatedPrice;
    protected ?\DateTimeImmutable $calculatedDeliveryDate = null;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCalculatedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->calculatedDeliveryDate;
    }

    /**
     * @param \DateTimeImmutable|null $calculatedDeliveryDate
     */
    public function setCalculatedDeliveryDate(?\DateTimeImmutable $calculatedDeliveryDate): void
    {
        $this->calculatedDeliveryDate = $calculatedDeliveryDate;
    }

    /**
     * @return int
     */
    public function getMaxQuantity(): int
    {
        return $this->maxQuantity;
    }

    /**
     * @param int $maxQuantity
     */
    public function setMaxQuantity(int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * @return int
     */
    public function getDeliveryDays(): int
    {
        return $this->deliveryDays;
    }

    /**
     * @param int $deliveryDays
     */
    public function setDeliveryDays(int $deliveryDays): void
    {
        $this->deliveryDays = $deliveryDays;
    }

    /**
     * @return int
     */
    public function getMinDeliveryDays(): int
    {
        return $this->minDeliveryDays;
    }

    /**
     * @param int $minDeliveryDays
     */
    public function setMinDeliveryDays(int $minDeliveryDays): self
    {
        $this->minDeliveryDays = $minDeliveryDays;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDeliveryDays(): int
    {
        return $this->maxDeliveryDays;
    }

    /**
     * @param int $maxDeliveryDays
     */
    public function setMaxDeliveryDays(int $maxDeliveryDays): self
    {
        $this->maxDeliveryDays = $maxDeliveryDays;

        return $this;
    }



    /**
     * @return array|null
     */
    public function getProvided(): ?array
    {
        return $this->provided;
    }

    /**
     * @param array|null $provided
     */
    public function setProvided(?array $provided): void
    {
        $this->provided = $provided;
    }

    /**
     * @return CalculatedPrice
     */
    public function getCalculatedPrice(): CalculatedPrice
    {
        return $this->calculatedPrice;
    }

    /**
     * @param CalculatedPrice $calculatedPrice
     */
    public function setCalculatedPrice(CalculatedPrice $calculatedPrice): void
    {
        $this->calculatedPrice = $calculatedPrice;
    }

    /**
     * @return bool
     */
    public function isPreSelected(): bool
    {
        return $this->preSelected;
    }

    /**
     * @param bool $preSelected
     */
    public function setPreSelected(bool $preSelected): void
    {
        $this->preSelected = $preSelected;
    }

    /**
     * @return string|null
     */
    public function getAccessoryGroupId(): ?string
    {
        return $this->accessoryGroupId;
    }

    /**
     * @param string|null $accessoryGroupId
     */
    public function setAccessoryGroupId(?string $accessoryGroupId): void
    {
        $this->accessoryGroupId = $accessoryGroupId;
    }

    /**
     * @return AccessoryGroupEntity|null
     */
    public function getAccessoryGroup(): ?AccessoryGroupEntity
    {
        return $this->accessoryGroup;
    }

    /**
     * @param AccessoryGroupEntity|null $accessoryGroup
     */
    public function setAccessoryGroup(?AccessoryGroupEntity $accessoryGroup): void
    {
        $this->accessoryGroup = $accessoryGroup;
    }

    /**
     * @return int
     */
    public function getMinQuantity(): int
    {
        return $this->minQuantity;
    }

    /**
     * @param int $minQuantity
     */
    public function setMinQuantity(int $minQuantity): void
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return PriceCollection|null
     */
    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }

    /**
     * @param PriceCollection|null $price
     */
    public function setPrice(?PriceCollection $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string|null
     */
    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    /**
     * @param string|null $taxId
     */
    public function setTaxId(?string $taxId): void
    {
        $this->taxId = $taxId;
    }

    /**
     * @return TaxEntity|null
     */
    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    /**
     * @param TaxEntity|null $tax
     */
    public function setTax(?TaxEntity $tax): void
    {
        $this->tax = $tax;
    }
}
