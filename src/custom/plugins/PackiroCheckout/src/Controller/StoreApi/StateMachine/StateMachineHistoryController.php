<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\StateMachine;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class StateMachineHistoryController
{
    public function __construct(
        private EntityRepository $orderRepository,
        private EntityRepository $stateMachineHistoryRepository,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * @Route("/store-api/order/{orderId}/state-history", name="store-api.packiro.order.state-history", methods={"GET"})
     */
    public function getOrderStateHistory(string $orderId, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $orderCriteria = (new Criteria([$orderId]))
            ->addAssociations([
                'transactions',
                'deliveries',
            ]);
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search($orderCriteria, $salesChannelContext->getContext())->first();

        if (
            !$salesChannelContext->getCustomer()
            || !$order
            || $order->getOrderCustomer()->getCustomerId() !== $salesChannelContext->getCustomer()->getId()
        ) {
            throw new NotFoundHttpException(sprintf('Order with id: %s is not found.', $orderId));
        }

        $stateMachineHistoryCriteria = (new Criteria())
            ->addAssociations([
                'fromStateMachineState',
                'toStateMachineState',
            ])
            ->addFilter(new EqualsAnyFilter('entityId.id', [
                $order->getId(),
                $order->getTransactions()->first()->getId(),
                $order->getDeliveries()->first()->getId(),
            ]))
            ->addFilter(new EqualsAnyFilter('entityName', [
                'order',
                'order_transaction',
                'order_delivery',
            ]))
            ->addSorting(new FieldSorting('createdAt', 'ASC'));

        return $this->responseFactory->createListingResponse(
            $stateMachineHistoryCriteria,
            $this->stateMachineHistoryRepository->search($stateMachineHistoryCriteria, $salesChannelContext->getContext()),
            $this->stateMachineHistoryRepository->getDefinition(),
            $request,
            $salesChannelContext->getContext(),
        );
    }
}
