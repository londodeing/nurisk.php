<?php

namespace App\Services\Sdui\Runtime\Sections;

use App\Services\Sdui\Runtime\Nodes\SectionNode;
use App\Services\Sdui\Runtime\Runtime;

class MenuSection
{
    public static function build(?array $profil): SectionNode
    {
        $section = Runtime::section('menu_section');

        if (!$profil) {
            return $section;
        }

        $statusAkun = $profil['status_akun'] ?? 'aktif';
        $am = self::getStatusAkunMapping($statusAkun);
        $terakhirMasuk = ($profil['terakhir_masuk'] ?? null) ? date('d M Y, H:i', strtotime($profil['terakhir_masuk'])) : 'Belum pernah';

        $menuRowsCol = Runtime::render('menu_rows_col', 'column', ['spacing' => 12])
            ->withChild(Runtime::render('menu_title', 'text', ['text' => 'Akun', 'style' => 'subtitle']))
            ->withChild(Runtime::render('menu_spacer_1', 'SizedBox', ['height' => 8]))
            ->withChild(
                Runtime::render('menu_row_hp', 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                    ->withChild(
                        Runtime::render('menu_row_hp_left', 'row', ['spacing' => 12])
                            ->withChild(Runtime::render('menu_row_hp_icon', 'icon', ['name' => 'phone', 'foreground' => 'text_muted']))
                            ->withChild(Runtime::render('menu_row_hp_label', 'text', ['text' => 'No HP', 'style' => 'body']))
                    )
                    ->withChild(Runtime::render('menu_row_hp_value', 'text', ['text' => $profil['no_hp'], 'style' => 'caption', 'foreground' => 'text_muted']))
            )
            ->withChild(Runtime::render('menu_divider_1', 'divider', []))
            ->withChild(
                Runtime::render('menu_row_status', 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                    ->withChild(
                        Runtime::render('menu_row_status_left', 'row', ['spacing' => 12])
                            ->withChild(Runtime::render('menu_row_status_icon', 'icon', ['name' => 'info', 'foreground' => 'text_muted']))
                            ->withChild(Runtime::render('menu_row_status_label', 'text', ['text' => 'Status Akun', 'style' => 'body']))
                    )
                    ->withChild(Runtime::render('menu_row_status_badge', 'badge', ['label' => $am['label'], 'background' => $am['bg'], 'foreground' => $am['color']]))
            )
            ->withChild(Runtime::render('menu_divider_2', 'divider', []))
            ->withChild(
                Runtime::render('menu_row_last_login', 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                    ->withChild(
                        Runtime::render('menu_row_last_login_left', 'row', ['spacing' => 12])
                            ->withChild(Runtime::render('menu_row_last_login_icon', 'icon', ['name' => 'schedule', 'foreground' => 'text_muted']))
                            ->withChild(Runtime::render('menu_row_last_login_label', 'text', ['text' => 'Terakhir Masuk', 'style' => 'body']))
                    )
                    ->withChild(Runtime::render('menu_row_last_login_value', 'text', ['text' => $terakhirMasuk, 'style' => 'caption', 'foreground' => 'text_muted']))
            );

        $namaPeran = $profil['nama_peran'] ?? 'publik';
        if ($namaPeran !== 'publik') {
            $menuRowsCol = $menuRowsCol
                ->withChild(Runtime::render('menu_divider_3', 'divider', []))
                ->withChild(
                    Runtime::render('menu_ganti_password', 'container', [
                        'padding' => ['y' => 8]
                    ], [
                        'on_tap' => ['type' => 'toast', 'message' => 'Fitur ganti kata sandi belum tersedia']
                    ])
                    ->withChild(
                        Runtime::render('menu_ganti_password_row', 'row', ['spacing' => 0, 'mainAxisAlignment' => 'spaceBetween'])
                            ->withChild(
                                Runtime::render('menu_ganti_password_left', 'row', ['spacing' => 12])
                                    ->withChild(Runtime::render('menu_ganti_password_icon', 'icon', ['name' => 'lock', 'foreground' => 'text_muted']))
                                    ->withChild(Runtime::render('menu_ganti_password_text', 'text', ['text' => 'Ganti Kata Sandi', 'style' => 'body']))
                            )
                            ->withChild(Runtime::render('menu_ganti_password_chevron', 'icon', ['name' => 'chevron_right', 'foreground' => 'text_muted']))
                    )
                )
                ->withChild(Runtime::render('menu_divider_4', 'divider', []))
                ->withChild(
                    Runtime::render('menu_logout', 'container', [
                        'padding' => ['y' => 8]
                    ], [
                        'on_tap' => [
                            'type' => 'submit',
                            'endpoint' => 'v1/auth/logout',
                            'method' => 'POST',
                            'requires_auth' => true,
                            'fields' => (object)[],
                            'confirm' => [
                                'title' => 'Keluar dari NURISK?',
                                'message' => 'Sesi Anda akan diakhiri. Data offline masih tersimpan di perangkat.',
                                'confirm_label' => 'Ya, Keluar',
                                'cancel_label' => 'Batal'
                            ],
                            'on_success' => ['type' => 'navigate', 'target' => '/auth/login', 'clear_stack' => true]
                        ]
                    ])
                    ->withChild(
                        Runtime::render('menu_logout_row', 'row', ['spacing' => 12])
                            ->withChild(Runtime::render('menu_logout_icon', 'icon', ['name' => 'logout', 'foreground' => 'danger']))
                            ->withChild(Runtime::render('menu_logout_text', 'text', ['text' => 'Keluar', 'style' => 'body', 'foreground' => 'danger']))
                    )
                );
        }

        $menuComponent = Runtime::component('menu_card')
            ->withRenderNode(
                Runtime::render('menu_card_container', 'container', [
                    'background' => 'surface',
                    'radius' => 'xl',
                    'padding' => ['all' => 16],
                    'margin' => ['t' => 12, 'l' => 16, 'r' => 16, 'b' => 24]
                ])
                ->withChild($menuRowsCol)
            );

        return $section->withComponent($menuComponent);
    }

    private static function getStatusAkunMapping(string $s): array
    {
        $map = [
            'menunggu' => ['label' => 'Menunggu', 'bg' => 'warning', 'color' => 'text_inverse'],
            'aktif'    => ['label' => 'Aktif', 'bg' => 'success', 'color' => 'text_inverse'],
            'nonaktif' => ['label' => 'Nonaktif', 'bg' => 'background', 'color' => 'text_muted'],
            'suspend'  => ['label' => 'Suspend', 'bg' => 'danger', 'color' => 'text_inverse'],
        ];
        return $map[$s] ?? ['label' => ucfirst($s), 'bg' => 'background', 'color' => 'text_muted'];
    }
}
