<?php

namespace App\Services;

class OperatorDashboardService
{
    protected $workQueue;
    protected $dataQuality;
    protected $activityFeed;

    public function __construct(
        OperatorWorkQueueService $workQueue,
        OperatorDataQualityService $dataQuality,
        OperatorActivityFeedService $activityFeed
    ) {
        $this->workQueue = $workQueue;
        $this->dataQuality = $dataQuality;
        $this->activityFeed = $activityFeed;
    }

    public function getShiftContext()
    {
        return [
            'operator_name' => auth()->user()->profil?->nama_lengkap ?? auth()->user()->no_hp ?? 'Operator',
            'posko_name' => 'Posko Alpha',
            'shift' => '08:00 - 16:00',
            'server_time' => now()->format('H:i:s'),
            'sync_status' => 'Synced',
        ];
    }

    public function getSubmissionQueue()
    {
        return [
            ['number' => 'SIT-002', 'type' => 'Sitrep Siap Submit', 'created' => 'Hari Ini', 'status' => 'Menunggu Review'],
            ['number' => 'LOG-090', 'type' => 'Permintaan Logistik', 'created' => 'Kemarin', 'status' => 'Menunggu Review'],
        ];
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'shift' => $this->getShiftContext(),
            'pending_queue' => $this->workQueue->getPendingQueue(),
            'quality_queue' => $this->dataQuality->getQualityQueue(),
            'submission_queue' => $this->getSubmissionQueue(),
            'activity_feed' => $this->activityFeed->getRecentActivities(),
        ];
    }
}
