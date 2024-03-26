<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibGroupEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class UpsertProLibGroupRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibGroupRepository
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-group", name="store-api.pro-lib-group.create", methods={"POST"}, defaults={"proLibGroupId"=null, "_loginRequired"=true})
     * @Route(path="/store-api/pro-lib-group/{proLibGroupId}", name="store-api.pro-lib-group.update", methods={"PATCH"}, defaults={"_loginRequired"=true})
     */
    public function upsert(?string $proLibGroupId, RequestDataBag $data, SalesChannelContext $context, CustomerEntity $customer): ProLibGroupRouteResponse
    {
        if (!$proLibGroupId) {
            $proLibGroupId = Uuid::randomHex();
        } else {
            $this->validateProLibGroup($proLibGroupId, $context, $customer);
        }

        $payload = [
            'id' => $proLibGroupId,
            'customerId' => $customer->getId(),
            'name' => $data->get('name'),
            'customerReference' => $data->get('customerReference'),
        ];

        $payload = $this->fixEmptyPayload($payload);

        $this->proLibGroupRepository->upsert([$payload], $context->getContext());

        $criteria = new Criteria([$proLibGroupId]);

        /** @var ProLibGroupEntity $entity */
        $entity = $this->proLibGroupRepository->search($criteria, $context->getContext())->first();

        return new ProLibGroupRouteResponse($entity);
    }
}
