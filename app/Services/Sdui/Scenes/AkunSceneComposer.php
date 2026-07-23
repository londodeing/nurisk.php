<?php

namespace App\Services\Sdui\Scenes;

use App\Models\AuthUser;
use App\Models\OperasiPenugasan;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Support\Facades\DB;

class AkunSceneComposer implements SceneComposer
{
    public function __construct(
        private AuthUser $user,
        private AuthorizationContextService $ctx
    ) {}

    public function compose(): array
    {
        $profil          = $this->fetchProfil();
        $jabatanAktif    = $this->fetchJabatanAktif();
        $keahlian        = $this->fetchKeahlian();
        $penugasan       = $this->fetchPenugasanAktif();
        $commandCenter   = $this->fetchCommandCenter();
        $alertInsiden    = $this->fetchAlertInsiden();

        return [
            'scene_id'    => 'akun',
            'version'     => $this->computeVersion($profil),
            'ttl_seconds' => 120,
            'etag'        => $this->computeEtag($profil),
            'meta'        => $this->buildMeta($profil),
            'app_bar'     => [
                'title'   => 'Akun',
                'actions' => [
                    ['icon' => 'refresh', 'action' => ['type' => 'reload']]
                ]
            ],
            'root'        => [
                'type'     => 'scrollable',
                'style'    => ['bg' => 'gray-100'],
                'children' => array_values(array_filter([
                    $this->buildIdentityCard($profil, $jabatanAktif, $keahlian),
                    $this->buildStatusOperasional($penugasan, $profil),
                    $this->buildAkunMenu($profil),
                    ['type' => 'spacer', 'style' => ['height' => '4']],
                ]))
            ]
        ];
    }

    private function fetchProfil(): ?array
    {
        $res = DB::selectOne("
            SELECT u.id_pengguna, u.no_hp, u.status_akun, u.is_tersedia,
                   u.terakhir_masuk, u.default_scope_type, u.default_scope_id, u.diperbarui_pada,
                   r.nama_peran, r.level_otoritas,
                   p.nama_lengkap, p.nik, p.email, p.id_desa_domisili
            FROM auth_users u
            JOIN auth_roles r ON u.id_peran = r.id_peran
            LEFT JOIN auth_pengguna_profil p ON u.id_pengguna = p.id_pengguna
            WHERE u.id_pengguna = ?
        ", [$this->user->id_pengguna]);

        return $res ? (array) $res : null;
    }

    private function fetchJabatanAktif(): ?array
    {
        $res = DB::selectOne("
            SELECT mj.nama_jabatan, mj.slug, pj.tipe_lingkup, pj.ditugaskan_pada, pj.berakhir_pada
            FROM pengguna_jabatan pj
            JOIN master_jabatan mj ON pj.id_jabatan_posisi = mj.id_jabatan_posisi
            WHERE pj.id_pengguna = ?
              AND pj.status_aktif = 1
              AND (pj.berakhir_pada IS NULL OR pj.berakhir_pada >= CURRENT_TIMESTAMP)
            ORDER BY pj.ditugaskan_pada DESC
            LIMIT 1
        ", [$this->user->id_pengguna]);
        return $res ? (array) $res : null;
    }

    private function fetchKeahlian(): array
    {
        $res = DB::select("
            SELECT ak.nama_keahlian
            FROM auth_pengguna_keahlian apk
            JOIN auth_keahlian_master ak ON apk.id_keahlian = ak.id_keahlian
            WHERE apk.id_pengguna = ?
            ORDER BY ak.id_keahlian ASC
        ", [$this->user->id_pengguna]);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchPenugasanAktif(): array
    {
        $res = DB::select("
            SELECT
                op.id_penugasan, op.id_insiden, op.peran_otoritas,
                op.waktu_mulai,
                oi.kode_kejadian, oi.status_insiden, oi.status_operasi, oi.prioritas,
                bj.nama_bencana
            FROM operasi_penugasan op
            JOIN operasi_insiden oi ON op.id_insiden = oi.id_insiden
            JOIN bencana_master_jenis bj ON oi.id_jenis_bencana = bj.id_jenis
            WHERE op.id_pengguna = ?
              AND op.status_penugasan = 'aktif'
              AND op.waktu_selesai IS NULL
              AND op.dihapus_pada IS NULL
              AND oi.dihapus_pada IS NULL
            ORDER BY op.waktu_mulai DESC
            LIMIT 3
        ", [$this->user->id_pengguna]);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchCommandCenter(): ?array
    {
        $role = $this->ctx->getRoleName();
        if (!$role || !in_array($role, ['super_admin', 'pwnu', 'pcnu'], true)) {
            return null;
        }
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        $params = [];
        $scopeFilter = '';

        if ($role === 'pcnu') {
            $scopeId = $this->ctx->getScopeId();
            if ($scopeId) {
                $scopeFilter = "AND i.id_pcnu = ?";
                $params[] = $scopeId;
            }
        }

        $sql = "
            SELECT i.id_insiden, i.kode_kejadian, i.status_insiden,
                   i.status_operasi AS command_state, i.prioritas,
                   TIMESTAMPDIFF(DAY, i.waktu_mulai, NOW()) AS lama_kejadian_hari,
                   COALESCE(mk.nama_klaster, 'Tunggu Aktivasi') AS nama_klaster,
                   (SELECT COUNT(*) FROM operasi_sitrep WHERE id_insiden = i.id_insiden) AS jumlah_sitrep
            FROM operasi_insiden i
            LEFT JOIN operasi_klaster ok ON i.id_insiden = ok.id_insiden
            LEFT JOIN operasi_master_klaster mk ON ok.id_klaster = mk.id_klaster
            WHERE i.status_insiden NOT IN ('selesai', 'dibatalkan')
            {$scopeFilter}
            ORDER BY FIELD(i.prioritas, 'kritis', 'tinggi', 'sedang', 'rendah'), i.waktu_mulai DESC
            LIMIT 5
        ";

        $res = DB::select($sql, $params);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function fetchAlertInsiden(): ?array
    {
        $role = $this->ctx->getRoleName();
        if (!$role || !in_array($role, ['super_admin', 'pwnu', 'pcnu'], true)) {
            return null;
        }
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [];
        }

        $params = [];
        $scopeFilter = '';

        if ($role === 'pcnu') {
            $scopeId = $this->ctx->getScopeId();
            if ($scopeId) {
                $scopeFilter = "AND i.id_pcnu = ?";
                $params[] = $scopeId;
            }
        }

        $sql = "
            SELECT i.kode_kejadian, bj.nama_bencana, p.nama_pcnu, i.waktu_mulai, i.prioritas
            FROM operasi_insiden i
            JOIN bencana_master_jenis bj ON i.id_jenis_bencana = bj.id_jenis
            JOIN organisasi_pcnu p ON i.id_pcnu = p.id_pcnu
            LEFT JOIN operasi_klaster ok ON i.id_insiden = ok.id_insiden
            WHERE ok.id_operasi_klaster IS NULL
              AND i.status_insiden NOT IN ('selesai', 'dibatalkan')
              {$scopeFilter}
            LIMIT 3
        ";

        $res = DB::select($sql, $params);
        return array_map(fn($item) => (array) $item, $res);
    }

    private function buildMeta(?array $profil): array
    {
        if (!$profil) {
            return [
                'rendered_for_role' => 'publik',
                'rendered_at' => now()->toIso8601String(),
                'scope' => ['type' => null, 'id' => null]
            ];
        }
        return [
            'rendered_for_role' => $profil['nama_peran'],
            'rendered_at' => now()->toIso8601String(),
            'scope' => [
                'type' => $profil['default_scope_type'],
                'id' => $profil['default_scope_id']
            ]
        ];
    }

    private function computeVersion(?array $profil): int
    {
        $base = $profil ? strtotime($profil['diperbarui_pada'] ?? '2020-01-01') : 0;
        $maxOpDate = OperasiPenugasan::where('id_pengguna', $this->user->id_pengguna)->max('diperbarui_pada');
        $maxOp = $maxOpDate ? strtotime($maxOpDate) : 0;
        return (int) max($base, $maxOp);
    }

    private function computeEtag(?array $profil): string
    {
        $role = $profil['nama_peran'] ?? 'publik';
        $scope = ($profil['default_scope_type'] ?? '') . '_' . ($profil['default_scope_id'] ?? '');
        return md5($this->computeVersion($profil) . '_' . $this->user->id_pengguna . '_' . $role . '_' . $scope);
    }

    private function generateInisial(?string $namaLengkap): string
    {
        if (!$namaLengkap) return '?';
        $kata = array_filter(explode(' ', trim($namaLengkap)));
        $inisial = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $inisial .= mb_strtoupper(mb_substr($k, 0, 1));
        }
        return $inisial ?: '?';
    }

    private function resolveScopeText(array $profil): string
    {
        $role = $profil['nama_peran'];
        if ($role === 'super_admin') return 'LPBI NU Jawa Tengah — Akses Penuh';
        if ($role === 'pwnu') return 'PWNU Jawa Tengah';
        if ($role === 'pcnu') {
            $pcnu = DB::table('organisasi_pcnu')->where('id_pcnu', $profil['default_scope_id'])->first();
            return $pcnu ? $pcnu->nama_pcnu : 'PCNU';
        }
        if ($role === 'relawan') {
            $penugasan = $this->fetchPenugasanAktif();
            if (!empty($penugasan)) {
                return 'Bertugas di ' . $penugasan[0]['kode_kejadian'];
            }
            return 'Relawan NU';
        }
        return '';
    }

    private function buildIdentityCard(?array $profil, ?array $jabatanAktif, array $keahlian): array
    {
        if (!$profil) {
            return [
                'type' => 'container',
                'style' => ['bg' => 'green-700', 'padding' => ['t' => '6', 'b' => '6', 'l' => '4', 'r' => '4']],
                'children' => [
                    ['type' => 'text', 'content' => 'Anda belum login', 'style' => ['color' => 'white', 'size' => 'xl', 'weight' => 'bold']],
                    ['type' => 'spacer', 'style' => ['height' => '4']],
                    ['type' => 'button', 'label' => 'Masuk ke NURISK', 'action' => ['type' => 'navigate', 'route' => 'login']]
                ]
            ];
        }

        $namaLengkap = $profil['nama_lengkap'] ?? $profil['no_hp'];
        $inisial = $this->generateInisial($namaLengkap);
        $roleTitle = ucwords(str_replace('_', ' ', $profil['nama_peran']));
        $scopeText = $this->resolveScopeText($profil);
        $isTersedia = (bool) $profil['is_tersedia'];

        $children = [];

        $row1Children = [
            [
                'type' => 'container',
                'style' => ['width' => 64, 'height' => 64, 'bg' => 'green-500', 'radius' => 'full', 'align_self' => 'center'],
                'children' => [
                    ['type' => 'text', 'content' => $inisial, 'style' => ['color' => 'white', 'size' => '2xl', 'weight' => 'bold', 'align' => 'center']]
                ]
            ],
            [
                'type' => 'column',
                'style' => ['flex' => 1, 'gap' => '1'],
                'children' => [
                    ['type' => 'text', 'content' => $namaLengkap, 'style' => ['color' => 'white', 'size' => 'xl', 'weight' => 'bold', 'max_lines' => 1, 'overflow' => 'ellipsis']],
                    ['type' => 'text', 'content' => $roleTitle, 'style' => ['color' => 'green-200', 'size' => 'sm']],
                ]
            ]
        ];

        if ($scopeText) {
            $row1Children[1]['children'][] = ['type' => 'text', 'content' => $scopeText, 'style' => ['color' => 'green-300', 'size' => 'xs']];
        }

        if ($profil['nama_peran'] !== 'publik' && $profil['status_akun'] === 'aktif') {
            $row1Children[] = [
                'type' => 'container',
                'style' => ['bg' => $isTersedia ? 'green-500' : 'gray-600', 'radius' => 'full', 'padding' => ['t' => '2', 'b' => '2', 'l' => '3', 'r' => '3']],
                'action' => [
                    'type' => 'action',
                    'payload' => ['action_type' => 'profil.toggle_tersedia', 'id_pengguna' => $this->user->id_pengguna],
                    'confirm' => $isTersedia ? [
                        'title' => 'Tandai Tidak Tersedia?',
                        'message' => 'Status Anda akan berubah menjadi tidak tersedia untuk penugasan.',
                        'confirm_label' => 'Ya',
                        'cancel_label' => 'Batal'
                    ] : null,
                    'on_success' => ['type' => 'reload']
                ],
                'children' => [
                    [
                        'type' => 'row',
                        'style' => ['gap' => '2', 'align' => 'center'],
                        'children' => [
                            ['type' => 'container', 'style' => ['width' => 8, 'height' => 8, 'radius' => 'full', 'bg' => $isTersedia ? 'white' : 'gray-400']],
                            ['type' => 'text', 'content' => $isTersedia ? 'Tersedia' : 'Tidak Tersedia', 'style' => ['color' => 'white', 'size' => 'xs', 'weight' => 'medium']]
                        ]
                    ]
                ]
            ];
        }

        $children[] = ['type' => 'row', 'style' => ['align' => 'center', 'gap' => '4'], 'children' => $row1Children];

        if ($profil['nama_peran'] !== 'publik' && $jabatanAktif) {
            $children[] = ['type' => 'spacer', 'style' => ['height' => '4']];
            $children[] = [
                'type' => 'row',
                'style' => ['align' => 'center', 'gap' => '3'],
                'children' => [
                    ['type' => 'icon', 'name' => 'star', 'style' => ['size' => 14, 'color' => 'yellow-300']],
                    ['type' => 'text', 'content' => $jabatanAktif['nama_jabatan'], 'style' => ['color' => 'green-100', 'size' => 'sm']]
                ]
            ];
        }

        if ($profil['nama_peran'] !== 'publik' && !empty($keahlian)) {
            $children[] = ['type' => 'spacer', 'style' => ['height' => '3']];
            $keahlianChips = [];
            foreach (array_slice($keahlian, 0, 4) as $k) {
                $keahlianChips[] = [
                    'type' => 'container',
                    'style' => ['bg' => 'green-600', 'radius' => 'full', 'padding' => ['t' => '1', 'b' => '1', 'l' => '3', 'r' => '3']],
                    'children' => [['type' => 'text', 'content' => $k['nama_keahlian'], 'style' => ['color' => 'green-100', 'size' => 'xs']]]
                ];
            }
            if (count($keahlian) > 4) {
                $keahlianChips[] = [
                    'type' => 'container',
                    'style' => ['bg' => 'green-600', 'radius' => 'full', 'padding' => ['t' => '1', 'b' => '1', 'l' => '3', 'r' => '3']],
                    'children' => [['type' => 'text', 'content' => '& ' . (count($keahlian) - 4) . ' lainnya', 'style' => ['color' => 'green-100', 'size' => 'xs']]]
                ];
            }
            $children[] = ['type' => 'wrap', 'style' => ['gap' => '2'], 'children' => $keahlianChips];
        }

        $res = [
            'type' => 'container',
            'style' => ['bg' => 'green-700', 'padding' => ['t' => '6', 'b' => '6', 'l' => '4', 'r' => '4']],
            'children' => $children
        ];

        if (empty($profil['nama_lengkap'])) {
            return [
                'type' => 'column',
                'children' => [
                    $res,
                    [
                        'type' => 'container',
                        'style' => ['bg' => 'yellow-50', 'padding' => '4'],
                        'action' => ['type' => 'navigate', 'route' => 'profil.edit'],
                        'children' => [
                            ['type' => 'text', 'content' => 'Lengkapi profil Anda untuk pengalaman lebih baik', 'style' => ['color' => 'yellow-800', 'size' => 'sm']]
                        ]
                    ]
                ]
            ];
        }

        return $res;
    }

    private function getPeranMapping(string $peran): array
    {
        $map = [
            'komandan_insiden' => ['label' => 'Komandan', 'bg' => 'red-100', 'color' => 'red-700', 'icon' => 'star'],
            'trc'              => ['label' => 'Tim Reaksi Cepat', 'bg' => 'orange-100', 'color' => 'orange-700', 'icon' => 'warning'],
            'relawan'          => ['label' => 'Relawan', 'bg' => 'blue-100', 'color' => 'blue-700', 'icon' => 'group'],
            'medis'            => ['label' => 'Tim Medis', 'bg' => 'green-100', 'color' => 'green-700', 'icon' => 'heart'],
            'logistik'         => ['label' => 'Koordinator Log.', 'bg' => 'yellow-100', 'color' => 'yellow-700', 'icon' => 'package'],
            'operator'         => ['label' => 'Operator Sistem', 'bg' => 'gray-100', 'color' => 'gray-600', 'icon' => 'edit'],
        ];
        return $map[$peran] ?? ['label' => 'Petugas', 'bg' => 'gray-100', 'color' => 'gray-600', 'icon' => 'person'];
    }

    private function getStatusInsidenMapping(string $status): array
    {
        $map = [
            'draft'         => ['label' => 'Draft', 'bg' => 'gray-100', 'color' => 'gray-600'],
            'terverifikasi' => ['label' => 'Terverifikasi', 'bg' => 'blue-100', 'color' => 'blue-700'],
            'respon'        => ['label' => 'RESPON', 'bg' => 'orange-100', 'color' => 'orange-700'],
            'pemulihan'     => ['label' => 'Pemulihan', 'bg' => 'yellow-100', 'color' => 'yellow-700'],
            'selesai'       => ['label' => 'Selesai', 'bg' => 'green-100', 'color' => 'green-700'],
            'dibatalkan'    => ['label' => 'Dibatalkan', 'bg' => 'red-100', 'color' => 'red-600'],
        ];
        return $map[$status] ?? ['label' => strtoupper($status), 'bg' => 'gray-100', 'color' => 'gray-600'];
    }

    private function getPrioritasMapping(string $p): array
    {
        $map = [
            'kritis' => ['label' => 'KRITIS', 'bg' => 'red-100', 'color' => 'red-700'],
            'tinggi' => ['label' => 'TINGGI', 'bg' => 'orange-100', 'color' => 'orange-700'],
            'sedang' => ['label' => 'SEDANG', 'bg' => 'blue-50', 'color' => 'blue-600'],
            'rendah' => ['label' => 'RENDAH', 'bg' => 'gray-100', 'color' => 'gray-500'],
        ];
        return $map[$p] ?? ['label' => strtoupper($p), 'bg' => 'gray-100', 'color' => 'gray-500'];
    }

    private function getCommandStateMapping(string $c): array
    {
        $map = [
            'monitoring'      => ['label' => 'Monitoring', 'color' => 'gray-500'],
            'siaga'           => ['label' => 'SIAGA', 'color' => 'yellow-600'],
            'tanggap_darurat' => ['label' => 'TANGGAP', 'color' => 'red-600'],
            'pemulihan'       => ['label' => 'Pemulihan', 'color' => 'blue-600'],
            'selesai'         => ['label' => 'Selesai', 'color' => 'green-600'],
        ];
        return $map[$c] ?? ['label' => ucfirst($c), 'color' => 'gray-600'];
    }

    private function getStatusAkunMapping(string $s): array
    {
        $map = [
            'menunggu' => ['label' => 'Menunggu', 'bg' => 'yellow-100', 'color' => 'yellow-700'],
            'aktif'    => ['label' => 'Aktif', 'bg' => 'green-100', 'color' => 'green-700'],
            'nonaktif' => ['label' => 'Nonaktif', 'bg' => 'gray-100', 'color' => 'gray-600'],
            'suspend'  => ['label' => 'Suspend', 'bg' => 'red-100', 'color' => 'red-600'],
        ];
        return $map[$s] ?? ['label' => ucfirst($s), 'bg' => 'gray-100', 'color' => 'gray-600'];
    }

    private function buildStatusOperasional(array $penugasan, ?array $profil): ?array
    {
        if (!$profil || $profil['nama_peran'] === 'publik') return null;

        if (empty($penugasan)) {
            return [
                'type' => 'container',
                'style' => ['margin' => ['t' => '3', 'l' => '4', 'r' => '4'], 'bg' => 'white', 'radius' => 'xl', 'shadow' => 'sm', 'padding' => '4'],
                'children' => [
                    ['type' => 'text', 'content' => 'Penugasan Aktif Saya', 'style' => ['weight' => 'semibold', 'size' => 'base']],
                    ['type' => 'spacer', 'style' => ['height' => '4']],
                    [
                        'type' => 'empty_state',
                        'data' => [
                            'icon' => 'person',
                            'message' => 'Tidak ada penugasan aktif saat ini',
                            'sub_message' => 'Anda akan muncul di sini saat ditugaskan ke operasi bencana'
                        ]
                    ]
                ]
            ];
        }

        $items = [];
        foreach ($penugasan as $p) {
            $pm = $this->getPeranMapping($p['peran_otoritas']);
            $sm = $this->getStatusInsidenMapping($p['status_insiden']);

            $items[] = [
                'type' => 'container',
                'style' => ['padding' => ['t' => '3', 'b' => '3']],
                'action' => ['type' => 'navigate', 'route' => 'insiden.detail', 'params' => ['id' => $p['id_insiden']]],
                'children' => [
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'gap' => '3'],
                        'children' => [
                            [
                                'type' => 'container',
                                'style' => ['width' => 40, 'height' => 40, 'radius' => 'lg', 'bg' => $pm['bg'], 'align' => 'center', 'justify' => 'center'],
                                'children' => [['type' => 'icon', 'name' => $pm['icon'], 'style' => ['size' => 20, 'color' => $pm['color']]]]
                            ],
                            [
                                'type' => 'column',
                                'style' => ['flex' => 1],
                                'children' => [
                                    ['type' => 'text', 'content' => $p['kode_kejadian'], 'style' => ['family' => 'mono', 'size' => 'sm', 'weight' => 'semibold', 'color' => 'gray-900']],
                                    ['type' => 'text', 'content' => $pm['label'] . ' · ' . $p['nama_bencana'], 'style' => ['size' => 'xs', 'color' => 'gray-500']],
                                    [
                                        'type' => 'row',
                                        'style' => ['gap' => '2', 'align' => 'center'],
                                        'children' => [
                                            ['type' => 'icon', 'name' => 'time', 'style' => ['size' => 11, 'color' => 'gray-400']],
                                            ['type' => 'text', 'content' => 'Sejak ' . date('d M Y, H:i', strtotime($p['waktu_mulai'])), 'style' => ['size' => 'xs', 'color' => 'gray-400']]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'badge',
                                'label' => $sm['label'],
                                'style' => ['bg' => $sm['bg'], 'text_color' => $sm['color'], 'size' => 'xs']
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'type' => 'container',
            'style' => ['margin' => ['t' => '3', 'l' => '4', 'r' => '4'], 'bg' => 'white', 'radius' => 'xl', 'shadow' => 'sm', 'padding' => '4'],
            'children' => [
                [
                    'type' => 'row',
                    'style' => ['align' => 'center', 'justify' => 'between'],
                    'children' => [
                        ['type' => 'text', 'content' => 'Penugasan Aktif Saya', 'style' => ['weight' => 'semibold', 'size' => 'base', 'color' => 'gray-900']],
                        ['type' => 'badge', 'label' => count($penugasan) . ' Aktif', 'style' => ['bg' => 'green-100', 'text_color' => 'green-700', 'size' => 'xs']]
                    ]
                ],
                ['type' => 'spacer', 'style' => ['height' => '3']],
                ['type' => 'divider'],
                ['type' => 'spacer', 'style' => ['height' => '3']],
                [
                    'type' => 'list',
                    'separator' => true,
                    'items' => $items
                ]
            ]
        ];
    }

    private function buildCommandCenterSection(?array $commandCenter, ?array $alertInsiden, ?array $profil): ?array
    {
        if ($commandCenter === null) return null;

        $children = [];

        if (!empty($alertInsiden)) {
            $alertList = [];
            foreach (array_slice($alertInsiden, 0, 2) as $a) {
                $alertList[] = ['type' => 'text', 'content' => $a['kode_kejadian'] . ' — ' . $a['nama_bencana'] . ', ' . $a['nama_pcnu'], 'style' => ['size' => 'xs', 'color' => 'yellow-700']];
            }

            $children[] = [
                'type' => 'container',
                'style' => ['margin' => ['t' => '3', 'l' => '4', 'r' => '4'], 'bg' => 'yellow-50', 'border_color' => 'yellow-300', 'border_width' => 1, 'radius' => 'xl', 'padding' => '4'],
                'children' => [
                    [
                        'type' => 'row',
                        'style' => ['gap' => '3', 'align' => 'start'],
                        'children' => [
                            ['type' => 'icon', 'name' => 'warning', 'style' => ['size' => 18, 'color' => 'yellow-600']],
                            [
                                'type' => 'column',
                                'style' => ['flex' => 1],
                                'children' => array_merge([
                                    ['type' => 'text', 'content' => count($alertInsiden) . ' insiden belum terbentuk klaster', 'style' => ['weight' => 'semibold', 'size' => 'sm', 'color' => 'yellow-900']]
                                ], $alertList)
                            ]
                        ]
                    ]
                ]
            ];
        }

        $children[] = [
            'type' => 'container',
            'style' => ['margin' => ['t' => '3', 'l' => '4', 'r' => '4']],
            'children' => [
                [
                    'type' => 'row',
                    'style' => ['align' => 'center', 'justify' => 'between'],
                    'children' => [
                        ['type' => 'text', 'content' => 'Pusat Komando', 'style' => ['weight' => 'bold', 'size' => 'lg', 'color' => 'gray-900']],
                        [
                            'type' => 'row',
                            'style' => ['gap' => '2', 'align' => 'center'],
                            'children' => [
                                ['type' => 'container', 'style' => ['width' => 8, 'height' => 8, 'radius' => 'full', 'bg' => !empty($commandCenter) ? 'green-500' : 'gray-300']],
                                ['type' => 'text', 'content' => count($commandCenter) . ' Insiden Aktif', 'style' => ['size' => 'xs', 'color' => 'gray-500']]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if (empty($commandCenter)) {
            $children[] = [
                'type' => 'container',
                'style' => ['bg' => 'white', 'radius' => 'xl', 'shadow' => 'sm', 'margin' => ['t' => '2', 'l' => '4', 'r' => '4'], 'padding' => '6'],
                'children' => [
                    ['type' => 'empty_state', 'data' => ['icon' => 'check', 'message' => 'Tidak ada insiden aktif', 'sub_message' => 'Seluruh insiden dalam lingkup Anda sudah selesai atau tidak ada laporan baru']]
                ]
            ];
        } else {
            $items = [];
            foreach ($commandCenter as $c) {
                $prm = $this->getPrioritasMapping($c['prioritas']);
                $sm = $this->getStatusInsidenMapping($c['status_insiden']);
                $cm = $this->getCommandStateMapping($c['command_state']);

                $items[] = [
                    'type' => 'container',
                    'style' => ['bg' => 'white', 'radius' => 'xl', 'shadow' => 'sm', 'padding' => '4', 'margin' => ['b' => '3']],
                    'action' => ['type' => 'navigate', 'route' => 'insiden.detail', 'params' => ['id' => $c['id_insiden']]],
                    'children' => [
                        [
                            'type' => 'row',
                            'style' => ['align' => 'center', 'justify' => 'between'],
                            'children' => [
                                ['type' => 'text', 'content' => $c['kode_kejadian'], 'style' => ['family' => 'mono', 'size' => 'sm', 'weight' => 'bold', 'color' => 'gray-900']],
                                [
                                    'type' => 'row',
                                    'style' => ['gap' => '2'],
                                    'children' => [
                                        ['type' => 'badge', 'label' => $prm['label'], 'style' => ['bg' => $prm['bg'], 'text_color' => $prm['color'], 'size' => 'xs']],
                                        ['type' => 'badge', 'label' => $sm['label'], 'style' => ['bg' => $sm['bg'], 'text_color' => $sm['color'], 'size' => 'xs']]
                                    ]
                                ]
                            ]
                        ],
                        ['type' => 'spacer', 'style' => ['height' => '2']],
                        [
                            'type' => 'row',
                            'style' => ['gap' => '2', 'align' => 'center'],
                            'children' => [
                                ['type' => 'icon', 'name' => 'group', 'style' => ['size' => 13, 'color' => ($c['nama_klaster'] ?? 'Tunggu Aktivasi') === 'Tunggu Aktivasi' ? 'yellow-500' : 'green-600']],
                                ['type' => 'text', 'content' => $c['nama_klaster'] ?? 'Tunggu Aktivasi', 'style' => ['size' => 'xs', 'color' => ($c['nama_klaster'] ?? 'Tunggu Aktivasi') === 'Tunggu Aktivasi' ? 'yellow-600' : 'green-700', 'weight' => ($c['nama_klaster'] ?? 'Tunggu Aktivasi') === 'Tunggu Aktivasi' ? 'medium' : 'normal']]
                            ]
                        ],
                        ['type' => 'spacer', 'style' => ['height' => '3']],
                        ['type' => 'divider'],
                        ['type' => 'spacer', 'style' => ['height' => '3']],
                        [
                            'type' => 'row',
                            'style' => ['justify' => 'around'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'style' => ['align' => 'center'],
                                    'children' => [
                                        ['type' => 'text', 'content' => (string)($c['lama_kejadian_hari'] ?? 0), 'style' => ['family' => 'mono', 'size' => 'xl', 'weight' => 'bold', 'color' => ($c['lama_kejadian_hari'] ?? 0) > 7 ? 'red-600' : 'gray-900']],
                                        ['type' => 'text', 'content' => 'Hari', 'style' => ['size' => 'xs', 'color' => 'gray-400']]
                                    ]
                                ],
                                ['type' => 'container', 'style' => ['width' => 1, 'height' => 32, 'bg' => 'gray-200']],
                                [
                                    'type' => 'column',
                                    'style' => ['align' => 'center'],
                                    'children' => [
                                        ['type' => 'text', 'content' => (string)($c['jumlah_sitrep'] ?? 0), 'style' => ['family' => 'mono', 'size' => 'xl', 'weight' => 'bold', 'color' => 'gray-900']],
                                        ['type' => 'text', 'content' => 'Sitrep', 'style' => ['size' => 'xs', 'color' => 'gray-400']]
                                    ]
                                ],
                                ['type' => 'container', 'style' => ['width' => 1, 'height' => 32, 'bg' => 'gray-200']],
                                [
                                    'type' => 'column',
                                    'style' => ['align' => 'center'],
                                    'children' => [
                                        ['type' => 'text', 'content' => $cm['label'], 'style' => ['size' => 'xs', 'weight' => 'semibold', 'color' => $cm['color'], 'align' => 'center']],
                                        ['type' => 'text', 'content' => 'Status Ops', 'style' => ['size' => 'xs', 'color' => 'gray-400']]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ];
            }
            $children[] = [
                'type' => 'list',
                'style' => ['margin' => ['t' => '2', 'l' => '4', 'r' => '4']],
                'separator' => false,
                'items' => $items
            ];
        }

        return ['type' => 'column', 'children' => $children];
    }

    private function buildAkunMenu(?array $profil): ?array
    {
        if (!$profil) return null;

        $statusAkun = $profil['status_akun'] ?? 'aktif';
        $am = $this->getStatusAkunMapping($statusAkun);
        $terakhirMasuk = $profil['terakhir_masuk'] ? date('d M Y, H:i', strtotime($profil['terakhir_masuk'])) : 'Belum pernah';

        $children = [
            [
                'type' => 'container',
                'style' => ['padding' => '4'],
                'children' => [
                    ['type' => 'text', 'content' => 'Akun', 'style' => ['weight' => 'semibold', 'size' => 'sm', 'color' => 'gray-500']],
                    ['type' => 'spacer', 'style' => ['height' => '3']],
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'justify' => 'between', 'padding' => ['t' => '2', 'b' => '2']],
                        'children' => [
                            ['type' => 'row', 'style' => ['gap' => '3', 'align' => 'center'], 'children' => [['type' => 'icon', 'name' => 'phone', 'style' => ['size' => 16, 'color' => 'gray-400']], ['type' => 'text', 'content' => 'No HP', 'style' => ['size' => 'sm', 'color' => 'gray-700']]]],
                            ['type' => 'text', 'content' => $profil['no_hp'], 'style' => ['size' => 'sm', 'color' => 'gray-500', 'family' => 'mono']]
                        ]
                    ],
                    ['type' => 'divider'],
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'justify' => 'between', 'padding' => ['t' => '2', 'b' => '2']],
                        'children' => [
                            ['type' => 'row', 'style' => ['gap' => '3', 'align' => 'center'], 'children' => [['type' => 'icon', 'name' => 'info', 'style' => ['size' => 16, 'color' => 'gray-400']], ['type' => 'text', 'content' => 'Status Akun', 'style' => ['size' => 'sm', 'color' => 'gray-700']]]],
                            ['type' => 'badge', 'label' => $am['label'], 'style' => ['bg' => $am['bg'], 'text_color' => $am['color'], 'size' => 'xs']]
                        ]
                    ],
                    ['type' => 'divider'],
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'justify' => 'between', 'padding' => ['t' => '2', 'b' => '2']],
                        'children' => [
                            ['type' => 'row', 'style' => ['gap' => '3', 'align' => 'center'], 'children' => [['type' => 'icon', 'name' => 'time', 'style' => ['size' => 16, 'color' => 'gray-400']], ['type' => 'text', 'content' => 'Terakhir Masuk', 'style' => ['size' => 'sm', 'color' => 'gray-700']]]],
                            ['type' => 'text', 'content' => $terakhirMasuk, 'style' => ['size' => 'xs', 'color' => 'gray-400']]
                        ]
                    ]
                ]
            ],
            ['type' => 'divider']
        ];

        if ($profil['nama_peran'] !== 'publik') {
            $children[] = [
                'type' => 'container',
                'style' => ['padding' => '4'],
                'action' => ['type' => 'navigate', 'route' => 'profil.ganti-password'],
                'children' => [
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'gap' => '3'],
                        'children' => [
                            ['type' => 'container', 'style' => ['width' => 36, 'height' => 36, 'radius' => 'lg', 'bg' => 'gray-100', 'align' => 'center', 'justify' => 'center'], 'children' => [['type' => 'icon', 'name' => 'lock', 'style' => ['size' => 18, 'color' => 'gray-600']]]],
                            ['type' => 'text', 'content' => 'Ganti Kata Sandi', 'style' => ['size' => 'sm', 'color' => 'gray-700', 'flex' => 1]],
                            ['type' => 'icon', 'name' => 'arrow_right', 'style' => ['size' => 16, 'color' => 'gray-300']]
                        ]
                    ]
                ]
            ];
            $children[] = ['type' => 'divider'];
            $children[] = [
                'type' => 'container',
                'style' => ['padding' => '4'],
                'action' => [
                    'type' => 'submit',
                    'endpoint' => '/api/v1/auth/logout',
                    'method' => 'POST',
                    'fields' => [],
                    'confirm' => [
                        'title' => 'Keluar dari NURISK?',
                        'message' => 'Sesi Anda akan diakhiri. Data offline masih tersimpan di perangkat.',
                        'confirm_label' => 'Ya, Keluar',
                        'cancel_label' => 'Batal'
                    ],
                    'on_success' => ['type' => 'navigate', 'route' => 'login', 'clear_stack' => true]
                ],
                'children' => [
                    [
                        'type' => 'row',
                        'style' => ['align' => 'center', 'gap' => '3'],
                        'children' => [
                            ['type' => 'container', 'style' => ['width' => 36, 'height' => 36, 'radius' => 'lg', 'bg' => 'red-50', 'align' => 'center', 'justify' => 'center'], 'children' => [['type' => 'icon', 'name' => 'logout', 'style' => ['size' => 18, 'color' => 'red-600']]]],
                            ['type' => 'text', 'content' => 'Keluar', 'style' => ['size' => 'sm', 'color' => 'red-600', 'weight' => 'medium']]
                        ]
                    ]
                ]
            ];
        }

        return [
            'type' => 'container',
            'style' => ['margin' => ['t' => '4', 'l' => '4', 'r' => '4', 'b' => '8'], 'bg' => 'white', 'radius' => 'xl', 'shadow' => 'sm'],
            'children' => $children
        ];
    }
}
