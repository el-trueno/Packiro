<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\Cart\Rule;

use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class VatIdRule extends Rule
{
    public function __construct(
        protected bool $customerHasVatId = true
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        /** @var CheckoutRuleScope $scope */
        $customer = $scope->getSalesChannelContext()->getCustomer();
        if (!$customer) {
            return false;
        }

        if ($this->customerHasVatId === true) {
            return $this->checkCustomerHasVatId($customer);
        }

        return !$this->checkCustomerHasVatId($customer);
    }

    public function getConstraints(): array
    {
        return [
            'customerHasVatId' => [new NotNull(), new Type('bool')],
        ];
    }

    public function getName(): string
    {
        return 'customerHasVatIdRule';
    }

    private function checkCustomerHasVatId(CustomerEntity $customer): bool
    {
        $vatIds = $customer->getVatIds();
        if (!is_array($vatIds)) {
            return false;
        }

        return !empty(array_filter($vatIds));
    }
}
