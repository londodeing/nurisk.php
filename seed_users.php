echo "--- AUTH ROLES ---\n";
foreach(DB::table('auth_roles')->get() as $p) echo "{$p->id_peran}: {$p->nama_peran}\n";
