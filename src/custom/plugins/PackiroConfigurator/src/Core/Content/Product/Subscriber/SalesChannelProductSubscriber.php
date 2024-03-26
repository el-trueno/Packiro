<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Product\Subscriber;

use Kuniva\PackiroConfigurator\Core\Service\AccessoryService;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AccessoryService $accessoryService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.loaded' => 'onSalesChannelEntityLoadedEvent',
            'sales_channel.product.process.criteria' => 'onSalesChannelProcessCriteriaEvent',
        ];
    }

    public function onSalesChannelEntityLoadedEvent(SalesChannelEntityLoadedEvent $event): void
    {
        /** @var SalesChannelProductEntity $product */
        foreach ($event->getEntities() as $product) {
            $this->accessoryService->overrideSalesChannelProduct(
                $product,
                $event->getSalesChannelContext()
            );
        }
    }

    public function onSalesChannelProcessCriteriaEvent(SalesChannelProcessCriteriaEvent $event): void
    {
        $this->accessoryService->enrichSalesChannelProductCriteria(
            $event->getCriteria(),
            $event->getSalesChannelContext()
        );
    }
}
