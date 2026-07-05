<?php

namespace App\Console\Commands;

use App\Models\WeatherSnapshot;
use Illuminate\Console\Command;

class WeatherPruneCommand extends Command
{
    protected $signature = 'weather:prune
        {--days=7 : Delete snapshots older than this many days}
        {--force : Skip confirmation}';

    protected $description = 'Delete old weather snapshots';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = WeatherSnapshot::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info("No snapshots older than {$days} days to prune.");
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Delete {$count} old weather snapshots?")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        WeatherSnapshot::where('created_at', '<', $cutoff)->delete();
        $this->info("Deleted {$count} snapshots older than {$days} days.");

        return self::SUCCESS;
    }
}
