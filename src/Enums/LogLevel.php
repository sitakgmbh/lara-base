<?php

namespace Sitakgmbh\LaraBase\Enums;

enum LogLevel: string
{
    case Error   = 'error';
    case Warning = 'warning';
    case Info    = 'info';
    case Debug   = 'debug';

    public function label(): string
    {
        return match($this) {
            self::Error   => 'Fehler',
            self::Warning => 'Warnung',
            self::Info    => 'Info',
            self::Debug   => 'Debug',
        };
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function isValid(string $value): bool
    {
        return in_array(strtolower($value), array_column(self::cases(), 'value'), true);
    }
}