<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InariskWmsProxyController extends Controller
{
    /**
     * Proxy WMS requests to Inarisk with caching.
     */
    public function proxy(Request $request)
    {
        $baseUrl = env('INARISK_WMS_URL', 'https://inarisk1.bnpb.go.id:8443/geoserver/raster/wms');
        $queryParams = $request->query();

        // Build a unique cache key based on query parameters
        ksort($queryParams);
        $cacheKey = 'wms_proxy_' . md5(json_encode($queryParams));

        // Attempt to get the image from cache
        $cachedImage = Cache::get($cacheKey);

        if ($cachedImage && is_array($cachedImage)) {
            return response($cachedImage['content'], 200)
                ->header('Content-Type', $cachedImage['type'])
                ->header('X-Proxy-Cache', 'HIT');
        }

        try {
            // WMS requests can take some time, especially for large bounds
            $response = Http::timeout(15)->get($baseUrl, $queryParams);

            if ($response->successful()) {
                $imageContent = $response->body();
                $contentType = $response->header('Content-Type') ?? 'image/png';

                // Cache for 30 days (2592000 seconds) as hazard layers are mostly static
                Cache::put($cacheKey, ['content' => $imageContent, 'type' => $contentType], 2592000);

                return response($imageContent, 200)
                    ->header('Content-Type', $contentType)
                    ->header('X-Proxy-Cache', 'MISS');
            }

            Log::error('Inarisk WMS Proxy failed: ' . $response->status(), ['url' => $baseUrl, 'params' => $queryParams]);
            
            // Return transparent 1x1 png on failure to not break leaflet tiles completely (optional, but good practice for maps)
            $transparentTile = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
            return response($transparentTile, 200)->header('Content-Type', 'image/png');

        } catch (\Exception $e) {
            Log::error('Inarisk WMS Proxy exception: ' . $e->getMessage(), ['url' => $baseUrl]);
            $transparentTile = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
            return response($transparentTile, 200)->header('Content-Type', 'image/png');
        }
    }

    /**
     * Convert {z}/{x}/{y} tile coordinates to WMS bbox and proxy to inaRISK.
     * MapLibre GL (Flutter) → WMS 1.3.0 GetMap → 256×256 PNG tile.
     * Cache: 30 days (2592000s).
     */
    public function tile(Request $request, int $z, int $x, int $y)
    {
        $layers = $request->query('layers', 'raster:INDEKS_BAHAYA_BANJIR1');

        $n = pow(2, $z);
        $lonWest = ($x / $n) * 360.0 - 180.0;
        $lonEast = (($x + 1) / $n) * 360.0 - 180.0;
        $latSouth = atan(sinh(M_PI * (1 - 2 * ($y + 1) / $n))) * 180.0 / M_PI;
        $latNorth = atan(sinh(M_PI * (1 - 2 * $y / $n))) * 180.0 / M_PI;

        $cacheKey = 'inarisk_tile_' . md5("{$z}_{$x}_{$y}_{$layers}");
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response($cached, 200)
                ->header('Content-Type', 'image/png')
                ->header('X-Cache', 'HIT');
        }

        try {
            $baseUrl = env('INARISK_WMS_URL', 'https://inarisk1.bnpb.go.id:8443/geoserver/raster/wms');

            $response = Http::retry(2, 1000)->timeout(20)->get($baseUrl, [
                'SERVICE' => 'WMS',
                'VERSION' => '1.3.0',
                'REQUEST' => 'GetMap',
                'LAYERS' => $layers,
                'STYLES' => 'index_bahaya',
                'FORMAT' => 'image/png',
                'TRANSPARENT' => 'TRUE',
                'WIDTH' => 256,
                'HEIGHT' => 256,
                'CRS' => 'EPSG:4326',
                'BBOX' => "{$latSouth},{$lonWest},{$latNorth},{$lonEast}",
            ]);

            if ($response->successful()) {
                Cache::put($cacheKey, $response->body(), 2592000);
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('X-Cache', 'MISS');
            }

            Log::error('Inarisk tile proxy failed', [
                'z' => $z, 'x' => $x, 'y' => $y,
                'status' => $response->status(),
                'body' => $response->body(),
                'layers' => $layers,
            ]);
        } catch (\Exception $e) {
            Log::error('Inarisk tile proxy exception', [
                'z' => $z, 'x' => $x, 'y' => $y,
                'layers' => $layers,
                'error' => $e->getMessage(),
            ]);
        }

        $transparentTile = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        return response($transparentTile, 200)->header('Content-Type', 'image/png');
    }
}
