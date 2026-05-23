<?php

namespace Sitakgmbh\LaraBase\Console\Install;

trait SetupLdap
{
    protected function setupLdap(): void
    {
        $path = config_path('ldap.php');

        if (!$this->option('force') && file_exists($path)) {
            $this->warn('⚠ config/ldap.php bereits vorhanden – übersprungen');
            return;
        }

        $content = <<<'PHP'
<?php

return [
    'default' => env('LDAP_CONNECTION', 'default'),

    'connections' => [
        'default' => [
            'hosts'        => array_filter(explode(',', env('LDAP_HOSTS', env('LDAP_HOST')))),
            'port'         => env('LDAP_PORT', 389),
            'username'     => env('LDAP_USERNAME'),
            'password'     => env('LDAP_PASSWORD'),
            'base_dn'      => env('LDAP_BASE_DN'),
            'timeout'      => env('LDAP_TIMEOUT', 5),
            'use_ssl'      => env('LDAP_SSL', false),
            'use_tls'      => env('LDAP_TLS', false),
            'use_sasl'     => env('LDAP_SASL', false),
            'sasl_options' => [],
            'options'      => [
                LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_ALLOW,
                LDAP_OPT_PROTOCOL_VERSION   => 3,
                LDAP_OPT_REFERRALS          => 0,
            ],
        ],
    ],

    'logging' => [
        'enabled' => env('LDAP_LOGGING', false),
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level'   => 'debug',
    ],

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver'  => env('CACHE_DRIVER', 'file'),
    ],
];
PHP;

        file_put_contents($path, $content);
        $this->info('✓ config/ldap.php erstellt');
    }
}