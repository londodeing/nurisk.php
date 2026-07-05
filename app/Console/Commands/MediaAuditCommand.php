<?php

namespace App\Console\Commands;

use App\Services\Media\MediaAuditService;
use Illuminate\Console\Command;

class MediaAuditCommand extends Command
{
    protected $signature = 'media:audit
        {--json : Output as JSON}
        {--severity= : Filter by severity (HIGH,MEDIUM,LOW)}
        {--type= : Filter by type (STORAGE_PUT, STORAGE_URL, etc.)}
        {--limit=0 : Limit number of results shown}';

    protected $description = 'Audit all Storage:: usage across the codebase for Media Layer migration';

    public function handle(MediaAuditService $audit): int
    {
        $this->info('Scanning codebase for non-Media Layer upload/storage usage...');
        $this->newLine();

        $results = $audit->audit();
        $summary = $audit->getSummary();

        $filtered = $results;

        if ($severity = $this->option('severity')) {
            $filtered = array_filter($filtered, fn($r) => $r['severity'] === strtoupper($severity));
        }

        if ($type = $this->option('type')) {
            $filtered = array_filter($filtered, fn($r) => $r['type'] === strtoupper($type));
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $filtered = array_slice($filtered, 0, $limit);
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'summary' => $summary,
                'results' => array_values($filtered),
            ], JSON_PRETTY_PRINT));
            return 0;
        }

        $this->table(
            ['Severity', 'Type', 'File', 'Line', 'Code'],
            array_map(fn($r) => [
                $this->severityBadge($r['severity']),
                $r['type'],
                $r['file'],
                $r['line'],
                mb_substr($r['code'], 0, 100),
            ], $filtered)
        );

        $this->newLine();
        $this->line("  <options=bold>Audit Summary</>");
        $this->line("  Total violations: <options=bold>{$summary['total']}</>");
        $this->line("  🔴 HIGH:   {$summary['high']}");
        $this->line("  🟡 MEDIUM: {$summary['medium']}");
        $this->line("  🟢 LOW:    {$summary['low']}");
        $this->newLine();

        $this->line("  <options=bold>By Type</>");
        foreach ($summary['by_type'] as $type => $count) {
            $this->line("  $type: $count");
        }
        $this->newLine();

        $this->line("  <options=bold>By File (top 10)</>");
        arsort($summary['by_file']);
        $i = 0;
        foreach ($summary['by_file'] as $file => $count) {
            if ($i++ >= 10) break;
            $this->line("  $file: $count violations");
        }

        if ($summary['total'] === 0) {
            $this->info('  ✅ No violations found. All media operations use the Media Layer.');
        } else {
            $this->warn("  ⚠️  {$summary['high']} HIGH, {$summary['medium']} MEDIUM, {$summary['low']} LOW violations found.");
        }

        return $summary['total'] === 0 ? 0 : 1;
    }

    private function severityBadge(string $severity): string
    {
        return match ($severity) {
            'HIGH' => '<fg=red>HIGH</>',
            'MEDIUM' => '<fg=yellow>MEDIUM</>',
            'LOW' => '<fg=green>LOW</>',
            default => $severity,
        };
    }
}
