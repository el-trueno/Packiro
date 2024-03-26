<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Struct\Order;

class ExportOrderResult
{
    private array $serializedOrders = [];
    private int $totalOrders = 0;

    public function getSerializedOrders(): array
    {
        return $this->serializedOrders;
    }

    public function setSerializedOrders(array $serializedOrders): self
    {
        $this->serializedOrders = $serializedOrders;

        return $this;
    }

    public function getTotalOrders(): int
    {
        return $this->totalOrders;
    }

    public function setTotalOrders(int $totalOrders): self
    {
        $this->totalOrders = $totalOrders;

        return $this;
    }
}
