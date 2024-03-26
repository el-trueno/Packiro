<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibGroupEntity;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ProLibGroupRouteResponse extends StoreApiResponse
{
    /**
     * @var ProLibGroupEntity
     */
    protected $object;

    public function __construct(ProLibGroupEntity $entity)
    {
        parent::__construct($entity);
    }

    public function getAddress(): ProLibGroupEntity
    {
        return $this->object;
    }
}
