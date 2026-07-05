<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sync:prune-tombstones --days=90')
    ->daily()
    ->description('Purge tombstone sync lebih dari 90 hari');

Schedule::command('queue:prune-failed --hours=168')
    ->daily()
    ->description('Hapus failed jobs lebih dari 7 hari');

Schedule::command('nurisk:aggregate-metrics')
    ->dailyAt('01:00')
    ->description('Aggregate pilot metrics for previous day');

Schedule::command('weather:fetch --scope=all')
    ->everyThirtyMinutes()
    ->description('Fetch weather forecast for all PCNU territories');

Schedule::command('weather:prune --days=7 --force')
    ->dailyAt('03:00')
    ->description('Delete weather snapshots older than 7 days');

Schedule::command('media:cleanup-orphans --force')
    ->dailyAt('04:00')
    ->description('Purge orphaned media files');
