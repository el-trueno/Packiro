<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(AccessoryOptionEntity $entity)
 * @method void            set(string $key, AccessoryOptionEntity $entity)
 * @method AccessoryOptionEntity[]    getIterator()
 * @method AccessoryOptionEntity[]    getElements()
 * @method AccessoryOptionEntity|null get(string $key)
 * @method AccessoryOptionEntity|null first()
 * @method AccessoryOptionEntity|null last()
 */
class AccessoryOptionCollection extends EntityCollection
{
    public function sortByPriority(): void
    {
        $this->sort(function (AccessoryOptionEntity $a, AccessoryOptionEntity $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
    }

    public function filterByAccessoryGroupId(string $id): self
    {
        return $this->filter(function (AccessoryOptionEntity $entity) use ($id) {
            return ($entity->getAccessoryGroupId() === $id);
        });
    }

    public function getTransformed(AccessoryGroupCollection $accessoryGroups): AccessoryGroupCollection
    {
        $this->sortByPriority();

        /** @var AccessoryGroupEntity $accessoryGroup */
        foreach ($accessoryGroups as $accessoryGroup) {
            $accessoryGroup->setAccessoryOptions($this->filterByAccessoryGroupId($accessoryGroup->getId()));
        }

        $accessoryGroups->sortByPriority();

        return $accessoryGroups;
    }
}
