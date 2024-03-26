<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Reader\Accessory;

use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionCollection;
use Kuniva\PackiroConfigurator\Core\Content\Accessory\AccessoryOptionEntity;
use Kuniva\PackiroConfigurator\Exception\AccessoryOptionNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class AccessoryReader implements AccessoryReaderInterface
{
    public function __construct(
        private EntityRepository $accessoryOptionReporitory,
    ) {
    }

    public function findAccessoryOptions(Criteria $criteria, Context $context): AccessoryOptionCollection
    {
        return $this->accessoryOptionReporitory->search($criteria, $context)->getEntities();
    }

    public function getAccessoryOptionById(string $accessoryOptionId, Context $context): AccessoryOptionEntity
    {
        $criteria = new Criteria([$accessoryOptionId]);

        $accessoryOptionCollection = $this->findAccessoryOptions($criteria, $context);
        if ($accessoryOptionCollection->count() === 0) {
            throw new AccessoryOptionNotFoundException(sprintf(
                'Accessory option with id %s does nopt exist',
                $accessoryOptionId,
            ));
        }

        return $accessoryOptionCollection->first();
    }
}
