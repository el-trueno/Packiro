<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\Cart;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\CartCollection;
use Packiro\Checkout\Event\Cart\CheckoutOrderPlacedEvent;
use Packiro\Checkout\Event\Cart\ItemAddedToSplitCartEvent;
use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Packiro\Checkout\Service\Order\OrderServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartItemAddRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartOrderRouteResponse;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CartOrderRoute extends AbstractCartOrderRoute
{
    public function __construct(
        private AbstractCartOrderRoute $decorated,
        private CartReaderInterface $cartReader,
        private OrderServiceInterface $orderService,
        private AbstractCartPersister $cartPersister,
        private EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private AbstractCartItemAddRoute $cartItemAAddRoute,
        private CartServiceInterface $cartService,
    ) {
    }

    public function getDecorated(): AbstractCartOrderRoute
    {
        return $this->decorated;
    }

    /**
     * @Route("/store-api/checkout/order", name="store-api.checkout.cart.order", methods={"POST"}, defaults={"_loginRequired"=true, "_loginRequiredAllowGuest"=true})
     */
    public function order(Cart $cart, SalesChannelContext $context, RequestDataBag $data): CartOrderRouteResponse
    {
        $splitCartsCollection = $this->cartReader->findSplitCarts($cart->getToken());

        if ($splitCartsCollection->count() === 0) {
            if (!$this->isPouchBundleCart($cart)) {
                return $this->createDefaultOrder($cart, $context, $data);
            }

            $splitCartsCollection = $this->createMissingSplitCarts($cart, $context);
        }

        $checkoutId = Random::getAlphanumericString(40);
        $orderCollection = new OrderCollection();
        /** @var Cart $splitCart */
        foreach ($splitCartsCollection as $splitCart) {
            $splitOrderResponse = $this->decorated->order($splitCart, $context, $data);

            $this->orderService->executePostSplitOrderCreationProcess(
                $splitCart->getToken(),
                $splitOrderResponse->getOrder(),
                $checkoutId,
                $context,
            );

            $orderCollection->add($splitOrderResponse->getOrder());
        }

        $this->cartPersister->delete($cart->getToken(), $context);

        $this->eventDispatcher->dispatch(new CheckoutOrderPlacedEvent(
            $orderCollection,
            // OrderCustomer for all split orders is supposed to be the same
            $orderCollection->first()->getOrderCustomer(),
            $checkoutId,
            $context,
        ));

        $splitOrderResponse->getOrder()->setExtensions([
            'splitOrders' => new ArrayStruct([
                'splitOrderIds' => $orderCollection->getIds(),
                'checkoutId' => $checkoutId,
            ]),
        ]);

        return $splitOrderResponse;
    }

    private function createDefaultOrder(
        Cart $cart,
        SalesChannelContext $context,
        RequestDataBag $data
    ): CartOrderRouteResponse {
        $cartOrderResponse = $this->decorated->order($cart, $context, $data);

        $request = Request::createFromGlobals();
        $this->logger->info('Default Order Creation', [
            'token' => $cart->getToken(),
            'orderId' => $cartOrderResponse->getOrder()->getId(),
            'salesChannelContext' => [
                'token' => $context->getToken(),
                'customerId' => $context->getCustomerId(),
            ],
            'requestUri' => $request->getRequestUri(),
            'headers' => [
                'sw-context-token' => $request->headers->get('sw-context-token'),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        $this->eventDispatcher->dispatch(new CheckoutOrderPlacedEvent(
            new OrderCollection([$cartOrderResponse->getOrder()]),
            $cartOrderResponse->getOrder()->getOrderCustomer(),
            null,
            $context,
        ));

        return $cartOrderResponse;
    }

    private function isPouchBundleCart(Cart $cart): bool
    {
        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->getType() === PouchBundleCartProcessor::TYPE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Temporary fallback solution to mitigate effect of missing checkout id issue
     * @link https://packiro.atlassian.net/browse/SHOP-1005
     */
    private function createMissingSplitCarts(Cart $parentCart, SalesChannelContext $salesChannelContext,): CartCollection
    {
        $request = Request::createFromGlobals();
        $this->logger->emergency('Pouch bundle cart without split carts is detected', [
            'token' => $parentCart->getToken(),
            'salesChannelContext' => [
                'token' => $salesChannelContext->getToken(),
                'customerId' => $salesChannelContext->getCustomerId(),
            ],
            'requestUri' => $request->getRequestUri(),
            'headers' => [
                'sw-context-token' => $request->headers->get('sw-context-token'),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        foreach ($parentCart->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== 'pc_pouch_bundle') {
                continue;
            }

            $pcCart = $this->cartService->getSplitCart($lineItem, $salesChannelContext);
            $this->cartItemAAddRoute->add(Request::createFromGlobals(), $pcCart, $salesChannelContext, [$lineItem]);

            $this->eventDispatcher->dispatch(new ItemAddedToSplitCartEvent($lineItem, $pcCart, $salesChannelContext));
        }

        return $this->cartReader->findSplitCarts($parentCart->getToken());
    }
}
