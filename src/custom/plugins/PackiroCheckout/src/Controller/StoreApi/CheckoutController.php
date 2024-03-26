<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi;

use Packiro\Checkout\Exception\CheckoutException;
use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CheckoutController
{
    public function __construct(
        private CartServiceInterface $checkoutService,
    ) {
    }

    /**
     * @Route("/store-api/context/switch-cart", name="store-api.packiro.context.switch-cart", methods={"POST"})
     */
    public function switchCartInContext(RequestDataBag $data, SalesChannelContext $salesChannelContext): Response
    {
        if (!$salesChannelContext->getCustomer()) {
            throw CartException::customerNotLoggedIn();
        }

        $cartToSwitchToken = $data->get('cartToken');

        try {
            $oldCartToken = $this->checkoutService->switchCartInContext($cartToSwitchToken, $salesChannelContext);

            return new JsonResponse([
                'oldCartToken' => $oldCartToken,
                'newCartToken' => $salesChannelContext->getToken(),
            ]);
        } catch (CheckoutException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
