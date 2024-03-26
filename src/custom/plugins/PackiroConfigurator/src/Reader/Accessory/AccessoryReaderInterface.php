<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\Accessory;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Exception\AccessoryOptionNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

interface AccessoryReaderInterface
{
    public function findAccessoryOptions(Criteria $criteria, Context $context): AccessoryOptionCollection;

    /**
     * @throws AccessoryOptionNotFoundException
     */
    public function getAccessoryOptionById(string $accessoryOptionId, Context $context): AccessoryOptionEntity;
}
