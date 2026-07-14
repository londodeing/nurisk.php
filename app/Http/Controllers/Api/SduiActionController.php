<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SduiActionController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $actionType = $request->input('action_type');
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        switch ($actionType) {
            case 'profil.toggle_tersedia':
                $user->update([
                    'is_tersedia' => $user->is_tersedia ? 0 : 1
                ]);

                return response()->json([
                    'type' => 'reload_scene',
                    'scene_id' => 'akun'
                ]);

            default:
                return response()->json(['success' => false, 'message' => 'Action type not found'], 404);
        }
    }
}
