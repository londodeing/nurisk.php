<?php

namespace App\Services;

class ClusterCoordinatorDashboardService
{
    protected $decisionQueue;
    protected $gapAnalysis;
    protected $coverage;

    public function __construct(
        ClusterCoordinatorDecisionQueueService $decisionQueue,
        ClusterCoordinatorGapAnalysisService $gapAnalysis,
        ClusterCoordinatorCoverageService $coverage
    ) {
        $this->decisionQueue = $decisionQueue;
        $this->gapAnalysis = $gapAnalysis;
        $this->coverage = $coverage;
    }

    public function getStats()
    {
        return [
            'total_kebutuhan' => 125,
            'posko_butuh_bantuan' => 3,
            'area_unserved' => count($this->coverage->getUnservedAreas()),
            'permintaan_menunggu' => 5,
        ];
    }

    public function getEscalations()
    {
        return [
            ['to' => 'PCNU', 'issue' => 'Permintaan Helikopter Evakuasi', 'status' => 'Pending', 'badge' => 'warning text-dark'],
            ['to' => 'Komandan Posko', 'issue' => 'Izin Bongkar Cadangan Beras', 'status' => 'Approved', 'badge' => 'success'],
        ];
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'stats' => $this->getStats(),
            'decision_queue' => $this->decisionQueue->getQueue(),
            'gap_matrix' => $this->gapAnalysis->getMatrix(),
            'unserved_area' => $this->coverage->getUnservedAreas(),
            'redistribution' => $this->gapAnalysis->getRedistribution(),
            'escalations' => $this->getEscalations(),
        ];
    }
}
