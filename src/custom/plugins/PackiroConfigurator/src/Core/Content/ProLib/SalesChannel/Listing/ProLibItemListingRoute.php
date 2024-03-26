<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel\Listing;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class ProLibItemListingRoute
{
    public function __construct(
        private ProLibItemListingLoader $listingLoader,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @Entity("pc_pro_lib_item")
     * @Route("/store-api/pro-lib-item-listing/{proLibGroupId}", name="store-api.pro-lib-item.listing", methods={"POST"}, defaults={"_loginRequired"=true})
     */
    public function load(string $proLibGroupId, Request $request, SalesChannelContext $context, Criteria $criteria, CustomerEntity $customer): ProLibItemListingRouteResponse
    {
        $criteria->setTitle('pro-lib-item-listing-route::loading');

        return new ProLibItemListingRouteResponse($this->listingLoader->load($proLibGroupId, $criteria, $context, $customer));
    }
}
