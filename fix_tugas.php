<?php
$files = array_merge(
    glob("tests/Feature/Operasi/*.php"),
    glob("tests/Feature/Relawan/*.php")
);
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = preg_replace('/RelawanKebutuhan::create\(\[\s*\'judul_posisi\' => \'Default Posisi\', \'id_insiden\'/s', 'RelawanKebutuhan::create([\'deskripsi_tugas\' => \'Deskripsi Default\', \'judul_posisi\' => \'Default Posisi\', \'id_insiden\'', $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Fixed $file\n";
        }
    }
}
