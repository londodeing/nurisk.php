<?php
$files = array_merge(
    glob("tests/Feature/Operasi/*.php"),
    glob("tests/Feature/Relawan/*.php")
);
foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = preg_replace_callback(
        '/AuthUser::(forceCreate|create)\(\[\s*/',
        function ($m) {
            $hp = '08' . rand(100000000, 999999999);
            return $m[0] . "'no_hp' => '$hp', 'kata_sandi' => 'hash', ";
        },
        $content
    );
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Fixed $file\n";
    }
}
