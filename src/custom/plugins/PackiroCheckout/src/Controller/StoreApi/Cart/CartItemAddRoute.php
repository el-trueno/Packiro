<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\Cart;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\CartCollection;
use Packiro\Checkout\Event\Cart\ItemAddedToSplitCartEvent;
use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartItemAddRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CartItemAddRoute extends AbstractCartItemAddRoute
{
    public function __construct(
        private AbstractCartItemAddRoute $decorated,
        private LineItemFactoryRegistry $lineItemFactoryRegistry,
        private CartServiceInterface $cartService,
        private EventDispatcherInterface $eventDispatcher,
        private CartCalculator $cartCalculator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getDecorated(): AbstractCartItemAddRoute
    {
        return $this->decorated;
    }

    /**
     * @Route("/store-api/checkout/cart/line-item", name="store-api.checkout.cart.add", methods={"POST"})
     */
    public function add(Request $request, Cart $cart, SalesChannelContext $context, ?array $items): CartResponse
    {
        if ($items === null) {
            $items = [];

            /** @var array<mixed> $item */
            foreach ($request->request->all('items') as $item) {
                $items[] = $this->lineItemFactoryRegistry->create($item, $context);
            }
        }

        $this->logger->info('Line Items Add', [
            'token' => $cart->getToken(),
            'items' => array_map(fn(LineItem $lineItem) => [
                'id' => $lineItem->getId(),
                'referencedId' => $lineItem->getReferencedId(),
                'type' => $lineItem->getType(),
            ], $items),
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

        $cartResponse = $this->decorated->add($request, $cart, $context, $items);
        // In case of added promotions
        $this->cartService->recalculateSplitCarts($cartResponse->getCart()->getToken(), $context);

        $splitCartCollection = new CartCollection();
        /** @var LineItem $item */
        foreach ($items as $item) {
            if ($item->getType() !== 'pc_pouch_bundle') {
                continue;
            }

            $pcCart = $this->cartService->getSplitCart($item, $context);

            $splitCartResponse = $this->decorated->add($request, $pcCart, $context, [$item]);
            $splitCartCollection->add($splitCartResponse->getCart());

            $this->logger->info('Item added to a split cart', [
                'splitCartToken' => $pcCart->getToken(),
            ]);

            $this->eventDispatcher->dispatch(new ItemAddedToSplitCartEvent($item, $pcCart, $context));
        }
        $cart = $this->cartCalculator->calculate($cart, $context);

        $this->logger->info('Line Items Added result', [
            'token' => $cart->getToken(),
            'itemsInCart' => array_keys($cart->getLineItems()->getElements()),
        ]);

        foreach ($splitCartCollection as $splitCart) {
            $this->logger->info('Line Items Added to split cart result', [
                'token' => $splitCart->getToken(),
                'itemsInCart' => array_keys($splitCart->getLineItems()->getElements()),
            ]);
        }

        return new CartResponse($cart);
    }
}
