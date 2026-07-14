<?php

namespace App\Services;

use App\Models\MapLayer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GisAdapterService
{
    /**
     * Get all active map layers configurations
     */
    public function getActiveLayers()
    {
        return MapLayer::where('is_active', true)->orderBy('display_order')->get();
    }

    /**
     * READ-ONLY: Get layer data for API.
     * MUST NOT perform synchronous external HTTP requests.
     */
    public function getLayerDataForApi(string $layerId)
    {
        $layer = MapLayer::where('layer_id', $layerId)->firstOrFail();

        // If it's a raster tile, return metadata, not GeoJSON
        if ($layer->render_type === 'raster_tile') {
            return [
                'type' => 'TileLayer',
                'layer_id' => $layer->layer_id,
                'source_url' => $layer->source_url,
                'legend' => $layer->legend_json,
            ];
        }

        $cacheKey = "gis_layer_data_{$layer->layer_id}";

        // READ ONLY - Do not pass a closure to compute if missing!
        // If missing, return empty feature collection. The Scheduler will fill it.
        $data = Cache::get($cacheKey);

        if (!$data) {
            Log::warning("GIS Cache Miss for layer {$layerId}. Returning empty collection.");
            return ['type' => 'FeatureCollection', 'features' => []];
        }

        return $data;
    }

    /**
     * WRITE-ONLY: Fetch data from provider and put in Cache.
     * This MUST ONLY be called by GisSyncCommand (Scheduler).
     */
    public function syncLayerData(MapLayer $layer)
    {
        $data = $this->executeProviderDriver($layer);

        if ($data) {
            $ttl = $layer->cache_ttl > 0 ? $layer->cache_ttl : 3600;
            Cache::put("gis_layer_data_{$layer->layer_id}", $data, $ttl);
            Log::info("GIS Layer {$layer->layer_id} synced and cached successfully.");
        } else {
            Log::error("GIS Layer {$layer->layer_id} sync failed or returned no data.");
        }
    }

    /**
     * Abstracted Provider Driver Execution
     */
    protected function executeProviderDriver(MapLayer $layer)
    {
        // In a true enterprise setup, these would be separate Driver classes.
        // For M2.2, we abstract them into discrete methods representing drivers.
        return match ($layer->source_type) {
            'bmkg' => $this->bmkgDriver($layer),
            'geojson_external' => $this->genericGeoJsonDriver($layer),
            default => null,
        };
    }

    protected function genericGeoJsonDriver(MapLayer $layer)
    {
        if (!$layer->source_url) return null;
        
        try {
             $response = Http::timeout(15)->get($layer->source_url);
             if ($response->successful()) {
                 return $response->json();
             }
        } catch (\Exception $e) {
             Log::error("Generic GeoJSON Provider Error [{$layer->layer_id}]: " . $e->getMessage());
        }
        return null;
    }

    protected function bmkgDriver(MapLayer $layer)
    {
        try {
            $response = Http::timeout(10)->get('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json');
            if ($response->successful()) {
                $data = $response->json();
                $gempaList = $data['Infogempa']['gempa'] ?? [];

                $features = [];
                foreach ($gempaList as $gempa) {
                    $coords = explode(',', $gempa['Coordinates']);
                    if (count($coords) === 2) {
                        $features[] = [
                            'type' => 'Feature',
                            'id' => uniqid('bmkg_'),
                            'geometry' => [
                                'type' => 'Point',
                                'coordinates' => [(float)$coords[1], (float)$coords[0]]
                            ],
                            'properties' => [
                                'layer' => $layer->layer_id,
                                'title' => 'Gempa Bumi ' . $gempa['Magnitude'] . ' SR',
                                'subtitle' => $gempa['Wilayah'],
                                'severity' => (float)$gempa['Magnitude'] > 5.0 ? 'Major' : 'Moderate',
                                'icon' => 'earthquake',
                                'color' => '#EF4444',
                                'clickable' => true,
                                'updated_at' => $gempa['Tanggal'] . ' ' . $gempa['Jam'],
                                'kedalaman' => $gempa['Kedalaman']
                            ]
                        ];
                    }
                }

                return [
                    'type' => 'FeatureCollection',
                    'features' => $features
                ];
            }
        } catch (\Exception $e) {
            Log::error("BMKG Provider Error: " . $e->getMessage());
        }
        return null;
    }
}
