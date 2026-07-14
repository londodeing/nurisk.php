<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceAuthController extends Controller
{
    private const MAX_DEVICES_PER_USER = 5;

    public function refreshToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_uuid' => 'required|string|regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format UUID tidak valid.',
            ], 422);
        }

        $deviceUuid = $request->input('device_uuid');
        $user = $request->user();

        $device = MobileDevice::where('uuid_device', $deviceUuid)->first();

        if (!$device) {
            $deviceCount = MobileDevice::where('id_pengguna', $user->id_pengguna)->count();
            if ($deviceCount >= self::MAX_DEVICES_PER_USER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maksimal ' . self::MAX_DEVICES_PER_USER . ' perangkat per pengguna.',
                ], 403);
            }

            $device = MobileDevice::create([
                'uuid_device' => $deviceUuid,
                'id_pengguna' => $user->id_pengguna,
                'platform' => $request->header('User-Agent', 'unknown'),
                'app_version' => $request->input('app_version', '1.0.0'),
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

        $tokenName = 'device-' . $deviceUuid;
        $token = $user->createToken($tokenName, ['device-access']);

        $device->update([
            'device_token' => hash('sha256', $token->plainTextToken),
            'token_expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'device_token' => $token->plainTextToken,
                'expires_at' => now()->addDays(30)->toIso8601String(),
            ]
        ]);
    }
}
