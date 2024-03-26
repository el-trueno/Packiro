<?php

declare(strict_types=1);

namespace Packiro\Checkout\DAL\Extension\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Framework\Struct\Struct;

class SplitCartExtension extends Struct
{
    public const EXTENSION_PROPERTY_NAME = 'splitCart';

    public function __construct(
        private string $cartToken,
        private string $parentToken,
        private string $productVariantId,
        private string $accessoryOptionId,
        private ?CartPrice $parentCartPrice = null,
    ) {
    }

    public function getCartToken(): ?string
    {
        return $this->cartToken;
    }

    public function setCartToken(?string $cartToken): self
    {
        $this->cartToken = $cartToken;

        return $this;
    }

    public function getParentToken(): ?string
    {
        return $this->parentToken;
    }

    public function setParentToken(?string $parentToken): self
    {
        $this->parentToken = $parentToken;

        return $this;
    }

    public function getProductVariantId(): ?string
    {
        return $this->productVariantId;
    }

    public function setProductVariantId(?string $productVariantId): self
    {
        $this->productVariantId = $productVariantId;

        return $this;
    }

    public function getAccessoryOptionId(): ?string
    {
        return $this->accessoryOptionId;
    }

    public function setAccessoryOptionId(?string $accessoryOptionId): self
    {
        $this->accessoryOptionId = $accessoryOptionId;

        return $this;
    }

    public function getParentCartPrice(): ?CartPrice
    {
        return $this->parentCartPrice;
    }

    public function setParentCartPrice(?CartPrice $parentCartPrice): self
    {
        $this->parentCartPrice = $parentCartPrice;

        return $this;
    }
}
