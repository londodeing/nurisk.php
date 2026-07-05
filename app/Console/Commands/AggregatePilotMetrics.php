<?php

namespace App\Console\Commands;

use App\Services\Metrics\PilotMetricsService;
use Illuminate\Console\Command;

class AggregatePilotMetrics extends Command
{
    protected $signature = 'nurisk:aggregate-metrics {--date= : Specific date (Y-m-d) to aggregate} {--range= : Date range (Y-m-d..Y-m-d)}';

    protected $description = 'Aggregate daily pilot metrics from operational logs';

    public function handle(PilotMetricsService $metrics): int
    {
        $dates = [];

        if ($range = $this->option('range')) {
            [$from, $to] = explode('..', $range);
            $current = \Carbon\Carbon::parse($from);
            $end = \Carbon\Carbon::parse($to);
            while ($current->lte($end)) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        } elseif ($date = $this->option('date')) {
            $dates[] = $date;
        } else {
            $dates[] = now()->yesterday()->format('Y-m-d');
        }

        $bar = $this->output->createProgressBar(count($dates));
        $bar->start();

        foreach ($dates as $d) {
            $record = $metrics->aggregateForDate($d);
            $this->newLine();
            $this->info("{$d}: sync={$record->sync_success}+{$record->sync_failed} conflicts={$record->sync_conflict_count} pdf={$record->pdf_success}+{$record->pdf_failed} backlog={$record->queue_backlog_max} sync_avg={$record->avg_sync_duration_ms}ms");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done. Aggregated ' . count($dates) . ' day(s).');

        return Command::SUCCESS;
    }
}
