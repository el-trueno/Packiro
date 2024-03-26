<?php

declare(strict_types=1);

namespace Packiro\Checkout\Tests\Service\Order;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Order\SplitOrderDefinition;
use Packiro\Checkout\Service\Order\OrderExtraFieldService;
use Packiro\Core\Service\UpdateExistingExtensionService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderExtraFieldServiceTest extends TestCase
{
    protected OrderExtraFieldService $orderExtraFieldService;

    protected Context $context;


    protected UpdateExistingExtensionService $updateExistingExtensionService;

    protected function setUp(): void
    {
        $orderRepository = $this->createMock(EntityRepository::class);
        $pcOrderRepository = $this->createMock(EntityRepository::class);
        $this->context = Context::createDefaultContext();
        $this->updateExistingExtensionService = $this->createMock(UpdateExistingExtensionService::class);
        $this->orderExtraFieldService = new OrderExtraFieldService(
            $orderRepository,
            $pcOrderRepository,
            $this->updateExistingExtensionService
        );
        parent::setUp();
    }

    public function testSaveOrderTypeServices(): void
    {
        $order = new OrderEntity();
        $orderLineItemCollection = new OrderLineItemCollection();
        $product = $this->createMock(ProductEntity::class);
        $product->method('getProductNumber')->willReturn(OrderExtraFieldService::PACKSHOT_PRODUCT_NUMBER);
        $lineItem = $this->createMock(OrderLineItemEntity::class);
        $lineItem->method('getProduct')->willReturn($product);
        $orderLineItemCollection->add($lineItem);
        $order->setLineItems($orderLineItemCollection);
        $order->setId(Uuid::randomHex());
        $this->updateExistingExtensionService
            ->expects($this->once())
            ->method('reload')
            ->with(
                $this->callback(function ($fillUpExistingExtension) {
                    return $fillUpExistingExtension->getUpdatedValues()['orderType'] === SplitOrderDefinition::SERVICE_ORDER_TYPE;
                })
            );
        $this->orderExtraFieldService->saveOrderType($order, $this->context);
    }

    public function testSaveOrderTypeNormal(): void
    {
        $order = new OrderEntity();
        $orderLineItemCollection = new OrderLineItemCollection();
        $product = $this->createMock(ProductEntity::class);
        $product->method('getProductNumber')->willReturn('');
        $lineItem = $this->createMock(OrderLineItemEntity::class);
        $lineItem->method('getProduct')->willReturn($product);
        $orderLineItemCollection->add($lineItem);
        $order->setLineItems($orderLineItemCollection);
        $order->setId(Uuid::randomHex());
        $this->updateExistingExtensionService
            ->expects($this->once())
            ->method('reload')
            ->with(
                $this->callback(function ($fillUpExistingExtension) {
                    return $fillUpExistingExtension->getUpdatedValues()['orderType'] === SplitOrderDefinition::NORMAL_ORDER_TYPE;
                })
            );
        $this->orderExtraFieldService->saveOrderType($order, $this->context);
    }
}
