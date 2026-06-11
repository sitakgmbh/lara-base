<?php

namespace Sitakgmbh\LaraBase\Pwa;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Sitakgmbh\LaraBase\Http\Controllers\PwaController;
use Sitakgmbh\LaraBase\Http\Controllers\PushSubscriptionController;

class PwaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerBladeDirective();

        if (config('lara-base.pwa.enabled', false)) {
            $this->registerPwaRoutes();
        }

        if (config('lara-base.pwa.push.enabled', false)) {
            $this->registerPushRoutes();
        }
    }

    protected function registerPwaRoutes(): void
    {
        $this->app['router']->get('/manifest.json', [PwaController::class, 'manifest'])
            ->name('pwa.manifest')
            ->middleware('web');

        $this->app['router']->get('/sw.js', [PwaController::class, 'serviceWorker'])
            ->name('pwa.sw')
            ->middleware('web');

        if (config('lara-base.pwa.offline_url')) {
            $this->app['router']->get('/offline', [PwaController::class, 'offline'])
                ->name('pwa.offline')
                ->middleware('web');
        }
    }

    protected function registerPushRoutes(): void
    {
        $middleware = ['web', 'auth'];

        // Subscription-Status für diesen Endpoint
        $this->app['router']->get('/pwa/subscriptions', [PushSubscriptionController::class, 'index'])
            ->name('pwa.subscriptions')
            ->middleware($middleware);

        // Alle Geräte des Users
        $this->app['router']->get('/pwa/devices', [PushSubscriptionController::class, 'devices'])
            ->name('pwa.devices')
            ->middleware($middleware);

        // Kategorie abonnieren
        $this->app['router']->post('/pwa/subscribe', [PushSubscriptionController::class, 'store'])
            ->name('pwa.subscribe')
            ->middleware($middleware);

        // Kategorie abbestellen
        $this->app['router']->delete('/pwa/subscribe', [PushSubscriptionController::class, 'destroy'])
            ->name('pwa.unsubscribe')
            ->middleware($middleware);

        // Ganzes Gerät entfernen
        $this->app['router']->delete('/pwa/device', [PushSubscriptionController::class, 'destroyDevice'])
            ->name('pwa.device.destroy')
            ->middleware($middleware);
    }

    protected function registerBladeDirective(): void
    {
        Blade::directive('pwaHead', function () {
            return "<?php echo view('lara-base::pwa.head')->render(); ?>";
        });
    }
}