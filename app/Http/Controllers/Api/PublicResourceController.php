<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicResourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 20);
        $category = $request->query('category');

        $query = OrgAsset::query()
            ->with(['ownerNode'])
            ->where('status', 'active')
            ->where('readiness', 'ready');

        if ($category) {
            $query->where('category', $category);
        }

        $resources = $query->paginate($limit);

        $data = collect($resources->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'asset_code' => $item->asset_code,
                'name' => $item->name,
                'category' => $item->category,
                'sub_category' => $item->sub_category,
                'owner' => $item->ownerNode ? $item->ownerNode->name : $item->legal_owner_name,
                'territory' => $item->home_territory_code,
                'readiness' => $item->readiness,
                'metadata' => $item->metadata,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $resources->currentPage(),
                'last_page' => $resources->lastPage(),
                'total' => $resources->total(),
            ]
        ]);
    }
}
