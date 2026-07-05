<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    public function __construct(
        private AuthorizationContextService $authCtx
    ) {}

    public function index(): JsonResponse
    {
        $user = $this->authCtx->getCurrentUser();

        $devices = $user->mobileDevices()
            ->orderByRaw('COALESCE(last_sync_at, dibuat_pada) desc')
            ->get()
            ->map(fn ($d) => [
                'uuid_device' => $d->uuid_device,
                'platform' => $d->platform,
                'app_version' => $d->app_version,
                'status' => $d->status,
                'last_sync_at' => $d->last_sync_at?->toIso8601String(),
                'trust_score' => $d->trust_score,
                'token_expires_at' => $d->token_expires_at?->toIso8601String(),
                'dibuat_pada' => $d->dibuat_pada?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'data' => $devices]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $user = $this->authCtx->getCurrentUser();

        $device = $user->mobileDevices()->where('uuid_device', $uuid)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        }

        // Revoke all Sanctum tokens for this device
        $user->tokens()->where('device_uuid', $uuid)->delete();

        // Mark device as revoked
        $device->update(['status' => 'revoked']);

        return response()->json(['success' => true, 'message' => 'Device revoked successfully']);
    }

    public function logoutAll(): JsonResponse
    {
        $user = $this->authCtx->getCurrentUser();

        // Revoke ALL Sanctum tokens
        $deletedTokens = $user->tokens()->delete();

        // Revoke ALL mobile devices for this user
        $updatedDevices = $user->mobileDevices()->update(['status' => 'revoked']);

        return response()->json([
            'success' => true,
            'message' => 'All devices logged out successfully',
            'meta' => [
                'tokens_revoked' => $deletedTokens,
                'devices_revoked' => $updatedDevices,
            ],
        ]);
    }
}
