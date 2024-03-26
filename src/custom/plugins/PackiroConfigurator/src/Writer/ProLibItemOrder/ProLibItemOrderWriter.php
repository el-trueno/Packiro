<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Writer\ProLibItemOrder;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class ProLibItemOrderWriter implements ProLibItemOrderWriterInterface
{
    public function __construct(
        private EntityRepository $proLibItemOrderRepository,
    ) {
    }

    public function attachProLibItemsToSlots(EntityCollection $proLibItemOrderCollection, Context $context): void
    {
        $dataToUpdate = $proLibItemOrderCollection->fmap(static function (ProLibItemOrderEntity $proLibItemOrder) {
            return [
                'id' => $proLibItemOrder->getId(),
                'quantity' => $proLibItemOrder->getQuantity(),
                'proLibItemId' => $proLibItemOrder->getProLibItemId(),
            ];
        });

        $this->proLibItemOrderRepository->update(array_values($dataToUpdate), $context);
    }
}
