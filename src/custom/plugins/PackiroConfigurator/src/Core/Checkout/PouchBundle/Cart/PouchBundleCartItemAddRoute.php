<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart;

use Kuniva\PackiroConfigurator\Core\Service\AccessoryService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartItemAddRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class PouchBundleCartItemAddRoute extends AbstractCartItemAddRoute
{
    public function __construct(
        private AbstractCartItemAddRoute $decorated,
        private RequestStack $requestStack,
        private ?AccessoryService $accessoryService = null
    ) {
    }

    public function getDecorated(): AbstractCartItemAddRoute
    {
        return $this->decorated;
    }

    public function add(Request $request, Cart $cart, SalesChannelContext $salesChannelContext, ?array $items): CartResponse
    {
        /* Repairing missing stuff in Shopware */
        if (!$request->request->all()) {
            $request = $this->requestStack->getCurrentRequest();
        }

        /* lineItems (Storefront), items (store-api) */
        if ($request->request->get('lineItems')) {
            $lineItems = $request->request->get('lineItems');
            foreach ($lineItems as &$lineItem) {
                $lineItem['quantity'] = (int) $lineItem['quantity'];
                $lineItem['stackable'] = false;
                $lineItem['removable'] = false;

                /* Override type if accessoryGroups exists */
                if (isset($lineItem['accessoryGroups'])) {
                    $lineItem['type'] = PouchBundleCartProcessor::TYPE;
                }

                if (isset($lineItem['accessoryOptions'])) {
                    $lineItem['type'] = PouchBundleCartProcessor::TYPE;
                }
            }

            $request->request->set('items', $lineItems);
        }

        return $this->decorated->add($this->requestStack->getCurrentRequest(), $cart, $salesChannelContext, $items);
    }
}
