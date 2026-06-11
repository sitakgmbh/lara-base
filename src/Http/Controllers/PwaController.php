<?php

namespace Sitakgmbh\LaraBase\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PwaController extends Controller
{
    /**
     * Liefert manifest.json dynamisch aus der Config.
     */
    public function manifest(): Response
    {
        $config = config('lara-base.pwa.manifest', []);

        $manifest = [
            'name'             => $config['name']             ?? config('app.name'),
            'short_name'       => $config['short_name']       ?? config('app.name'),
            'description'      => $config['description']      ?? '',
            'start_url'        => $config['start_url']        ?? '/',
            'display'          => $config['display']          ?? 'standalone',
            'background_color' => $config['background_color'] ?? '#ffffff',
            'theme_color'      => $config['theme_color']      ?? '#000000',
            'orientation'      => $config['orientation']      ?? 'any',
            'icons'            => $config['icons']            ?? [],
			'screenshots'      => $config['screenshots']      ?? [],
        ];

        return response(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Liefert den Service Worker als JS.
     */
    public function serviceWorker(): Response
    {
        $sw = config('lara-base.pwa.service_worker', []);

        $cacheName    = $sw['cache_name']    ?? 'app-v1';
        $strategy     = $sw['strategy']      ?? 'network-first';
        $precacheUrls = $sw['precache_urls'] ?? ['/'];
        $offlineUrl   = config('lara-base.pwa.offline_url', '/offline');

        $content = view('lara-base::pwa.sw', compact(
            'cacheName',
            'strategy',
            'precacheUrls',
            'offlineUrl'
        ))->render();

        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Offline-Fallback Seite.
     */
    public function offline(): \Illuminate\View\View
    {
        return view('lara-base::pwa.offline');
    }
}