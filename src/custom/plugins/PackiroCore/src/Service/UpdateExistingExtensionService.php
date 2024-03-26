<?php

declare(strict_types=1);

namespace Packiro\Core\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\Struct;

class UpdateExistingExtensionService implements UpdateExistingExtensionServiceInterface
{
    public function reload(
        Struct $fillUpExistingExtension,
        EntityRepository $mainRepository,
        EntityRepository $extensionRepository,
        string $extensionName,
        Context $context
    ): void {
        $criteria = (new Criteria())->addFilter(
            new EqualsFilter($fillUpExistingExtension->getIdName(), $fillUpExistingExtension->getIdValue())
        );
        $extensionEntity = $extensionRepository->search($criteria, $context)->first();
        if (!$extensionEntity) {
            $mainRepository->upsert([[
                'id' => $fillUpExistingExtension->getIdValue(),
                $extensionName => $fillUpExistingExtension->getUpdatedValues(),
            ]], $context);

            return;
        }

        $filled = $this->prepareMethodsArrayFromEntity($extensionEntity);

        $extensionRepository->delete([[
            $fillUpExistingExtension->getIdName() => $fillUpExistingExtension->getIdValue()]], $context);
        $mainRepository->upsert([[
            'id' => $fillUpExistingExtension->getIdValue(),
            $extensionName => $fillUpExistingExtension->merge($filled),
        ]], $context);
    }

    private function prepareMethodsArrayFromEntity(Entity $extensionEntity): array
    {
        $extensionEntityWithParent = $extensionEntity->getVars();
        $existingKeys = array_diff(array_keys($extensionEntityWithParent), array_keys((new Entity())->getVars()));
        $filled = [];
        foreach ($existingKeys as $key) {
            if ($extensionEntityWithParent[$key]) {
                $filled[$key] = $extensionEntityWithParent[$key];
            }
        }

        return $filled;
    }
}
