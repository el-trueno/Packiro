<?php

declare(strict_types=1);

namespace Packiro\Payment\Processor;

use Packiro\Payment\Service\CommissionServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CommissionProcessor implements CartProcessorInterface
{
    public function __construct(private readonly CommissionServiceInterface $commissionService)
    {}

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->commissionService->addCommission(
            $data,
            $original,
            $toCalculate,
            $context);
    }
}
