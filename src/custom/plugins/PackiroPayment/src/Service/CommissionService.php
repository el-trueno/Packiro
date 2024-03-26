<?php declare(strict_types=1);

namespace Packiro\Payment\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CommissionService implements CommissionServiceInterface
{
    public function __construct(private readonly SystemConfigService $systemConfigService)
    {}

    public function addCommission(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context): void {
     //   $this->systemConfigService->get()
     //   $k = $data;
        dd($data);

    }
}

