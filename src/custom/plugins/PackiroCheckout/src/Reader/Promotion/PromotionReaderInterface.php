<?php

declare(strict_types=1);

namespace Packiro\Checkout\Reader\Promotion;

use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Promotion\Cart\CartPromotionsDataDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface PromotionReaderInterface
{
    public function findPromotionsByCodes(
        CartDataCollection $data,
        array $allCodes,
        SalesChannelContext $context
    ): CartPromotionsDataDefinition;
}
