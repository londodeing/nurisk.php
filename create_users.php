$pcnu = \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->first();

$pwnuId = null;
$pcnuId = $pcnu ? $pcnu->id_pcnu : null;

$users = [
    [
        'nama' => 'Admin PWNU',
        'email' => 'admin.pwnu@nurisk.id',
        'no_hp' => '08111111111',
        'role' => 2,
        'scope_type' => 'pwnu',
        'scope_id' => $pwnuId
    ],
    [
        'nama' => 'TRC PWNU',
        'email' => 'trc.pwnu@nurisk.id',
        'no_hp' => '08222222222',
        'role' => 6,
        'scope_type' => 'pwnu',
        'scope_id' => $pwnuId
    ],
    [
        'nama' => 'Admin PCNU',
        'email' => 'admin.pcnu@nurisk.id',
        'no_hp' => '08333333333',
        'role' => 3,
        'scope_type' => 'pcnu',
        'scope_id' => $pcnuId
    ],
    [
        'nama' => 'TRC PCNU',
        'email' => 'trc.pcnu@nurisk.id',
        'no_hp' => '08444444444',
        'role' => 6,
        'scope_type' => 'pcnu',
        'scope_id' => $pcnuId
    ],
    [
        'nama' => 'Relawan',
        'email' => 'relawan@nurisk.id',
        'no_hp' => '08555555555',
        'role' => 4,
        'scope_type' => null,
        'scope_id' => null
    ],
];

foreach ($users as $idx => $u) {
    $existing = \App\Models\AuthPenggunaProfil::where('email', $u['email'])->first();
    if ($existing) {
        $existingUser = \App\Models\AuthUser::find($existing->id_pengguna);
        if ($existingUser) $existingUser->delete();
        $existing->delete();
    }

    $user = \App\Models\AuthUser::create([
        'id_peran' => $u['role'],
        'no_hp' => $u['no_hp'],
        'status_akun' => 'aktif',
        'is_tersedia' => 1,
        'default_scope_type' => $u['scope_type'],
        'default_scope_id' => $u['scope_id'],
        'kata_sandi' => \Illuminate\Support\Facades\Hash::make('password')
    ]);

    \App\Models\AuthPenggunaProfil::create([
        'id_pengguna' => $user->id_pengguna,
        'nama_lengkap' => $u['nama'],
        'email' => $u['email'],
        'nik' => '351' . rand(1000000000000, 9999999999999),
        'id_desa_domisili' => null
    ]);

    echo "Created {$u['nama']} (Email: {$u['email']}, Password: password)\n";
}
