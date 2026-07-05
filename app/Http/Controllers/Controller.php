<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    protected function apiResponse($data = null, string $message = 'Data berhasil diambil', int $status = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function apiPaginatedResponse($paginator, $resourceClass, string $message = 'Data berhasil diambil'): \Illuminate\Http\JsonResponse
    {
        $resource = $resourceClass::collection($paginator)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource['data'],
            'meta' => [
                'page' => $resource['meta']['current_page'] ?? 1,
                'per_page' => $resource['meta']['per_page'] ?? 15,
                'total' => $resource['meta']['total'] ?? 0,
            ]
        ]);
    }
}
