<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(ProLibItemOrderEntity $entity)
 * @method void               set(string $key, ProLibItemOrderEntity $entity)
 * @method ProLibItemOrderEntity[]    getIterator()
 * @method ProLibItemOrderEntity[]    getElements()
 * @method ProLibItemOrderEntity|null get(string $key)
 * @method ProLibItemOrderEntity|null first()
 * @method ProLibItemOrderEntity|null last()
 */
class ProLibItemOrderCollection extends EntityCollection
{
    public function filterByCartLineItem(string $cartLineItemId): self
    {
        return $this->filter(
            static function (ProLibItemOrderEntity $entity) use ($cartLineItemId) {
                return $entity->getCartLineItemId() === $cartLineItemId;
            }
        );
    }

    public function getApiAlias(): string
    {
        return 'pc_pro_lib_item_order_collection';
    }
}
