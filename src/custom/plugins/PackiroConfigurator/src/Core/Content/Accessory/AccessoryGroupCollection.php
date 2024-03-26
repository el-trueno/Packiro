<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Accessory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(AccessoryGroupEntity $entity)
 * @method void            set(string $key, AccessoryGroupEntity $entity)
 * @method AccessoryGroupEntity[]    getIterator()
 * @method AccessoryGroupEntity[]    getElements()
 * @method AccessoryGroupEntity|null get(string $key)
 * @method AccessoryGroupEntity|null first()
 * @method AccessoryGroupEntity|null last()
 */
class AccessoryGroupCollection extends EntityCollection
{
    public function sortByPriority(): void
    {
        $this->sort(function (AccessoryGroupEntity $a, AccessoryGroupEntity $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
    }
}
