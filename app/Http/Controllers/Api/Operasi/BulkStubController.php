<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkStubController extends Controller
{
    public function logistikBulk(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $items = $request->input('items');
        $successes = [];
        foreach ($items as $index => $item) {
            $successes[] = [
                'index' => $index,
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'message' => 'Logistik record processed successfully (stub)',
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses bulk logistik selesai (stub)',
            'data' => [
                'processed' => count($items),
                'success_count' => count($items),
                'failed_count' => 0,
                'successes' => $successes,
                'failures' => [],
            ]
        ]);
    }

    public function mobilisasiBulk(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $items = $request->input('items');
        $successes = [];
        foreach ($items as $index => $item) {
            $successes[] = [
                'index' => $index,
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'message' => 'Mobilisasi record processed successfully (stub)',
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses bulk mobilisasi selesai (stub)',
            'data' => [
                'processed' => count($items),
                'success_count' => count($items),
                'failed_count' => 0,
                'successes' => $successes,
                'failures' => [],
            ]
        ]);
    }
}
