<?php

declare(strict_types=1);

namespace Packiro\Payment\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CommissionServiceInterface
{
    public function addCommission(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context
    ): void;
}