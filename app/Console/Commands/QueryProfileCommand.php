<?php

namespace App\Console\Commands;

use App\Models\AuthUser;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryProfileCommand extends Command
{
    protected $signature = 'query:profile
        {--requests=10 : Number of request iterations per endpoint}';

    protected $description = 'Profile database queries across key API endpoints';

    private array $results = [];

    public function handle(): int
    {
        $requestCount = max(1, (int) $this->option('requests'));

        $this->info('Database Query Profiler');
        $this->line('Iterations per endpoint: ' . $requestCount);
        $this->newLine();

        $user = AuthUser::find(1);
        if (!$user) {
            $this->error('User id=1 not found.');
            return self::FAILURE;
        }

        $token = $user->createToken('query-profile-token')->plainTextToken;

        $insidenUuid = DB::table('operasi_insiden')->value('uuid_insiden');
        if (!$insidenUuid) {
            $this->error('No incidents found.');
            return self::FAILURE;
        }

        $endpoints = [
            [
                'label' => 'GET /api/v1/sync/status',
                'method' => 'GET',
                'path' => '/api/v1/sync/status',
                'params' => [],
            ],
            [
                'label' => 'GET /api/v1/sync/metrics',
                'method' => 'GET',
                'path' => '/api/v1/sync/metrics',
                'params' => [],
            ],
            [
                'label' => 'GET /api/v1/assessment',
                'method' => 'GET',
                'path' => '/api/v1/assessment',
                'params' => ['uuid_insiden' => $insidenUuid],
            ],
            [
                'label' => 'GET /api/v1/penugasan',
                'method' => 'GET',
                'path' => '/api/v1/penugasan',
                'params' => ['uuid_insiden' => $insidenUuid],
            ],
            [
                'label' => 'POST /api/v1/sync',
                'method' => 'POST',
                'path' => '/api/v1/sync',
                'params' => [
                    'request_id' => Str::uuid()->toString(),
                    'device_uuid' => 'profiler-device-' . Str::random(6),
                    'cursors' => [],
                    'changes' => [],
                ],
            ],
        ];

        $capture = false;
        $collector = new \stdClass();
        $collector->queries = [];

        DB::listen(function ($query) use ($collector, &$capture) {
            if ($capture) {
                $collector->queries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ];
            }
        });

        foreach ($endpoints as $endpoint) {
            $this->line('Profiling ' . $endpoint['label'] . '...');

            $endpointQueries = [];

            for ($i = 0; $i < $requestCount; $i++) {
                if ($endpoint['method'] === 'POST') {
                    $params = [
                        'request_id' => Str::uuid()->toString(),
                        'device_uuid' => 'profiler-device-' . Str::random(6),
                        'cursors' => [],
                        'changes' => [],
                    ];
                } else {
                    $params = $endpoint['params'];
                }

                $collector->queries = [];
                $capture = true;

                $kernel = app(HttpKernel::class);
                $request = Request::create(
                    $endpoint['path'],
                    $endpoint['method'],
                    $params,
                    [],
                    [],
                    ['HTTP_ACCEPT' => 'application/json']
                );
                $request->headers->set('Authorization', 'Bearer ' . $token);
                $request->headers->set('Accept', 'application/json');

                $response = $kernel->handle($request);
                $kernel->terminate($request, $response);

                $capture = false;
                $endpointQueries = array_merge($endpointQueries, $collector->queries);
            }

            $this->processResults($endpoint['label'], $endpointQueries, $requestCount);
            $this->line('  -> ' . count($endpointQueries) . ' total queries across ' . $requestCount . ' requests');
        }

        $this->displaySummary();
        $this->displayTopDuplicates();
        $this->displayTopSlow();

        return self::SUCCESS;
    }

    private function processResults(string $label, array $queries, int $requestCount): void
    {
        $queryCount = count($queries);
        $totalTime = array_sum(array_column($queries, 'time'));
        $avgTime = $queryCount > 0 ? $totalTime / $queryCount : 0;
        $avgPerRequest = $requestCount > 0 ? round($queryCount / $requestCount, 1) : 0;

        $sqlCounts = [];
        foreach ($queries as $q) {
            $sql = $q['sql'];
            if (!isset($sqlCounts[$sql])) {
                $sqlCounts[$sql] = ['count' => 0, 'total_time' => 0, 'sql' => $sql];
            }
            $sqlCounts[$sql]['count']++;
            $sqlCounts[$sql]['total_time'] += $q['time'];
        }

        $duplicates = array_filter($sqlCounts, fn($s) => $s['count'] > $requestCount);
        $duplicateCount = count($duplicates);

        $slowQueries = array_filter($queries, fn($q) => $q['time'] > 50);
        $slowQueryCount = count($slowQueries);

        $nPlusOne = $this->detectNPlusOne($queries, $requestCount);

        usort($duplicates, fn($a, $b) => $b['count'] <=> $a['count']);
        $topDuplicates = array_slice(array_values($duplicates), 0, 5);

        $slowSorted = array_filter($queries, fn($q) => $q['time'] > 50);
        usort($slowSorted, fn($a, $b) => $b['time'] <=> $a['time']);
        $topSlow = array_slice(array_values($slowSorted), 0, 5);

        $this->results[$label] = [
            'query_count' => $queryCount,
            'avg_per_request' => $avgPerRequest,
            'avg_time_ms' => round($avgTime, 2),
            'total_time_ms' => round($totalTime, 2),
            'duplicate_count' => $duplicateCount,
            'slow_query_count' => $slowQueryCount,
            'n_plus_one_count' => count($nPlusOne),
            'top_duplicates' => $topDuplicates,
            'top_slow' => $topSlow,
            'n_plus_one' => $nPlusOne,
        ];
    }

    private function detectNPlusOne(array $queries, int $threshold): array
    {
        $normalized = [];
        foreach ($queries as $q) {
            $key = preg_replace('/\b\d+\b/', '?', $q['sql']);
            $key = preg_replace('/\?+/', '?', $key);
            if (!isset($normalized[$key])) {
                $normalized[$key] = ['sql' => $q['sql'], 'count' => 0];
            }
            $normalized[$key]['count']++;
        }

        $candidates = [];
        foreach ($normalized as $info) {
            if ($info['count'] > $threshold * 2 && preg_match('/^select\s/i', $info['sql'])) {
                $candidates[] = $info;
            }
        }

        usort($candidates, fn($a, $b) => $b['count'] <=> $a['count']);
        return array_slice($candidates, 0, 5);
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== QUERY PROFILE SUMMARY ===');
        $this->newLine();

        $headers = ['Endpoint', 'Total', 'Avg/Req', 'Avg(ms)', 'Total(ms)', 'Dups', 'Slow>50ms', 'N+1'];
        $rows = [];

        foreach ($this->results as $label => $r) {
            $rows[] = [
                $label,
                (string) $r['query_count'],
                (string) $r['avg_per_request'],
                (string) $r['avg_time_ms'],
                (string) $r['total_time_ms'],
                (string) $r['duplicate_count'],
                (string) $r['slow_query_count'],
                (string) $r['n_plus_one_count'],
            ];
        }

        $this->table($headers, $rows);
    }

    private function displayTopDuplicates(): void
    {
        $hasAny = false;
        foreach ($this->results as $r) {
            if (!empty($r['top_duplicates'])) { $hasAny = true; break; }
        }
        if (!$hasAny) { return; }

        $this->newLine();
        $this->info('--- TOP DUPLICATED QUERIES ---');
        $this->newLine();

        foreach ($this->results as $label => $r) {
            if (empty($r['top_duplicates'])) { continue; }
            $this->line($label . ':');
            foreach ($r['top_duplicates'] as $dup) {
                $this->line(sprintf(
                    '  x%d - %s (%.2fms)',
                    $dup['count'],
                    Str::limit($dup['sql'], 150),
                    $dup['total_time']
                ));
            }
            $this->newLine();
        }
    }

    private function displayTopSlow(): void
    {
        $hasAny = false;
        foreach ($this->results as $r) {
            if (!empty($r['top_slow'])) { $hasAny = true; break; }
        }
        if (!$hasAny) { return; }

        $this->newLine();
        $this->info('--- TOP SLOW QUERIES (>50ms) ---');
        $this->newLine();

        foreach ($this->results as $label => $r) {
            if (empty($r['top_slow'])) { continue; }
            $this->line($label . ':');
            foreach ($r['top_slow'] as $slow) {
                $this->line(sprintf(
                    '  %.2fms - %s',
                    $slow['time'],
                    Str::limit($slow['sql'], 150)
                ));
            }
            $this->newLine();
        }
    }
}
