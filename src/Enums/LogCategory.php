<?php

namespace Sitakgmbh\LaraBase\Enums;

enum LogCategory: string
{
    case System   = 'system';
    case Database = 'database';
    case Auth     = 'auth';
    case Api      = 'api';
    case Email    = 'email';

    public function label(): string
    {
        return match($this) {
            self::System   => 'System',
            self::Database => 'Datenbank',
            self::Auth     => 'Authentifizierung',
            self::Api      => 'API',
            self::Email    => 'E-Mail',
        };
    }

    public static function labels(): array
    {
        $base = collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();

        $extra = config('lara-base.log_categories', []);

        return array_merge($base, $extra);
    }

    public static function isValid(string $value): bool
    {
        $baseValues  = array_column(self::cases(), 'value');
        $extraValues = array_keys(config('lara-base.log_categories', []));

        return in_array(strtolower($value), array_merge($baseValues, $extraValues), true);
    }
}