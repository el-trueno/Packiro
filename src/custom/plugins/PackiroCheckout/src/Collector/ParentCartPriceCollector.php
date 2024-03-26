<?php

declare(strict_types=1);

namespace Packiro\Checkout\Collector;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ParentCartPriceCollector implements CartDataCollectorInterface
{
    public function __construct(
        private AbstractCartPersister $cartPersister,
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if (!$original->hasExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME)) {
            return;
        }

        /** @var SplitCartExtension $splitCartExtension */
        $splitCartExtension = $original->getExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME);

        try {
            $parentCart = $this->cartPersister->load($splitCartExtension->getParentToken(), $context);
        } catch (CartTokenNotFoundException $exception) {
            // The parent cart has already been deleted, we use data that are already saved in a split cart
            return;
        }

        $splitCartExtension->setParentCartPrice($parentCart->getPrice());
    }
}
