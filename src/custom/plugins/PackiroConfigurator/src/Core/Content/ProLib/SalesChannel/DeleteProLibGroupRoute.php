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
class DeleteProLibGroupRoute
{
    use ProLibValidationTrait;

    public function __construct(
        private EntityRepositoryInterface $proLibGroupRepository
    ) {
    }

    /**
    * @Route(path="/store-api/pro-lib-group/{proLibGroupId}", name="store-api.pro-lib-group.delete", methods={"DELETE"}, defaults={"_loginRequired"=true})
    */
    public function delete(string $proLibGroupId, SalesChannelContext $context, CustomerEntity $customer): NoContentResponse
    {
        $this->validateProLibGroup($proLibGroupId, $context, $customer);

        $this->proLibGroupRepository->delete([['id' => $proLibGroupId]], $context->getContext());

        return new NoContentResponse();
    }
}
