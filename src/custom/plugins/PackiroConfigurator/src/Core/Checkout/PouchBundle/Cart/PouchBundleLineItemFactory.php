<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PouchBundleLineItemFactory implements LineItemFactoryInterface
{
    public function supports(string $type): bool
    {
        return $type === PouchBundleCartProcessor::TYPE;
    }

    /**
     * @param array $data
     * @param SalesChannelContext $context
     * @return LineItem
     * @deprecated will be replaced by createV2()
     */
    public function create(array $data, SalesChannelContext $context): LineItem
    {
        if (isset($data['accessoryOptions'])) {
            return $this->createV2($data, $context);
        }

        if (!isset($data['accessoryGroups'])) {
            throw new MissingRequestParameterException('accessoryGroups');
        }

        $accessoryGroups = $data['accessoryGroups'];

        /* Create an empty bundle */
        $parentLineItem = new LineItem(
            Uuid::randomHex(),
            PouchBundleCartProcessor::TYPE
        );

        if (!empty($data['sizeHeight'])) {
            $parentLineItem->setPayloadValue('sizeHeight', (int) $data['sizeHeight']);
            $parentLineItem->setPayloadValue('sizeWidth', (int) $data['sizeWidth']);
            $parentLineItem->setPayloadValue('sizeDepth', (int) $data['sizeDepth']);
        }

        if (isset($data['hasCustomPrice'])) {
            $parentLineItem->setPayloadValue('hasCustomPrice', (bool) $data['hasCustomPrice']);
            $parentLineItem->setPayloadValue('customUnitPrice', (float) $data['customUnitPrice']);
        }

        /* Different delivery address per line item */
        if (isset($data['customerAddressId'])) {
            $parentLineItem->setPayloadValue('customerAddressId', (string) $data['customerAddressId']);
        }

        if (isset($data['proLibItemId'])) {
            $parentLineItem->setPayloadValue('proLibItemId', (string) $data['proLibItemId']);
        }

        $childrenLineItems = new LineItemCollection();

        /* Main product is first child */
        $lineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $data['referencedId'],
            (int) $data['quantity'] ?: 1,
        );
        $lineItem->setStackable(false);
        $lineItem->setRemovable(true);

        $childrenLineItems->add($lineItem);

        foreach ($accessoryGroups as $accessoryGroup) {
            if (isset($accessoryGroup['accessoryOptions'])) {
                /* Accessory group has multiple selections (checkbox/multiple select) */
                $accessoryOptions = $accessoryGroup['accessoryOptions'];

                foreach ($accessoryOptions as $accessoryOption) {
                    /* Prevent quantity error if quantity is given and 0 */
                    $quantity = isset($accessoryOption['quantity']) && (int) $accessoryOption['quantity'] > 0 ? (int) $accessoryOption['quantity'] : 1;

                    $childrenLineItems->add(new LineItem(
                        Uuid::randomHex(),
                        $accessoryGroup['type'],
                        $accessoryOption['id'],
                        $quantity
                    ));
                }
            } else {
                /* Skip if no option selected */
                if (empty($accessoryGroup['id'])) {
                    continue;
                }

                /* Accessory group has single selection (radio/single select) */
                /* Prevent quantity error if quantity is given and 0 */
                $quantity = isset($accessoryGroup['quantity']) && (int) $accessoryGroup['quantity'] > 0 ? (int) $accessoryGroup['quantity'] : 1;

                $childrenLineItems->add(new LineItem(
                    Uuid::randomHex(),
                    $accessoryGroup['type'],
                    $accessoryGroup['id'],
                    $quantity
                ));
            }
        }

        $parentLineItem->setChildren($childrenLineItems);

        return $parentLineItem;
    }

    public function update(LineItem $lineItem, array $data, SalesChannelContext $context): void
    {
        // TODO: Implement update() method.
    }

    private function createV2(array $data, SalesChannelContext $context): LineItem
    {
        $accessoryOptions = $data['accessoryOptions'];

        /* Create an empty bundle */
        $parentLineItem = new LineItem(
            Uuid::randomHex(),
            PouchBundleCartProcessor::TYPE,
            $data['referencedId'],
        );

        /* cartLineItemId is used in ProLibItemOrderService::getOrderLineItemId */
        $parentLineItem->setPayloadValue('cartLineItemId', $parentLineItem->getId());
        /* quantity is same as productLineItem quantity */
        $parentLineItem->setPayloadValue('quantity', (int) $data['quantity'] ?: 1);
        /* Useful to generate a prod lib item/version by line item */
        $parentLineItem->setPayloadValue('accessoryOptions', $accessoryOptions);

        /* store custom size information */
        if (!empty($data['sizeHeight'])) {
            $parentLineItem->setPayloadValue('sizeHeight', (int) $data['sizeHeight']);
            $parentLineItem->setPayloadValue('sizeWidth', (int) $data['sizeWidth']);
            $parentLineItem->setPayloadValue('sizeDepth', (int) $data['sizeDepth']);
        }

        /* Set a custom price by Admin, TODO: Make this more safety */
        if (isset($data['hasCustomPrice'])) {
            $parentLineItem->setPayloadValue('hasCustomPrice', (bool) $data['hasCustomPrice']);
            $parentLineItem->setPayloadValue('customUnitPrice', (float) $data['customUnitPrice']);
        }

        /* Different delivery address per line item */
        if (isset($data['customerAddressId'])) {
            $parentLineItem->setPayloadValue('customerAddressId', (string) $data['customerAddressId']);
        }

        /* If add to cart from a prod lib item/version */
        if (isset($data['proLibItemId'])) {
            $parentLineItem->setPayloadValue('proLibItemId', (string) $data['proLibItemId']);
        }

        $childrenLineItems = new LineItemCollection();

        /* Main product is first child */
        $productLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $data['referencedId'],
            (int) $data['quantity'] ?: 1,
        );
        $productLineItem->setStackable(false);
        $productLineItem->setRemovable(true);

        $childrenLineItems->add($productLineItem);

        foreach ($accessoryOptions as $accessoryOption) {
            if (empty($accessoryOption['id'])) {
                continue;
            }

            /* Prevent quantity error if quantity is given and 0 */
            $quantity = isset($accessoryOption['quantity']) && (int) $accessoryOption['quantity'] > 0 ? (int) $accessoryOption['quantity'] : 1;

            $accessoryLineItem = new LineItem(
                Uuid::randomHex(),
                $accessoryOption['type'],
                $accessoryOption['id'],
                $quantity
            );
            $accessoryLineItem->setStackable(true);

            $childrenLineItems->add($accessoryLineItem);
        }

        $parentLineItem->setChildren($childrenLineItems);

        return $parentLineItem;
    }
}
