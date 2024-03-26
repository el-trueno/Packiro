<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Service;

use Shopware\Core\System\Country\CountryEntity;

interface VatValidationServiceInterface
{
    public const PREVALIDATION_REGEXP = '/[^a-zA-Z0-9]/';

    public function isVatIdValid(string $vatId, CountryEntity $countryEntity): bool;
}
