<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(ProLibGroupEntity $entity)
 * @method void               set(string $key, ProLibGroupEntity $entity)
 * @method ProLibGroupEntity[]    getIterator()
 * @method ProLibGroupEntity[]    getElements()
 * @method ProLibGroupEntity|null get(string $key)
 * @method ProLibGroupEntity|null first()
 * @method ProLibGroupEntity|null last()
 */
class ProLibGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'pc_pro_lib_group_collection';
    }
}
