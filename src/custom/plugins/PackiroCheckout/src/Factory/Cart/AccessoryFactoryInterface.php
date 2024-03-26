<?php

declare(strict_types=1);

namespace Packiro\Checkout\Factory\Cart;

use Packiro\Checkout\Struct\Accessory\AccessoryStruct;
use Packiro\Checkout\Struct\Accessory\AccessoryStructCollection;

interface AccessoryFactoryInterface
{
    public function createAccessoryStructCollection(array $accessoriesData): AccessoryStructCollection;

    public function createAccessoryStruct(array $accessoryData): ?AccessoryStruct;
}
