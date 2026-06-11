<?php

namespace Sitakgmbh\LaraBase\Logging;

use Illuminate\Support\Facades\Log;
use Sitakgmbh\LaraBase\Enums\LogCategory;
use Sitakgmbh\LaraBase\Enums\LogLevel;

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        Log::channel('serverlog')->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        Log::channel('serverlog')->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        Log::channel('serverlog')->error($message, $context);
    }

	public static function debug(string $message, array $context = []): void
	{
		if (\Sitakgmbh\LaraBase\Models\Setting::getValue('debug_mode', false)) {
			Log::channel('debuglog')->debug($message, $context);
		}
	}
	
    public static function db(string $category, string $level, string $message, array $context = []): void
    {
        $category = strtolower($category);
        $level    = strtolower($level);

        if (!LogCategory::isValid($category)) {
            self::warning("Ungültige Log-Kategorie: {$category}", [
                'message' => $message,
                'context' => $context,
            ]);
            return;
        }

        if (!LogLevel::isValid($level)) {
            self::warning("Ungültiger Log-Level: {$level}", [
                'message' => $message,
                'context' => $context,
            ]);
            return;
        }

        Log::channel('db')->{$level}(
            $message,
            array_merge(['category' => $category], $context)
        );
    }
}