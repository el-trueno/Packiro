<?php

declare(strict_types=1);

namespace Packiro\Core\Util\Uuid;

use Shopware\Core\Framework\Uuid\Uuid as ShopwareUuid;

class Uuid extends ShopwareUuid
{
    public static function addHyphens(string $uuid): string
    {
        return sprintf(
            "%s-%s-%s-%s-%s",
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 12, 4),
            substr($uuid, 16, 4),
            substr($uuid, 20),
        );
    }
}
