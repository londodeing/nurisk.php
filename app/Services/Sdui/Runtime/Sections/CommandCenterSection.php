<?php

namespace App\Services\Sdui\Runtime\Sections;

use App\Services\Sdui\Runtime\Nodes\SectionNode;
use App\Services\Sdui\Runtime\Runtime;

class CommandCenterSection
{
    public static function build(?array $commandCenter, ?array $alertInsiden, ?array $profil): SectionNode
    {
        $section = Runtime::section('command_center');

        if ($commandCenter === null) {
            return $section; // not authorized
        }

        // 1. Alerts block if active
        if (!empty($alertInsiden)) {
            $alertListCol = Runtime::render('alert_list_col', 'column', ['spacing' => 4]);
            foreach (array_slice($alertInsiden, 0, 2) as $idx => $a) {
                $alertListCol = $alertListCol->withChild(
                    Runtime::render('alert_item_' . $idx, 'text', [
                        'text' => $a['kode_kejadian'] . ' — ' . $a['nama_bencana'] . ', ' . $a['nama_pcnu'],
                        'style' => 'caption',
                        'foreground' => 'warning'
                    ])
                );
            }

            $alertComponent = Runtime::component('cc_alerts')
                ->withRenderNode(
                    Runtime::render('alerts_container', 'container', [
                        'background' => 'warning',
                        'radius' => 'xl',
                        'padding' => ['all' => 16],
                        'margin' => ['t' => 12, 'l' => 16, 'r' => 16]
                    ])
                    ->withChild(
                        Runtime::render('alerts_row', 'row', ['spacing' => 12])
                            ->withChild(Runtime::render('alerts_icon', 'icon', ['name' => 'warning', 'foreground' => 'warning']))
                            ->withChild(
                                Runtime::render('alerts_content_col', 'column', ['spacing' => 6])
                                    ->withChild(Runtime::render('alerts_title', 'text', [
                                        'text' => count($alertInsiden) . ' insiden belum terbentuk klaster',
                                        'style' => 'subtitle',
                                        'foreground' => 'warning'
                                    ]))
                                    ->withChild($alertListCol)
                            )
                    )
                );
            $section = $section->withComponent($alertComponent);
        }

        // 2. Title & active count header
        $headerComponent = Runtime::component('cc_header')
            ->withRenderNode(
                Runtime::render('cc_header_wrapper_container', 'container', [
                    'margin' => ['t' => 12, 'l' => 16, 'r' => 16, 'b' => 12]
                ])->withChild(
                    Runtime::render('cc_header_row', 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                        ->withChild(Runtime::render('cc_header_title', 'text', ['text' => 'Pusat Komando', 'style' => 'headline']))
                        ->withChild(
                            Runtime::render('cc_header_badge_row', 'row', ['spacing' => 4])
                                ->withChild(Runtime::render('cc_dot', 'icon', ['name' => 'check_circle', 'foreground' => !empty($commandCenter) ? 'success' : 'text_muted']))
                                ->withChild(Runtime::render('cc_badge_text', 'text', ['text' => count($commandCenter) . ' Insiden Aktif', 'style' => 'caption']))
                        )
                )
            );
        $section = $section->withComponent($headerComponent);

        // 3. Command center list or empty state
        if (empty($commandCenter)) {
            $emptyComponent = Runtime::component('cc_empty')
                ->withRenderNode(
                    Runtime::render('cc_empty_container', 'container', [
                        'background' => 'surface',
                        'radius' => 'xl',
                        'padding' => ['all' => 16],
                        'margin' => ['b' => 12, 'l' => 16, 'r' => 16]
                    ])
                    ->withChild(Runtime::render('cc_empty_state', 'column', ['spacing' => 8])
                        ->withChild(Runtime::render('cc_empty_icon', 'icon', ['name' => 'check_circle', 'foreground' => 'success']))
                        ->withChild(Runtime::render('cc_empty_msg', 'text', ['text' => 'Tidak ada insiden aktif', 'style' => 'body']))
                        ->withChild(Runtime::render('cc_empty_sub', 'text', ['text' => 'Seluruh insiden dalam lingkup Anda sudah selesai atau tidak ada laporan baru', 'style' => 'caption']))
                    )
                );
            $section = $section->withComponent($emptyComponent);
        } else {
            foreach ($commandCenter as $idx => $c) {
                $prm = self::getPrioritasMapping($c['prioritas']);
                $sm = self::getStatusInsidenMapping($c['status_insiden']);
                $cm = self::getCommandStateMapping($c['command_state']);
                
                $klasterForeground = $c['nama_klaster'] === 'Tunggu Aktivasi' ? 'warning' : 'success';

                $ccItem = Runtime::component('cc_item_' . $idx)
                    ->withRenderNode(
                        Runtime::render('cc_item_container_' . $idx, 'container', [
                            'background' => 'surface',
                            'radius' => 'xl',
                            'padding' => ['all' => 16],
                            'margin' => ['b' => 12, 'l' => 16, 'r' => 16]
                        ], [
                            'on_tap' => ['type' => 'navigate', 'target' => '/incident/' . $c['id_insiden']]
                        ])
                        ->withChild(
                            Runtime::render('cc_item_row1_' . $idx, 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                                ->withChild(Runtime::render('cc_item_code_' . $idx, 'text', ['text' => $c['kode_kejadian'], 'style' => 'subtitle']))
                                ->withChild(
                                    Runtime::render('cc_item_badges_row_' . $idx, 'row', ['spacing' => 8])
                                        ->withChild(Runtime::render('cc_item_prio_badge_' . $idx, 'badge', ['label' => $prm['label'], 'background' => $prm['bg'], 'foreground' => $prm['color']]))
                                        ->withChild(Runtime::render('cc_item_status_badge_' . $idx, 'badge', ['label' => $sm['label'], 'background' => $sm['bg'], 'foreground' => $sm['color']]))
                                )
                        )
                        ->withChild(Runtime::render('cc_item_spacer1_' . $idx, 'SizedBox', ['height' => 8]))
                        ->withChild(
                            Runtime::render('cc_item_row2_' . $idx, 'row', ['spacing' => 8])
                                ->withChild(Runtime::render('cc_item_cluster_icon_' . $idx, 'icon', ['name' => 'group', 'foreground' => $klasterForeground]))
                                ->withChild(Runtime::render('cc_item_cluster_text_' . $idx, 'text', ['text' => $c['nama_klaster'], 'style' => 'body', 'foreground' => $klasterForeground]))
                        )
                        ->withChild(Runtime::render('cc_item_spacer2_' . $idx, 'SizedBox', ['height' => 12]))
                        ->withChild(Runtime::render('cc_item_divider_' . $idx, 'divider', []))
                        ->withChild(Runtime::render('cc_item_spacer3_' . $idx, 'SizedBox', ['height' => 12]))
                        ->withChild(
                            Runtime::render('cc_item_stats_row_' . $idx, 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceAround'])
                                ->withChild(
                                    Runtime::render('cc_stat_days_col_' . $idx, 'column', ['spacing' => 4])
                                        ->withChild(Runtime::render('cc_stat_days_val_' . $idx, 'text', ['text' => (string)$c['lama_kejadian_hari'], 'style' => 'headline', 'foreground' => $c['lama_kejadian_hari'] > 7 ? 'danger' : 'text_main']))
                                        ->withChild(Runtime::render('cc_stat_days_lbl_' . $idx, 'text', ['text' => 'Hari', 'style' => 'caption']))
                                )
                                ->withChild(Runtime::render('cc_stat_vdiv1_' . $idx, 'container', ['width' => 1, 'height' => 32, 'background' => 'background']))
                                ->withChild(
                                    Runtime::render('cc_stat_sitreps_col_' . $idx, 'column', ['spacing' => 4])
                                        ->withChild(Runtime::render('cc_stat_sitreps_val_' . $idx, 'text', ['text' => (string)$c['jumlah_sitrep'], 'style' => 'headline']))
                                        ->withChild(Runtime::render('cc_stat_sitreps_lbl_' . $idx, 'text', ['text' => 'Sitrep', 'style' => 'caption']))
                                )
                                ->withChild(Runtime::render('cc_stat_vdiv2_' . $idx, 'container', ['width' => 1, 'height' => 32, 'background' => 'background']))
                                ->withChild(
                                    Runtime::render('cc_stat_status_col_' . $idx, 'column', ['spacing' => 4])
                                        ->withChild(Runtime::render('cc_stat_status_val_' . $idx, 'text', ['text' => $cm['label'], 'style' => 'subtitle', 'foreground' => $cm['color']]))
                                        ->withChild(Runtime::render('cc_stat_status_lbl_' . $idx, 'text', ['text' => 'Status Ops', 'style' => 'caption']))
                                )
                        )
                    );
                $section = $section->withComponent($ccItem);
            }
        }

        return $section;
    }

    private static function getPrioritasMapping(string $p): array
    {
        $map = [
            'kritis' => ['label' => 'KRITIS', 'bg' => 'danger', 'color' => 'text_inverse'],
            'tinggi' => ['label' => 'TINGGI', 'bg' => 'warning', 'color' => 'text_inverse'],
            'sedang' => ['label' => 'SEDANG', 'bg' => 'info', 'color' => 'text_inverse'],
            'rendah' => ['label' => 'RENDAH', 'bg' => 'background', 'color' => 'text_muted'],
        ];
        return $map[$p] ?? ['label' => strtoupper($p), 'bg' => 'background', 'color' => 'text_muted'];
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

    private static function getCommandStateMapping(string $c): array
    {
        $map = [
            'monitoring'      => ['label' => 'Monitoring', 'color' => 'text_muted'],
            'siaga'           => ['label' => 'SIAGA', 'color' => 'warning'],
            'tanggap_darurat' => ['label' => 'TANGGAP', 'color' => 'danger'],
            'pemulihan'       => ['label' => 'Pemulihan', 'color' => 'secondary'],
            'selesai'         => ['label' => 'Selesai', 'color' => 'success'],
        ];
        return $map[$c] ?? ['label' => ucfirst($c), 'color' => 'text_main'];
    }
}
