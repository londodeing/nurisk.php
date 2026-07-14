<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\LaporanKejadian;

class DashboardHomeService
{
    public function getWidgets(?AuthUser $user): array
    {
        $children = [];

        // 1. Weather Info (Publik) -> Primitive Card
        $children[] = [
            'type' => 'Card',
            'props' => ['padding' => 16, 'margin_bottom' => 12],
            'children' => [
                ['type' => 'Row', 'children' => [
                    ['type' => 'Icon', 'props' => ['name' => 'wb_sunny', 'color' => 'warning']],
                    ['type' => 'Text', 'props' => ['text' => ' Cuaca Hari Ini (Cerah Berawan, 31°C)', 'style' => 'subtitle']],
                ]],
                ['type' => 'Text', 'props' => ['text' => 'Semarang, Jawa Tengah', 'style' => 'body']],
            ]
        ];

        // 2. Warning Info (Publik) -> Primitive Card
        $children[] = [
            'type' => 'Card',
            'props' => ['padding' => 16, 'margin_bottom' => 12, 'background_color' => 'danger_light'],
            'children' => [
                ['type' => 'Row', 'children' => [
                    ['type' => 'Icon', 'props' => ['name' => 'warning', 'color' => 'danger']],
                    ['type' => 'Text', 'props' => ['text' => ' Peringatan Dini (SIAGA)', 'style' => 'subtitle', 'color' => 'danger']],
                ]],
                ['type' => 'Text', 'props' => ['text' => 'Potensi hujan lebat disertai angin kencang di pesisir utara.', 'style' => 'body']],
            ]
        ];

        // Jika user terautentikasi, kita injeksikan widget operasional
        if ($user) {
            $user->loadMissing('peran');
            $role = $user->peran ? $user->peran->nama_peran : null;

            if ($role === 'pcnu' || $role === 'pwnu' || $role === 'super_admin') {
                $pendingCount = LaporanKejadian::where('is_valid', 'menunggu')
                    ->when($role === 'pcnu' && $user->default_scope_id, fn($q) => $q->where('id_pcnu', $user->default_scope_id))
                    ->count();

                $children[] = [
                    'type' => 'Card',
                    'props' => ['padding' => 16, 'margin_bottom' => 12],
                    'children' => [
                        ['type' => 'Text', 'props' => ['text' => 'Antrean Keputusan', 'style' => 'subtitle']],
                        ['type' => 'Text', 'props' => ['text' => "$pendingCount Menunggu Approval", 'style' => 'headline', 'color' => 'warning']],
                    ]
                ];
            } elseif ($role === 'trc') {
                $children[] = [
                    'type' => 'Card',
                    'props' => ['padding' => 16, 'margin_bottom' => 12],
                    'children' => [
                        ['type' => 'Text', 'props' => ['text' => 'Misi Aktif Hari Ini', 'style' => 'subtitle']],
                        ['type' => 'Text', 'props' => ['text' => "2 Misi, 5 Assessment", 'style' => 'body']],
                    ]
                ];
            }
        }

        // 3. News (Publik) -> Primitive Card
        $children[] = [
            'type' => 'Card',
            'props' => ['padding' => 16, 'margin_bottom' => 12],
            'children' => [
                ['type' => 'Text', 'props' => ['text' => 'Berita Terkini', 'style' => 'subtitle']],
                ['type' => 'Text', 'props' => ['text' => '• LPBI NU Salurkan Bantuan Banjir Demak (2026-07-08)', 'style' => 'body']],
                ['type' => 'Text', 'props' => ['text' => '• Pelatihan Mitigasi Bencana PWNU Jawa Tengah (2026-07-05)', 'style' => 'body']],
            ]
        ];

        // 4. Latest Incident (Publik)
        $children[] = [
            'type' => 'Card',
            'props' => ['padding' => 16, 'margin_bottom' => 12],
            'children' => [
                ['type' => 'Text', 'props' => ['text' => 'Laporan Kejadian Terkini', 'style' => 'subtitle']],
                ['type' => 'Text', 'props' => ['text' => LaporanKejadian::where('is_valid', 'ya')->count() . ' Laporan Aktif', 'style' => 'body']],
            ]
        ];

        return $children;
    }
}
