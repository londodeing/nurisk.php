<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthPenggunaPin;
use App\Models\PenggunaJabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthApiController extends Controller
{
    private const MAX_ATTEMPTS = 3;
    private const LOCKOUT_MINUTES = 15;

    public function selectMandate(Request $request): JsonResponse
    {
        $request->validate([
            'mandate_id' => 'required|integer|exists:pengguna_jabatan,id_pengguna_jabatan',
        ]);

        $user = $request->user();

        $jabatan = PenggunaJabatan::with('jabatan')
            ->where('id_pengguna_jabatan', $request->mandate_id)
            ->where('id_pengguna', $user->id_pengguna)
            ->first();

        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Mandat tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $role = $user->peran?->nama_peran ?? 'relawan';
        $scopeType = $jabatan->tipe_lingkup ?? $user->default_scope_type ?? 'pcnu';
        $scopeId = $jabatan->id_lingkup ?? $user->default_scope_id;

        return response()->json([
            'success' => true,
            'message' => 'Mandat berhasil diaktifkan.',
            'data' => [
                'role' => $role,
                'scope_id' => (string) $scopeId,
                'scope_type' => $scopeType,
                'jabatan_name' => $jabatan->jabatan?->nama_jabatan ?? 'Anggota',
                'mandate_id' => (string) $jabatan->id_pengguna_jabatan,
            ]
        ]);
    }

    public function setPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|digits:6',
            'pin_confirmation' => 'required|digits:6|same:pin',
        ]);

        $user = $request->user();

        AuthPenggunaPin::setPin($user->id_pengguna, $request->pin);

        return response()->json([
            'status' => 'success',
            'message' => 'PIN berhasil dibuat.',
        ]);
    }

    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $user = $request->user();

        if ($this->isLockedOut($user->id_pengguna)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan PIN. Silakan coba lagi dalam 15 menit.',
            ], 429);
        }

        $pinRecord = AuthPenggunaPin::where('id_pengguna', $user->id_pengguna)->first();

        if (!$pinRecord) {
            $this->recordAttempt($user->id_pengguna, $request, false);
            return response()->json([
                'status' => 'error',
                'message' => 'PIN belum dibuat. Silakan buat PIN terlebih dahulu.',
            ], 403);
        }

        $isValid = $pinRecord->verify($request->pin);

        $this->recordAttempt($user->id_pengguna, $request, $isValid);

        if (!$isValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN Tidak Valid.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'PIN Valid.',
        ]);
    }

    private function isLockedOut(int $idPengguna): bool
    {
        $recentAttempts = DB::table('auth_pin_attempts')
            ->where('id_pengguna', $idPengguna)
            ->where('was_successful', false)
            ->where('attempted_at', '>=', now()->subMinutes(self::LOCKOUT_MINUTES))
            ->count();

        return $recentAttempts >= self::MAX_ATTEMPTS;
    }

    private function recordAttempt(int $idPengguna, Request $request, bool $wasSuccessful): void
    {
        DB::table('auth_pin_attempts')->insert([
            'id_pengguna' => $idPengguna,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'was_successful' => $wasSuccessful,
            'attempted_at' => now(),
        ]);

        Log::info('PIN verification attempt', [
            'id_pengguna' => $idPengguna,
            'was_successful' => $wasSuccessful,
            'ip_address' => $request->ip(),
        ]);
    }
}
