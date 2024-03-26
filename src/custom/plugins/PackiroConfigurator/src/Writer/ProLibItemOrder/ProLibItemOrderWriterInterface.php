<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Writer\ProLibItemOrder;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

interface ProLibItemOrderWriterInterface
{
    public function attachProLibItemsToSlots(EntityCollection $proLibItemOrderCollection, Context $context): void;
}
