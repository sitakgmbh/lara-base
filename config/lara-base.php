<?php

return [
    'test_mode' => env('APP_DEBUG_MODE', false),

	'auth' => [
		'mode' => env('APP_AUTH_MODE', 'local'), // local | ldap
	],

    'ldap' => [
        'groups' => [
            'admin' => env('APP_ADMIN_GROUP', 'app-admins'),
            'user'  => env('APP_USER_GROUP', 'app-users'),
        ],
    ],

	'mail' => [
		'test_mode' => env('APP_TEST_MODE', false),
	],

    'log_categories' => [],

	'settings' => [
		// Projekt-spezifische Standard-Settings hier:
		// [
		//     'key'         => 'show_help',
		//     'name'        => 'Hilfe anzeigen',
		//     'description' => 'Zeigt Hilfe-Links im Footer an',
		//     'value'       => 'off',
		//     'type'        => 'enum',
		// ],
	],

    'menu' => [
        [
            'title' => 'Navigation',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon'  => 'mdi mdi-view-dashboard',
                    'url'   => '/dashboard',
                ],
            ],
        ],
        [
            'title' => 'Admin',
            'items' => [
                [
                    'label' => 'Systemsteuerung',
                    'url' => '/admin',
                    'icon' => 'mdi mdi-apps',
                ],
            ],
        ],
    ],

	'admin_dashboard' => [
		[
			'group'       => 'Allgemein',
			'title'       => 'Einstellungen',
			'description' => 'Systemweite Einstellungen ändern',
			'icon'        => 'mdi mdi-cog-outline',
			'color'       => 'dark',
			'route'       => 'admin.settings',
			'is_external' => false,
		],
		[
			'group'       => 'Allgemein',
			'title'       => 'Benutzerverwaltung',
			'description' => 'Benutzerzugriffe verwalten',
			'icon'        => 'mdi mdi-account-multiple-outline',
			'color'       => 'dark',
			'route'       => 'admin.users.index',
			'is_external' => false,
		],
		[
			'group'       => 'Allgemein',
			'title'       => 'Updates',
			'description' => 'Aktualisierungen suchen und installieren',
			'icon'        => 'mdi mdi-update',
			'color'       => 'dark',
			'route'       => 'admin.update',
			'is_external' => false,
		],
		[
			'group'       => 'Werkzeuge',
			'title'       => 'Aufgabenplaner',
			'description' => 'Tasks einsehen und ausführen',
			'icon'        => 'mdi mdi-console',
			'color'       => 'dark',
			'route'       => 'admin.tools.task-scheduler',
			'is_external' => false,
		],
		[
			'group'       => 'Werkzeuge',
			'title'       => 'Model Query',
			'description' => 'Datenbank durchsuchen über Models',
			'icon'        => 'mdi mdi-database-search-outline',
			'color'       => 'dark',
			'route'       => 'admin.tools.model-query',
			'is_external' => false,
		],
		[
			'group'       => 'Info',
			'title'       => 'Incidents',
			'description' => 'Incidents einsehen und verwalten',
			'icon'        => 'mdi mdi-alert-circle-outline',
			'color'       => 'dark',
			'route'       => 'admin.incidents.index',
			'is_external' => false,
		],
		[
			'group'       => 'Info',
			'title'       => 'Logs',
			'description' => 'Systemlogs einsehen',
			'icon'        => 'mdi mdi-clipboard-text-outline',
			'color'       => 'dark',
			'route'       => 'admin.logs.index',
			'is_external' => false,
		],
		[
			'group'       => 'Info',
			'title'       => 'Server',
			'description' => 'Informationen zum System',
			'icon'        => 'mdi mdi-server-outline',
			'color'       => 'dark',
			'route'       => 'admin.server-info',
			'is_external' => false,
		],
		[
			'group'       => 'Info',
			'title'       => 'Changelog',
			'description' => 'Änderungen einsehen',
			'icon'        => 'mdi mdi-newspaper-variant-outline',
			'color'       => 'dark',
			'route'       => 'admin.changelog',
			'is_external' => false,
		],
	],

	'company' => [
		'name' => env('COMPANY_NAME', ''),
		'phone' => env('COMPANY_PHONE', ''),
		'email' => env('COMPANY_EMAIL', ''),
	],

	'help' => [
		'title' => env('APP_NAME') . ' Hilfe',
		'phone' => env('COMPANY_PHONE', ''),
		'email' => env('COMPANY_EMAIL', ''),
	],

	'task_scheduler' => [
		'allowed' => [
			'db:backup',
			'server:check-update'
		],
	],

	'model_query' => [
		'models' => [
			'User' => \App\Models\User::class,
			'Log'  => \Sitakgmbh\LaraBase\Models\Log::class,
		],
	],

	'pwa' => [

		'enabled' => env('PWA_ENABLED', false),

		'manifest' => [
			'name'             => env('PWA_NAME', env('APP_NAME', 'App')),
			'short_name'       => env('PWA_SHORT_NAME', env('APP_NAME', 'App')),
			'description'      => env('PWA_DESCRIPTION', ''),
			'start_url'        => env('PWA_START_URL', '/'),
			'display'          => env('PWA_DISPLAY', 'standalone'), // standalone | minimal-ui | fullscreen | browser
			'background_color' => env('PWA_BACKGROUND_COLOR', '#ffffff'),
			'theme_color'      => env('PWA_THEME_COLOR', '#000000'),
			'orientation'      => env('PWA_ORIENTATION', 'any'),
			'icons' => [
				['src' => '/icons-pwa/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
				['src' => '/icons-pwa/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
				['src' => '/icons-pwa/icon-512x512.png',  'sizes' => '512x512',  'type' => 'image/png', 'purpose' => 'any'],
				['src' => '/icons-pwa/icon-512x512.png',  'sizes' => '512x512',  'type' => 'image/png', 'purpose' => 'maskable'],
			],
			'screenshots' => [
				[
					'src'          => '/icons-pwa/screenshot-desktop.png',
					'sizes'        => '1280x720',
					'type'         => 'image/png',
					'form_factor'  => 'wide',
					'label'        => config('app.name'),
				],
				[
					'src'          => '/icons-pwa/screenshot-mobile.png',
					'sizes'        => '390x844',
					'type'         => 'image/png',
					'form_factor'  => 'narrow',
					'label'        => config('app.name'),
				],
			],
		],

		'service_worker' => [
			// Cache-Strategie für Navigation/HTML-Requests
			'strategy'      => env('PWA_SW_STRATEGY', 'network-first'), // network-first | cache-first | stale-while-revalidate
			'cache_name'    => env('PWA_CACHE_NAME', env('APP_NAME', 'app') . '-v1'),
			// URLs die beim SW-Install sofort gecacht werden
			'precache_urls' => [
				'/',
				'/offline',
			],
		],

		// Offline-Fallback Route (null = deaktiviert)
		'offline_url' => '/offline',

		'push' => [

			'enabled' => env('PWA_PUSH_ENABLED', false),

			'vapid' => [
				'subject'     => env('VAPID_SUBJECT', 'mailto:' . env('COMPANY_EMAIL', 'admin@example.com')),
				'public_key'  => env('VAPID_PUBLIC_KEY', ''),
				'private_key' => env('VAPID_PRIVATE_KEY', ''),
			],

			// Kategorien — pro Projekt anpassbar
			// 'roles' => []  = alle authentifizierten User
			// 'roles' => ['admin'] = nur Admins sehen diese Kategorie im Profil
			'categories' => [
				[
					'key'   => 'system',
					'label' => 'System-Meldungen',
					'roles' => ['admin'],
				],
				[
					'key'   => 'incidents',
					'label' => 'Incidents',
					'roles' => ['admin'],
				],
			],

		],

	],

];