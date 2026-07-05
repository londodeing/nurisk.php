<?php

namespace App\Services;

class OperatorActivityFeedService
{
    public function getRecentActivities()
    {
        return [
            ['text' => 'Sitrep #SIT-002 berhasil di-submit.', 'time' => '2 Menit Lalu'],
            ['text' => 'Relawan "Ahmad" ditambahkan ke posko.', 'time' => '15 Menit Lalu'],
            ['text' => 'Logistik Beras (50 Kg) masuk ke gudang.', 'time' => '1 Jam Lalu'],
            ['text' => 'Assessment #ASM-040 diperbarui.', 'time' => '2 Jam Lalu'],
            ['text' => 'Surat Tugas #ST-09 dicetak.', 'time' => '3 Jam Lalu'],
        ];
    }
}
