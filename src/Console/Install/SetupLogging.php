<?php

namespace Sitakgmbh\LaraBase\Console\Install;

trait SetupLogging
{
    protected function setupLogging(): void
    {
        $path    = config_path('logging.php');
        $content = file_get_contents($path);

        if (!$this->option('force') && str_contains($content, 'serverlog')) {
            $this->warn('⚠ Logging-Channels bereits vorhanden – übersprungen');
            return;
        }

        // Bestehende Channels entfernen falls --force
        if ($this->option('force')) {
            $content = preg_replace(
                "/'serverlog'.*?'db' => \[.*?\],\s*/s",
                '',
                $content
            );
        }

        $channels = <<<'PHP'

        'serverlog' => [
            'driver'    => 'single',
            'path'      => storage_path('logs/server.log'),
            'level'     => 'debug',
            'formatter' => Sitakgmbh\LaraBase\Logging\MultiLineFormatter::class,
        ],

        'debuglog' => [
            'driver'    => 'single',
            'path'      => storage_path('logs/debug.log'),
            'level'     => 'debug',
            'formatter' => Sitakgmbh\LaraBase\Logging\SingleLineFormatter::class,
        ],

        'db' => [
            'driver' => 'custom',
            'via'    => Sitakgmbh\LaraBase\Logging\CreateDbLogger::class,
            'level'  => 'debug',
        ],

PHP;

        $content = preg_replace(
            "/'channels'\s*=>\s*\[/",
            "'channels' => [{$channels}",
            $content
        );

        file_put_contents($path, $content);
        $this->info('✓ Logging-Channels hinzugefügt');
    }
}