<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Controller\Api;

use Kuniva\PackiroConfigurator\Service\ExportOrderServiceInterface;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ["api"]])]
class ExportController extends AbstractController
{
    public function __construct(
        private readonly ExportOrderServiceInterface $exportOrderService
    ) {
    }

    #[Route('/api/order/ops-export/{offset}/{limit}', name: "api.packiro.order.ops-export", methods: ["GET"])]
    public function exportAction(Context $context, int $offset = 0, int $limit = 100): JsonResponse
    {
        $limit = min(200, $limit);
        $exportOrderResult = $this->exportOrderService->export($context, $offset, $limit);

        return new CollectionResponse(
            $exportOrderResult->getSerializedOrders(),
            $exportOrderResult->getTotalOrders(),
            $limit,
            $offset,
        );
    }
}
