<?php

declare(strict_types=1);

namespace Packiro\Checkout\DAL\Extension\Core\Checkout\Cart;

use Shopware\Core\Framework\Struct\Collection;

class CartCollection extends Collection
{
    public const EXTENSION_PROPERTY_NAME = 'splitCarts';
}
