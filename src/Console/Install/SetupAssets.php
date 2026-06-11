<?php

namespace Sitakgmbh\LaraBase\Console\Install;

use Illuminate\Support\Facades\Artisan;
use ZipArchive;

trait SetupAssets
{
    protected function setupAssets(): void
    {
        $this->publishAssets();
        $this->publishErrorViews();
    }

    private function publishAssets(): void
    {
        $zip  = new ZipArchive();
        $src  = __DIR__ . '/../../../public.zip';
        $dest = public_path();

        if (!file_exists($src)) {
            $this->warn('⚠ public.zip nicht gefunden – übersprungen');
            return;
        }

        if ($zip->open($src) === true) {
            $zip->extractTo($dest);
            $zip->close();
            $this->info('✓ Public Assets entpackt');
        } else {
            $this->error('✗ public.zip konnte nicht geöffnet werden');
        }
    }

    private function publishErrorViews(): void
    {
        Artisan::call('vendor:publish', [
            '--tag'   => 'lara-base-errors',
            '--force' => true,
            '--quiet' => true,
        ]);
        $this->info('✓ Error-Views publiziert');
    }
}