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

                // Cache for 7 days (604800 seconds) as hazard layers are mostly static
                Cache::put($cacheKey, ['content' => $imageContent, 'type' => $contentType], 604800);

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
}
