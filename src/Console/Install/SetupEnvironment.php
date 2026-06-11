<?php

namespace Sitakgmbh\LaraBase\Console\Install;

trait SetupEnvironment
{
    protected function setupEnvironment(): void
    {
        $src = __DIR__ . '/../../../.env.example';

        if (!file_exists($src)) {
            $this->warn('⚠ .env.example nicht gefunden – übersprungen');
            return;
        }

        $dest = base_path('.env');

        if (!$this->option('force') && file_exists($dest)) {
            $this->warn('⚠ .env bereits vorhanden – übersprungen');
        } else {
            copy($src, $dest);
            $this->info('✓ .env erstellt');
        }

        $example = base_path('.env.example');
        if (file_exists($example)) {
            unlink($example);
            $this->info('✓ .env.example gelöscht');
        }
    }
}