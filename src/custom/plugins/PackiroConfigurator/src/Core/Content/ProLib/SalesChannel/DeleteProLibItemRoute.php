<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class DeleteProLibItemRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibItemRepository
    ) {
    }

    /**
    * @Route(path="/store-api/pro-lib-item/{proLibItemId}", name="store-api.pro-lib-item.delete", methods={"DELETE"}, defaults={"_loginRequired"=true})
    */
    public function delete(string $proLibItemId, SalesChannelContext $context, CustomerEntity $customer): NoContentResponse
    {
        $this->validateProLibItem($proLibItemId, $context, $customer);

        $this->proLibItemRepository->delete([['id' => $proLibItemId]], $context->getContext());

        return new NoContentResponse();
    }
}
