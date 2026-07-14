<?php

namespace App\Services\Sdui\Runtime\Sections;

use App\Services\Sdui\Runtime\Nodes\SectionNode;
use App\Services\Sdui\Runtime\Runtime;

class StatusOperasionalSection
{
    public static function build(array $penugasan, ?array $profil): SectionNode
    {
        $section = Runtime::section('status_operasional');

        if (!$profil || $profil['nama_peran'] === 'publik') {
            return $section;
        }

        if (empty($penugasan)) {
            $component = Runtime::component('status_empty_card')
                ->withRenderNode(
                    Runtime::render('empty_card_container', 'container', [
                        'background' => 'surface',
                        'radius' => 'xl',
                        'padding' => ['all' => 16],
                        'margin' => ['t' => 12, 'l' => 16, 'r' => 16]
                    ])
                    ->withChild(Runtime::render('empty_title', 'text', ['text' => 'Penugasan Aktif Saya', 'style' => 'subtitle']))
                    ->withChild(Runtime::render('empty_spacer', 'SizedBox', ['height' => 16]))
                    ->withChild(Runtime::render('empty_state_widget', 'column', ['spacing' => 8])
                        ->withChild(Runtime::render('empty_state_icon', 'icon', ['name' => 'person', 'foreground' => 'text_muted']))
                        ->withChild(Runtime::render('empty_state_msg', 'text', ['text' => 'Tidak ada penugasan aktif saat ini', 'style' => 'body']))
                        ->withChild(Runtime::render('empty_state_sub', 'text', ['text' => 'Anda akan muncul di sini saat ditugaskan ke operasi bencana', 'style' => 'caption']))
                    )
                );
            return $section->withComponent($component);
        }

        $listColumn = Runtime::render('penugasan_list_col', 'column', ['spacing' => 12]);

        foreach ($penugasan as $index => $p) {
            $pm = self::getPeranMapping($p['peran_otoritas']);
            $sm = self::getStatusInsidenMapping($p['status_insiden']);

            $itemNode = Runtime::render('penugasan_item_' . $index, 'container', [
                'padding' => ['y' => 12]
            ], [
                'on_tap' => ['type' => 'navigate', 'target' => '/incident/' . $p['id_insiden']]
            ])
            ->withChild(
                Runtime::render('penugasan_row_' . $index, 'row', ['spacing' => 12])
                    ->withChild(
                        Runtime::render('peran_icon_container_' . $index, 'container', [
                            'width' => 40,
                            'height' => 40,
                            'radius' => 'lg',
                            'background' => $pm['bg']
                        ])
                        ->withChild(Runtime::render('peran_icon_' . $index, 'icon', ['name' => $pm['icon'], 'foreground' => $pm['color']]))
                    )
                    ->withChild(
                        Runtime::render('penugasan_text_col_' . $index, 'column', ['spacing' => 4])
                            ->withChild(Runtime::render('penugasan_code_' . $index, 'text', ['text' => $p['kode_kejadian'], 'style' => 'subtitle']))
                            ->withChild(Runtime::render('penugasan_desc_' . $index, 'text', ['text' => $pm['label'] . ' · ' . $p['nama_bencana'], 'style' => 'caption']))
                            ->withChild(
                                Runtime::render('penugasan_time_row_' . $index, 'row', ['spacing' => 4])
                                    ->withChild(Runtime::render('penugasan_time_icon_' . $index, 'icon', ['name' => 'schedule', 'foreground' => 'text_muted']))
                                    ->withChild(Runtime::render('penugasan_time_text_' . $index, 'text', ['text' => 'Sejak ' . date('d M Y, H:i', strtotime($p['waktu_mulai'])), 'style' => 'caption', 'foreground' => 'text_muted']))
                            )
                    )
                    ->withChild(
                        Runtime::render('penugasan_badge_' . $index, 'badge', ['label' => $sm['label'], 'background' => $sm['bg'], 'foreground' => $sm['color']])
                    )
            );

            if ($index > 0) {
                $listColumn = $listColumn->withChild(Runtime::render('penugasan_divider_' . $index, 'divider', []));
            }

            $listColumn = $listColumn->withChild($itemNode);
        }

        $mainCard = Runtime::component('status_list_card')
            ->withRenderNode(
                Runtime::render('status_card_container', 'container', [
                    'background' => 'surface',
                    'radius' => 'xl',
                    'padding' => ['all' => 16],
                    'margin' => ['t' => 12, 'l' => 16, 'r' => 16]
                ])
                ->withChild(
                    Runtime::render('status_header_row', 'row', ['spacing' => 8])
                        ->withChild(Runtime::render('status_title', 'text', ['text' => 'Penugasan Aktif Saya', 'style' => 'subtitle']))
                        ->withChild(Runtime::render('status_count_badge', 'badge', [
                            'label' => count($penugasan) . ' Aktif',
                            'background' => 'success',
                            'foreground' => 'text_inverse'
                        ]))
                )
                ->withChild(Runtime::render('status_header_spacer_1', 'SizedBox', ['height' => 12]))
                ->withChild(Runtime::render('status_header_divider', 'divider', []))
                ->withChild(Runtime::render('status_header_spacer_2', 'SizedBox', ['height' => 4]))
                ->withChild($listColumn)
            );

        return $section->withComponent($mainCard);
    }

    private static function getPeranMapping(string $peran): array
    {
        $map = [
            'komandan_insiden' => ['label' => 'Komandan', 'bg' => 'danger', 'color' => 'text_inverse', 'icon' => 'star'],
            'trc'              => ['label' => 'Tim Reaksi Cepat', 'bg' => 'warning', 'color' => 'text_inverse', 'icon' => 'warning'],
            'relawan'          => ['label' => 'Relawan', 'bg' => 'secondary', 'color' => 'text_inverse', 'icon' => 'person'],
            'medis'            => ['label' => 'Tim Medis', 'bg' => 'success', 'color' => 'text_inverse', 'icon' => 'favorite'],
            'logistik'         => ['label' => 'Koordinator Log.', 'bg' => 'info', 'color' => 'text_inverse', 'icon' => 'info'],
            'operator'         => ['label' => 'Operator Sistem', 'bg' => 'primary', 'color' => 'text_inverse', 'icon' => 'edit_document'],
        ];
        return $map[$peran] ?? ['label' => 'Petugas', 'bg' => 'background', 'color' => 'text_main', 'icon' => 'person'];
    }

    private static function getStatusInsidenMapping(string $status): array
    {
        $map = [
            'draft'         => ['label' => 'Draft', 'bg' => 'background', 'color' => 'text_muted'],
            'terverifikasi' => ['label' => 'Terverifikasi', 'bg' => 'info', 'color' => 'text_inverse'],
            'respon'        => ['label' => 'RESPON', 'bg' => 'warning', 'color' => 'text_inverse'],
            'pemulihan'     => ['label' => 'Pemulihan', 'bg' => 'secondary', 'color' => 'text_inverse'],
            'selesai'       => ['label' => 'Selesai', 'bg' => 'success', 'color' => 'text_inverse'],
            'dibatalkan'    => ['label' => 'Dibatalkan', 'bg' => 'danger', 'color' => 'text_inverse'],
        ];
        return $map[$status] ?? ['label' => strtoupper($status), 'bg' => 'background', 'color' => 'text_main'];
    }
}
