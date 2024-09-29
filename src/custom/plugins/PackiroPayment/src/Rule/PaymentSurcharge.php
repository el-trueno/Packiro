<?php

declare(strict_types=1);

namespace Packiro\Payment\Rule;

use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\Type;

class PaymentSurcharge extends Rule
{
    final public const RULE_NAME = 'payment_surcharge';

    protected bool $isPaymentCommissionExists = true;

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getType() === 'test') {
                return $this->isPaymentCommissionExists;
            }
        }

        return !$this->isPaymentCommissionExists;
    }

    public function getConstraints(): array
    {
        return [
            'isPaymentCommissionExists' => [new Type('bool')]
        ];
    }
}
