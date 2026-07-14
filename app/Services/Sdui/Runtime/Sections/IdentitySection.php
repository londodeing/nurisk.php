<?php

namespace App\Services\Sdui\Runtime\Sections;

use App\Services\Sdui\Runtime\Nodes\SectionNode;
use App\Services\Sdui\Runtime\Runtime;
use Illuminate\Support\Facades\DB;

class IdentitySection
{
    public static function build(?array $profil, ?array $jabatanAktif, array $keahlian, array $penugasan): SectionNode
    {
        $section = Runtime::section('identity_section');

        if (!$profil) {
            $guestComponent = Runtime::component('guest_card')
                ->withRenderNode(
                    Runtime::render('guest_container', 'container', [
                        'background' => 'primary',
                        'radius' => 'xl',
                        'padding' => ['all' => 16],
                        'margin' => ['t' => 12, 'l' => 16, 'r' => 16]
                    ])
                    ->withChild(Runtime::render('guest_text', 'text', [
                        'text' => 'Anda belum login',
                        'style' => 'subtitle',
                        'foreground' => 'text_inverse'
                    ]))
                    ->withChild(Runtime::render('guest_spacer', 'SizedBox', ['height' => 12]))
                    ->withChild(
                        Runtime::render('guest_login_btn', 'container', [
                            'background' => 'surface',
                            'radius' => 'md',
                            'padding' => ['x' => 16, 'y' => 8]
                        ], [
                            'on_tap' => ['type' => 'navigate', 'target' => '/login']
                        ])
                        ->withChild(Runtime::render('guest_login_btn_text', 'text', [
                            'text' => 'Masuk ke NURISK',
                            'style' => 'body',
                            'foreground' => 'primary'
                        ]))
                    )
                );
            return $section->withComponent($guestComponent);
        }

        $namaLengkap = $profil['nama_lengkap'] ?? $profil['no_hp'];
        $inisial = self::generateInisial($namaLengkap);
        $roleTitle = ucwords(str_replace('_', ' ', $profil['nama_peran']));
        $scopeText = self::resolveScopeText($profil, $penugasan);
        $isTersedia = (bool) $profil['is_tersedia'];

        // Row 1: Avatar + Name + Toggle
        $row1 = Runtime::render('profile_row_main', 'row', ['spacing' => 12])
            ->withChild(
                Runtime::render('profile_avatar_container', 'container', [
                    'width' => 64,
                    'height' => 64,
                    'background' => 'success',
                    'radius' => 'full'
                ])
                ->withChild(Runtime::render('profile_avatar_text', 'text', [
                    'text' => $inisial,
                    'style' => 'headline',
                    'foreground' => 'text_inverse',
                    'align' => 'center'
                ]))
            );

        $nameCol = Runtime::render('profile_name_col', 'column', ['spacing' => 4])
            ->withChild(Runtime::render('profile_name_text', 'text', [
                'text' => $namaLengkap,
                'style' => 'headline',
                'foreground' => 'text_inverse',
                'maxLines' => 1,
                'overflow' => 'ellipsis'
            ]))
            ->withChild(Runtime::render('profile_role_text', 'text', [
                'text' => $roleTitle,
                'style' => 'caption',
                'foreground' => 'text_inverse'
            ]));

        if (!empty($scopeText)) {
            $nameCol = $nameCol->withChild(Runtime::render('profile_scope_text', 'text', [
                'text' => $scopeText,
                'style' => 'caption',
                'foreground' => 'text_inverse'
            ]));
        }

        $expandedNameCol = Runtime::render('profile_name_expanded', 'Expanded', [])
            ->withChild($nameCol);

        $row1 = $row1->withChild($expandedNameCol);

        // Toggle availability action if active
        if ($profil['nama_peran'] !== 'publik' && $profil['status_akun'] === 'aktif') {
            $toggleNode = Runtime::render('profile_toggle_container', 'container', [
                'background' => $isTersedia ? 'success' : 'text_muted',
                'radius' => 'full',
                'padding' => ['x' => 12, 'y' => 8]
            ], [
                'on_tap' => [
                    'type' => 'action',
                    'action_type' => 'profil.toggle_tersedia',
                    'endpoint' => 'v1/profil/toggle-tersedia',
                    'method' => 'POST',
                    'requires_auth' => true,
                    'body' => ['id_pengguna' => $profil['id_pengguna']],
                    'optimistic' => true,
                    'optimistic_patches' => [
                        'profile_toggle_dot' => ['background' => $isTersedia ? 'text_muted' : 'success'],
                        'profile_toggle_text' => ['text' => $isTersedia ? 'Tidak Tersedia' : 'Tersedia'],
                        'profile_toggle_container' => ['background' => $isTersedia ? 'text_muted' : 'success'],
                    ],
                    'confirm' => $isTersedia ? [
                        'title' => 'Tandai Tidak Tersedia?',
                        'message' => 'Status Anda akan berubah menjadi tidak tersedia untuk penugasan.',
                        'confirm_label' => 'Ya',
                        'cancel_label' => 'Batal'
                    ] : null,
                    'on_success' => ['type' => 'reload']
                ]
            ])
            ->withChild(
                Runtime::render('profile_toggle_row', 'row', ['spacing' => 8])
                    ->withChild(Runtime::render('profile_toggle_dot', 'container', [
                        'width' => 8,
                        'height' => 8,
                        'radius' => 'full',
                        'background' => $isTersedia ? 'surface' : 'text_muted'
                    ]))
                    ->withChild(Runtime::render('profile_toggle_text', 'text', [
                        'text' => $isTersedia ? 'Tersedia' : 'Tidak Tersedia',
                        'style' => 'caption',
                        'foreground' => 'text_inverse'
                    ]))
            );
            $row1 = $row1->withChild($toggleNode);
        }

        $mainColumn = Runtime::render('profile_main_column', 'column', ['spacing' => 12])
            ->withChild($row1);

        // Active Position (star icon)
        if ($profil['nama_peran'] !== 'publik' && $jabatanAktif) {
            $mainColumn = $mainColumn
                ->withChild(Runtime::render('profile_position_spacer', 'SizedBox', ['height' => 4]))
                ->withChild(
                    Runtime::render('profile_position_row', 'row', ['spacing' => 8])
                        ->withChild(Runtime::render('profile_position_icon', 'icon', ['name' => 'favorite', 'foreground' => 'warning']))
                        ->withChild(Runtime::render('profile_position_text', 'text', [
                            'text' => $jabatanAktif['nama_jabatan'],
                            'style' => 'body',
                            'foreground' => 'text_inverse'
                        ]))
                );
        }

        // Skills (Chips)
        if ($profil['nama_peran'] !== 'publik' && !empty($keahlian)) {
            $mainColumn = $mainColumn->withChild(Runtime::render('profile_skills_spacer', 'SizedBox', ['height' => 4]));
            
            $skillsRow = Runtime::render('profile_skills_row', 'row', ['spacing' => 8]);
            foreach (array_slice($keahlian, 0, 4) as $idx => $k) {
                $skillsRow = $skillsRow->withChild(
                    Runtime::render('profile_skill_chip_' . $idx, 'container', [
                        'background' => 'success',
                        'radius' => 'full',
                        'padding' => ['x' => 12, 'y' => 4]
                    ])
                    ->withChild(Runtime::render('profile_skill_text_' . $idx, 'text', [
                        'text' => $k['nama_keahlian'],
                        'style' => 'caption',
                        'foreground' => 'text_inverse'
                    ]))
                );
            }
            
            if (count($keahlian) > 4) {
                $skillsRow = $skillsRow->withChild(
                    Runtime::render('profile_skill_more', 'container', [
                        'background' => 'success',
                        'radius' => 'full',
                        'padding' => ['x' => 12, 'y' => 4]
                    ])
                    ->withChild(Runtime::render('profile_skill_more_text', 'text', [
                        'text' => '& ' . (count($keahlian) - 4) . ' lainnya',
                        'style' => 'caption',
                        'foreground' => 'text_inverse'
                    ]))
                );
            }
            
            $mainColumn = $mainColumn->withChild($skillsRow);
        }

        $identityCard = Runtime::component('identity_card')
            ->withRenderNode(
                Runtime::render('identity_card_container', 'container', [
                    'background' => 'primary',
                    'radius' => 'xl',
                    'padding' => ['all' => 16],
                    'margin' => ['t' => 12, 'l' => 16, 'r' => 16]
                ])
                ->withChild($mainColumn)
            );

        return $section->withComponent($identityCard);
    }

    private static function generateInisial(?string $namaLengkap): string
    {
        if (!$namaLengkap) return '?';
        $kata = array_filter(explode(' ', trim($namaLengkap)));
        $inisial = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $inisial .= mb_strtoupper(mb_substr($k, 0, 1));
        }
        return $inisial ?: '?';
    }

    private static function resolveScopeText(array $profil, array $penugasan): string
    {
        $role = $profil['nama_peran'];
        if ($role === 'super_admin') return 'LPBI NU Jawa Tengah — Akses Penuh';
        if ($role === 'pwnu') return 'PWNU Jawa Tengah';
        if ($role === 'pcnu') {
            $pcnu = DB::table('organisasi_pcnu')->where('id_pcnu', $profil['default_scope_id'])->first();
            return $pcnu ? $pcnu->nama_pcnu : 'PCNU';
        }
        if ($role === 'relawan') {
            if (!empty($penugasan)) {
                return 'Bertugas di ' . $penugasan[0]['kode_kejadian'];
            }
            return 'Relawan NU';
        }
        return '';
    }
}
