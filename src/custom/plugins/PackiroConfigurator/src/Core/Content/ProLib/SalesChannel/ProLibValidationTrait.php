<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Validate ownership of current customer
 */
trait ProLibValidationTrait
{
    private function fixEmptyPayload(array $payload): array
    {
        foreach ($payload as $k => $v) {
            if ($v === null) {
                unset($payload[$k]);
            }
        }

        return $payload;
    }

    private function validateProLibGroup(string $id, SalesChannelContext $context, CustomerEntity $customer): void
    {
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));

        if (\count($this->proLibGroupRepository->searchIds($criteria, $context->getContext())->getIds())) {
            return;
        }

        throw new \Exception('not valid pro lib group');
    }

    private function validateProLibItem(string $id, SalesChannelContext $context, CustomerEntity $customer): void
    {
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter('proLibGroup.customerId', $customer->getId()));

        /* Check if proLibItemRepository is SalesChannelRepository */
        if ($this->proLibItemRepository instanceof SalesChannelRepository) {
            if (\count($this->proLibItemRepository->searchIds($criteria, $context)->getIds())) {
                return;
            }
        } else {
            if (\count($this->proLibItemRepository->searchIds($criteria, $context->getContext())->getIds())) {
                return;
            }
        }

        throw new \Exception('not valid pro lib item');
    }

    private function validateProLibItemOrder(string $id, SalesChannelContext $context, CustomerEntity $customer): void
    {
        return;
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('order.orderCustomer.customerId', $customer->getId()),
        ]));

        if (\count($this->proLibItemOrderRepository->searchIds($criteria, $context->getContext())->getIds())) {
            return;
        }

        throw new \Exception('not valid pro lib item order');
    }

    private function validateOrderLineItem(string $id, SalesChannelContext $context, CustomerEntity $customer): void
    {
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter('order.orderCustomer.customerId', $customer->getId()));

        if (\count($this->orderLineItemRepository->searchIds($criteria, $context->getContext())->getIds())) {
            return;
        }

        throw new \Exception('not valid order line item');
    }
}
