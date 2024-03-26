<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemOrderEntity;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ProLibItemOrderRouteResponse extends StoreApiResponse
{
    /**
     * @var ProLibItemOrderEntity
     */
    protected $object;

    public function __construct(ProLibItemOrderEntity $entity)
    {
        parent::__construct($entity);
    }

    public function getAddress(): ProLibItemOrderEntity
    {
        return $this->object;
    }
}
