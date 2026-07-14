<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CommandCenter\DecisionQueueService;
use App\Services\CommandCenter\QuickActionService;

class ProfileApiController extends Controller
{
    public function __construct(
        private DecisionQueueService $decisionQueue,
        private QuickActionService $quickActions
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Optional Auth check
        /** @var \App\Models\AuthUser|null $user */
        $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'identity' => [
                        'name' => 'Tamu',
                        'call_sign' => 'Guest',
                        'avatar_url' => null,
                        'online_status' => 'siaga',
                        'is_available' => false
                    ],
                    'active_mandate' => [
                        'mandate_id' => 'guest',
                        'role' => 'publik',
                        'position' => 'TAMU NURISK',
                        'organization' => 'NU Peduli',
                        'location' => 'Jawa Tengah'
                    ],
                    'tasks' => [],
                    'quick_actions' => [
                        ['id' => 'qa_lapor', 'title' => 'Lapor Insiden', 'action_type' => 'ACTION_ASSESSMENT', 'badge_count' => 0]
                    ],
                    'statistics' => []
                ]
            ]);
        }

        $user->load(['profil', 'peran']);
        $role = $user->peran ? strtolower($user->peran->nama_peran) : 'relawan';
        
        // Identity
        $identity = [
            'name' => $user->profil->nama_lengkap ?? 'Anggota NU Peduli',
            'call_sign' => $user->profil->call_sign ?? 'Cakra-' . ($user->id_pengguna % 100),
            'avatar_url' => null,
            'online_status' => 'siaga',
            'is_available' => (bool) $user->is_available
        ];

        // Active Mandate
        $scopeType = $user->default_scope_type ?: 'pcnu';
        $activeMandate = [
            'mandate_id' => $user->id_pengguna,
            'role' => $role,
            'position' => strtoupper($role) . ' ' . strtoupper($scopeType),
            'organization' => 'NU Peduli ' . strtoupper($scopeType),
            'location' => 'Jawa Tengah'
        ];

        // Get live queues and actions from services
        $rawQueue = $this->decisionQueue->getQueue($user);
        $rawActions = $this->quickActions->getActions($user);

        // Map Queue to Mobile 'tasks' format
        $tasks = collect($rawQueue)->map(function ($item, $idx) {
            return [
                'id' => $item['id'] ?? 'task_' . $idx,
                'title' => $item['judul'] ?? '-',
                'category' => $item['kategori'] ?? 'General',
                'due_date' => $item['waktu'] ?? now()->toIso8601String(),
                'severity' => $item['severity'] ?? 'normal',
                'description' => $item['deskripsi'] ?? '',
                'action_route' => $item['tautan'] ?? null
            ];
        })->toArray();

        // Map Actions to Mobile 'quick_actions' format
        $quickActions = collect($rawActions)->map(function ($item, $idx) {
            $actionType = 'ACTION_UNKNOWN';
            // Map web action keys to mobile action enums
            switch($item['action']) {
                case 'approve-surat': $actionType = 'ACTION_APPROVAL'; break;
                case 'finalisasi-pleno': $actionType = 'ACTION_APPROVAL'; break;
                case 'buat-sitrep': $actionType = 'ACTION_ASSESSMENT'; break;
                case 'assign-personel': $actionType = 'ACTION_MAP'; break;
                case 'checkin': $actionType = 'ACTION_JOIN_MISSION'; break;
                case 'update-progres': $actionType = 'ACTION_UPLOAD_EVIDENCE'; break;
            }
            
            // Add some role-specific fallbacks to ensure mobile UI looks good if services are empty
            return [
                'id' => 'qa_' . $idx,
                'title' => $item['label'] ?? '-',
                'action_type' => $actionType,
                'badge_count' => 0 // Can be derived from task severity/counts if needed
            ];
        })->toArray();

        // If raw actions are empty from service (e.g. TRC role not explicitly handled in QuickActionService), fallback:
        if (empty($quickActions)) {
            if (strpos($role, 'trc') !== false) {
                $quickActions = [
                    ['id' => 'qa_assess', 'title' => 'Assessment', 'action_type' => 'ACTION_ASSESSMENT', 'badge_count' => 0],
                    ['id' => 'qa_map', 'title' => 'Peta COP', 'action_type' => 'ACTION_MAP', 'badge_count' => 0],
                    ['id' => 'qa_upload', 'title' => 'Upload Bukti', 'action_type' => 'ACTION_UPLOAD_EVIDENCE', 'badge_count' => 0]
                ];
            } else {
                $quickActions = [
                    ['id' => 'qa_join', 'title' => 'Join Misi', 'action_type' => 'ACTION_JOIN_MISSION', 'badge_count' => 0]
                ];
            }
        }

        // Determine statistics based on role (Fallback for now as there's no KPI service yet)
        $statistics = [];
        if ($role === 'pcnu' || $role === 'pwnu' || $role === 'super_admin') {
            $pendingLaporanQuery = \App\Models\LaporanKejadian::where('is_valid', 'menunggu');
            if ($role === 'pcnu' && $user->default_scope_id) {
                $pendingLaporanQuery->where('id_pcnu', $user->default_scope_id);
            }
            $pendingLaporanCount = $pendingLaporanQuery->count();

            // Tambahkan Quick Action Validasi Laporan
            array_unshift($quickActions, [
                'id' => 'qa_validasi_laporan',
                'title' => 'Validasi Laporan',
                'action_type' => 'ACTION_VALIDATE_LAPORAN',
                'badge_count' => $pendingLaporanCount,
            ]);

            $statistics = [
                ['label' => 'Antrean', 'value' => $pendingLaporanCount, 'key' => 'queue'],
                ['label' => 'Keputusan', 'value' => 3, 'key' => 'drafts'],
            ];
        } else if (strpos($role, 'trc') !== false) {
            $statistics = [
                ['label' => 'Misi Aktif', 'value' => count($rawQueue), 'key' => 'missions'],
                ['label' => 'Assessment', 'value' => 5, 'key' => 'assessments'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'identity' => $identity,
                'active_mandate' => $activeMandate,
                'statistics' => $statistics,
                'quick_actions' => $quickActions,
                'tasks' => $tasks,
                'organization' => [
                    'level' => strtoupper($scopeType),
                    'name' => 'NU Peduli Wilayah ' . strtoupper($scopeType),
                    'office_address' => 'Kantor NU Peduli Jawa Tengah'
                ],
                'resources' => [], // Removed mock
                'activities' => [], // Removed mock
                'settings_config' => [
                  'pin_configured' => true,
                  'biometric_enabled' => false,
                  'offline_mode_ready' => true
                ]
            ]
        ]);
    }
}
