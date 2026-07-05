<?php
$files = glob("tests/Feature/Relawan/*.php");
foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = preg_replace('/RelawanKebutuhan::create\(\[\s*\'id_insiden\'/s', 'RelawanKebutuhan::create([\'judul_posisi\' => \'Default Posisi\', \'id_insiden\'', $content);
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Fixed $file\n";
    }
}
