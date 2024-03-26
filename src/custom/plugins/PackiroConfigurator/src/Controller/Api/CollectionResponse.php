<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Controller\Api;

use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Symfony\Component\HttpFoundation\Response;

class CollectionResponse extends JsonApiResponse
{
    public function __construct(
        array $data,
        int $total,
        int $limit,
        int $offset = 0,
        int $status = Response::HTTP_OK,
        array $headers = []
    ) {
        $responseData = [
            'payload' => $data,
            'pagination' => ['total' => $total, 'offset' => $offset, 'limit' => $limit, 'count' => count($data)],
        ];
        parent::__construct($responseData, $status, $headers);
    }
}
