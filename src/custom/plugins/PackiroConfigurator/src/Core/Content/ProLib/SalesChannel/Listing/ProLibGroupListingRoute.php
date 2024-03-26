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
class ProLibGroupListingRoute
{
    public function __construct(
        private ProLibGroupListingLoader $listingLoader,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @Entity("pc_pro_lib_group")
     * @Route("/store-api/pro-lib-group-listing", name="store-api.pro-lib-group.listing", methods={"POST"}, defaults={"_loginRequired"=true})
     */
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria, CustomerEntity $customer): ProLibGroupListingRouteResponse
    {
        $criteria->setTitle('pro-lib-group-listing-route::loading');

        return new ProLibGroupListingRouteResponse($this->listingLoader->load($criteria, $context, $customer));
    }
}
