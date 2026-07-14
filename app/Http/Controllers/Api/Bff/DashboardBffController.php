<?php

namespace App\Http\Controllers\Api\Bff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Bff\BffContextService;
use App\Http\Controllers\Api\Bff\Widgets\SummaryCardWidget;
use App\Http\Controllers\Api\Bff\Widgets\ActionListWidget;
use App\Http\Controllers\Api\Bff\Widgets\DocumentQueueWidget;
use App\Http\Controllers\Api\Bff\Widgets\HeaderBannerWidget;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;

class DashboardBffController extends Controller
{
    protected BffContextService $context;

    public function __construct(BffContextService $context)
    {
        $this->context = $context;
    }

    public function index()
    {
        $widgets = [];
        $activeIncidentsCount = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])->count();
        $volunteersCount = AuthUser::whereHas('peran', function($q) { $q->where('nama_peran', 'relawan'); })->count();

        // 1. Header Banner if there's an active incident
        if ($activeIncidentsCount > 0) {
            $latestIncident = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])
                                            ->latest('dibuat_pada')->first();
            if ($latestIncident) {
                $widgets[] = HeaderBannerWidget::make('Peringatan: Ada ' . $activeIncidentsCount . ' insiden aktif!', 'danger')
                    ->setAction('on_tap', ['type' => 'navigate', 'target' => '/incident/' . $latestIncident->id_insiden])
                    ->toArray();
            }
        }

        // 2. Summary Cards
        $widgets[] = SummaryCardWidget::make('Insiden Aktif', (string)$activeIncidentsCount, 'fire', '#EF4444')
            ->setAction('on_tap', ['type' => 'navigate', 'target' => '/map'])
            ->toArray();
            
        if ($this->context->getActiveRole() !== 'guest') {
            $widgets[] = SummaryCardWidget::make('Relawan Terdaftar', (string)$volunteersCount, 'users', '#3B82F6')
                ->setAction('on_tap', ['type' => 'navigate', 'target' => '/resource'])
                ->toArray();
        }

        // 3. Document Queue (Only for users who can approve)
        if ($this->context->canApproveDocuments()) {
            // Real implementation would fetch from Governance Approvals
            $documents = [
                ['id' => 1, 'title' => 'Persetujuan SPK TRC', 'sla' => 'Merah'],
            ];

            $queueWidget = DocumentQueueWidget::make('Menunggu Persetujuan Anda', $documents)
                ->setAction('on_approve', ['type' => 'api_call', 'endpoint' => '/api/governance/process', 'method' => 'POST', 'payload' => ['action' => 'approve']])
                ->setAction('on_reject', ['type' => 'api_call', 'endpoint' => '/api/governance/process', 'method' => 'POST', 'payload' => ['action' => 'reject']]);

            $widgets[] = $queueWidget->toArray();
        }

        // 4. Actions List Menu
        $menuItems = [];
        $menuItems[] = ['id' => 'm1', 'label' => 'Lapor Kejadian', 'icon' => 'plus-circle', 'target' => '/p/report'];
        $menuItems[] = ['id' => 'm2', 'label' => 'Peta Operasional', 'icon' => 'map', 'target' => '/p/map'];
        
        if ($this->context->getActiveRole() === 'trc') {
             $menuItems[] = ['id' => 'm3', 'label' => 'Antrean Assessment', 'icon' => 'clipboard', 'target' => '/assessment/queue'];
        }

        $widgets[] = ActionListWidget::make('Menu Utama', $menuItems)->toArray();

        return response()->json([
            'status' => 'success',
            'version' => '1.0',
            'data' => [
                'screen_title' => 'Beranda Utama',
                'layout_type' => 'scrollable_column',
                'widgets' => $widgets,
                'bottom_nav' => [
                    ['id' => 'tab_home', 'label' => 'Beranda', 'icon' => 'home', 'target_endpoint' => '/api/bff/dashboard'],
                    ['id' => 'tab_map', 'label' => 'Peta', 'icon' => 'map', 'target_route' => '/map']
                ]
            ]
        ], 200);
    }
}
