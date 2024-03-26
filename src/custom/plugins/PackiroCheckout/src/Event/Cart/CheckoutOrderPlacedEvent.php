<?php

declare(strict_types=1);

namespace Packiro\Checkout\Event\Cart;

use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\CustomerAware;
use Shopware\Core\Framework\Event\EventData\EntityCollectionType;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class CheckoutOrderPlacedEvent extends Event implements SalesChannelAware, MailAware, CustomerAware
{
    public const EVENT_NAME = 'packiro.checkout.order.placed';

    private MailRecipientStruct $mailRecipientStruct;

    public function __construct(
        private OrderCollection $orders,
        private OrderCustomerEntity $orderCustomerEntity,
        private ?string $checkoutId,
        private SalesChannelContext $salesChannelContext,
    ) {
        $this->mailRecipientStruct = new MailRecipientStruct([
            $orderCustomerEntity->getEmail() => $orderCustomerEntity->getFirstName() . ' ' . $orderCustomerEntity->getLastName(),
        ]);
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getOrders(): OrderCollection
    {
        return $this->orders;
    }

    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function getOrderCustomer(): OrderCustomerEntity
    {
        return $this->orderCustomerEntity;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('orders', new EntityCollectionType(OrderDefinition::class))
            ->add('checkoutId', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('orderCustomer', new EntityType(OrderCustomerDefinition::class))
        ;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getMailStruct(): MailRecipientStruct
    {
        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelContext->getSalesChannelId();
    }

    public function getCustomerId(): string
    {
        return $this->orderCustomerEntity->getCustomerId();
    }
}
