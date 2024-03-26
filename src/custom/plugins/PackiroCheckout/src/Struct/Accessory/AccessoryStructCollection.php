<?php

declare(strict_types=1);

namespace Packiro\Checkout\Struct\Accessory;

use Shopware\Core\Framework\Struct\Collection;

/**
 * @extends Collection<AccessoryStruct>
 */
class AccessoryStructCollection extends Collection
{
    public function getIds(): array
    {
        return $this->fmap(static function (AccessoryStruct $accessoryStruct) {
            return $accessoryStruct->getId();
        });
    }

    protected function getExpectedClass(): string
    {
        return AccessoryStruct::class;
    }
}
