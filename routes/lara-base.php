<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () 
{
    Route::get('/login', \Sitakgmbh\LaraBase\Livewire\Pages\Auth\LoginPage::class)->name('login');
});

Route::middleware(['web', 'auth'])->group(function () 
{
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', \Sitakgmbh\LaraBase\Livewire\Pages\DashboardPage::class)->name('dashboard');
	
	Route::get('/profile/edit', \Sitakgmbh\LaraBase\Livewire\Pages\Profile\Edit::class)->name('profile.edit');
	Route::get('/profile/settings', \Sitakgmbh\LaraBase\Livewire\Pages\Profile\UserSettings::class)->name('profile.settings');

	Route::get('/help/{key}', \Sitakgmbh\LaraBase\Livewire\Help\Viewer::class)->name('help.viewer');
});

Route::middleware(['web', 'auth', 'role:admin'])->group(function ()
{
    Route::get('/admin/settings', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\SettingsPage::class)->name('admin.settings');
    Route::get('/admin', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\AdminDashboard::class)->name('admin.dashboard');

    Route::prefix('admin/incidents')->name('admin.incidents.')->group(function () {
        Route::get('/',      \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents\Index::class)->name('index');
        Route::get('/{id}',  \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents\Show::class)->name('show');
    });

	Route::get('/admin/tools/task-scheduler', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools\TaskScheduler::class)->name('admin.tools.task-scheduler');
	Route::get('/admin/tools/model-query', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools\ModelQuery::class)->name('admin.tools.model-query');

	Route::get('/admin/logfiles', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs\Logfiles::class)->name('admin.logfiles.index');

	Route::prefix('admin/logs')->name('admin.logs.')->group(function () {
		Route::get('/',     \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs\Index::class)->name('index');
		Route::get('/{log}', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs\Show::class)->name('show');
	});

    Route::get('/admin/server-info', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\ServerInfo::class)->name('admin.server-info');
    Route::get('/admin/changelog',   \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Changelog::class)->name('admin.changelog');	

    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/',           \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Index::class)->name('index');
        Route::get('/create',     \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Create::class)->name('create');
        Route::get('/{user}/edit', \Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users\Edit::class)->name('edit');
    });

});