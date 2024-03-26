<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderEntity;
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
class UpsertProLibItemOrderRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibItemOrderRepository,
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-item-order", name="store-api.pro-lib-item-order.create", methods={"POST"}, defaults={"proLibItemOrderId"=null, "_loginRequired"=true})
     * @Route(path="/store-api/pro-lib-item-order/{proLibItemOrderId}", name="store-api.pro-lib-item-order.update", methods={"PATCH"}, defaults={"_loginRequired"=true})
     */
    public function upsert(?string $proLibItemOrderId, RequestDataBag $data, SalesChannelContext $context, CustomerEntity $customer): ProLibItemOrderRouteResponse
    {
        if (!$proLibItemOrderId) {
            $proLibItemOrderId = Uuid::randomHex();
        } else {
            $this->validateProLibItemOrder($proLibItemOrderId, $context, $customer);
        }

        $payload = [
            'id' => $proLibItemOrderId,
            'quantity' => $data->get('quantity'),
            'proLibItemId' => $data->get('proLibItemId'),
        ];

        $payload = $this->fixEmptyPayload($payload);

        if ($data->get("proLibItemId", false) === null) {
            $payload["proLibItemId"] = null;
        }

        $this->proLibItemOrderRepository->upsert([$payload], $context->getContext());

        $criteria = new Criteria([$proLibItemOrderId]);

        /** @var ProLibItemOrderEntity $entity */
        $entity = $this->proLibItemOrderRepository->search($criteria, $context->getContext())->first();

        return new ProLibItemOrderRouteResponse($entity);
    }
}
