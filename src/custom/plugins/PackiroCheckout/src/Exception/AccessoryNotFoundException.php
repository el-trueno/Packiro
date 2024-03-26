<?php

declare(strict_types=1);

namespace Packiro\Checkout\Exception;

use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AccessoryNotFoundException extends HttpException
{
    public const ACCESSORY_NOT_FOUND_CODE = 'CHECKOUT__ACCESSORY_NOT_FOUND';
    public const ERROR_MESSAGE = 'Accessory with id {{ accessoryId }} is not found';

    public function __construct(string $accessoryId)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            self::ACCESSORY_NOT_FOUND_CODE,
            self::ERROR_MESSAGE,
            [
                'accessoryId' => $accessoryId,
            ],
        );
    }
}
