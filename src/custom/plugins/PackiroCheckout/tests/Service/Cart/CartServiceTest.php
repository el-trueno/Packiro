<?php

declare(strict_types=1);

namespace Packuro\Checkout\Tests\Service\Cart;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Packiro\Checkout\Exception\UsedCartException;
use Packiro\Checkout\Factory\Cart\LineItemFactoryInterface;
use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Packiro\Checkout\Reader\Promotion\PromotionReaderInterface;
use Packiro\Checkout\Service\Cart\CartService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\GrossPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\NetPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartServiceTest extends TestCase
{
    /**
     * @dataProvider switchCartInContextDataProvider
     */
    public function testSwitchCartInContextWithCorrectDataSwitchesCart(
        string $contextToken,
        ?string $cartToSwitchToken,
        bool $throwException,
    ): void {
        // Prepare
        $checkoutService = new CartService(
            $this->createCartPersister($throwException),
            $this->createSalesChannelContextPersister(),
            $this->createCartReader(),
            $this->createPromotionReader(),
            $this->createLineItemFactory(),
            $this->createCartCalculator(),
            $this->createQuantityPriceCalculator(),
            $this->createDeliveryCalculator()
        );

        // Execute
        $result = $checkoutService->switchCartInContext($cartToSwitchToken, $this->createSalesChannelContext($contextToken));

        // Assert
        if ($throwException) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
            $this->assertNotEquals($contextToken, $result);
        }
    }

    /**
     * @return array<array<string|null>>
     */
    public function switchCartInContextDataProvider(): array
    {
        return [
            [Random::getAlphanumericString(32), Random::getAlphanumericString(32), true],
            [Random::getAlphanumericString(32), null, true],
            [Random::getAlphanumericString(32), Random::getAlphanumericString(32), false],
            [Random::getAlphanumericString(32), null, false],
        ];
    }

    public function testSwitchCartInContextWithOccupiedTokenThrowsException(): void
    {
        // Prepare
        $checkoutService = new CartService(
            $this->createCartPersister(),
            $this->createSalesChannelContextPersister(false),
            $this->createCartReader(),
            $this->createPromotionReader(),
            $this->createLineItemFactory(),
            $this->createCartCalculator(),
            $this->createQuantityPriceCalculator(),
            $this->createDeliveryCalculator(),
        );

        // Execute
        $this->expectException(UsedCartException::class);

        $checkoutService->switchCartInContext(
            Random::getAlphanumericString(32),
            $this->createSalesChannelContext(Random::getAlphanumericString(32))
        );
    }

    /**
     * @dataProvider removeDuplicatedDeliveryCostsDataProvider
     */
    public function testRemoveDuplicatedDeliveryCosts(Cart $originalCart, bool $shouldRemoveDeliveryDuplications): void
    {
        // Prepare
        $checkoutService = new CartService(
            $this->createCartPersister(),
            $this->createSalesChannelContextPersister(),
            $this->createCartReader(),
            $this->createPromotionReader(),
            $this->createLineItemFactory(),
            $this->createCartCalculator(),
            $this->createQuantityPriceCalculator(),
            $this->createDeliveryCalculator(),
        );

        // Execute
        $resultedCart = $checkoutService->removeDuplicatedDeliveryCosts($originalCart, $this->createSalesChannelContext(''));

        // Assert
        $deliveryCostsApplied = false;
        foreach ($resultedCart->getLineItems() as $parentLineItem) {
            /** @var LineItem $deliveryLineItem */
            $deliveryLineItem = $parentLineItem->getChildren()->filterType(PouchBundleCartProcessor::TYPE_DELIVERY_TIME)->first();
            $deliveryUnitPrice = $deliveryLineItem->getPrice()->getUnitPrice();
            if ($deliveryCostsApplied) {
                if ($shouldRemoveDeliveryDuplications) {
                    $this->assertEquals(0.0, $deliveryUnitPrice, 'Duplicated delivery costs are not removed');
                } else {
                    $this->assertGreaterThan(0.0, $deliveryUnitPrice, 'No delivery costs should be removed');
                }
            }
            if (!$deliveryCostsApplied && $deliveryUnitPrice > 0.0) {
                $deliveryCostsApplied = true;
            }
        }

        $this->assertTrue($deliveryCostsApplied, 'All delivery costs equal 0');
    }

    public function removeDuplicatedDeliveryCostsDataProvider(): \Generator
    {
        $originalCart = new Cart('', '');

        $itemReferenceId = Random::getAlphanumericString(32);
        $deliveryReferenceId = Random::getAlphanumericString(32);

        $originalCart->addLineItems(new LineItemCollection([
            $this->createPouchBundleLineItem($itemReferenceId, $deliveryReferenceId),
            $this->createPouchBundleLineItem($itemReferenceId, $deliveryReferenceId),
            $this->createPouchBundleLineItem($itemReferenceId, $deliveryReferenceId),
        ]));

        yield [$originalCart, true];

        $originalCart = new Cart('', '');
        $originalCart->addLineItems(new LineItemCollection([
            $this->createPouchBundleLineItem(Random::getAlphanumericString(32), $deliveryReferenceId),
            $this->createPouchBundleLineItem(Random::getAlphanumericString(32), $deliveryReferenceId),
            $this->createPouchBundleLineItem(Random::getAlphanumericString(32), $deliveryReferenceId),
        ]));

        yield [$originalCart, false];

        $originalCart = new Cart('', '');
        $originalCart->addLineItems(new LineItemCollection([
            $this->createPouchBundleLineItem($itemReferenceId, Random::getAlphanumericString(32)),
            $this->createPouchBundleLineItem($itemReferenceId, Random::getAlphanumericString(32)),
            $this->createPouchBundleLineItem($itemReferenceId, Random::getAlphanumericString(32)),
        ]));

        yield [$originalCart, false];
    }

    private function createPouchBundleLineItem(string $parentItemReferenceId, string $deliveryReferenceId): LineItem
    {
        $childrenLineItems = new LineItemCollection([
            $this->createLineItem(
                PouchBundleCartProcessor::TYPE_PSEUDO_PRODUCT,
                Random::getAlphanumericString(32),
                100
            ),
            $this->createLineItem(PouchBundleCartProcessor::TYPE_DELIVERY_TIME, $deliveryReferenceId, 500),
        ]);

        $parentLineItem = $this->createLineItem(
            PouchBundleCartProcessor::TYPE,
            $parentItemReferenceId,
            $childrenLineItems->getPrices()->sum()->getUnitPrice(),
        );
        $parentLineItem->setChildren($childrenLineItems);

        return $parentLineItem;
    }

    private function createLineItem(string $type, string $referenceId, float $price = 0.0): LineItem
    {
        $priceDefinition = new QuantityPriceDefinition($price, new TaxRuleCollection(), 1);
        $calculatedPrice = new CalculatedPrice($price, $price, new CalculatedTaxCollection(), new TaxRuleCollection(), 1);

        return (new LineItem(Random::getAlphanumericString(32), $type, $referenceId))
            ->setPriceDefinition($priceDefinition)
            ->setPrice($calculatedPrice);
    }

    private function createCartPersister(bool $throwException = false): AbstractCartPersister
    {
        $cartPersisterMock = $this->createMock(AbstractCartPersister::class);

        if ($throwException) {
            $cartPersisterMock->method('load')
                ->willThrowException(new CartTokenNotFoundException(''));
        } else {
            $cartPersisterMock->method('load')
                ->willReturn(new Cart('', ''));
        }

        return $cartPersisterMock;
    }

    private function createSalesChannelContextPersister(bool $loadEmptyContext = true): SalesChannelContextPersister
    {
        $salesChannelContextPersisterMock = $this->createMock(SalesChannelContextPersister::class);

        if ($loadEmptyContext) {
            $salesChannelContextPersisterMock->method('load')
                ->willReturn([]);
        } else {
            $salesChannelContextPersisterMock->method('load')
                ->willReturn(['token' => 'some token']);
        }

        return $salesChannelContextPersisterMock;
    }

    private function createSalesChannelContext(string $contextToken): SalesChannelContext
    {
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        $salesChannelContext->method('getToken')
            ->willReturn($contextToken);

        return $salesChannelContext;
    }

    private function createCartReader(): CartReaderInterface
    {
        return $this->createStub(CartReaderInterface::class);
    }

    private function createPromotionReader(): PromotionReaderInterface
    {
        return $this->createStub(PromotionReaderInterface::class);
    }

    private function createLineItemFactory(): LineItemFactoryInterface
    {
        return $this->createStub(LineItemFactoryInterface::class);
    }

    private function createCartCalculator(): CartCalculator
    {
        return $this->createStub(CartCalculator::class);
    }

    private function createQuantityPriceCalculator(): QuantityPriceCalculator
    {
        $priceRounding = new CashRounding();

        $taxCalculator = new TaxCalculator();

        return new QuantityPriceCalculator(
            new GrossPriceCalculator($taxCalculator, $priceRounding),
            new NetPriceCalculator($taxCalculator, $priceRounding),
        );
    }

    private function createDeliveryCalculator(): DeliveryCalculator
    {
        return $this->createStub(DeliveryCalculator::class);
    }
}
