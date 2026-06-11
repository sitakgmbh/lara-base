<?php

namespace Sitakgmbh\LaraBase\Console\Install;

use ZipArchive;

trait SetupAssets
{
    protected function setupAssets(): void
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
}