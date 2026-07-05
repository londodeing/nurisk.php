<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncAuditLog;
use App\Models\SyncConflict;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'disk' => $this->checkDisk(),
            'migration' => $this->checkMigration(),
            'sync' => $this->checkSync(),
        ];

        $allHealthy = collect($checks)->every(fn ($v) => is_string($v)
            ? !in_array($v, ['fail'], true)
            : !in_array($v['status'] ?? '', ['fail', 'degraded'], true)
        );

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'version' => config('app.version', '1.0.0'),
            'git_sha' => $this->getGitSha(),
            'time' => now()->toIso8601String(),
            ...$checks,
        ], $allHealthy ? 200 : 503);
    }

    private function checkSync(): array
    {
        try {
            if (!Schema::hasTable('sync_audit_logs')) {
                return ['status' => 'unavailable'];
            }

            $lastSync = SyncAuditLog::orderBy('dibuat_pada', 'desc')->first();
            $totalSync = SyncAuditLog::count();
            $conflictsCount = SyncConflict::count();
            $avgDuration = SyncAuditLog::avg('duration_ms') ?? 0;

            return [
                'status' => 'ok',
                'last_sync_at' => $lastSync?->dibuat_pada?->toIso8601String(),
                'sync_duration_ms' => round($avgDuration, 2),
                'entities_synced' => (int) SyncAuditLog::sum('entities_synced'),
                'conflicts_count' => $conflictsCount,
                'total_sync_requests' => $totalSync,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'error' => $e->getMessage()];
        }
    }

    private function getGitSha(): string
    {
        $headFile = base_path('.git/HEAD');
        if (!file_exists($headFile)) {
            return 'unknown';
        }
        $head = trim(file_get_contents($headFile));
        if (str_starts_with($head, 'ref: ')) {
            $refPath = base_path('.git/' . substr($head, 5));
            return file_exists($refPath) ? trim(file_get_contents($refPath)) : 'unknown';
        }
        return $head;
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return 'ok';
        } catch (\Throwable) {
            return 'fail';
        }
    }

    private function checkCache(): string
    {
        try {
            Cache::store(config('cache.default'))->set('health_check', true, 10);
            Cache::store(config('cache.default'))->pull('health_check');
            return 'ok';
        } catch (\Throwable) {
            return 'fail';
        }
    }

    private function checkStorage(): string
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));
            $disk->put('health_check.txt', 'ok');
            $disk->delete('health_check.txt');
            return 'ok';
        } catch (\Throwable) {
            return 'fail';
        }
    }

    private function checkQueue(): array
    {
        try {
            if (!Schema::hasTable('jobs')) {
                return ['status' => 'ok', 'pending' => 0, 'failed' => 0];
            }
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $status = 'ok';
            if ($pending > 100) {
                $status = 'warn';
            }
            if ($failed > 10) {
                $status = 'degraded';
            }
            return compact('status', 'pending', 'failed');
        } catch (\Throwable) {
            return ['status' => 'fail', 'pending' => -1, 'failed' => -1];
        }
    }

    private function checkDisk(): array
    {
        try {
            $diskFree = disk_free_space(storage_path());
            $diskTotal = disk_total_space(storage_path());
            $usedPct = $diskTotal > 0 ? round((1 - $diskFree / $diskTotal) * 100) : 0;
            $status = $usedPct > 85 ? 'degraded' : ($usedPct > 70 ? 'warn' : 'ok');
            return ['status' => $status, 'used_pct' => $usedPct];
        } catch (\Throwable) {
            return ['status' => 'fail', 'used_pct' => -1];
        }
    }

    private function checkMigration(): string
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return 'unavailable';
            }

            $total = DB::table('migrations')->count();
            if ($total === 0) {
                return 'unavailable';
            }

            $latestBatch = DB::table('migrations')->max('batch');
            $pendingInLastBatch = DB::table('migrations')
                ->where('batch', $latestBatch)
                ->count();

            if ($pendingInLastBatch > 0 && $total > 0) {
                $migrationFiles = glob(database_path('migrations/*.php'));
                $totalMigrations = count($migrationFiles);
                $appliedMigrations = $total;

                if ($appliedMigrations < $totalMigrations) {
                    return 'pending';
                }
            }

            return 'current';
        } catch (\Throwable) {
            return 'fail';
        }
    }
}
