<?php

namespace Sitakgmbh\LaraBase\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class BaseUser extends Authenticatable
{
	use Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
        'password',
        'auth_type',
        'ad_sid',
        'firstname',
        'lastname',
        'is_enabled',
		'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_enabled'        => 'boolean',
			'settings'          => 'array',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname}");
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function isLdap(): bool
    {
        return $this->auth_type === 'ldap';
    }

    public function isLocal(): bool
    {
        return $this->auth_type === 'local';
    }

	public function adUser()
	{
		return $this->hasOne(\Sitakgmbh\LaraBase\Models\AdUser::class, 'sid', 'ad_sid');
	}

	public function getSetting(string $key, $default = null)
	{
		return data_get($this->settings ?? [], $key, $default);
	}

	public function setSetting(string $key, $value): void
	{
		$settings       = $this->settings ?? [];
		$settings[$key] = $value;
		$this->settings = $settings;
		$this->save();
	}
}
