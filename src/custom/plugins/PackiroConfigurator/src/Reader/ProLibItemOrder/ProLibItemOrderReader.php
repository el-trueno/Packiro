<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\ProLibItemOrder;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProLibItemOrderReader implements ProLibItemOrderReaderInterface
{
    public function __construct(
        private EntityRepository $proLibItemOrderRepository,
    ) {
    }

    public function findOrderProLibSlots(string $orderId, Context $context): EntitySearchResult
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('orderId', $orderId))
        ;

        return $this->proLibItemOrderRepository->search($criteria, $context);
    }

    public function findProLibSlotsForLineItems(LineItemCollection $lineItemCollection, Context $context): EntitySearchResult
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsAnyFilter(
                'cartLineItemId',
                $lineItemCollection->fmap(fn(LineItem $lineItem) => $lineItem->getId())
            ))
        ;

        return $this->proLibItemOrderRepository->search($criteria, $context);
    }
}
