<?php
$dir = new RecursiveDirectoryIterator('tests/');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = preg_replace(
            '/AuthRole::factory\(\)->create\(\[\s*\'nama_peran\'\s*=>\s*\'([^\']+)\'[^\]]*\]\)/s',
            'AuthRole::firstOrCreate([\'nama_peran\' => \'$1\'], [\'deskripsi\' => \'Role\', \'level_otoritas\' => 1])',
            $content
        );
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Fixed " . $file->getPathname() . "\n";
        }
    }
}
