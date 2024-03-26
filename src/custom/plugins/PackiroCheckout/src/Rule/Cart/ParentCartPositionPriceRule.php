<?php

declare(strict_types=1);

namespace Packiro\Checkout\Rule\Cart;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleComparison;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

class ParentCartPositionPriceRule extends Rule
{
    private const RULE_NAME = 'parentCartPositionPrice';

    protected float $amount;

    protected string $operator;

    /**
     * @internal
     */
    public function __construct(string $operator = self:: OPERATOR_EQ, ?float $amount = null)
    {
        parent::__construct();

        $this->operator = $operator;
        $this->amount = (float) $amount;
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }
        $cart = $scope->getCart();
        /** @var SplitCartExtension $splitCartExtension */
        $splitCartExtension = $cart->getExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME);
        if ($splitCartExtension && $splitCartExtension->getParentCartPrice()) {
            return RuleComparison::numeric(
                $splitCartExtension->getParentCartPrice()->getPositionPrice(),
                $this->amount,
                $this->operator,
            );
        }

        return RuleComparison::numeric($cart->getPrice()->getPositionPrice(), $this->amount, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'amount' => RuleConstraints::float(),
            'operator' => RuleConstraints::numericOperators(false),
        ];
    }

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->numberField('amount');
    }
}
