<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Profil\ToggleTersediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfilController extends Controller
{
    public function __construct(
        private ToggleTersediaService $toggleTersediaService
    ) {}

    public function toggleTersedia(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $oldStatus = $user->is_tersedia;
        $newStatus = $this->toggleTersediaService->toggle($user);

        Log::info('[ProfilController] Bearer: {token}', [
            'token' => $request->bearerToken() ? substr($request->bearerToken(), 0, 20) . '...' : 'NONE',
        ]);

        return response()->json([
            'type' => 'reload_scene',
            'scene_id' => 'akun',
            'is_tersedia_before' => $oldStatus,
            'is_tersedia_after' => $newStatus,
        ]);
    }
}
