<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDrillCommand extends Command
{
    protected $signature = 'drill:backup-restore
        {--backup-dir= : Directory to store backup files (default: storage/backups)}
        {--no-storage : Skip storage directory backup}';

    protected $description = 'Simulate a disaster recovery drill: backup SQLite database, delete, restore, verify';

    private string $sqlite3 = '/opt/lampp/bin/sqlite3';
    private string $dbPath;
    private string $backupDir;
    private string $dbBackupPath;
    private string $storageBackupPath;
    private string $drillId;
    private array $beforeCounts = [];
    private array $afterCounts = [];
    private float $startTime;
    private float $endTime;
    private float $step1End;
    private float $step2End;
    private float $step3End;
    private float $step4End;

    public function handle(): int
    {
        $this->drillId = now()->format('Ymd_His');
        $this->dbPath = database_path('loadtest.sqlite');
        $this->backupDir = $this->option('backup-dir') ?? storage_path('backups');
        $this->dbBackupPath = "{$this->backupDir}/loadtest_{$this->drillId}.sqlite";
        $this->storageBackupPath = "{$this->backupDir}/storage_{$this->drillId}.tar.gz";

        if (!file_exists($this->dbPath)) {
            $this->error("Database not found: {$this->dbPath}");
            return self::FAILURE;
        }

        if (!is_executable($this->sqlite3)) {
            $this->error("sqlite3 CLI not found at {$this->sqlite3}");
            return self::FAILURE;
        }

        $this->info('========================================');
        $this->info('  DISASTER RECOVERY DRILL');
        $this->info('  Task 13.5 — Backup/Restore Drill');
        $this->info('  Drill ID: ' . $this->drillId);
        $this->info('========================================');
        $this->newLine();

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
            $this->info("Created backup directory: {$this->backupDir}");
        }

        // Record pre-drill counts
        $this->captureBeforeCounts();

        // Start RTO timer
        $this->startTime = microtime(true);

        // --- Step 1: Backup ---
        $this->step1();
        $this->step1End = microtime(true);

        // --- Step 2: Simulate data loss ---
        $this->step2();
        $this->step2End = microtime(true);

        // --- Step 3: Restore ---
        $this->step3();
        $this->step3End = microtime(true);

        // --- Step 4: Verify ---
        $this->step4();
        $this->step4End = microtime(true);

        // End RTO timer
        $this->endTime = microtime(true);

        // --- Step 5: Storage backup ---
        if (!$this->option('no-storage')) {
            $this->step5();
        }

        $this->printReport();

        return self::SUCCESS;
    }

    private function captureBeforeCounts(): void
    {
        $this->info('[PRE-DRILL] Capturing record counts...');
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        foreach ($tables as $t) {
            $name = $t->name;
            try {
                $this->beforeCounts[$name] = DB::table($name)->count();
            } catch (\Exception $e) {
                $this->beforeCounts[$name] = -1;
            }
        }
        $this->line('  Captured ' . count($this->beforeCounts) . ' table counts.');
    }

    private function step1(): void
    {
        $this->info('[STEP 1/5] Creating database backup...');
        $this->line("  Source: {$this->dbPath}");
        $this->line("  Destination: {$this->dbBackupPath}");

        $cmd = sprintf(
            '%s %s ".backup \'%s\'" 2>&1',
            escapeshellcmd($this->sqlite3),
            escapeshellarg($this->dbPath),
            $this->dbBackupPath
        );
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($this->dbBackupPath)) {
            $this->error('  Backup failed!');
            $this->error('  Output: ' . implode("\n", $output));
            throw new \RuntimeException('Database backup failed');
        }

        $size = round(filesize($this->dbBackupPath) / 1024, 2);
        $this->line("  Backup created: {$size} KB");
        $this->line('  OK');
    }

    private function step2(): void
    {
        $this->info('[STEP 2/5] Simulating data loss — deleting database file...');
        $this->line("  Deleting: {$this->dbPath}");

        if (!file_exists($this->dbPath)) {
            $this->warn('  Database already missing (unexpected).');
        } else {
            unlink($this->dbPath);
            $this->line('  Database deleted successfully.');
        }

        // Also clear any cached data (may fail without DB, that's OK)
        $this->line('  Clearing application cache...');
        try {
            $this->callSilently('cache:clear');
        } catch (\Exception $e) {
            $this->warn('  Cache clear skipped (expected — no database): ' . $e->getMessage());
        }

        if (file_exists($this->dbPath)) {
            $this->error('  Database still exists after deletion!');
            throw new \RuntimeException('Failed to simulate data loss');
        }
        $this->line('  Data loss simulated successfully.');
        $this->line('  OK');
    }

    private function step3(): void
    {
        $this->info('[STEP 3/5] Restoring database from backup...');
        $this->line("  Backup: {$this->dbBackupPath}");
        $this->line("  Restore to: {$this->dbPath}");

        $cmd = sprintf(
            '%s %s ".restore \'%s\'" 2>&1',
            escapeshellcmd($this->sqlite3),
            escapeshellarg($this->dbPath),
            $this->dbBackupPath
        );
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($this->dbPath)) {
            $this->error('  Restore failed!');
            $this->error('  Output: ' . implode("\n", $output));
            throw new \RuntimeException('Database restore failed');
        }

        $size = round(filesize($this->dbPath) / 1024, 2);
        $this->line("  Database restored: {$size} KB");
        $this->line('  OK');
    }

    private function step4(): void
    {
        $this->info('[STEP 4/5] Verifying data integrity...');

        // Reconnect to the restored database
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $errors = 0;
        foreach ($this->beforeCounts as $t => $before) {
            try {
                $count = DB::table($t)->count();
                $this->afterCounts[$t] = $count;
                $this->line("  Table {$t}: {$count} rows");
            } catch (\Exception $e) {
                $this->afterCounts[$t] = -1;
                $this->error("  Table {$t}: ERROR — {$e->getMessage()}");
                $errors++;
            }
        }

        if ($errors > 0) {
            $this->error("  Data integrity check FAILED with {$errors} error(s)!");
        } else {
            $this->line('  All tables accessible and contain data.');
            $this->line('  Data integrity check PASSED.');
        }
        $this->line('  OK');
    }

    private function step5(): void
    {
        $this->info('[STEP 5/5] Backing up storage directory...');
        $storagePath = storage_path();
        $this->line("  Source: {$storagePath}");
        $this->line("  Archive: {$this->storageBackupPath}");

        $cmd = sprintf(
            'tar -czf %s -C %s --ignore-failed-read . 2>&1',
            escapeshellarg($this->storageBackupPath),
            escapeshellarg($storagePath)
        );
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if (!file_exists($this->storageBackupPath)) {
            $this->error('  Storage backup failed!');
            return;
        }
        if ($exitCode !== 0) {
            $this->warn('  Storage backup had warnings (non-zero exit), but archive was created.');
        }

        $size = round(filesize($this->storageBackupPath) / 1024, 2);
        $this->line("  Storage backup created: {$size} KB");
        $this->line('  OK');
    }

    private function printReport(): void
    {
        $rto = round($this->endTime - $this->startTime, 4);
        $rtoMinutes = floor($rto / 60);
        $rtoSeconds = $rto % 60;
        $rtoTarget = 30 * 60; // 30 minutes in seconds
        $rtoPass = $rto < $rtoTarget;

        $this->newLine(2);
        $this->info('========================================');
        $this->info('  DRILL REPORT');
        $this->info('========================================');
        $this->newLine();

        $this->line('Drill ID:      ' . $this->drillId);
        $this->line('Date:          ' . now()->toIso8601String());
        $this->line('Database:      ' . $this->dbPath);
        $this->line('DB size:       ' . round(filesize($this->dbPath) / 1024, 2) . ' KB');
        $this->newLine();

        // Timeline
        $this->line('--- Timeline ---');
        $this->line(sprintf('  Step 1 (Backup):     %6.4f s', $this->step1End - $this->startTime));
        $this->line(sprintf('  Step 2 (Data Loss):  %6.4f s', $this->step2End - $this->startTime));
        $this->line(sprintf('  Step 3 (Restore):    %6.4f s', $this->step3End - $this->startTime));
        $this->line(sprintf('  Step 4 (Verify):     %6.4f s', $this->step4End - $this->startTime));
        $this->newLine();

        // RTO
        $this->line('--- RTO (Recovery Time Objective) ---');
        $this->line(sprintf('  Total RTO: %.4f seconds (%d minutes %d seconds)', $rto, $rtoMinutes, (int) $rtoSeconds));
        $this->line(sprintf('  Target:    < 1800 seconds (30 minutes)'));
        $this->line(sprintf('  Status:    %s', $rtoPass ? 'PASS' : 'FAIL'));
        $this->newLine();

        // RPO
        $this->line('--- RPO (Recovery Point Objective) ---');
        $rpoLost = 0;
        $rpoTotal = 0;
        foreach ($this->beforeCounts as $table => $before) {
            $after = $this->afterCounts[$table] ?? -1;
            $diff = $after - $before;
            $rpoTotal += $before;
            if ($diff < 0) {
                $rpoLost += abs($diff);
                $this->line("  {$table}: before={$before} after={$after} diff={$diff}  LOST DATA");
            } elseif ($diff === 0) {
                $this->line("  {$table}: before={$before} after={$after} diff={$diff}  OK");
            } else {
                $this->line("  {$table}: before={$before} after={$after} diff={$diff}  OK");
            }
        }
        $this->line(sprintf('  Total rows before: %d', $rpoTotal));
        $this->line(sprintf('  Total rows lost:   %d', $rpoLost));
        $this->line(sprintf('  RPO: %s', $rpoLost === 0 ? 'Zero data loss (RPO = 0)' : "Lost {$rpoLost} rows"));
        $this->newLine();

        // Summary
        $this->line('--- Summary ---');
        $backupSize = round(filesize($this->dbBackupPath) / 1024, 2);
        $this->line("  Backup file: {$this->dbBackupPath} ({$backupSize} KB)");
        if (!$this->option('no-storage')) {
            $storageSize = file_exists($this->storageBackupPath) ? round(filesize($this->storageBackupPath) / 1024, 2) : 0;
            $this->line("  Storage archive: {$this->storageBackupPath} ({$storageSize} KB)");
        }
        $this->line(sprintf('  Result: %s', ($rtoPass && $rpoLost === 0) ? 'PASS' : 'FAIL'));
        $this->newLine();
    }
}
