<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Service;

use DateTime;
use DateTimeInterface;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Kuniva\PackiroConfigurator\Reader\Order\OrderReaderInterface;
use Kuniva\PackiroConfigurator\Struct\Order\ExportOrderResult;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryEntity;

class ExportOrderService implements ExportOrderServiceInterface
{
    public function __construct(
        private readonly OrderReaderInterface $orderReader,
        private readonly EntityRepository $historyRepository,
    ) {
    }

    public function export(Context $context, ?int $offset = 0, ?int $limit = 100): ExportOrderResult
    {
        $criteria = (new Criteria())
            ->setOffset($offset)
            ->setLimit($limit)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
            ->addSorting(new FieldSorting('orderDateTime', FieldSorting::DESCENDING))
            ->addAssociation('lineItems.children')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('addresses.country')
            ->addAssociation('billingAddress')
            ->addAssociation('transactions.stateMachineState')
            ->addAssociation('transactions.paymentMethod')
            ->addAssociation('lineItems.' . ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME . '.proLibItem');
        foreach ($this->getFilters() as $filter) {
            $criteria->addFilter($filter);
        }
        $ordersSearchResult = $this->orderReader->fetch($context, $offset, $limit, $criteria);

        $histories = $this->getOrdersAcceptedDates($ordersSearchResult->getEntities(), $context);
        $ordersAsArray = [];
        /** @var OrderEntity $order */
        foreach ($ordersSearchResult as $order) {
            $orderAsArray = $order->jsonSerialize();
            $orderHistory = $histories->filter(
                function (StateMachineHistoryEntity $stateMachineHistoryEntity) use ($order) {
                    return $stateMachineHistoryEntity->getEntityId()['id'] === $order->getId();
                }
            );
            if (count($orderHistory)) {
                $orderAsArray['approvedAt'] = $orderHistory->first()->getCreatedAt()->format(
                    DateTimeInterface::RFC3339_EXTENDED
                );
            }
            $ordersAsArray[$order->getId()] = $orderAsArray;
        }

        return (new ExportOrderResult())
            ->setSerializedOrders($ordersAsArray)
            ->setTotalOrders($ordersSearchResult->getTotal());
    }

    /**
     * @return EntityCollection<StateMachineHistoryEntity>
     */
    private function getOrdersAcceptedDates(EntityCollection $orders, Context $context): EntityCollection
    {
        $historyCriteria = new Criteria();
        $historyCriteria
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING))
            ->addFilter(
                new EqualsAnyFilter(
                    'entityId.id',
                    $orders->map(static function (OrderEntity $order) {
                        return $order->getId();
                    })
                )
            )
            ->addFilter(new EqualsFilter('entityName', 'order'))
            ->addFilter(new EqualsFilter('transitionActionName', 'accept_data'));

        return $this->historyRepository->search($historyCriteria, $context)->getEntities();
    }

    private function getFilters(): array
    {
        return [
            new NotFilter(
                MultiFilter::CONNECTION_OR,
                [
                    // check for OP orders type
                    new EqualsFilter(
                        'splitOrder.orderType',
                        'SERVICES'
                    ),
                ]
            ),
            new NotFilter(
                MultiFilter::CONNECTION_OR,
                [new EqualsFilter('customFields.kuniva_is_op_order', true)]
            ),
            new OrFilter([
                new NotFilter(
                    MultiFilter::CONNECTION_OR,
                    [
                        new EqualsFilter('stateMachineState.technicalName', 'completed'),
                    ]
                ),
                new RangeFilter(
                    'orderDate',
                    [RangeFilter::GTE => (new DateTime())->modify('-6 months')->format(DATE_ATOM)]
                ),
            ]),
            new OrFilter([
                new NotFilter(
                    MultiFilter::CONNECTION_OR,
                    [
                        new EqualsFilter('stateMachineState.technicalName', 'cancelled'),
                    ]
                ),
                new RangeFilter(
                    'orderDate',
                    [RangeFilter::GTE => (new DateTime())->modify('-6 months')->format(DATE_ATOM)]
                ),
            ]),
            new OrFilter([
                    new NotFilter(
                        MultiFilter::CONNECTION_OR,
                        [
                            new EqualsFilter('campaignCode', 'samplekit'),
                            new EqualsFilter('campaignCode', 'packshot-tool'),
                        ]
                    ),
                    new EqualsFilter('campaignCode', null),
                ]),
        ];
    }
}
