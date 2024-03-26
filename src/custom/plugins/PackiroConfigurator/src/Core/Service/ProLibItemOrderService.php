<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Service;

use Kuniva\PackiroConfigurator\Core\Checkout\PouchBundle\Cart\PouchBundleCartProcessor;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderCollection;
use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderDefinition;
use Monolog\Logger;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProLibItemOrderService implements ProLibItemOrderServiceInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly EntityRepositoryInterface $proLibItemOrderRepository,
    ) {
    }

    public function patchProLibItemOrders(Cart $cart, OrderEntity $order, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('cartToken', $cart->getToken()));

        /** @var ProLibItemOrderCollection $proLibItemOrders */
        $proLibItemOrders = $this->proLibItemOrderRepository->search($criteria, $context)->getEntities();
        if ($proLibItemOrders->count() === 0) {
            return;
        }

        $data = [];
        foreach ($proLibItemOrders as $proLibItemOrder) {
            $data[] = [
                'id' => $proLibItemOrder->getId(),
                'orderId' => $order->getId(),
                'orderLineItemId' => $this->getOrderLineItemId($proLibItemOrder->getCartLineItemId(), $order),
            ];
        }

        $this->proLibItemOrderRepository->update($data, $context);
    }

    public function createProLibItemSlots(LineItem $parentLineItem, string $cartToken, Context $context): void
    {
        /** @var LineItem $artworkLineItem */
        $artworkLineItem = $parentLineItem->getChildren()->filterType(PouchBundleCartProcessor::TYPE_ARTWORK)->first();

        if (!$artworkLineItem) {
            return;
        }

        /** @var LineItem $productLineItem */
        $productLineItem = $parentLineItem->getChildren()->filterType(PouchBundleCartProcessor::TYPE_PRODUCT)->first();

        $this->logger->debug(sprintf(
            "A new item with quantity %s, id %s and %s artworks was added to cart. Creating proLibItemOrders...",
            $productLineItem ? $productLineItem->getQuantity() : '',
            $parentLineItem->getId(),
            $artworkLineItem->getQuantity()
        ), [
            'token' => $cartToken,
        ]);

        $data = [];
        for ($x = 0; $x < $artworkLineItem->getQuantity(); $x++) {
            $data[] = [
                'id' => Uuid::randomHex(),
                'cartToken' => $cartToken,
                'cartLineItemId' => $parentLineItem->getId(),
                'productId' => $parentLineItem->getReferencedId(),
                'proLibItemId' => $parentLineItem->getPayloadValue('proLibItemId'),
                'quantity' => 0,
            ];
        }

        // Deleting first in case if cart token has been changed
        $this->deleteExistingSlots($parentLineItem->getId(), $context);

        $entityWriteContainerEvent = $this->proLibItemOrderRepository->create($data, $context);

        $criteria = new Criteria($entityWriteContainerEvent->getPrimaryKeys(ProLibItemOrderDefinition::ENTITY_NAME));
        $parentLineItem->addExtension(
            ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME,
            $this->proLibItemOrderRepository->search($criteria, $context)->getEntities(),
        );
    }

    public function enrichCart(Cart $cart, LineItemCollection $lineItems, SalesChannelContext $salesChannelContext): void
    {
        /* The context token can change, use ids of line items instead */
        $lineItemIds = [];
        foreach ($lineItems as $lineItem) {
            $lineItemIds[] = $lineItem->getId();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('cartLineItemId', $lineItemIds));

        $proLibItemOrders = $this->proLibItemOrderRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        )->getEntities();

        $cart->addExtension(ProLibItemOrderDefinition::COLLECTION_PROPERTY_NAME, $proLibItemOrders);
    }

    private function getOrderLineItemId(string $cartLineItemId, OrderEntity $order): ?string
    {
        /** @var OrderLineItemEntity $lineItem */
        foreach ($order->getLineItems() as $lineItem) {
            $pl = $lineItem->getPayload();
            if (isset($pl['cartLineItemId']) && $pl['cartLineItemId'] === $cartLineItemId) {
                return $lineItem->getId();
            }
        }
        return null;
    }

    private function deleteExistingSlots(string $cartLineItemId, Context $context): void
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('cartLineItemId', $cartLineItemId));

        $existingSlotIds = $this->proLibItemOrderRepository->searchIds($criteria, $context)->getIds();

        if (count($existingSlotIds) === 0) {
            return;
        }

        // Shopware requires format array<array<'id', string>>
        $deleteData = array_map(
            fn(string $slotId) => ['id' => $slotId],
            $existingSlotIds,
        );

        $this->proLibItemOrderRepository->delete($deleteData, $context);
    }
}
