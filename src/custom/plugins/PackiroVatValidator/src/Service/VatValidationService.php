<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Service;

use Packiro\VatValidator\Client\VatValidatorClientInterface;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class VatValidationService implements VatValidationServiceInterface
{
    private const DEFAULT_REGEXP = '/^[a-z0-9]{0,14}$/i';
    private const EU_MEMBER_CODES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR',
        'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO',
        'SE', 'SI', 'SK',
    ];

    public function __construct(
        private readonly VatValidatorClientInterface $viesClient,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function isVatIdValid(string $vatId, CountryEntity $countryEntity): bool
    {
        $vatId = preg_replace(self::PREVALIDATION_REGEXP, "", $vatId);
        $vatRegex = $countryEntity->getVatIdPattern() ? sprintf('/%s/', $countryEntity->getVatIdPattern()) : self::DEFAULT_REGEXP;

        if (preg_match($vatRegex, $vatId) !== 1) {
            return false;
        }

        $checkInVies = $this->systemConfigService->get('PackiroVatValidator.config.checkInVies');
        if ($checkInVies && $this->isEUMember($countryEntity->getIso() ?? '')) {
            return $this->viesClient->isVatIdValid($vatId);
        }

        return true;
    }

    private function isEUMember(string $iso): bool
    {
        return in_array($iso, self::EU_MEMBER_CODES);
    }
}
