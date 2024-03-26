<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Order;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Order\SplitOrderDefinition;
use Packiro\Checkout\Writer\Order\OrderPersisterInterface;
use Packiro\Core\DAL\Struct\FillUpExistingExtensionStruct;
use Packiro\Core\Service\UpdateExistingExtensionServiceInterface;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\AdminSalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private AbstractCartPersister $cartPersister,
        private EntityRepository $orderRepository,
        private EntityRepository $pcOrderRepository,
        private UpdateExistingExtensionServiceInterface $updateExistingExtensionService,
        private OrderPersisterInterface $orderPersister,
    ) {
    }

    public function executePostSplitOrderCreationProcess(
        string $cartToken,
        OrderEntity $splitOrder,
        string $checkoutId,
        SalesChannelContext $salesChannelContext,
    ): void {
        $this->cartPersister->delete($cartToken, $salesChannelContext);
        $this->saveSplitOrderExtension($splitOrder, $checkoutId, $salesChannelContext->getContext());
    }

    public function addCreatedByIdIntoOrder(OrderEntity $order, Context $context): void
    {
        $source = $context->getSource();
        if (!$source instanceof AdminSalesChannelApiSource) {
            return;
        }
        $userId = $source->getOriginalContext()?->getSource()?->getUserId();
        if (!$userId) {
            return;
        }
        $this->orderPersister->addCreatedByIdIntoOrder($order, (string)$userId, $context);
    }

    private function saveSplitOrderExtension(OrderEntity $orderEntity, string $checkoutId, Context $context): void
    {
        $fillUpExistingExtension = (new FillUpExistingExtensionStruct())
            ->setIdName('orderId')
            ->setIdValue($orderEntity->getId())
            ->setUpdatedValues([
                'checkoutId' => $checkoutId,
                'orderVersionId' => $orderEntity->getVersionId(),
            ]);

        $this->updateExistingExtensionService->reload(
            $fillUpExistingExtension,
            $this->orderRepository,
            $this->pcOrderRepository,
            SplitOrderDefinition::EXTENSION_PROPERTY_NAME,
            $context
        );
    }
}
