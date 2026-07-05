<?php
$files = array_merge(
    glob("tests/Feature/Operasi/*.php"),
    glob("tests/Feature/Relawan/*.php"),
    glob("tests/Unit/Operasi/*.php")
);
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = preg_replace('/OperasiPosaju::create\(\[\s*\'id_insiden\'/s', 'OperasiPosaju::create([\'alamat_lokasi\' => \'Alamat Default\', \'id_insiden\'', $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Fixed $file\n";
        }
    }
}
