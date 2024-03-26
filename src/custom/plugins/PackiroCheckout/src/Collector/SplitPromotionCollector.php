<?php

declare(strict_types=1);

namespace Packiro\Checkout\Collector;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Cart\SplitCartExtension;
use Packiro\Checkout\Service\Cart\CartServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SplitPromotionCollector implements CartDataCollectorInterface
{
    public function __construct(
        private CartServiceInterface $cartService,
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if (!$original->hasExtension(SplitCartExtension::EXTENSION_PROPERTY_NAME)) {
            return;
        }

        $discountLineItems = $this->cartService->getSplitDiscountLineItems($data, $original, $context);

        if (\count($discountLineItems) > 0) {
            $data->set(PromotionProcessor::DATA_KEY, new LineItemCollection($discountLineItems));
        } else {
            $data->remove(PromotionProcessor::DATA_KEY);
        }
    }
}
