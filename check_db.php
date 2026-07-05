$users = DB::table('auth_users')->orderBy('id_pengguna', 'desc')->take(5)->get();
foreach ($users as $u) {
    echo "HP: {$u->no_hp} | Hash: " . substr($u->kata_sandi, 0, 10) . "... | Status: {$u->status_akun}\n";
    echo "Check 'password': " . (Hash::check('password', $u->kata_sandi) ? 'OK' : 'FAIL') . "\n";
}
