<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPrice;

class SalesChannelAccessoryOptionEntity extends AccessoryOptionEntity
{
    protected CalculatedPrice $calculatedPrice;
    protected ?float $purchaseUnit = null;
    protected ?float $referenceUnit = null;
    protected ?string $unitId = null;
    protected ?ProductPriceCollection $prices = null;
    protected ?CheapestPrice $cheapestPrice = null;
    protected PriceCollection $calculatedPrices;
    protected ?array $streamIds = null;
    protected bool $available = true;
    protected bool $disabled = false;
    protected array $notCombinableWith = [];

    /**
     * put here data important for OPS import
     * @return array{
     *     totalPrice: float,
     *     minQuantity: int,
     *     maxQuantity: int,
     *     deliveryDays: int,
     *     minDeliveryDays: int,
     *     maxDeliveryDays: int
     * }
     */
    public function toExportArray(): array
    {
        return [
            'unitPrice' => $this->calculatedPrice->getUnitPrice(),
            'minQuantity' => $this->minQuantity,
            'maxQuantity' => $this->maxQuantity,
            'deliveryDays' => $this->deliveryDays,
            'minDeliveryDays' => $this->minDeliveryDays,
            'maxDeliveryDays' => $this->maxDeliveryDays,
        ];
    }

    /**
     * @return bool
     */
    public function getDisabled(): bool
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
     * @return array
     */
    public function getNotCombinableWith(): array
    {
        return $this->notCombinableWith;
    }

    /**
     * @param array $notCombinableWith
     */
    public function setNotCombinableWith(array $notCombinableWith): void
    {
        $this->notCombinableWith = $notCombinableWith;
    }

    public function addNotCombinableWith(string $id): void
    {
        if (in_array($id, $this->notCombinableWith)) {
            return;
        }
        $this->notCombinableWith[] = $id;
    }

    /**
     * @return bool
     */
    public function getAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * @return string|null
     */
    public function getUnitId(): ?string
    {
        return $this->unitId;
    }

    /**
     * @param string|null $unitId
     */
    public function setUnitId(?string $unitId): void
    {
        $this->unitId = $unitId;
    }

    /**
     * @return ProductPriceCollection|null
     */
    public function getPrices(): ?ProductPriceCollection
    {
        return $this->prices;
    }

    /**
     * @param ProductPriceCollection|null $prices
     */
    public function setPrices(?ProductPriceCollection $prices): void
    {
        $this->prices = $prices;
    }

    /**
     * @return CheapestPrice|null
     */
    public function getCheapestPrice(): ?CheapestPrice
    {
        return $this->cheapestPrice;
    }

    /**
     * @param CheapestPrice|null $cheapestPrice
     */
    public function setCheapestPrice(?CheapestPrice $cheapestPrice): void
    {
        $this->cheapestPrice = $cheapestPrice;
    }

    /**
     * @return PriceCollection|null
     */
    public function getCalculatedPrices(): ?PriceCollection
    {
        return $this->calculatedPrices;
    }

    /**
     * @param PriceCollection|null $calculatedPrices
     */
    public function setCalculatedPrices(?PriceCollection $calculatedPrices): void
    {
        $this->calculatedPrices = $calculatedPrices;
    }

    /**
     * @return array|null
     */
    public function getStreamIds(): ?array
    {
        return $this->streamIds;
    }

    /**
     * @param array|null $streamIds
     */
    public function setStreamIds(?array $streamIds): void
    {
        $this->streamIds = $streamIds;
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
     * @return float|null
     */
    public function getPurchaseUnit(): ?float
    {
        return $this->purchaseUnit;
    }

    /**
     * @param float|null $purchaseUnit
     */
    public function setPurchaseUnit(?float $purchaseUnit): void
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    /**
     * @return float|null
     */
    public function getReferenceUnit(): ?float
    {
        return $this->referenceUnit;
    }

    /**
     * @param float|null $referenceUnit
     */
    public function setReferenceUnit(?float $referenceUnit): void
    {
        $this->referenceUnit = $referenceUnit;
    }
}
