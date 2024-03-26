<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Service;

use DateTime;
use Shopware\Core\System\Country\CountryEntity;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedVatValidationService implements VatValidationServiceInterface
{
    public function __construct(
        private readonly VatValidationServiceInterface $validationService,
        private readonly CacheInterface $cache,
    ) {
    }

    public function isVatIdValid(string $vatId, CountryEntity $countryEntity): bool
    {
        return (bool)$this->cache->get(
            $this->createCacheKey($vatId),
            function (ItemInterface $cacheItem) use ($vatId, $countryEntity) {
                $cacheItem->expiresAt(new DateTime('tomorrow'));

                return $this->validationService->isVatIdValid($vatId, $countryEntity);
            },
        );
    }

    private function createCacheKey(string $vatId): string
    {
        $vatId = preg_replace(self::PREVALIDATION_REGEXP, "", $vatId);

        return sprintf('PackiroVatValidator.validationResult.%s', $vatId);
    }
}
