<?php

namespace Sitakgmbh\LaraBase\Console\Install;

trait SetupModels
{
    protected function setupModels(): void
    {
        $this->publishUserModel();
        $this->deleteDefaultUserMigration();
    }

    private function publishUserModel(): void
    {
        $path = app_path('Models/User.php');

        if (!$this->option('force') && file_exists($path)) {
            $this->warn('⚠ App\Models\User bereits vorhanden – übersprungen');
            return;
        }

        $content = <<<'PHP'
<?php

namespace App\Models;

use Sitakgmbh\LaraBase\Models\BaseUser;

class User extends BaseUser
{
    // projekt-spezifische Ergänzungen hier
}
PHP;
        file_put_contents($path, $content);
        $this->info('✓ App\Models\User angepasst');
    }

    private function deleteDefaultUserMigration(): void
    {
        $path = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (file_exists($path)) {
            unlink($path);
            $this->info('✓ Standard User-Migration gelöscht');
        }
    }
}