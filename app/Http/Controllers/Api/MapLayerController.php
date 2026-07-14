<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GisAdapterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapLayerController extends Controller
{
    protected GisAdapterService $gisService;

    public function __construct(GisAdapterService $gisService)
    {
        $this->gisService = $gisService;
    }

    /**
     * Get the registry of all available layers
     */
    public function index(): JsonResponse
    {
        $layers = $this->gisService->getActiveLayers();
        return response()->json([
            'status' => 'success',
            'data' => $layers
        ]);
    }

    /**
     * Get GeoJSON or Tile config for a specific layer
     */
    public function show(string $layerId): JsonResponse
    {
        try {
            $data = $this->gisService->getLayerDataForApi($layerId);
            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Layer not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function config(): JsonResponse
    {
        // Server-Driven Legend & Layer Control
        return response()->json([
            'status' => 'success',
            'version' => '1.1.0',
            'supported_render_types' => ['geojson_generic', 'raster_tile'],
            'groups' => [
                [
                    'id' => 'hazard',
                    'name' => 'Hazard & Ancaman',
                    'layers' => [
                        ['id' => 'flood', 'name' => 'Banjir', 'type' => 'raster', 'color' => '#3b82f6', 'default_visible' => false],
                        ['id' => 'hotspot', 'name' => 'Titik Panas', 'type' => 'geojson', 'color' => '#ef4444', 'default_visible' => false]
                    ]
                ],
                [
                    'id' => 'operational',
                    'name' => 'Operasional',
                    'layers' => [
                        ['id' => 'incident', 'name' => 'Insiden', 'type' => 'operational', 'color' => '#f97316', 'icon' => 'warning', 'default_visible' => true],
                        ['id' => 'ambulance', 'name' => 'Ambulans', 'type' => 'operational', 'color' => '#ef4444', 'icon' => 'local_hospital', 'default_visible' => true],
                        ['id' => 'posko', 'name' => 'Posko', 'type' => 'operational', 'color' => '#10b981', 'icon' => 'home', 'default_visible' => true],
                        ['id' => 'shelter', 'name' => 'Shelter', 'type' => 'operational', 'color' => '#8b5cf6', 'icon' => 'house', 'default_visible' => true],
                        ['id' => 'volunteer', 'name' => 'Relawan', 'type' => 'operational', 'color' => '#06b6d4', 'icon' => 'person', 'default_visible' => false]
                    ]
                ]
            ]
        ]);
    }

    public function providers(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                ['id' => 'internal', 'name' => 'NURISK Internal'],
                ['id' => 'bmkg', 'name' => 'BMKG'],
                ['id' => 'inarisk', 'name' => 'BNPB InaRISK'],
            ]
        ]);
    }

    /**
     * Operational Digital Twin API with Spatial Filter Engine
     */
    public function operationalObjects(Request $request, string $type): JsonResponse
    {
        $query = \App\Models\OperationalObject::query();

        // 1. Filter Type (Multiple types separated by comma if needed, or fallback to single type)
        if ($type !== 'all') {
            $types = explode(',', $type);
            $query->whereIn('object_type', $types);
        }

        // 2. Spatial Filter: Radius (lat, lng, radius_km)
        if ($request->has('lat') && $request->has('lng') && $request->has('radius')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = (float) $request->radius; // in km

            // Haversine formula approximation for fast query
            // This assumes Earth radius is 6371 km
            $query->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                  ->having('distance', '<=', $radius);
        }

        // 3. Filter Severity / Status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Fetch objects
        $objects = $query->orderBy('priority', 'desc')->get();

        $features = $objects->map(function ($obj) {
            return [
                'type' => 'Feature',
                'id' => $obj->id,
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$obj->longitude, $obj->latitude],
                ],
                'properties' => [
                    'object_type' => $obj->object_type,
                    'title' => $obj->title,
                    'summary' => $obj->summary,
                    'status' => $obj->status,
                    'icon' => $obj->icon,
                    'color' => $obj->color,
                    'priority' => $obj->priority,
                    'clickable' => true,
                    'action_type' => 'open_bottomsheet',
                    'popup_json' => $obj->popup_json,
                    'timeline_json' => $obj->timeline_json,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
