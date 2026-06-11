<?php

namespace Sitakgmbh\LaraBase\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\DB;

class DbLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $context  = $record->context;
        $category = $context['category'] ?? 'system';
        unset($context['category']);

        DB::table('logs')->insert([
            'category'   => strtolower($category),
            'level'      => strtolower($record->level->getName()),
            'message'    => $record->message,
            'context'    => json_encode($context),
            'created_at' => now(),
        ]);
    }
}