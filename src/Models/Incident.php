<?php

namespace Sitakgmbh\LaraBase\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'metadata',
        'created_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'resolved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'created_by');
    }

    public function resolver()
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function resolve(?int $userId = null): void
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
        ]);
    }
}