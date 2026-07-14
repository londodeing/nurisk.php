<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'layer_id',
        'name',
        'category',
        'render_type',
        'source_url',
        'source_type',
        'is_active',
        'is_public',
        'display_order',
        'refresh_interval_minutes',
        'cache_ttl',
        'legend_json',
        'style_json',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
        'cache_ttl' => 'integer',
        'legend_json' => 'array',
        'style_json' => 'array',
        'metadata' => 'array',
        'refresh_interval_minutes' => 'integer',
    ];
}
