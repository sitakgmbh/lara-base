<?php

namespace Sitakgmbh\LaraBase\Console;

use Illuminate\Console\Command;
use Sitakgmbh\LaraBase\Console\Install\SetupEnvironment;
use Sitakgmbh\LaraBase\Console\Install\SetupModels;
use Sitakgmbh\LaraBase\Console\Install\SetupLogging;
use Sitakgmbh\LaraBase\Console\Install\SetupLdap;
use Sitakgmbh\LaraBase\Console\Install\SetupProject;
use Sitakgmbh\LaraBase\Console\Install\SetupAssets;
use Sitakgmbh\LaraBase\Console\Install\SetupDatabase;

class InstallCommand extends Command
{
    use SetupEnvironment;
    use SetupModels;
    use SetupLogging;
    use SetupLdap;
    use SetupProject;
    use SetupAssets;
    use SetupDatabase;

    protected $signature   = 'lara-base:install {--force : Überschreibt bestehende Dateien}';
    protected $description = 'Installiert lara-base und richtet das Projekt ein';

    public function handle(): void
    {
        $this->info('lara-base wird installiert...');
        $this->info('');

        $this->setupEnvironment();
        $this->setupModels();
        $this->setupLogging();
        $this->setupLdap();
        $this->setupProject();
        $this->setupAssets();
        $this->setupDatabase();

        $this->info('');
        $this->info('✓ Installation abgeschlossen!');
    }
}