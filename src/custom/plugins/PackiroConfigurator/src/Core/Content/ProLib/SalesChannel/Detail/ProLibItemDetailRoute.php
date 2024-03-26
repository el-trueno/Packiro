<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel\Detail;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel\ProLibValidationTrait;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class ProLibItemDetailRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private SalesChannelRepositoryInterface $proLibItemRepository
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-item/{proLibItemId}", name="store-api.pro-lib-item.detail", methods={"GET"}, defaults={"_loginRequired"=true})
     */
    public function load(string $proLibItemId, SalesChannelContext $context, CustomerEntity $customer): ProLibItemDetailRouteResponse
    {
        $this->validateProLibItem($proLibItemId, $context, $customer);

        $criteria = new Criteria([$proLibItemId]);
        $criteria->addAssociation('proLibGroup');
        $criteria->addAssociation('product.options.group');
        $criteria->addAssociation('accessoryOptions.tags');
        $criteria->addAssociation('proLibItemOrders.order.deliveries');

        /** @var ProLibItemEntity $proLibItem */
        $proLibItem = $this->proLibItemRepository->search($criteria, $context)->first();

        return new ProLibItemDetailRouteResponse($proLibItem);
    }
}
