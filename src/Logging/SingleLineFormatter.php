<?php

namespace Sitakgmbh\LaraBase\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class SingleLineFormatter extends LineFormatter
{
    public function format(LogRecord $record): string
    {
        $output = "[{$record->datetime->format('Y-m-d H:i:s')}] {$record->level->getName()}: {$record->message}";

        if (!empty($record->context)) {
            $output .= ' ' . json_encode($record->context, JSON_UNESCAPED_UNICODE);
        }

        return $output . "\n";
    }
}