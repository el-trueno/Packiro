<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\Cart;

use Packiro\Checkout\Factory\Cart\AccessoryFactoryInterface;
use Packiro\Checkout\Service\Cart\LineItemAccessoryServiceInterface;
use Packiro\Checkout\Struct\Accessory\AccessoryStructCollection;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/store-api/checkout/cart", defaults={"_routeScope"={"store-api"}})
 */
class CartAccessoryController
{
    public function __construct(
        private readonly LineItemAccessoryServiceInterface $lineItemAccessoryService,
        private readonly AccessoryFactoryInterface $accessoryFactory,
    ) {
    }

    /**
     * @Route("/{lineItemId}/accessory", name="store-api.packiro.checkout.cart.accessory.add", methods={"POST"})
     */
    public function addAccessory(
        string $lineItemId,
        Request $request,
        Cart $cart,
        SalesChannelContext $salesChannelContext,
    ): CartResponse {
        $cart = $this->lineItemAccessoryService->addAccessoriesToLineItem(
            $lineItemId,
            $cart,
            $this->createAccessoryCollection($request),
            $salesChannelContext,
        );

        return new CartResponse($cart);
    }

    /**
     * @Route("/{lineItemId}/accessory", name="store-api.packiro.checkout.cart.accessory.update", methods={"PATCH"})
     */
    public function updateAccessory(
        string $lineItemId,
        Request $request,
        Cart $cart,
        SalesChannelContext $salesChannelContext,
    ): CartResponse {
        $cart = $this->lineItemAccessoryService->updateAccessoriesInLineItem(
            $lineItemId,
            $cart,
            $this->createAccessoryCollection($request),
            $salesChannelContext,
        );

        return new CartResponse($cart);
    }

    /**
     * @Route("/{lineItemId}/accessory", name="store-api.packiro.checkout.cart.accessory.delete", methods={"DELETE"})
     */
    public function deleteAccessory(
        string $lineItemId,
        Request $request,
        Cart $cart,
        SalesChannelContext $salesChannelContext,
    ): CartResponse {
        $cart = $this->lineItemAccessoryService->deleteAccessoriesFromLineItem(
            $lineItemId,
            $cart,
            $this->createAccessoryCollection($request),
            $salesChannelContext,
        );

        return new CartResponse($cart);
    }

    private function createAccessoryCollection(Request $request): AccessoryStructCollection
    {
        if (!$request->request->has('accessoryOptions')) {
            throw new BadRequestHttpException('Missing "accessoryOptions" key in the body.');
        }

        return $this->accessoryFactory->createAccessoryStructCollection($request->request->all('accessoryOptions'));
    }
}
