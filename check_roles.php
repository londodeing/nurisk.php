echo "--- AUTH PERAN ---\n";
foreach(DB::table('auth_peran')->get() as $p) echo "{$p->id_peran}: {$p->nama_peran}\n";

echo "\n--- MASTER JABATAN ---\n";
foreach(DB::table('master_jabatan')->get() as $j) echo "{$j->id_jabatan}: {$j->nama_jabatan} ({$j->tingkat_organisasi})\n";

echo "\n--- PWNU ---\n";
$pwnu = DB::table('organisasi_pwnu')->first();
if ($pwnu) echo "ID: {$pwnu->id_pwnu}, Nama: {$pwnu->nama_pwnu}\n";

echo "\n--- PCNU ---\n";
$pcnu = DB::table('organisasi_pcnu')->first();
if ($pcnu) echo "ID: {$pcnu->id_pcnu}, Nama: {$pcnu->nama_pcnu}\n";
