<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\ProLib;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class ProLibItemOrderIndexerEvent extends NestedEvent
{
    public function __construct(private array $ids, private Context $context)
    {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
