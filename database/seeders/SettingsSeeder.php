<?php

namespace Sitakgmbh\LaraBase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
			[
				'key'         => 'app_update_url',
				'name'        => 'Update URL',
				'group'       => 'Update',
				'description' => 'URL zum WinStage Update-Verzeichnis.',
				'value'       => 'https://myserver.local/update',
				'type'        => 'string',
			],
            [
                'key'         => 'app_version',
                'name'        => 'Installierte Version',
                'group'       => 'Update',
                'description' => 'Aktuell installierte Version der Anwendung.',
                'value'       => '1.0.0',
                'type'        => 'string',
            ],
			[
				'key'         => 'app_version_latest',
				'name'        => 'Neueste Version',
				'group'       => 'Update',
				'description' => 'Neueste verfügbare Version der Anwendung.',
				'value'       => null,
				'type'        => 'string',
			],
            [
                'key'         => 'app_version_checked_at',
                'name'        => 'Letzter Update-Check',
                'group'       => 'Update',
                'description' => 'Zeitpunkt des letzten Versions-Checks.',
                'value'       => null,
                'type'        => 'string',
            ],
            [
                'key'         => 'debug_mode',
                'name'        => 'Debug-Modus',
				'group'       => 'System',
                'description' => 'Aktiviert erweiterte Debug-Ausgaben',
                'value'       => '0',
                'type'        => 'bool',
            ],
			[
				'key'         => 'show_help',
				'name'        => 'Hilfe anzeigen',
				'group'       => 'System',
				'description' => 'Zeigt Hilfe-Links im Footer an',
				'value'       => 'off',
				'type'        => 'enum',
			],
			[
				'key'         => 'show_contact',
				'name'        => 'Kontakt anzeigen',
				'group'       => 'System',
				'description' => 'Zeigt den Kontakt-Link im Footer an',
				'value'       => '1',
				'type'        => 'bool',
			],
        ];

        // Projekt-spezifische Settings aus Config
        $extra = config('lara-base.settings', []);

        foreach (array_merge($base, $extra) as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}