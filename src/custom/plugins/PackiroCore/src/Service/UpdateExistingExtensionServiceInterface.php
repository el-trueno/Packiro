<?php

declare(strict_types=1);

namespace Packiro\Core\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Struct\Struct;

interface UpdateExistingExtensionServiceInterface
{
    public function reload(
        Struct $fillUpExistingExtension,
        EntityRepository $mainRepository,
        EntityRepository $extensionRepository,
        string $extensionName,
        Context $context
    ): void;
}
