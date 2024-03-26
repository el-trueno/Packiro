<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CreateProLibItemFromRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibItemRepository,
        private EntityRepositoryInterface $orderLineItemRepository,
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-item/create-from/{orderLineItemId}", name="store-api.pro-lib-item.create-from.v1", methods={"POST"}, defaults={"proLibGroupId"=null, "_loginRequired"=true})
     * @Route(path="/store-api/pro-lib-item/create-from/{orderLineItemId}/{proLibGroupId}", name="store-api.pro-lib-item.create-from.v2", methods={"POST"}, defaults={"_loginRequired"=true})
     */
    public function create(?string $orderLineItemId, ?string $proLibGroupId, RequestDataBag $data, SalesChannelContext $context, CustomerEntity $customer): ProLibItemRouteResponse
    {
        $proLibGroupId = $data->get('proLibGroupId', $proLibGroupId);

        $this->validateOrderLineItem($orderLineItemId, $context, $customer);

        $criteria = new Criteria([$orderLineItemId]);
        $criteria->addAssociation('children');

        /** @var OrderLineItemEntity $orderLineItem */
        $orderLineItem = $this->orderLineItemRepository->search($criteria, $context->getContext())->first();

        if (!$proLibGroupId) {
            $proLibGroupId = Uuid::randomHex();

            $proLibGroupPayload = [
                'id' => $proLibGroupId,
                'customerId' => $customer->getId(),
                'name' => $data->get('name') ?: $orderLineItem->getLabel(),
                'customerReference' => $data->get('customerReference'),
            ];
        } else {
            $this->validateProLibGroup($proLibGroupId, $context, $customer);

            $proLibGroupPayload = null;
        }

        $proLibItemId = Uuid::randomHex();

        $payload = [
            'id' => $proLibItemId,
            'proLibGroupId' => $proLibGroupId,
            'productId' => $orderLineItem->getReferencedId(),
            'name' => $data->get('name') ?: $orderLineItem->getLabel(),
            'accessoryOptions' => null, // TODO: Get by child line items
            'payload' => $data->all(),
            'proLibGroup' => $proLibGroupPayload,
        ];

        $this->proLibItemRepository->create([$payload], $context->getContext());

        $criteria = new Criteria([$proLibItemId]);
        $criteria->addAssociation('proLibGroup');

        /** @var ProLibItemEntity $entity */
        $entity = $this->proLibItemRepository->search($criteria, $context->getContext())->first();

        return new ProLibItemRouteResponse($entity);
    }
}
