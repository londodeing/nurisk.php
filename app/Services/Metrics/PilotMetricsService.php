<?php

namespace App\Services\Metrics;

use App\Models\OperasiMetricsDaily;
use App\Models\SyncAuditLog;
use App\Models\SyncConflict;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;

class PilotMetricsService
{
    public function aggregateForDate(string $date): OperasiMetricsDaily
    {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';

        $syncSuccess = SyncAuditLog::where('status', 'success')
            ->whereBetween('dibuat_pada', [$start, $end])
            ->count();

        $syncFailed = SyncAuditLog::where('status', 'failed')
            ->whereBetween('dibuat_pada', [$start, $end])
            ->count();

        $avgSyncMs = SyncAuditLog::whereBetween('dibuat_pada', [$start, $end])
            ->avg('duration_ms') ?? 0;

        $bootstrapCount = SyncAuditLog::where('status', 'bootstrap')
            ->whereBetween('dibuat_pada', [$start, $end])
            ->count();

        $avgBootstrapMs = SyncAuditLog::where('status', 'bootstrap')
            ->whereBetween('dibuat_pada', [$start, $end])
            ->avg('duration_ms') ?? 0;

        $conflictCount = SyncConflict::whereBetween('dibuat_pada', [$start, $end])
            ->count();

        $loginCount = PersonalAccessToken::whereBetween('created_at', [$start, $end])
            ->count();

        $queueBacklog = DB::table('jobs')
            ->whereNull('reserved_at')
            ->where('available_at', '<=', now()->timestamp)
            ->count();

        $pdfFailed = DB::table('failed_jobs')
            ->where('queue', 'pdf-generation')
            ->whereBetween('failed_at', [$start, $end])
            ->count();

        $pdfSuccess = DB::table('job_batches')
            ->where('name', 'like', '%pdf%')
            ->whereBetween('created_at', [strtotime($start), strtotime($end)])
            ->sum('total_jobs');

        return OperasiMetricsDaily::updateOrCreate(
            ['tanggal' => $date],
            [
                'login_count' => $loginCount,
                'sync_success' => $syncSuccess,
                'sync_failed' => $syncFailed,
                'sync_conflict_count' => $conflictCount,
                'bootstrap_count' => $bootstrapCount,
                'pdf_success' => $pdfSuccess,
                'pdf_failed' => $pdfFailed,
                'queue_backlog_max' => $queueBacklog,
                'avg_sync_duration_ms' => round($avgSyncMs, 2),
                'avg_bootstrap_duration_ms' => round($avgBootstrapMs, 2),
            ]
        );
    }

    public function getDailyRange(string $from, string $to): array
    {
        return OperasiMetricsDaily::whereBetween('tanggal', [$from, $to])
            ->orderBy('tanggal')
            ->get()
            ->toArray();
    }

    public function getLatest(): ?OperasiMetricsDaily
    {
        return OperasiMetricsDaily::orderBy('tanggal', 'desc')->first();
    }

    public function getSummary(): array
    {
        $latest = $this->getLatest();

        $totals = OperasiMetricsDaily::select(
            DB::raw('SUM(sync_success) as total_sync_success'),
            DB::raw('SUM(sync_failed) as total_sync_failed'),
            DB::raw('SUM(sync_conflict_count) as total_conflicts'),
            DB::raw('SUM(pdf_success) as total_pdf_success'),
            DB::raw('SUM(pdf_failed) as total_pdf_failed'),
            DB::raw('ROUND(AVG(avg_sync_duration_ms), 2) as overall_avg_sync_ms'),
            DB::raw('COUNT(*) as days_tracked'),
        )->first();

        return [
            'latest_date' => $latest?->tanggal?->toDateString(),
            'days_tracked' => (int) ($totals?->days_tracked ?? 0),
            'total_sync_success' => (int) ($totals?->total_sync_success ?? 0),
            'total_sync_failed' => (int) ($totals?->total_sync_failed ?? 0),
            'sync_success_rate' => $this->rate($totals?->total_sync_success, $totals?->total_sync_success + $totals?->total_sync_failed),
            'total_conflicts' => (int) ($totals?->total_conflicts ?? 0),
            'total_pdf_success' => (int) ($totals?->total_pdf_success ?? 0),
            'total_pdf_failed' => (int) ($totals?->total_pdf_failed ?? 0),
            'overall_avg_sync_ms' => (float) ($totals?->overall_avg_sync_ms ?? 0),
        ];
    }

    private function rate(int $numerator, int $denominator): string
    {
        if ($denominator === 0) return '0%';
        return round(($numerator / $denominator) * 100, 2) . '%';
    }
}
