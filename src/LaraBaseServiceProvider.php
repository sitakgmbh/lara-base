<?php

namespace Sitakgmbh\LaraBase;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LaraBaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lara-base.php',
            'lara-base'
        );

        $this->app->booting(function () {
            $mode = config('lara-base.auth.mode', 'local');

            config([
                'auth.guards.sso' => [
                    'driver'   => 'session',
                    'provider' => 'users',
                ],
                'auth.providers.users' => [
                    'driver' => $mode === 'ldap' ? 'ldap-auth' : 'local-auth',
                    'model'  => config('auth.providers.users.model', \App\Models\User::class),
                ],
            ]);
        });

        $this->app->singleton('laralog', function () {
            return new \Sitakgmbh\LaraBase\Logging\Logger();
        });

		$this->app->singleton('larasettings', function () {
			return new \Sitakgmbh\LaraBase\Settings\SettingsManager();
		});

		$this->app->singleton('larausersettings', function () {
			return new \Sitakgmbh\LaraBase\Settings\UserSettingsManager();
		});

        $this->app->singleton(
            \Sitakgmbh\LaraBase\Auth\LdapProvisioningService::class
        );

        $this->app->register(\Sitakgmbh\LaraBase\Auth\AuthServiceProvider::class);
    }

    public function boot(): void
    {

		\Illuminate\Support\Facades\Event::listen(
			\Illuminate\Auth\Events\Login::class,
			function ($event) {
				$user = $event->user;
				if (method_exists($user, 'getSetting')) {
					session(['darkmode_enabled' => (bool) $user->getSetting('darkmode_enabled', false)]);
				}
			}
		);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lara-base');
        $this->loadRoutesFrom(__DIR__ . '/../routes/lara-base.php');

		app('router')->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
		app('router')->aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);

        $this->publishes([
            __DIR__ . '/../config/lara-base.php' => config_path('lara-base.php'),
        ], 'lara-base-config');

        $this->publishes([
            __DIR__ . '/../resources/js'   => resource_path('js'),
            __DIR__ . '/../resources/css'  => resource_path('css'),
            __DIR__ . '/../resources/scss' => resource_path('scss'),
        ], 'lara-base-assets');

		$this->publishes([
			__DIR__ . '/../database/migrations/optional/' => database_path('migrations'),
		], 'lara-base-ldap-sync');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/lara-base'),
        ], 'lara-base-views');

        $this->publishes([
            __DIR__ . '/../database/seeders/' => database_path('seeders'),
        ], 'lara-base-seeders');

		$this->publishes([
			__DIR__ . '/../resources/views/vendor/livewire' => resource_path('views/vendor/livewire'),
		], 'lara-base-pagination');

		$this->publishes([
			__DIR__ . '/../resources/help' => resource_path('help'),
		], 'lara-base-help');

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('LaraLog', \Sitakgmbh\LaraBase\Facades\LaraLog::class);
		$loader->alias('LaraSettings', \Sitakgmbh\LaraBase\Facades\LaraSettings::class);
		$loader->alias('LaraUserSettings', \Sitakgmbh\LaraBase\Facades\LaraUserSettings::class);

		$this->commands([
			\Sitakgmbh\LaraBase\Console\Server\BackupDatabase::class,
			\Sitakgmbh\LaraBase\Console\InstallCommand::class,
		]);

        Livewire::component('layout.topbar',  \Sitakgmbh\LaraBase\Livewire\Layout\Topbar::class);
        Livewire::component('layout.sidebar', \Sitakgmbh\LaraBase\Livewire\Layout\Sidebar::class);
        Livewire::component('layout.footer',  \Sitakgmbh\LaraBase\Livewire\Layout\Footer::class);
        Livewire::component('actions.logout', \Sitakgmbh\LaraBase\Livewire\Actions\Logout::class);
		Livewire::component('admin.admin-dashboard', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\AdminDashboard::class);
		Livewire::component('admin.settings-page', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\SettingsPage::class);
		Livewire::component('tables.logs-table',  \Sitakgmbh\LaraBase\Livewire\Components\Tables\LogsTable::class);
		Livewire::component('tables.users-table', \Sitakgmbh\LaraBase\Livewire\Components\Tables\UsersTable::class);
		Livewire::component('admin.users.index',  \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Index::class);
		Livewire::component('admin.users.create', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Create::class);
		Livewire::component('admin.users.edit',   \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Edit::class);
		Livewire::component('admin.server-info', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\ServerInfo::class);
		Livewire::component('admin.changelog',   \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Changelog::class);
		Livewire::component('admin.logs.index', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs\Index::class);
		Livewire::component('components.modals.modal-manager',   \Sitakgmbh\LaraBase\Livewire\Components\Modals\ModalManager::class);
		Livewire::component('components.modals.artisan-output',  \Sitakgmbh\LaraBase\Livewire\Components\Modals\ArtisanOutput::class);
		Livewire::component('admin.tools.task-scheduler', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools\TaskScheduler::class);
		Livewire::component('components.modals.alert-modal', \Sitakgmbh\LaraBase\Livewire\Components\Modals\AlertModal::class);
		Livewire::component('components.modals.log-context', \Sitakgmbh\LaraBase\Livewire\Components\Modals\LogContext::class);
		Livewire::component('admin.logs.show', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs\Show::class);
		Livewire::component('admin.tools.model-query', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools\ModelQuery::class);
		Livewire::component('help.viewer', \Sitakgmbh\LaraBase\Livewire\Help\Viewer::class);
		Livewire::component('tables.incidents-table',    \Sitakgmbh\LaraBase\Livewire\Components\Tables\IncidentsTable::class);
		Livewire::component('admin.incidents.index',     \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents\Index::class);
		Livewire::component('admin.incidents.show',      \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents\Show::class);
		Livewire::component('profile.edit', \Sitakgmbh\LaraBase\Livewire\Pages\Profile\Edit::class);
		Livewire::component('profile.user-settings', \Sitakgmbh\LaraBase\Livewire\Pages\Profile\UserSettings::class);
		Livewire::component('components.modals.confirm-modal', \Sitakgmbh\LaraBase\Livewire\Components\Modals\ConfirmModal::class);
    }
}