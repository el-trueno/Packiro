<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\ProLibItemOrder;

use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

interface ProLibItemOrderReaderInterface
{
    public function findOrderProLibSlots(string $orderId, Context $context): EntityCollection;

    public function findProLibSlotsForLineItems(LineItemCollection $lineItemCollection, Context $context): EntityCollection;
}
