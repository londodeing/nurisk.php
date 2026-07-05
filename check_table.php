$cols = DB::select('SHOW COLUMNS FROM auth_pengguna_profil');
foreach ($cols as $c) echo "{$c->Field} - {$c->Null} - {$c->Default}\n";
