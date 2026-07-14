<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuthUser;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthenticationApiController extends Controller
{
    public function __construct(
        private AuthenticationService $authService,
        private RegistrationService $registrationService
    ) {}

    /**
     * Memproses login dari mobile/API dan mengembalikan Bearer Token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'no_hp' => ['required', 'string'],
            'kata_sandi' => ['required', 'string'],
        ]);

        try {
            // Karena AuthenticationService::login() melakukan login web (session),
            // kita akan memanggilnya dan mengekstrak user, lalu mengeluarkan token.
            // Namun agar API fully stateless, kita cukup verifikasi kredensial di sini.
            
            $user = AuthUser::where('no_hp', $request->no_hp)->first();

            if (!$user || !\Illuminate\Support\Facades\Hash::check($request->kata_sandi, $user->kata_sandi)) {
                throw ValidationException::withMessages([
                    'no_hp' => [__('Nomor handphone atau kata sandi yang Anda masukkan salah.')],
                ]);
            }

            if (!$user->isAktif()) {
                throw ValidationException::withMessages([
                    'no_hp' => [__('Akun Anda belum aktif atau sedang dinonaktifkan/ditangguhkan.')],
                ]);
            }

            $user->update(['terakhir_masuk' => now()]);

            // Gunakan device_name jika ada (opsional), default 'api'
            $deviceName = $request->post('device_name', 'api-client');
            $token = $user->createToken($deviceName)->plainTextToken;

            // Load relasi standar agar profilnya terbawa
            $user->load(['profil', 'peran', 'jabatanAktif.jabatan']);

            // Sertakan daftar mandat aktif untuk MandatePickerScreen
            $mandates = $user->jabatanAktif->map(function ($jabatan) {
                return [
                    'id' => (string) $jabatan->id_pengguna_jabatan,
                    'role' => $jabatan->jabatan?->nama_jabatan ?? 'Anggota',
                    'territory' => $jabatan->tipe_lingkup . ':' . $jabatan->id_lingkup,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                    'mandates' => $mandates,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memproses pendaftaran dari mobile/API.
     */
    public function register(RegisterRequest $request, string $jenis): JsonResponse
    {
        if (!in_array($jenis, RegistrationService::SEMUA_JENIS)) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis pendaftaran tidak valid.'
            ], 400);
        }

        try {
            $user = $this->registrationService->daftar($request->validated(), $jenis);

            // Jika langsung aktif, otomatis buatkan token
            if ($user->isAktif()) {
                $deviceName = $request->post('device_name', 'api-client');
                $token = $user->createToken($deviceName)->plainTextToken;
                
                $user->load(['profil', 'peran']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil. Akun Anda langsung aktif.',
                    'data' => [
                        'token' => $token,
                        'user' => $user
                    ]
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil dikirim dan menunggu persetujuan.',
                'data' => [
                    'user' => $user->load(['profil', 'peran'])
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran gagal.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mengambil profil pengguna yang sedang login (membutuhkan Bearer Token).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profil', 'peran', 'jabatanPosisi.jabatan']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
            $user->update(['is_tersedia' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ]);
    }
}
