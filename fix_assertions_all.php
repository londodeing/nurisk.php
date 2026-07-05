<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('tests/Feature'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        $modified = false;
        
        $content = preg_replace_callback('/assertDatabaseHas\([^;]+;?/s', function($matches) {
            $block = $matches[0];
            if (strpos($block, "'uuid_insiden'") !== false && strpos($block, "'operasi_insiden'") === false) {
                return str_replace(
                    ["'uuid_insiden' => \$insiden->uuid_insiden", "'uuid_insiden' => \$insidenDraft->uuid_insiden", "'uuid_insiden' => \$insidenSelesai->uuid_insiden"], 
                    ["'id_insiden' => \$insiden->id_insiden", "'id_insiden' => \$insidenDraft->id_insiden", "'id_insiden' => \$insidenSelesai->id_insiden"], 
                    $block
                );
            }
            return $block;
        }, $content);
        
        if ($content !== file_get_contents($file->getRealPath())) {
            file_put_contents($file->getRealPath(), $content);
            echo "Fixed " . $file->getFilename() . "\n";
        }
    }
}
