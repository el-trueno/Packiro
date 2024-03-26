<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\Order;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class OrderReader implements OrderReaderInterface
{
    public function __construct(
        private readonly EntityRepository $orderRepository,
    ) {
    }

    public function fetch(Context $context, int $offset = 0, int $limit = 20, ?Criteria $criteria = null): EntitySearchResult
    {
        $criteria ??= (new Criteria())
            ->addSorting(new FieldSorting('orderDateTime', FieldSorting::DESCENDING))
            ->addAssociation('lineItems.children')
            ->addAssociation('lineItems.' . ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME . '.proLibItem');

        $criteria
            ->setOffset($offset)
            ->setLimit($limit)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        return $this->orderRepository->search($criteria, $context);
    }
}
