<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory\Subscriber;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Kuniva\PackiroConfigurator\Core\Service\AccessoryService;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelAccessorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AccessoryService $accessoryService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.pc_accessory_option.loaded' => 'onSalesChannelEntityLoadedEvent',
            'sales_channel.pc_accessory_option.process.criteria' => 'onSalesChannelProcessCriteriaEvent',
        ];
    }

    public function onSalesChannelEntityLoadedEvent(SalesChannelEntityLoadedEvent $event): void
    {
        /** @var AccessoryOptionCollection $accessoryOptions */
        $accessoryOptions = $event->getEntities();

        $this->accessoryService->setSalesChannelContext($event->getSalesChannelContext());
        $this->accessoryService->enrichCalculatedPrice($accessoryOptions);
    }

    public function onSalesChannelProcessCriteriaEvent(SalesChannelProcessCriteriaEvent $event): void
    {
    }
}
