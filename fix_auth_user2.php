<?php
$files = array_merge(
    glob("tests/Feature/Operasi/*.php"),
    glob("tests/Feature/Relawan/*.php")
);
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Replace hardcoded duplicate no_hp strings with rand call inside the test
        $newContent = preg_replace('/\'no_hp\' => \'08\d{8,9}\'/', '\'no_hp\' => \'08\' . rand(100000000, 999999999)', $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Fixed $file\n";
        }
    }
}
