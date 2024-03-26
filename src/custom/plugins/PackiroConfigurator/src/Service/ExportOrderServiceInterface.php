<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Service;

use Kuniva\PackiroConfigurator\Struct\Order\ExportOrderResult;
use Shopware\Core\Framework\Context;

interface ExportOrderServiceInterface
{
    public function export(Context $context, ?int $offset = 0, ?int $limit = 100): ExportOrderResult;
}
