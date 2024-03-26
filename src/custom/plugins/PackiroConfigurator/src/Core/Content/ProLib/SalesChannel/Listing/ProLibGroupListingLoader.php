<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel\Listing;

use Doctrine\DBAL\Connection;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibGroupCollection;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibGroupDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProLibGroupListingLoader
{
    public function __construct(
        private EntityRepositoryInterface $repository,
        private SystemConfigService $systemConfigService,
        private Connection $connection,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function load(Criteria $origin, SalesChannelContext $context, CustomerEntity $customer): EntitySearchResult
    {
        $criteria = clone $origin;

        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));

        $ids = $this->repository->searchIds($criteria, $context->getContext());

        $aggregations = $this->repository->aggregate($criteria, $context->getContext());

        if (empty($ids->getIds())) {
            return new EntitySearchResult(
                ProLibGroupDefinition::ENTITY_NAME,
                0,
                new ProLibGroupCollection(),
                $aggregations,
                $origin,
                $context->getContext()
            );
        }

        $criteria->addAssociation('proLibItems');
        $criteria->addAssociation('lastOrderItem');
        $criteria->addAssociation('lastVersionItem');
        $criteria->addAssociation('lastItemOrder');

        $entities = $this->repository->search($criteria, $context->getContext());

        $result = new EntitySearchResult(ProLibGroupDefinition::ENTITY_NAME, $ids->getTotal(), $entities->getEntities(), $aggregations, $origin, $context->getContext());
        $result->addState(...$ids->getStates());

        return $result;
    }
}
