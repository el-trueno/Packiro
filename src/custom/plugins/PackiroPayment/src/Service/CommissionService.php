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

class CommissionService implements CommissionServiceInterface
{
    public function __construct(
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

        $commissionPercent = $context->getPaymentMethod()->getExtension('paymentMethodExtension')?->getCommission();

        if (!$commissionPercent) {
            return;
        }
        $lineItems = $original->getLineItems();
        if (count($lineItems) === 0) {
            return;
        }
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
        }
        $calculatedDefinition = new QuantityPriceDefinition(
            0,
            new TaxRuleCollection(),
            1);
        $calculatedPrice = new CalculatedPrice(
            0, $sumCalculatedPrices ?? 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 1);

        $commissionLineItem->setPriceDefinition($calculatedDefinition)->setPrice($calculatedPrice);

        $toCalculate->add($commissionLineItem);
    }
}

