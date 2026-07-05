<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('tests/Feature'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        
        $content = preg_replace_callback('/::create\(\[(.*?)\]\)/s', function($matches) {
            $block = $matches[0];
            // Only if it's NOT OperasiInsiden::create or AuthUser::create etc.
            // Actually, does OperasiInsiden::create use uuid_insiden? No, it uses it, but it's fine.
            if (strpos($block, "'uuid_insiden'") !== false) {
                // Change back to id_insiden => $...->id_insiden
                return str_replace(
                    ["'uuid_insiden' => \$insiden->uuid_insiden", "'uuid_insiden' => \$this->uuidInsiden", "'uuid_insiden' => \$insidenPcnu1->uuid_insiden", "'uuid_insiden' => \$insidenPcnu2->uuid_insiden"], 
                    ["'id_insiden' => \$insiden->id_insiden", "'id_insiden' => \$this->idInsiden", "'id_insiden' => \$insidenPcnu1->id_insiden", "'id_insiden' => \$insidenPcnu2->id_insiden"], 
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
