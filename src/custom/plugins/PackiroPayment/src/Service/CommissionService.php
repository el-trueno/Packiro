<?php

declare(strict_types=1);

namespace Packiro\Payment\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Error\IncompleteLineItemError;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\Util\FloatComparator;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CommissionService implements CommissionServiceInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly QuantityPriceCalculator $calculator,
    ) {}

    public function addCommission(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context): void {
        if (!$context->getCustomer()) {
            return;
        }
        $commissionPercentKey = $this->prepareKeyForSystemConfigService($context->getCustomer()->getDefaultPaymentMethod()->getName());
        $commissionPercent = $this->systemConfigService->get($commissionPercentKey);
        if (!$commissionPercent) {
            return;
        }
        $lineItems = $original->getLineItems();
        $commissionLineItem = new LineItem(
            Uuid::randomHex(),
            'test',
            null,
            1
        );
        $commissionLineItem->setLabel('Commission');
        $sumCalculatedPrices = null;
        foreach ($lineItems as $lineItem) {
            $oldDefinition = $lineItem->getPriceDefinition();

            if (!$oldDefinition instanceof QuantityPriceDefinition) {
                continue;
            }
            $newDefinition = new QuantityPriceDefinition(
                FloatComparator::cast($oldDefinition->getPrice() * $commissionPercent/100),
                $oldDefinition->getTaxRules(),
                $oldDefinition->getQuantity());

             try {
               $sumCalculatedPrices += $this->calculator->calculate(
                    $newDefinition,
                    $context)->getTotalPrice();

            } catch (CartException) {
                $original->remove($lineItem->getId());
                $toCalculate->addErrors(new IncompleteLineItemError($lineItem->getId(), 'price'));

                continue;
            }
           // $lineItem->setPrice($calculatedPrice);

           // $toCalculate->add($lineItem);
        }
        $calculatedDefinition = new QuantityPriceDefinition(
            $sumCalculatedPrices ?? 0,
            new TaxRuleCollection(),
            1);
        $calculatedPrice = new CalculatedPrice(
            0, $sumCalculatedPrices ?? 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 1);
        $commissionLineItem->setPriceDefinition($calculatedDefinition)->setPrice($calculatedPrice);
        $toCalculate->add($commissionLineItem);
    }

    private function prepareKeyForSystemConfigService(string $nameOfCommission): string
    {
        $nameOfCommission = ucwords($nameOfCommission);
        $nameOfCommission = str_replace(' ', '', $nameOfCommission);

        return sprintf('PackiroPayment.config.%sCommission', lcfirst($nameOfCommission));
    }
}

