<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Kuniva\PackiroConfigurator\Core\Service\AccessoryService;
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
class UpsertProLibItemRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibItemRepository,
        private AccessoryService $accessoryService
    ) {
    }

    /**
     * @Route(path="/store-api/pro-lib-item", name="store-api.pro-lib-item.create", methods={"POST"}, defaults={"proLibItemId"=null, "_loginRequired"=true})
     * @Route(path="/store-api/pro-lib-item/{proLibItemId}", name="store-api.pro-lib-item.update", methods={"PATCH"}, defaults={"_loginRequired"=true})
     */
    public function upsert(?string $proLibItemId, RequestDataBag $data, SalesChannelContext $context, CustomerEntity $customer): ProLibItemRouteResponse
    {
        if (!$proLibItemId) {
            $proLibItemId = Uuid::randomHex();
        } else {
            $this->validateProLibItem($proLibItemId, $context, $customer);
        }

        $payload = [
            'id' => $proLibItemId,
            'proLibGroupId' => $data->get('proLibGroupId'),
            'productId' => $data->get('productId'),
            'name' => $data->get('name'),
            'note' => $data->get('note'),
            'artworkId' => $data->get('artworkId'),
            'artworkState' => $data->get('artworkState'),
            'artworkAccess' => $data->get('artworkAccess'),
            'version' => $data->get('version'), // null = draft, 0 = create new version
            'packshotPurchased' => $data->get('packshotPurchased'),
            'payload' => $data->all(),
            'artworkStatus' => $data->get('artworkStatus'),
            'expertCheckApproved' => $data->get('expertCheckApproved'),
        ];

        if ($data->get('accessoryOptions')) {
            $payload['accessoryOptions'] = $this->accessoryService->getAccessoryOptionIdsByProperty(
                $data->get('accessoryOptions')->all(),
                'technicalName',
                true
            );
        }

        $payload = $this->fixEmptyPayload($payload);

        $this->proLibItemRepository->upsert([$payload], $context->getContext());

        $criteria = new Criteria([$proLibItemId]);

        /** @var ProLibItemEntity $entity */
        $entity = $this->proLibItemRepository->search($criteria, $context->getContext())->first();

        return new ProLibItemRouteResponse($entity);
    }
}
