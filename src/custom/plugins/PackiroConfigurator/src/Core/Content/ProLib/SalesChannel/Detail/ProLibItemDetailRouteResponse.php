<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib\SalesChannel\Detail;

use Kuniva\PackiroConfigurator\Core\Content\ProLib\ProLibItemEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ProLibItemDetailRouteResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct<string, mixed>
     */
    protected $object;

    public function __construct(ProLibItemEntity $proLibItem)
    {
        parent::__construct(new ArrayStruct([
            'proLibItem' => $proLibItem,
        ], 'pro_lib_item_detail'));
    }

    /**
     * @return ArrayStruct<string, mixed>
     */
    public function getResult(): ArrayStruct
    {
        return $this->object;
    }

    public function getProLibItem(): ProLibItemEntity
    {
        return $this->object->get('proLibItem');
    }
}
