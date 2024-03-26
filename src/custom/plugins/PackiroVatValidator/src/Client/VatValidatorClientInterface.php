<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Client;

use Shopware\Core\System\Country\CountryEntity;

interface VatValidatorClientInterface
{
    public function isVatIdValid(string $vatId): bool;
}
