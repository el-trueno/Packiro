<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Order\Subscriber;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Kuniva\PackiroConfigurator\Core\Service\ProLibItemOrderService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Checkout\Cart\Order\OrderConvertedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    private Cart $cart;

    public function __construct(
        private ProLibItemOrderService $proLibItemOrderService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'onCartConvertedEvent',
            OrderConvertedEvent::class => 'onOrderConvertedEvent',
            CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlacedEvent',
        ];
    }

    public function onOrderConvertedEvent(OrderConvertedEvent $event): void
    {
    }

    public function onCheckoutOrderPlacedEvent(CheckoutOrderPlacedEvent $event): void
    {
        $this->proLibItemOrderService->patchProLibItemOrders(
            $this->cart,
            $event->getOrder(),
            $event->getContext()
        );
    }

    public function onCartConvertedEvent(CartConvertedEvent $event): void
    {
        $this->cart = $event->getCart();

        $convertedCart = $event->getConvertedCart();

        foreach ($convertedCart['lineItems'] as &$lineItem) {
            if (in_array($lineItem['type'], [PouchBundleCartProcessor::TYPE])) {
                $lineItem['productId'] =  $lineItem['referencedId'];
            }
        }

        $event->setConvertedCart($convertedCart);
    }
}
