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

];