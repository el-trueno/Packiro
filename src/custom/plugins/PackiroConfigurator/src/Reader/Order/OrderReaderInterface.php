<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\Order;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

interface OrderReaderInterface
{
    public function fetch(Context $context, int $offset = 0, int $limit = 20, ?Criteria $criteria = null): EntitySearchResult;
}
