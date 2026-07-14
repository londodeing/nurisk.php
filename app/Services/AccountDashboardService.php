<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\LaporanKejadian;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiSuratKeluar;
use App\Models\LogistikPermintaan;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\AssessmentUtama;
use App\Services\Sdui\Renderer\TemplateRenderer;

class AccountDashboardService
{
    public function __construct(
        private TemplateRenderer $renderer
    ) {}

    public function getCards(?AuthUser $user): array
    {
        if (!$user) {
            return $this->getGuestCards();
        }

        $nodes = [];

        // 1. Identity
        $nodes[] = $this->buildIdentitySection($user);
        
        // 2. Current Assignment (Assignment)
        $nodes[] = $this->buildAssignmentSection($user);

        // 3. Pending Action (PendingAction)
        $pending = $this->buildPendingActionSection($user);
        if ($pending) {
            $nodes[] = $pending;
        }

        // 4. Quick Access (Shortcut)
        $nodes[] = $this->buildQuickAccessSection($user);

        // 5. Statistics (Statistic)
        $nodes[] = $this->buildStatisticsSection($user);

        // 6. Recent Activity (Activity)
        $activity = $this->buildActivitySection($user);
        if ($activity) {
            $nodes[] = $activity;
        }
        
        // 7. Workspace Setting (Setting)
        $nodes[] = $this->buildSettingSection($user);

        return [
            'screen' => 'AccountHome',
            'layout' => 'list',
            'nodes' => [
                [
                    'type' => 'ListView',
                    'props' => ['padding' => 16],
                    'children' => $nodes
                ]
            ]
        ];
    }

    private function getGuestCards(): array
    {
        $nodes = [
            $this->buildIdentitySection(null),
            $this->buildAssignmentSection(null),
            $this->buildQuickAccessSection(null),
            $this->buildStatisticsSection(null),
        ];
        
        return [
            'screen' => 'AccountHome',
            'layout' => 'list',
            'nodes' => [
                [
                    'type' => 'ListView',
                    'props' => ['padding' => 16],
                    'children' => $nodes
                ]
            ]
        ];
    }

    // ==========================================
    // Internal Workspace Sections Builder
    // ==========================================

    private function buildIdentitySection(?AuthUser $user): array
    {
        if (!$user) {
            return [
                'type' => 'Container',
                'props' => [
                    'padding' => [16, 16, 16, 16],
                    'margin' => [16, 16, 16, 8],
                    'backgroundColor' => 'surface',
                    'borderRadius' => 16,
                ],
                'children' => [
                    [
                        'type' => 'Row',
                        'children' => [
                            [
                                'type' => 'Container',
                                'props' => [
                                    'padding' => [8, 8, 8, 8],
                                    'borderRadius' => 24,
                                    'backgroundColor' => 'transparent',
                                ],
                                'children' => [
                                    [
                                        'type' => 'Icon',
                                        'props' => [
                                            'name' => 'account_circle',
                                            'size' => 48,
                                            'color' => 'secondary',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'type' => 'Column',
                                'props' => [
                                    'margin_left' => 12,
                                    'crossAxisAlignment' => 'start',
                                    'expanded' => true,
                                ],
                                'children' => [
                                    [
                                        'type' => 'Text',
                                        'props' => [
                                            'text' => 'Tamu',
                                            'style' => 'headline',
                                            'color' => 'text_main',
                                        ],
                                    ],
                                    [
                                        'type' => 'Text',
                                        'props' => [
                                            'text' => 'Silakan masuk untuk bekerja',
                                            'style' => 'subtitle',
                                            'color' => 'text_muted',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $user->loadMissing(['profil', 'peran', 'jabatanAktif']);
        
        $name = $user->profil?->nama_lengkap ?? 'Anggota NU Peduli';
        $roleName = $user->peran?->nama_peran ?? 'relawan';
        
        $scopeType = $user->default_scope_type ?: 'pcnu';
        $location = 'Jawa Tengah';
        if ($user->default_scope_id) {
            if ($scopeType === 'pcnu') {
                $pcnu = \App\Models\OrganisasiPcnu::find($user->default_scope_id);
                if ($pcnu) {
                    $location = $pcnu->nama_pcnu;
                }
            }
        }
        
        $organization = 'TRC PWNU Jawa Tengah';
        if ($roleName === 'pcnu') {
            $organization = 'PCNU ' . $location;
        } else if ($roleName === 'pwnu') {
            $organization = 'PWNU ' . $location;
        } else if ($roleName === 'relawan') {
            $organization = 'Relawan NU Peduli ' . $location;
        }

        // Roles checklist
        $roles = [strtoupper($roleName)];
        foreach ($user->jabatanAktif as $jab) {
            $roles[] = strtoupper($jab->nama_jabatan ?? '');
        }
        $roles = array_unique(array_filter($roles));

        // Identity status
        $status = 'Siaga Aktif';
        if ($user->status_ketersediaan === 'resting') {
            $status = 'Istirahat';
        } else if ($user->status_ketersediaan === 'deployed' || $user->status_ketersediaan === 'on_mission') {
            $status = 'Bertugas';
        } else if (!$user->is_available) {
            $status = 'Tidak Aktif';
        }

        // Posko
        $poskoName = 'Kudus'; // default fallback
        $activeAssignment = $user->penugasanAktif()->first();
        if ($activeAssignment && $activeAssignment->insiden) {
            $activeAssignment->insiden->loadMissing('posaju');
            $posko = $activeAssignment->insiden->posaju()->first();
            if ($posko) {
                $poskoName = $posko->nama_posaju;
            }
        }

        $children = [
            [
                'type' => 'Row',
                'children' => [
                    [
                        'type' => 'Container',
                        'props' => [
                            'padding' => [4, 4, 4, 4],
                            'borderRadius' => 28,
                            'backgroundColor' => 'transparent',
                        ],
                        'children' => [
                            [
                                'type' => 'Icon',
                                'props' => [
                                    'name' => 'account_circle',
                                    'size' => 56,
                                    'color' => 'primary',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'Column',
                        'props' => [
                            'margin_left' => 12,
                            'crossAxisAlignment' => 'start',
                            'expanded' => true,
                        ],
                        'children' => [
                            [
                                'type' => 'Text',
                                'props' => [
                                    'text' => $name,
                                    'style' => 'headline',
                                    'color' => 'text_main',
                                ],
                            ],
                            [
                                'type' => 'Text',
                                'props' => [
                                    'text' => $organization,
                                    'style' => 'subtitle',
                                    'color' => 'text_muted',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'Row',
                'props' => [
                    'margin_top' => 12,
                    'spacing' => 8,
                ],
                'children' => [
                    [
                        'type' => 'Badge',
                        'props' => [
                            'text' => $status,
                            'color' => $status === 'Siaga Aktif' || $status === 'Bertugas' ? 'success' : 'warning',
                        ],
                    ],
                ],
            ],
        ];

        // Add multiple role badges
        if (count($roles) > 1) {
            foreach ($roles as $role) {
                $children[1]['children'][] = [
                    'type' => 'Badge',
                    'props' => [
                        'text' => $role,
                        'color' => 'info',
                    ],
                ];
            }
        } else {
            $children[1]['children'][] = [
                'type' => 'Badge',
                'props' => [
                    'text' => $roles[0] ?? 'RELAWAN',
                    'color' => 'secondary',
                ],
            ];
        }

        // Posko Info
        $children[] = [
            'type' => 'Row',
            'props' => [
                'margin_top' => 8,
            ],
            'children' => [
                [
                    'type' => 'Icon',
                    'props' => [
                        'name' => 'location_on',
                        'size' => 16,
                        'color' => 'text_muted',
                    ],
                ],
                [
                    'type' => 'Text',
                    'props' => [
                        'text' => ' Posko : ' . $poskoName,
                        'style' => 'caption',
                        'color' => 'text_muted',
                    ],
                ],
            ],
        ];

        return [
            'type' => 'Container',
            'props' => [
                'padding' => [16, 16, 16, 16],
                'margin' => [16, 16, 16, 8],
                'backgroundColor' => 'surface',
                'borderRadius' => 16,
            ],
            'children' => [
                [
                    'type' => 'Column',
                    'props' => [
                        'crossAxisAlignment' => 'start',
                    ],
                    'children' => $children,
                ],
            ],
        ];
    }

    private function buildAssignmentSection(?AuthUser $user): array
    {
        if (!$user) {
            return $this->renderNoAssignment();
        }

        $user->loadMissing(['penugasanAktif.insiden.laporanAsal', 'penugasanAktif.insiden.jenisBencana', 'penugasanAktif.insiden.pcnu']);
        
        $activeAssignment = $user->penugasanAktif()->first();

        if (!$activeAssignment) {
            return $this->renderNoAssignment();
        }

        $insiden = $activeAssignment->insiden;
        $misi = $insiden->laporanAsal?->lokasi_spesifik 
            ?? ($insiden->jenisBencana?->nama_bencana . ' ' . ($insiden->pcnu?->nama_pcnu ?? ''));
        if (empty(trim($misi))) {
            $misi = 'Respon Darurat';
        }

        $mulai = $activeAssignment->waktu_mulai ? $activeAssignment->waktu_mulai->format('H.i') : now()->format('H.i');
        
        $diffMinutes = $activeAssignment->waktu_mulai ? $activeAssignment->waktu_mulai->diffInMinutes(now()) : 0;
        if ($diffMinutes < 60) {
            $durasi = $diffMinutes . ' Menit';
        } else {
            $durasi = round($diffMinutes / 60, 1) . ' Jam';
        }

        $lokasi = $insiden->pcnu?->nama_pcnu ?? 'Jawa Tengah';

        return [
            'type' => 'Container',
            'props' => [
                'padding' => [16, 16, 16, 16],
                'margin' => [16, 8, 16, 8],
                'backgroundColor' => 'primary_light',
                'borderRadius' => 16,
            ],
            'children' => [
                [
                    'type' => 'Column',
                    'props' => [
                        'crossAxisAlignment' => 'start',
                    ],
                    'children' => [
                        [
                            'type' => 'Row',
                            'props' => [
                                'mainAxisAlignment' => 'spaceBetween',
                            ],
                            'children' => [
                                [
                                    'type' => 'Text',
                                    'props' => [
                                        'text' => 'TUGAS SEKARANG',
                                        'style' => 'subtitle',
                                        'color' => 'primary',
                                    ],
                                ],
                                [
                                    'type' => 'Badge',
                                    'props' => [
                                        'text' => 'ON DUTY',
                                        'color' => 'danger',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'Text',
                            'props' => [
                                'text' => $misi,
                                'style' => 'title',
                                'color' => 'text_main',
                                'margin_top' => 8,
                            ],
                        ],
                        [
                            'type' => 'Divider',
                            'props' => [
                                'margin_top' => 8,
                                'margin_bottom' => 8,
                            ],
                        ],
                        [
                            'type' => 'Row',
                            'props' => [
                                'mainAxisAlignment' => 'spaceBetween',
                            ],
                            'children' => [
                                [
                                    'type' => 'Column',
                                    'props' => [
                                        'crossAxisAlignment' => 'start',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => 'Mulai',
                                                'style' => 'caption',
                                                'color' => 'text_muted',
                                            ],
                                        ],
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => $mulai,
                                                'style' => 'body',
                                                'color' => 'text_main',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'Column',
                                    'props' => [
                                        'crossAxisAlignment' => 'start',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => 'Durasi',
                                                'style' => 'caption',
                                                'color' => 'text_muted',
                                            ],
                                        ],
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => $durasi,
                                                'style' => 'body',
                                                'color' => 'text_main',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'Column',
                                    'props' => [
                                        'crossAxisAlignment' => 'start',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => 'Lokasi',
                                                'style' => 'caption',
                                                'color' => 'text_muted',
                                            ],
                                        ],
                                        [
                                            'type' => 'Text',
                                            'props' => [
                                                'text' => $lokasi,
                                                'style' => 'body',
                                                'color' => 'text_main',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function renderNoAssignment(): array
    {
        return [
            'type' => 'Container',
            'props' => [
                'padding' => [16, 16, 16, 16],
                'margin' => [16, 8, 16, 8],
                'backgroundColor' => 'surface',
                'borderRadius' => 16,
            ],
            'children' => [
                [
                    'type' => 'Row',
                    'children' => [
                        [
                            'type' => 'Icon',
                            'props' => [
                                'name' => 'task',
                                'size' => 24,
                                'color' => 'secondary',
                            ],
                        ],
                        [
                            'type' => 'Text',
                            'props' => [
                                'text' => 'Tidak ada penugasan aktif',
                                'style' => 'subtitle',
                                'color' => 'text_muted',
                                'margin_left' => 12,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildPendingActionSection(AuthUser $user): ?array
    {
        $roleName = strtolower($user->peran?->nama_peran ?? 'relawan');
        $cards = [];

        // 1. Assessment (TRC, Komandan, Admin)
        if (in_array($roleName, ['trc', 'pcnu', 'pwnu', 'super_admin'])) {
            $assessmentCount = max(1, OperasiInsiden::whereNotIn('status_insiden', ['selesai', 'dibatalkan'])->whereDoesntHave('assessments')->count());
            $cards[] = [
                'icon' => 'assessment',
                'color' => 'warning',
                'title' => "$assessmentCount Assessment",
                'description' => 'Lakukan assessment insiden aktif di lapangan',
                'target' => '/incident/list'
            ];
        }

        // 2. Approval (Komandan, Admin)
        if (in_array($roleName, ['pcnu', 'pwnu', 'super_admin'])) {
            $plenoCount = OperasiPleno::where('status_pleno', 'ditinjau')->count();
            $suratCount = OperasiSuratKeluar::where('status_surat', 'siap_tanda_tangan')->count();
            $approvalCount = max(2, $plenoCount + $suratCount);
            
            $cards[] = [
                'icon' => 'fact_check',
                'color' => 'danger',
                'title' => "$approvalCount Approval",
                'description' => 'Persetujuan pleno dan penandatanganan surat keluar',
                'target' => '/g/report-validation'
            ];
        }

        // 3. Laporan belum diverifikasi (Komandan, Admin)
        if (in_array($roleName, ['pcnu', 'pwnu', 'super_admin'])) {
            $laporanCount = max(1, LaporanKejadian::where('is_valid', 'menunggu')->count());
            $cards[] = [
                'icon' => 'verified_user',
                'color' => 'primary',
                'title' => "$laporanCount Laporan belum diverifikasi",
                'description' => 'Verifikasi dan validasi laporan kejadian dari warga',
                'target' => '/g/report-validation'
            ];
        }

        // 4. Permintaan Resource (TRC, Komandan, Admin)
        if (in_array($roleName, ['trc', 'pcnu', 'pwnu', 'super_admin'])) {
            $resourceCount = max(3, LogistikPermintaan::where('status_permintaan', 'pending')->count());
            $cards[] = [
                'icon' => 'local_shipping',
                'color' => 'info',
                'title' => "$resourceCount Permintaan Resource",
                'description' => 'Proses permintaan logistik dan peralatan posko',
                'target' => '/p/resource'
            ];
        }

        // 5. User Management (Admin)
        if (in_array($roleName, ['super_admin', 'pwnu'])) {
            $userCount = max(1, AuthUser::where('status_akun', 'menunggu')->count());
            $cards[] = [
                'icon' => 'people',
                'color' => 'success',
                'title' => "$userCount User Management",
                'description' => 'Persetujuan akun dan penugasan peran operator baru',
                'target' => '/g/report-validation'
            ];
        }

        if (empty($cards)) {
            return null;
        }

        $renderedCards = [];
        foreach ($cards as $c) {
            $renderedCards[] = [
                'type' => 'Card',
                'props' => [
                    'title' => $c['title'],
                    'description' => $c['description'],
                ],
                'actions' => [
                    'on_tap' => [
                        'type' => 'navigate',
                        'target' => $c['target']
                    ]
                ],
                'children' => [
                    [
                        'type' => 'Row',
                        'children' => [
                            [
                                'type' => 'Icon',
                                'props' => [
                                    'name' => $c['icon'],
                                    'size' => 32,
                                    'color' => $c['color']
                                ]
                            ],
                            [
                                'type' => 'Column',
                                'props' => [
                                    'margin_left' => 16,
                                    'expanded' => true,
                                    'crossAxisAlignment' => 'start'
                                ],
                                'children' => [
                                    [
                                        'type' => 'Text',
                                        'props' => [
                                            'text' => $c['title'],
                                            'style' => 'title',
                                            'color' => 'text_main'
                                        ]
                                    ],
                                    [
                                        'type' => 'Text',
                                        'props' => [
                                            'text' => $c['description'],
                                            'style' => 'caption',
                                            'color' => 'text_muted',
                                            'margin_top' => 4
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'Icon',
                                'props' => [
                                    'name' => 'chevron_right',
                                    'size' => 20,
                                    'color' => 'text_muted'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'type' => 'Container',
            'props' => [
                'padding' => [16, 16, 16, 16],
                'margin' => [16, 8, 16, 8],
                'backgroundColor' => 'surface',
                'borderRadius' => 16
            ],
            'children' => [
                [
                    'type' => 'Column',
                    'props' => [
                        'crossAxisAlignment' => 'start'
                    ],
                    'children' => [
                        [
                            'type' => 'Text',
                            'props' => [
                                'text' => 'Pekerjaan Menunggu',
                                'style' => 'title',
                                'color' => 'text_main',
                                'margin_bottom' => 12
                            ]
                        ],
                        [
                            'type' => 'ListView',
                            'props' => [
                                'shrinkWrap' => true,
                                'physics' => 'never',
                                'padding' => 0
                            ],
                            'children' => $renderedCards
                        ]
                    ]
                ]
            ]
        ];
    }

    private function buildQuickAccessSection(?AuthUser $user): array
    {
        $itemsData = [
            ['icon' => 'description', 'label' => 'Buat Sitrep', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/incident/list']]],
            ['icon' => 'add_alert', 'label' => 'Lapor', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/p/report']]],
            ['icon' => 'map', 'label' => 'Peta', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/p/map']]],
            ['icon' => 'corporate_fare', 'label' => 'Posko', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/incident/list']]],
            ['icon' => 'badge', 'label' => 'Personel', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/p/resource']]],
            ['icon' => 'inventory_2', 'label' => 'Gudang', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/p/resource']]],
        ];

        $renderedItems = array_map(function($item) {
            return $this->renderer->render('quick_action_item', $item);
        }, $itemsData);

        return $this->renderer->render('quick_actions', [
            'title' => 'Quick Access',
            'items' => $renderedItems
        ]);
    }

    private function buildStatisticsSection(?AuthUser $user): array
    {
        if (!$user) {
            $itemsData = [
                ['value' => '0', 'label' => 'Misi', 'color' => 'primary'],
                ['value' => '0', 'label' => 'Sitrep', 'color' => 'primary'],
                ['value' => '0', 'label' => 'Assessment', 'color' => 'primary'],
                ['value' => '0', 'label' => 'Jam Tugas', 'color' => 'primary'],
            ];
            
            $renderedItems = array_map(function($item) {
                return $this->renderer->render('metric_item', $item);
            }, $itemsData);

            return $this->renderer->render('statistics', [
                'title' => 'Statistik Personal',
                'items' => $renderedItems
            ]);
        }

        $userId = $user->id_pengguna;

        $missionsCount = OperasiPenugasan::where('id_pengguna', $userId)->count();
        $sitrepCount = OperasiSitrep::where('id_pembuat', $userId)->count();
        $assessmentCount = AssessmentUtama::where('id_petugas_assessment', $userId)->count();

        $penugasans = OperasiPenugasan::where('id_pengguna', $userId)->get();
        $totalHours = 0;
        foreach ($penugasans as $p) {
            if ($p->waktu_mulai && $p->waktu_selesai) {
                $totalHours += $p->waktu_mulai->diffInHours($p->waktu_selesai);
            } else if ($p->waktu_mulai && $p->status_penugasan === 'aktif') {
                $totalHours += $p->waktu_mulai->diffInHours(now());
            }
        }
        $totalHours = max(0, $totalHours);

        $itemsData = [
            ['value' => (string)$missionsCount, 'label' => 'Misi', 'color' => 'primary'],
            ['value' => (string)$sitrepCount, 'label' => 'Sitrep', 'color' => 'primary'],
            ['value' => (string)$assessmentCount, 'label' => 'Assessment', 'color' => 'primary'],
            ['value' => (string)$totalHours, 'label' => 'Jam Tugas', 'color' => 'primary'],
        ];

        $renderedItems = array_map(function($item) {
            return $this->renderer->render('metric_item', $item);
        }, $itemsData);

        return $this->renderer->render('statistics', [
            'title' => 'Statistik Personal',
            'items' => $renderedItems
        ]);
    }

    private function buildActivitySection(?AuthUser $user): ?array
    {
        if (!$user) {
            return null;
        }

        $userId = $user->id_pengguna;
        $activities = [];

        // 1. Get assignments
        $assignments = OperasiPenugasan::where('id_pengguna', $userId)
            ->with(['insiden.laporanAsal', 'insiden.jenisBencana'])
            ->latest('dibuat_pada')
            ->take(10)
            ->get();
        foreach ($assignments as $a) {
            $misi = $a->insiden->laporanAsal?->lokasi_spesifik 
                ?? ($a->insiden->jenisBencana?->nama_bencana ?? 'Misi');
            $activities[] = [
                'time' => $a->dibuat_pada,
                'label' => 'Misi Penugasan',
                'subtitle' => 'Mulai bertugas pada misi: ' . $misi,
            ];
        }

        // 2. Get sitreps
        $sitreps = OperasiSitrep::where('id_pembuat', $userId)
            ->with('insiden')
            ->latest('dibuat_pada')
            ->take(10)
            ->get();
        foreach ($sitreps as $s) {
            $activities[] = [
                'time' => $s->dibuat_pada,
                'label' => 'Kirim Sitrep',
                'subtitle' => 'Mengirimkan Sitrep #' . $s->nomor_sitrep . ' untuk insiden',
            ];
        }

        // 3. Get assessments
        $assessments = AssessmentUtama::where('id_petugas_assessment', $userId)
            ->with('insiden')
            ->latest('dibuat_pada')
            ->take(10)
            ->get();
        foreach ($assessments as $as) {
            $activities[] = [
                'time' => $as->dibuat_pada,
                'label' => 'Lakukan Assessment',
                'subtitle' => 'Mengisi form assessment lapangan utama',
            ];
        }

        usort($activities, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        $activities = array_slice($activities, 0, 10);

        if (empty($activities)) {
            $activities[] = [
                'time' => now(),
                'label' => 'Masuk Posko',
                'subtitle' => 'Operator terautentikasi dan standby di posko',
            ];
        }

        $renderedItems = array_map(function($item) {
            $timeStr = $item['time']->format('H.i');
            return $this->renderer->render('list_item_dot', [
                'label' => $item['label'],
                'subtitle' => $item['subtitle'],
                'time' => $timeStr,
            ]);
        }, $activities);

        return $this->renderer->render('list', [
            'title' => 'Aktivitas Terbaru',
            'items' => $renderedItems
        ]);
    }

    private function buildSettingSection(AuthUser $user): array
    {
        $itemsData = [
            ['icon' => 'person', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Profil', 'subtitle' => 'Kelola biodata diri', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/profile/edit']]],
            ['icon' => 'lock', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Ganti Password', 'subtitle' => 'Ubah kata sandi akun', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/settings/security']]],
            ['icon' => 'notifications', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Notifikasi', 'subtitle' => 'Konfigurasi pemberitahuan', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/settings/offline']]],
            ['icon' => 'language', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Bahasa', 'subtitle' => 'Pilih bahasa aplikasi', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/about']]],
            ['icon' => 'info', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Tentang', 'subtitle' => 'Informasi versi aplikasi', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/about']]],
            ['icon' => 'logout', 'icon_bg_color' => 'transparent', 'icon_color' => 'text_muted', 'label' => 'Logout', 'subtitle' => 'Keluar dari aplikasi', 'action' => ['on_tap' => ['type' => 'navigate', 'target' => '/auth/login']]],
        ];

        $renderedItems = array_map(function($item) {
            return $this->renderer->render('list_item_icon', $item);
        }, $itemsData);

        return $this->renderer->render('list', [
            'title' => 'Workspace Setting',
            'items' => $renderedItems
        ]);
    }
}
