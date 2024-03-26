<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(ProLibItemEntity $entity)
 * @method void               set(string $key, ProLibItemEntity $entity)
 * @method ProLibItemEntity[]    getIterator()
 * @method ProLibItemEntity[]    getElements()
 * @method ProLibItemEntity|null get(string $key)
 * @method ProLibItemEntity|null first()
 * @method ProLibItemEntity|null last()
 */
class ProLibItemCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'pc_pro_lib_item_collection';
    }
}
