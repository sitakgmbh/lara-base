<?php

namespace Sitakgmbh\LaraBase\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PushSubscriptionController extends Controller
{
    /**
     * Gibt für diesen Endpoint zurück welche Kategorien subscribed sind.
     * GET /pwa/subscriptions?endpoint=https://...
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user     = auth()->user();
        $endpoint = $request->input('endpoint');

        $subscribed = $user->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->pluck('category')
            ->toArray();

        $categories = config('lara-base.pwa.push.categories', []);
        $result     = [];

        foreach ($categories as $cat) {
            $result[$cat['key']] = in_array($cat['key'], $subscribed);
        }

        return response()->json($result);
    }

    /**
     * Gibt alle Geräte des Users zurück (gruppiert nach Endpoint).
     * GET /pwa/devices
     */
    public function devices(Request $request): JsonResponse
    {
        $user = auth()->user();

        $rows = DB::table('push_subscriptions')
            ->where('subscribable_id', $user->id)
            ->where('subscribable_type', get_class($user))
            ->orderBy('created_at')
            ->get(['endpoint', 'category', 'device_name', 'created_at']);

        // Gruppieren nach Endpoint
        $devices = [];
        foreach ($rows as $row) {
            $ep = $row->endpoint;
            if (! isset($devices[$ep])) {
                $devices[$ep] = [
                    'endpoint'    => $ep,
                    'device_name' => $row->device_name ?? 'Unbekanntes Gerät',
                    'categories'  => [],
                    'created_at'  => $row->created_at,
                ];
            }
            $devices[$ep]['categories'][] = $row->category;
        }

        return response()->json(array_values($devices));
    }

    /**
     * Subscription für eine Kategorie speichern.
     * POST /pwa/subscribe
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'        => ['required', 'string'],
            'keys.p256dh'     => ['nullable', 'string'],
            'keys.auth'       => ['nullable', 'string'],
            'contentEncoding' => ['nullable', 'string'],
            'category'        => ['required', 'string'],
        ]);

        $user     = auth()->user();
        $category = $request->input('category');

        if (! $this->userCanSubscribe($user, $category)) {
            return response()->json(['error' => 'Unauthorized category'], 403);
        }

        DB::table('push_subscriptions')->updateOrInsert(
            [
                'subscribable_id'   => $user->id,
                'subscribable_type' => get_class($user),
                'endpoint'          => $request->input('endpoint'),
                'category'          => $category,
            ],
            [
                'public_key'       => $request->input('keys.p256dh'),
                'auth_token'       => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
                'device_name'      => $this->parseUserAgent($request->userAgent() ?? ''),
                'updated_at'       => now(),
                'created_at'       => now(),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Subscription für eine Kategorie entfernen.
     * DELETE /pwa/subscribe
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'category' => ['required', 'string'],
        ]);

        auth()->user()->pushSubscriptions()
            ->where('endpoint', $request->input('endpoint'))
            ->where('category', $request->input('category'))
            ->delete();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Ganzes Gerät (alle Kategorien dieses Endpoints) entfernen.
     * DELETE /pwa/device
     */
    public function destroyDevice(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user = auth()->user();

        DB::table('push_subscriptions')
            ->where('subscribable_id', $user->id)
            ->where('subscribable_type', get_class($user))
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json(['status' => 'ok']);
    }

    protected function userCanSubscribe(mixed $user, string $category): bool
    {
        $categories = config('lara-base.pwa.push.categories', []);

        foreach ($categories as $cat) {
            if ($cat['key'] !== $category) continue;
            $roles = $cat['roles'] ?? [];
            if (empty($roles)) return true;
            return $user->hasAnyRole($roles);
        }

        return false;
    }

    protected function parseUserAgent(string $ua): string
    {
        // Browser
        $browser = match (true) {
            str_contains($ua, 'Firefox')        => 'Firefox',
            str_contains($ua, 'Edg')            => 'Edge',
            str_contains($ua, 'OPR')            => 'Opera',
            str_contains($ua, 'Chrome')         => 'Chrome',
            str_contains($ua, 'Safari')         => 'Safari',
            default                             => 'Browser',
        };

        // OS
        $os = match (true) {
            str_contains($ua, 'iPhone')         => 'iPhone',
            str_contains($ua, 'iPad')           => 'iPad',
            str_contains($ua, 'Android')        => 'Android',
            str_contains($ua, 'Windows')        => 'Windows',
            str_contains($ua, 'Macintosh')      => 'macOS',
            str_contains($ua, 'Linux')          => 'Linux',
            default                             => 'Gerät',
        };

        return "{$browser} auf {$os}";
    }
}