<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\Cart\Tax;

use Packiro\VatValidator\Service\VatValidationServiceInterface;
use Shopware\Core\Checkout\Cart\Tax\TaxDetector;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\TaxFreeConfig;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class TaxDetectorDecorator extends TaxDetector
{
    private ?array $countryTaxFreeRuleIds = null;

    public function __construct(
        private TaxDetector $decorated,
        private EntityRepositoryInterface $countryTaxFreeRuleRepository,
        private readonly VatValidationServiceInterface $vatValidationService,
    ) {
    }

    public function isNetDelivery(SalesChannelContext $context): bool
    {
        $countryTaxFreeRuleIds = $this->getCountryTaxFreeRuleIds($context);
        if (!$countryTaxFreeRuleIds) {
            return $this->decorated->isNetDelivery($context);
        }

        foreach ($countryTaxFreeRuleIds as $countryTaxFreeRuleId) {
            if (!array_key_exists('rule_id', $countryTaxFreeRuleId)) {
                continue;
            }
            if (in_array($countryTaxFreeRuleId['rule_id'], $context->getRuleIds())) {
                return $this->validateVatIds($context);
            }
        }

        return false;
    }

    public function isCompanyTaxFree(SalesChannelContext $context, CountryEntity $shippingLocationCountry): bool
    {
        $countryTaxFreeRuleIds = $this->getCountryTaxFreeRuleIds($context);
        if (!$countryTaxFreeRuleIds) {
            return $this->decorated->isCompanyTaxFree($context, $shippingLocationCountry);
        }

        foreach ($countryTaxFreeRuleIds as $countryTaxFreeRuleId) {
            if (!array_key_exists('rule_id', $countryTaxFreeRuleId)) {
                continue;
            }
            if (in_array($countryTaxFreeRuleId['rule_id'], $context->getRuleIds())) {
                /* This value need to be set */
                $shippingLocationCountry->setCompanyTax(new TaxFreeConfig(true));

                return $this->validateVatIds($context);
            }
        }

        return false;
    }

    private function validateVatIds(SalesChannelContext $context): bool
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            return false;
        }

        $vatIds = array_filter($customer->getVatIds() ?? []);
        if (empty($vatIds)) {
            return true;
        }

        $country = $context->getShippingLocation()->getCountry();
        foreach ($vatIds as $vatId) {
            if (!$this->vatValidationService->isVatIdValid($vatId, $country)) {
                return false;
            }
        }

        return true;
    }

    private function getCountryTaxFreeRuleIds(SalesChannelContext $context): array
    {
        if (!$this->countryTaxFreeRuleIds) {
            $countryIds = [];
            if ($context->getCustomer()) {
                $countryIds[] = $context->getCustomer()->getActiveBillingAddress()->getCountry()->getId();
            }
            $countryIds[] = $context->getShippingLocation()->getCountry()->getId();
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter('countryId', $countryIds));

            $countryRuleIds = $this->countryTaxFreeRuleRepository->searchIds($criteria, $context->getContext());

            $this->countryTaxFreeRuleIds = $countryRuleIds->getIds();
        }

        return $this->countryTaxFreeRuleIds;
    }
}
