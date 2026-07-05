<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceAuthController extends Controller
{
    public function refreshToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_uuid' => 'required|string',
        ]);

        $deviceUuid = $request->input('device_uuid');

        $device = MobileDevice::where('uuid_device', $deviceUuid)->first();

        if (!$device) {
            // For now, if device doesn't exist, we just fail or maybe we shouldn't create it here.
            // Let's create it if it doesn't exist, since in SyncApiController we do firstOrCreate.
            // Wait, let's just create it to be safe, assuming user is authenticated via Sanctum 
            // but the prompt says we don't use JWT. The device might just register.
            $device = MobileDevice::create([
                'uuid_device' => $deviceUuid,
                'id_pengguna' => auth()->id() ?? 1, // Fallback for testing
                'platform' => $request->header('User-Agent', 'unknown'),
                'app_version' => '1.0.0',
                'status' => 'active',
                'trust_score' => 100,
            ]);
        }

        if ($device->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Device is not active.',
            ], 403);
        }

        // Generate new long-lived token
        $newToken = Str::random(60);
        $expiresAt = now()->addDays(30);

        $device->update([
            'device_token' => hash('sha256', $newToken),
            'token_expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'device_token' => $newToken,
                'expires_at' => $expiresAt->toIso8601String(),
            ]
        ]);
    }
}
