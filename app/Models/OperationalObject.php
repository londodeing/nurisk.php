<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalObject extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'object_type',
        'status',
        'title',
        'summary',
        'latitude',
        'longitude',
        'icon',
        'color',
        'priority',
        'popup_json',
        'timeline_json',
        'dashboard_json',
        'permissions',
        'refresh_interval',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'priority' => 'integer',
        'refresh_interval' => 'integer',
        'popup_json' => 'array',
        'timeline_json' => 'array',
        'dashboard_json' => 'array',
        'permissions' => 'array',
    ];
}
