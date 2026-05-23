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