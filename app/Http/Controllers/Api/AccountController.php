<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('password')) {
            if (!Hash::check($request->password, $user->kata_sandi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata sandi yang Anda masukkan salah.',
                ], 403);
            }
        }

        $user->tokens()->delete();
        $user->mobileDevices()->delete();

        $user->profil()->update([
            'nik' => null,
            'nama_lengkap' => '[Dihapus]',
            'email' => null,
            'id_desa_domisili' => null,
            'alamat' => null,
            'tanggal_lahir' => null,
            'jenis_kelamin' => null,
            'tempat_lahir' => null,
            'profesi' => null,
            'pengalaman_kebencanaan' => null,
        ]);

        $user->update([
            'no_hp' => null,
            'kata_sandi' => Hash::make('[deleted-' . now()->timestamp . ']'),
            'status_akun' => 'nonaktif',
            'is_tersedia' => false,
            'terakhir_masuk' => null,
            'status_ketersediaan' => AuthUser::READINESS_NOT_READY,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus. Seluruh data pribadi Anda telah dianonimkan.',
        ]);
    }
}
