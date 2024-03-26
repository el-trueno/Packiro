<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ProLibItemRouteResponse extends StoreApiResponse
{
    /**
     * @var ProLibItemEntity
     */
    protected $object;

    public function __construct(ProLibItemEntity $entity)
    {
        parent::__construct($entity);
    }

    public function getAddress(): ProLibItemEntity
    {
        return $this->object;
    }
}
