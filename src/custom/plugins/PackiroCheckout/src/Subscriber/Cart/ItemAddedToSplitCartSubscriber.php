<?php

declare(strict_types=1);

namespace Packiro\Checkout\Subscriber\Cart;

use Kuniva\PackiroConfigurator\Core\Service\ProLibItemOrderServiceInterface;
use Packiro\Checkout\Event\Cart\ItemAddedToSplitCartEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemAddedToSplitCartSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProLibItemOrderServiceInterface $proLibItemOrderService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ItemAddedToSplitCartEvent::class => 'onItemAddedToSplitCartEvent',
        ];
    }

    public function onItemAddedToSplitCartEvent(ItemAddedToSplitCartEvent $event): void
    {
        $this->proLibItemOrderService->createProLibItemSlots(
            $event->getLineItem(),
            $event->getCart()->getToken(),
            $event->getContext(),
        );
    }
}
