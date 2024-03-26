<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Cart;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\CartCollection;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;

interface CartReaderInterface
{
    public function findSplitCarts(string $parentCartToken): CartCollection;

    public function findPcCart(string $parentCartToken, string $productVariantId, string $accessoryOptionId): ?SplitCartExtension;

    public function findPcCartByToken(string $cartToken): ?SplitCartExtension;
}
