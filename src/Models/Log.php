<?php

namespace Sitakgmbh\LaraBase\Models;

use Illuminate\Database\Eloquent\Model;
use Sitakgmbh\LaraBase\Enums\LogLevel;
use Sitakgmbh\LaraBase\Enums\LogCategory;

class Log extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
        'created_at',
    ];

    protected $casts = [
        'level'      => LogLevel::class,
        'category'   => LogCategory::class,
        'created_at' => 'datetime',
    ];
}