<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\Product\Cart;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Find and set the quantity based price definitions to any similar product
 */
class ProductStackProcessor implements CartDataCollectorInterface
{
    public function __construct(
        private AbstractProductPriceCalculator $priceCalculator,
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::product-stack::collect', function () use ($data, $original, $context, $behavior): void {
            $items = $this->getProducts($original->getLineItems());

            $lineItems = array_column($items, 'item');

            $stackQuantity = [];
            /** @var LineItem $lineItem */
            foreach ($lineItems as $lineItem) {
                if (empty($stackQuantity[$this->uniqueKey($lineItem)])) {
                    $stackQuantity[$this->uniqueKey($lineItem)] = $lineItem->getQuantity();
                } else {
                    $stackQuantity[$this->uniqueKey($lineItem)] += $lineItem->getQuantity();
                }
            }

            /** @var LineItem $item */
            foreach ($lineItems as $lineItem) {
                $this->enrichPriceByStack($context, $lineItem, $data, $stackQuantity);
            }
        }, 'cart');
    }

    private function uniqueKey(LineItem $lineItem): string
    {
        $uniqueKey = $lineItem->getReferencedId();
        $deliveryTime = $lineItem->getPayloadValue(PouchBundleCartProcessor::TYPE_DELIVERY_TIME);
        if ($deliveryTime) {
            $uniqueKey = md5(sprintf("%s-%s", $uniqueKey, $deliveryTime));
        }

        $lineItem->setPayloadValue('uniqueKey', $uniqueKey);

        return $uniqueKey;
    }

    /**
     * Override the default price definitions from ProductCartProcessor
     */
    private function enrichPriceByStack(
        SalesChannelContext $context,
        LineItem $lineItem,
        CartDataCollection $data,
        array $stackQuantity
    ): void {
        $id = $lineItem->getReferencedId();
        $product = $data->get(
            $this->getDataKey((string) $id)
        );

        // no data for enrich exists
        if (!$product instanceof SalesChannelProductEntity) {
            throw new \Exception("Priority of ProductStackProcessor should be under 5000");
        }

        /* Configuration has been remove in admin? */
        if (empty($stackQuantity[$this->uniqueKey($lineItem)])) {
            return;
        }

        $lineItem->setPriceDefinition(
            $this->getPriceDefinition(
                $product,
                $context,
                (int) $stackQuantity[$this->uniqueKey($lineItem)]
            )
        );
    }

    /**
     * @deprecated Same as ProductCartProcessor::getProducts
     */
    private function getProducts(LineItemCollection $items): array
    {
        $matches = [];
        foreach ($items as $item) {
            if ($item->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $matches[] = ['item' => $item, 'scope' => $items];
            }

            $nested = $this->getProducts($item->getChildren());

            foreach ($nested as $match) {
                $matches[] = $match;
            }
        }

        return $matches;
    }

    /**
     * Same as ProductCartProcessor::getPriceDefinition
     */
    private function getPriceDefinition(SalesChannelProductEntity $product, SalesChannelContext $context, int $quantity): QuantityPriceDefinition
    {
        $this->priceCalculator->calculate([$product], $context);

        if ($product->getCalculatedPrices()->count() === 0) {
            return $this->buildPriceDefinition($product->getCalculatedPrice(), $quantity);
        }

        // keep loop reference to $price variable to get last quantity price in case of "null"
        $price = $product->getCalculatedPrice();
        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $this->buildPriceDefinition($price, $quantity);
    }

    /**
     * Same as ProductCartProcessor::buildPriceDefinition
     */
    private function buildPriceDefinition(CalculatedPrice $price, int $quantity): QuantityPriceDefinition
    {
        $definition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $quantity);
        if ($price->getListPrice() !== null) {
            $definition->setListPrice($price->getListPrice()->getPrice());
        }

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        return $definition;
    }

    /**
     * Same as ProductCartProcessor::getDataKey
     */
    private function getDataKey(string $id): string
    {
        return 'product-' . $id;
    }
}
