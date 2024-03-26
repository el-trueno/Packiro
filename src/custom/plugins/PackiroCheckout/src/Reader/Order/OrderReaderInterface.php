<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Order;

use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

interface OrderReaderInterface
{
    public function findSplitOrders(string $checkoutId, Context $context, ?Criteria $criteria = null): OrderCollection;
}
