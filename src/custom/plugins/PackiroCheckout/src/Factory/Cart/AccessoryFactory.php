<?php

declare(strict_types=1);

namespace Packiro\Checkout\Factory\Cart;

use Packiro\Checkout\Struct\Accessory\AccessoryStruct;
use Packiro\Checkout\Struct\Accessory\AccessoryStructCollection;

class AccessoryFactory implements AccessoryFactoryInterface
{
    public function createAccessoryStructCollection(array $accessoriesData): AccessoryStructCollection
    {
        $accessoryStructCollection = new AccessoryStructCollection();
        foreach ($accessoriesData as $accessoryData) {
            $accessoryStruct = $this->createAccessoryStruct($accessoryData);

            if ($accessoryStruct) {
                $accessoryStructCollection->add($accessoryStruct);
            }
        }

        return $accessoryStructCollection;
    }

    public function createAccessoryStruct(array $accessoryData): ?AccessoryStruct
    {
        if (!isset($accessoryData['id'])) {
            return null;
        }

        return new AccessoryStruct(
            $accessoryData['id'],
            $accessoryData['type'] ?? null,
            isset($accessoryData['quantity']) ? (int)$accessoryData['quantity'] : null,
        );
    }
}
