<?php

namespace App\Console\Commands;

use App\Services\Weather\WeatherForecastService;
use Illuminate\Console\Command;

class WeatherFetchCommand extends Command
{
    protected $signature = 'weather:fetch
        {--scope=all : Territory scope: all, pwnu, pcnu:{id}}
        {--pcnu= : Specific PCNU ID}';

    protected $description = 'Fetch weather forecast for all territories and persist to database';

    public function handle(WeatherForecastService $forecastService): int
    {
        $scope = $this->option('scope');
        $pcnuId = $this->option('pcnu');

        if ($pcnuId) {
            $territoryCode = 'pcnu:' . $pcnuId;
            $this->info("Fetching weather for {$territoryCode}...");
            $snapshot = $forecastService->refreshTerritory($territoryCode);
            if ($snapshot) {
                $this->info("OK: {$territoryCode} — cached at {$snapshot->cached_at}");
                return self::SUCCESS;
            }
            $this->error("Failed to fetch weather for {$territoryCode}");
            return self::FAILURE;
        }

        if ($scope === 'all' || $scope === 'pwnu') {
            $this->info('Fetching weather for all PCNU territories...');
            $results = $forecastService->refreshAllPcnu();

            $ok = count(array_filter($results, fn($r) => $r === 'ok'));
            $failed = count(array_filter($results, fn($r) => $r !== 'ok'));

            $this->info("Done. {$ok} territories OK, {$failed} failed.");

            foreach ($results as $code => $status) {
                if ($status !== 'ok') {
                    $this->warn("  {$code}: {$status}");
                }
            }

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        }

        if (str_starts_with($scope, 'pcnu:')) {
            $territoryCode = $scope;
        } elseif ($this->option('pcnu')) {
            $territoryCode = 'pcnu:' . $this->option('pcnu');
        } else {
            $this->error('Specify --scope=pcnu:{id} or --pcnu={id}');
            return self::FAILURE;
        }

        $this->info("Fetching weather for {$territoryCode}...");
        $snapshot = $forecastService->refreshTerritory($territoryCode);

        if ($snapshot) {
            $this->info("OK: {$territoryCode} — cached at {$snapshot->cached_at}");
            return self::SUCCESS;
        }

        $this->error("Failed to fetch weather for {$territoryCode}");
        return self::FAILURE;
    }
}
