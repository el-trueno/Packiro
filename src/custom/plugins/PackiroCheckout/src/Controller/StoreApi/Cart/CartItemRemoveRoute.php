<?php

declare(strict_types=1);

namespace Packiro\Checkout\Controller\StoreApi\Cart;

use Packiro\Checkout\Reader\Cart\CartReaderInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartItemRemoveRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CartItemRemoveRoute extends AbstractCartItemRemoveRoute
{
    public function __construct(
        private AbstractCartItemRemoveRoute $decorated,
        private CartReaderInterface $cartReader,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getDecorated(): AbstractCartItemRemoveRoute
    {
        return $this->decorated;
    }

    /**
     * @Route("/store-api/checkout/cart/line-item", name="store-api.checkout.cart.remove-item", methods={"DELETE"})
     */
    public function remove(Request $request, Cart $cart, SalesChannelContext $context): CartResponse
    {
        $cartResponse = $this->decorated->remove($request, $cart, $context);

        $lineItemIds = $request->get('ids');
        $splitCartsCollection = $this->cartReader->findSplitCarts($cart->getToken());
        /** @var Cart $splitCart */
        foreach ($splitCartsCollection as $splitCart) {
            $splitCartLineItemIds = array_keys($splitCart->getLineItems()->getElements());
            $lineItemsToDelete = array_intersect($lineItemIds, $splitCartLineItemIds);
            if (empty($lineItemsToDelete)) {
                continue;
            }

            $modifiedRequest = clone $request;
            $modifiedRequest->query->set('ids', $lineItemsToDelete);

            try {
                $this->decorated->remove($modifiedRequest, $splitCart, $context);
                $this->logger->info('Line Item Deleted from split cart', [
                    'token' => $splitCart->getToken(),
                    'customerId' => $context->getCustomerId(),
                    'ids' => $request->get('ids'),
                    'lineItemsToDelete' => $lineItemsToDelete,
                    'itemsInCart' => array_keys($splitCart->getLineItems()->getElements()),
                ]);
            } catch (CartException | LineItemNotFoundException $exception) {
                $this->logger->error('Line Item deletion from split cart failed', [
                    'token' => $splitCart->getToken(),
                    'customerId' => $context->getCustomerId(),
                    'ids' => $request->get('ids'),
                    'lineItemsToDelete' => $lineItemsToDelete,
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
            }
        }

        $this->logger->info('Line Item Deleted', [
            'token' => $cart->getToken(),
            'customerId' => $context->getCustomerId(),
            'ids' => $request->get('ids'),
            'itemsInCart' => array_keys($cart->getLineItems()->getElements()),
            'requestUri' => $request->getRequestUri(),
            'headers' => [
                'sw-context-token' => $request->headers->get('sw-context-token'),
                'referer' => $request->headers->get('referer'),
            ],
        ]);

        return $cartResponse;
    }
}
