<?php

declare(strict_types=1);

namespace Packiro\Core\Factory\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

class StreamLoggerFactory implements LoggerFactoryInterface
{
    public function createLogger(string $name, string $path = 'php://stderr', string $level = Logger::DEBUG): LoggerInterface
    {
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler($path, $level));
        $logger->pushProcessor(new PsrLogMessageProcessor());

        return $logger;
    }
}
