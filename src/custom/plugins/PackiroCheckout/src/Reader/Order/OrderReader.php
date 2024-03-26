<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Order;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Order\SplitOrderDefinition;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class OrderReader implements OrderReaderInterface
{
    public function __construct(
        private EntityRepository $orderRepository,
    ) {
    }

    public function findSplitOrders(string $checkoutId, Context $context, ?Criteria $criteria = null): OrderCollection
    {
        if ($criteria === null) {
            $criteria = new Criteria();
        }

        $criteria->addFilter(new EqualsFilter(SplitOrderDefinition::EXTENSION_PROPERTY_NAME . '.checkoutId', $checkoutId));
        $criteria->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));
        $criteria->getAssociation('deliveries')->addSorting(new FieldSorting('createdAt'));

        return new OrderCollection($this->orderRepository->search($criteria, $context)->getElements());
    }
}
