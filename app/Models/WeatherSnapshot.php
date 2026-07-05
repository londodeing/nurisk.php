<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherSnapshot extends Model
{
    protected $table = 'weather_snapshots';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'territory_code',
        'territory_type',
        'territory_id',
        'provider',
        'current_weather',
        'hourly_forecast',
        'daily_forecast',
        'risk_analysis',
        'cached_at',
        'expires_at',
    ];

    protected $casts = [
        'current_weather' => 'array',
        'hourly_forecast' => 'array',
        'daily_forecast' => 'array',
        'risk_analysis' => 'array',
        'cached_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeFresh($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeStale($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByTerritory($query, string $territoryCode)
    {
        return $query->where('territory_code', $territoryCode);
    }

    public function isFresh(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }

    public function isStale(): bool
    {
        return !$this->isFresh();
    }
}
