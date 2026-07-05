<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Identitas
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'nik'           => ['required', 'string', 'max:16'],
            'tempat_lahir'  => ['required', 'string', 'max:150'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'no_hp'         => ['required', 'string', 'max:20', 'unique:auth_users,no_hp'],
            'email'         => ['required', 'email', 'max:255', 'unique:auth_pengguna_profil,email'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Wilayah & Alamat
            'alamat'        => ['required', 'string'],
            'id_desa'       => ['required', 'string', 'exists:wilayah_desa,id_desa'],
            'id_pcnu'       => ['required', 'string', 'exists:organisasi_pcnu,id_pcnu'],
            
            // Pendaftaran & Skill
            'jenis_pendaftaran' => ['required', 'in:relawan,trc,admin_pcnu,admin_pwnu'],
            'profesi' => ['required', 'string', 'max:150'],
            'pengalaman_kebencanaan' => ['nullable', 'string'],
        ]);

        // Cross-Check Wilayah: Jika daftar admin_pcnu, pcnu yang dipilih harus satu kabupaten dengan domisili
        if ($request->jenis_pendaftaran === 'admin_pcnu') {
            $desa = \App\Models\WilayahDesa::with('kecamatan.kabupaten')->find($request->id_desa);
            if ($desa && $desa->kecamatan && $desa->kecamatan->id_kab) {
                $pcnu = \App\Models\OrganisasiPcnu::find($request->id_pcnu);
                if ($pcnu && $pcnu->unit?->id_wilayah !== $desa->kecamatan->id_kab) {
                    return back()->withInput()->withErrors(['id_pcnu' => 'Wilayah PCNU yang dipilih harus sesuai dengan Kabupaten tempat domisili Anda.']);
                }
            }
        }

        $user = DB::transaction(function () use ($request) {
            // Determine Role ID and Account Status based on Registration Type
            $roleName = '';
            $statusAkun = '';
            $isTersedia = true;
            
            if ($request->jenis_pendaftaran === 'relawan') {
                $roleName = 'relawan';
                $statusAkun = AuthUser::STATUS_ACTIVE;
            } elseif ($request->jenis_pendaftaran === 'trc') {
                $roleName = 'trc';
                $statusAkun = AuthUser::STATUS_ACTIVE;
            } elseif ($request->jenis_pendaftaran === 'admin_pcnu') {
                $roleName = 'kandidat_admin_pcnu';
                $statusAkun = AuthUser::STATUS_REGISTERED;
                $isTersedia = false;
            } elseif ($request->jenis_pendaftaran === 'admin_pwnu') {
                $roleName = 'kandidat_admin_pwnu';
                $statusAkun = AuthUser::STATUS_REGISTERED;
                $isTersedia = false;
            }

            $role = \App\Models\AuthRole::where('nama_peran', $roleName)->first();

            $authUser = AuthUser::create([
                'id_peran'           => $role->id_peran,
                'no_hp'              => $request->no_hp,
                'kata_sandi'         => Hash::make($request->password),
                'status_akun'        => $statusAkun,
                'is_tersedia'        => $isTersedia,
                'default_scope_type' => 'pcnu',
                'default_scope_id'   => $request->id_pcnu,
            ]);

            AuthPenggunaProfil::create([
                'id_pengguna'      => $authUser->id_pengguna,
                'nama_lengkap'     => $request->nama_lengkap,
                'nik'              => $request->nik,
                'email'            => $request->email,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'alamat'           => $request->alamat,
                'id_desa_domisili' => $request->id_desa,
                'profesi'          => $request->profesi,
                'pengalaman_kebencanaan' => $request->pengalaman_kebencanaan,
            ]);

            // If Admin, auto-create Role Application
            if (in_array($request->jenis_pendaftaran, ['admin_pcnu', 'admin_pwnu'])) {
                $targetRoleName = ($request->jenis_pendaftaran === 'admin_pcnu') ? 'pcnu' : 'pwnu';
                $targetRole = \App\Models\AuthRole::where('nama_peran', $targetRoleName)->first();
                
                \App\Models\AuthRoleApplication::create([
                    'id_pengguna' => $authUser->id_pengguna,
                    'id_peran_diminta' => $targetRole->id_peran,
                    'status_aplikasi' => 'pending',
                    'waktu_pengajuan' => now(),
                    'catatan' => 'Pendaftaran otomatis melalui Form Registrasi V2',
                ]);
            }

            return $authUser;
        });

        event(new Registered($user));
        Auth::login($user);

        if ($user->status_akun === AuthUser::STATUS_ACTIVE) {
            return redirect(route('dashboard', absolute: false));
        }

        // Redirect pending verifications
        return redirect()->route('role-application.create')->with('info', 'Akun Anda sedang menunggu verifikasi administrator.');
    }
}
