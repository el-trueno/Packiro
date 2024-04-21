<?php

declare(strict_types=1);

namespace Packiro\Payment\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Error\IncompleteLineItemError;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Framework\Util\FloatComparator;
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
        foreach ($lineItems as $lineItem) {
            $oldDefinition = $lineItem->getPriceDefinition();

            if (!$oldDefinition instanceof QuantityPriceDefinition) {
                continue;
            }
            $newDefinition = new QuantityPriceDefinition(
                FloatComparator::cast($oldDefinition->getPrice() * ((100 + $commissionPercent)/100)),
                $oldDefinition->getTaxRules(),
                $oldDefinition->getQuantity());

            try {
                $calculatedPrice = $this->calculator->calculate(
                    $newDefinition,
                    $context);

            } catch (CartException) {
                $original->remove($lineItem->getId());
                $toCalculate->addErrors(new IncompleteLineItemError($lineItem->getId(), 'price'));

                continue;
            }
            $lineItem->setPrice($calculatedPrice);

            $toCalculate->add($lineItem);
        }
    }

    private function prepareKeyForSystemConfigService(string $nameOfCommission): string
    {
        $nameOfCommission = ucwords($nameOfCommission);
        $nameOfCommission = str_replace(' ', '', $nameOfCommission);

        return sprintf('PackiroPayment.config.%sCommission', lcfirst($nameOfCommission));
    }
}

