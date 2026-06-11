<?php

namespace Sitakgmbh\LaraBase\Logging;

use Monolog\Logger;

class CreateDbLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('db');
        $logger->pushHandler(new DbLogHandler());
        return $logger;
    }
}