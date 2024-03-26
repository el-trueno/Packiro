<?php

declare(strict_types=1);

namespace Packiro\Checkout\Exception;

use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AccessoriesAlreadyExistsException extends HttpException
{
    public const ACCESSORIES_ALREADY_EXISTS_CODE = 'CHECKOUT__ACCESSORIES_ALREADY_EXISTS';
    public const ERROR_MESSAGE = 'Accessories already exist';

    public function __construct()
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, self::ACCESSORIES_ALREADY_EXISTS_CODE, self::ERROR_MESSAGE);
    }
}
