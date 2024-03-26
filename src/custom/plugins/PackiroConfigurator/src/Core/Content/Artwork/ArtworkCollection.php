<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(ArtworkEntity $entity)
 * @method void               set(string $key, ArtworkEntity $entity)
 * @method ArtworkEntity[]    getIterator()
 * @method ArtworkEntity[]    getElements()
 * @method ArtworkEntity|null get(string $key)
 * @method ArtworkEntity|null first()
 * @method ArtworkEntity|null last()
 */
class ArtworkCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'pc_artwork_collection';
    }
}
