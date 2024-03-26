<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\Cart;

use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartLoadRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CartLoadRoute extends AbstractCartLoadRoute
{
    public function __construct(
        private AbstractCartLoadRoute $decorated,
        private CartServiceInterface $cartService,
    ) {
    }

    public function getDecorated(): AbstractCartLoadRoute
    {
        return $this->decorated;
    }

    /**
     * @Route("/store-api/checkout/cart", name="store-api.checkout.cart.read", methods={"GET", "POST"})
     */
    public function load(Request $request, SalesChannelContext $context): CartResponse
    {
        $token = $request->get('token', $context->getToken());
        $this->cartService->recalculateSplitCarts($token, $context);

        return $this->decorated->load($request, $context);
    }
}
