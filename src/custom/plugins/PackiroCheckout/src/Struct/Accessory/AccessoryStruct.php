<?php

declare(strict_types=1);

namespace Packiro\Checkout\Struct\Accessory;

class AccessoryStruct
{
    public function __construct(
        private string $id,
        private ?string $type = null,
        private ?int $quantity = null,
    ) {
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'quantity' => $this->getQuantity(),
            'type' => $this->getType(),
        ];
    }
}
