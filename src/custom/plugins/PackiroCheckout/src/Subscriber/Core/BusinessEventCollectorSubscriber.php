<?php

declare(strict_types=1);

namespace Packiro\Checkout\Subscriber\Core;

use Packiro\Checkout\Event\Cart\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BusinessEventCollectorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BusinessEventCollector $businessEventCollector,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            BusinessEventCollectorEvent::NAME => 'onEventCollect',
        ];
    }

    public function onEventCollect(BusinessEventCollectorEvent $event): void
    {
        $definition = $this->businessEventCollector->define(CheckoutOrderPlacedEvent::class);
        if (!$definition) {
            return;
        }

        $event->getCollection()->set($definition->getName(), $definition);
    }
}
