<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Test\Service;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Kuniva\PackiroConfigurator\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class OrderServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testInitiation()
    {
        $service = new OrderService($this->createStub(DefinitionInstanceRegistry::class));
        self::assertInstanceOf(OrderService::class, $service);
    }

    public function testExportStructure()
    {
        /** @var OrderService $s */
        $s = $this->getContainer()->get(OrderService::class);
        $exportData = $s->export(Context::createDefaultContext(), 0, 100);

        self::assertArrayHasKey(1, $exportData);//total
        $this->assertIsInt($exportData[1]);
        $this->assertInstanceOf(EntityCollection::class, $exportData[0]);//orders
        $orders = $exportData[0];
        $this->assertCount(1, $orders);
        /** @var OrderEntity $order */
        $order = $orders->filter(fn(OrderEntity $order) => $order->getId() === '0ed58e3eec5642e4aec497635e58be05')->first();
        $this->assertInstanceOf(OrderEntity::class, $order);
        $this->assertCount(19, $order->getLineItems());
        $this->assertCount(2, $order->getNestedLineItems());
        $this->assertCount(2, $order->getLineItems()->filter(fn (OrderLineItemEntity $li) => $li->getType() === 'delivery'));
        $this->assertCount(2, $order->getLineItems()->filter(fn (OrderLineItemEntity $li) => $li->getType() === 'artwork'));
        $this->assertCount(11, $order->getLineItems()->filter(fn (OrderLineItemEntity $li) => $li->getType() === 'pseudo-product'));
        /** @var OrderLineItemCollection $pouches */
        $pouches = $order->getLineItems()->filter(fn (OrderLineItemEntity $li) => $li->getType() === 'pc_pouch_bundle');
        $this->assertCount(2, $pouches->getElements());
        $pcOptions1 = $pouches->first()->getPayload()[PouchBundleCartProcessor::OPTIONS_KEY];
        $this->assertArrayHasKey('SIZE', $pcOptions1);
        $this->assertEquals('M', $pcOptions1['SIZE']);
        $this->assertArrayHasKey('MATERIAL', $pcOptions1);
        $this->assertEquals('ROBIN', $pcOptions1['MATERIAL']);
        $this->assertEquals('SP001.9', $pcOptions1['productNumber']);
        $pcOptions2 = $pouches->getAt(1)->getPayload()[PouchBundleCartProcessor::OPTIONS_KEY];
        $this->assertArrayHasKey('SIZE', $pcOptions2);
        $this->assertArrayHasKey('MATERIAL', $pcOptions2);
        $this->assertEquals('L', $pcOptions2['SIZE']);
        $this->assertEquals('MARTHA', $pcOptions2['MATERIAL']);
        $this->assertEquals('SP001.9', $pcOptions2['productNumber']);
        //TODO the rest
    }
}
