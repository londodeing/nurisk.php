<?php

$file = 'app/Http/Controllers/Api/Operasi/SyncApiController.php';
$content = file_get_contents($file);

// Add 'operasi_mobilisasi', 'mobilisasi' => [OperasiMobilisasi::class, 'uuid_mobilisasi']
$content = str_replace(
    "'operasi_penugasan', 'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan'],",
    "'operasi_penugasan', 'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan'],\n                    'operasi_mobilisasi', 'mobilisasi' => [\App\Models\OperasiMobilisasi::class, 'uuid_mobilisasi'],",
    $content
);

// Add 'mobilisasi' to supported entities
$content = str_replace(
    "['assessment', 'sitrep', 'klaster', 'penugasan']",
    "['assessment', 'sitrep', 'klaster', 'penugasan', 'mobilisasi']",
    $content
);

// Add to Resource match
$content = str_replace(
    "'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan', PenugasanResource::class],",
    "'penugasan' => [OperasiPenugasan::class, 'uuid_penugasan', PenugasanResource::class],\n                    'mobilisasi' => [\App\Models\OperasiMobilisasi::class, 'uuid_mobilisasi', \App\Http\Resources\Operasi\MobilisasiResource::class],",
    $content
);

// UUID resolution logic for 'mobilisasi'
$uuidLogic = <<<'PHP'
                    if ($table === 'penugasan') {
                        if (isset($data['uuid_insiden'])) {
                            $insiden = \App\Models\OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->first();
                            if ($insiden) {
                                $data['id_insiden'] = $insiden->id_insiden;
                                unset($data['uuid_insiden']);
                            }
                        }
                    }
PHP;

$uuidLogicNew = <<<'PHP'
                    if ($table === 'penugasan' || $table === 'mobilisasi') {
                        if (isset($data['uuid_insiden'])) {
                            $insiden = \App\Models\OperasiInsiden::where('uuid_insiden', $data['uuid_insiden'])->first();
                            if ($insiden) {
                                $data['id_insiden'] = $insiden->id_insiden;
                                unset($data['uuid_insiden']);
                            }
                        }
                    }
PHP;

$content = str_replace($uuidLogic, $uuidLogicNew, $content);

file_put_contents($file, $content);
