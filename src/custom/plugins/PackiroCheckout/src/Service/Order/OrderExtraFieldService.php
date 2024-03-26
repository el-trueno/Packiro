<?php

declare(strict_types=1);

namespace Packiro\Checkout\Service\Order;

use Packiro\Checkout\DAL\Extension\Core\Checkout\Order\SplitOrderDefinition;
use Packiro\Core\DAL\Struct\FillUpExistingExtensionStruct;
use Packiro\Core\Service\UpdateExistingExtensionServiceInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;

class OrderExtraFieldService implements OrderExtraFieldServiceInterface
{
    public const PACKSHOT_PRODUCT_TYPE = 'PCKS';
    public const PACKSHOT_PRODUCT_NUMBER = 'packshot-tool';

    public function __construct(
        private EntityRepository $orderRepository,
        private EntityRepository $pcOrderRepository,
        private UpdateExistingExtensionServiceInterface $updateExistingExtensionService,
    ) {
    }

    public function saveOrderType(OrderEntity $order, Context $context): void
    {
        $fillUpExistingExtension = (new FillUpExistingExtensionStruct())
            ->setIdName('orderId')
            ->setIdValue($order->getId());
        foreach ($order->getLineItems() as $lineItem) {
            $product = $lineItem->getProduct();
            $productExtension = $product?->getExtension('pcProduct');
            if (
                ($product && $product->getProductNumber() === self::PACKSHOT_PRODUCT_NUMBER)
                || ($productExtension && $productExtension->getTechnicalName() === self::PACKSHOT_PRODUCT_TYPE)
            ) {
                $fillUpExistingExtension->addUpdatedValue(
                    [
                        'orderType' => SplitOrderDefinition::SERVICE_ORDER_TYPE,
                        'orderVersionId' => $order->getVersionId(),
                    ]
                );
            }
        }

        if (!$fillUpExistingExtension->hasKey('orderType')) {
            $fillUpExistingExtension->addUpdatedValue(
                [
                    'orderType' => SplitOrderDefinition::NORMAL_ORDER_TYPE,
                    'orderVersionId' => $order->getVersionId(),
                ]
            );
        }
        $this->updateExistingExtensionService->reload(
            $fillUpExistingExtension,
            $this->orderRepository,
            $this->pcOrderRepository,
            SplitOrderDefinition::EXTENSION_PROPERTY_NAME,
            $context
        );
    }
}
