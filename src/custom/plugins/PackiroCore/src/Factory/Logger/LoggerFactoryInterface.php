<?php

declare(strict_types=1);

namespace Packiro\Core\Factory\Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function createLogger(string $name, string $path, string $level = Logger::DEBUG): LoggerInterface;
}
