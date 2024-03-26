<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CloneProLibItemRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibItemRepository
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-item/clone/{proLibItemId}", name="store-api.pro-lib-item.clone", methods={"GET"}, defaults={"_loginRequired"=true})
     */
    public function clone(string $proLibItemId, SalesChannelContext $context, CustomerEntity $customer): ProLibItemRouteResponse
    {
        $this->validateProLibItem($proLibItemId, $context, $customer);

        $this->proLibItemRepository->clone($proLibItemId, $context->getContext(), null, new CloneBehavior([
            'version' => null,
        ], false));

        $criteria = new Criteria([$proLibItemId]);

        /** @var ProLibItemEntity $entity */
        $entity = $this->proLibItemRepository->search($criteria, $context->getContext())->first();

        return new ProLibItemRouteResponse($entity);
    }
}
