<?php

declare(strict_types=1);

namespace Packiro\Checkout\Collector;

use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ShippingCostsCollector implements CartDataCollectorInterface
{
    public function __construct(
        private CartServiceInterface $cartService,
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->cartService->collectSplitCartDeliveries($data, $original);
    }
}
