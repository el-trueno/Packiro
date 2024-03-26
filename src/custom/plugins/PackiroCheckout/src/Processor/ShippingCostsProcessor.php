<?php

declare(strict_types=1);

namespace Packiro\Checkout\Processor;

use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ShippingCostsProcessor implements CartProcessorInterface
{
    public function __construct(
        private CartServiceInterface $cartService,
    ) {
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior,
    ): void {

        $this->cartService->prepareShippingCostsWithSplitCart(
            $data,
            $original,
            $toCalculate,
            $context,
        );
    }
}
