<?php

declare(strict_types=1);

namespace Packiro\Checkout\Subscriber\Order;

use Packiro\Checkout\Service\Order\OrderExtraFieldServiceInterface;
use Packiro\Checkout\Service\Order\OrderService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Packiro\Checkout\Event\Cart\CheckoutOrderPlacedEvent as CollectionCheckoutOrderPlacedEvent;

class OrderWrittenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private OrderExtraFieldServiceInterface $orderExtraFieldService,
        private EventDispatcherInterface $eventDispatcher,
        private EntityRepository $orderRepository,
        private OrderService $orderService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onSaveOrderEvent',
            CollectionCheckoutOrderPlacedEvent::class => 'onAddCreatedByIdIntoOrder',
            CheckoutOrderPlacedCriteriaEvent::class => 'beforeOrderSent',
        ];
    }

    public function onSaveOrderEvent(CheckoutOrderPlacedEvent $checkoutOrderPlacedEvent): void
    {
        $this->orderExtraFieldService->saveOrderType(
            $checkoutOrderPlacedEvent->getOrder(),
            $checkoutOrderPlacedEvent->getContext()
        );
    }

    public function onAddCreatedByIdIntoOrder(CollectionCheckoutOrderPlacedEvent $checkoutOrderPlacedEvent): void
    {
        foreach ($checkoutOrderPlacedEvent->getOrders() as $order) {
            $this->orderService->addCreatedByIdIntoOrder($order, $checkoutOrderPlacedEvent->getContext());
        }
    }

    public function beforeOrderSent(
        CheckoutOrderPlacedCriteriaEvent $checkoutOrderPlacedCriteriaEvent
    ): void {
        $criteria = $checkoutOrderPlacedCriteriaEvent->getCriteria();
        $criteria->addAssociation('lineItems.product');
    }
}
