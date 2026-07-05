# RFC-004: Jurnal Retention Policy

**Status:** DRAFT (Under Review)
**Author:** Technical Lead
**Date:** 2026-06-18

## 1. Problem Statement

Tabel `operasi_jurnal` berfungsi sebagai audit log untuk semua event dalam modul governance (pleno, eskalasi, surat). Saat ini:

- Tidak ada mekanisme purging — data tumbuh monoton
- Tidak ada partisi atau archiving
- Query jurnal semakin lambat seiring volume data
- Tidak ada batas retensi yang jelas

## 2. Volume Projection

### 2.1. Baseline (from production-equivalent load test)

| Metrik                | Nilai              | Sumber                       |
| --------------------- | ------------------ | ---------------------------- |
| Rata-rata event/hari  | ~250               | Berdasarkan aktivitas 10 insiden/hari × 25 event/insiden |
| Rata-rata row size    | ~350 bytes         | id_jurnal(8) + id_insiden(8) + id_pengguna(8) + kategori(20) + judul(100) + deskripsi(200) + referensi(4+2) + waktu(5) |
| Event per insiden     | ~25                | Pleno(5) + Keputusan(5) + Peserta(5) + Eskalasi(3) + Surat(7) |
| Growth per day        | ~87.5 KB           | 250 × 350 bytes              |
| Growth per month      | ~2.6 MB            | 30 × 87.5 KB                 |

### 2.2. 1-Year Projection

| Bulan | Total Baris | Size (MB) | Keterangan                            |
| ----- | ----------- | --------- | ------------------------------------- |
| M1    | 7,500       | 2.6       | Initial, low activity                 |
| M3    | 22,500      | 7.9       | Governance module fully active        |
| M6    | 45,000      | 15.8      | Middle of operational year            |
| M9    | 67,500      | 23.7      | Seasonal peak (bencana alam)          |
| M12   | 91,250      | 32.0      | End of first year                     |

### 2.3. 3-Year Projection

| Tahun | Total Baris | Size (MB) | Query Performance Impact |
| ----- | ----------- | --------- | ------------------------ |
| 1     | 91,250      | 32        | Negligible (dengan index) |
| 2     | 182,500     | 64        | Moderate; archive needed  |
| 3     | 273,750     | 96        | Significant; purge wajib  |

### 2.4. Worst Case (Bencana Massal — 5× normal activity)

| Periode    | Baris      | Size (MB) |
| ---------- | ---------- | --------- |
| 1 bulan    | 37,500     | 13        |
| 6 bulan    | 225,000    | 79        |
| 1 tahun    | 456,250    | 160       |
| 3 tahun    | 1,368,750  | 479       |

## 3. Archive Cadence

### 3.1. Strategy: Time-Based Partitioning

Gunakan pendekatan **monthly archive** dengan tabel terpisah:

```
operasi_jurnal (active)          → current month + last 2 months
operasi_jurnal_archive            → older data (monthly partitions)
```

### 3.2. Archive Schedule

| Frekuensi  | Action                              | Trigger              |
| ---------- | ----------------------------------- | -------------------- |
| Daily      | Copy rows > 90 hari ke archive      | Cron: 03:00 WIB     |
| Weekly     | Verifikasi integritas archive       | Cron: Minggu 04:00  |
| Monthly    | Rotasi partisi bulanan              | Cron: 1st 05:00     |
| Quarterly  | Full consistency check              | Manual + laporan    |

### 3.3. Archive Table Schema

```sql
CREATE TABLE operasi_jurnal_archive (
    id_jurnal BIGINT UNSIGNED NOT NULL,
    id_insiden BIGINT UNSIGNED NOT NULL,
    id_pengguna BIGINT UNSIGNED NOT NULL,
    kategori_event VARCHAR(50) NOT NULL,
    judul_event VARCHAR(255) NOT NULL,
    deskripsi_event TEXT,
    id_referensi BIGINT UNSIGNED,
    tabel_referensi VARCHAR(50),
    waktu_event TIMESTAMP NOT NULL,
    diarsipkan_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_jurnal, waktu_event),
    INDEX idx_archive_insiden (id_insiden),
    INDEX idx_archive_waktu (waktu_event),
    INDEX idx_archive_kategori (kategori_event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
PARTITION BY RANGE (TO_DAYS(waktu_event)) (
    PARTITION p_2024_q4 VALUES LESS THAN (TO_DAYS('2025-01-01')),
    PARTITION p_2025_q1 VALUES LESS THAN (TO_DAYS('2025-04-01')),
    PARTITION p_2025_q2 VALUES LESS THAN (TO_DAYS('2025-07-01')),
    PARTITION p_2025_q3 VALUES LESS THAN (TO_DAYS('2025-10-01')),
    PARTITION p_2025_q4 VALUES LESS THAN (TO_DAYS('2026-01-01')),
    PARTITION p_2026_q1 VALUES LESS THAN (TO_DAYS('2026-04-01')),
    PARTITION p_2026_q2 VALUES LESS THAN (TO_DAYS('2026-07-01')),
    PARTITION p_2026_q3 VALUES LESS THAN (TO_DAYS('2026-10-01')),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### 3.4. Archive Procedure (Pseudocode)

```php
class ArchiveJurnalJob implements ShouldQueue
{
    public function handle(): void
    {
        $cutoff = now()->subDays(90);

        DB::transaction(function () use ($cutoff) {
            // 1. Copy to archive
            DB::insert("
                INSERT INTO operasi_jurnal_archive
                    (id_jurnal, id_insiden, id_pengguna, kategori_event,
                     judul_event, deskripsi_event, id_referensi,
                     tabel_referensi, waktu_event, diarsipkan_pada)
                SELECT j.*, NOW()
                FROM operasi_jurnal j
                WHERE j.waktu_event < ?
                AND NOT EXISTS (
                    SELECT 1 FROM operasi_jurnal_archive a
                    WHERE a.id_jurnal = j.id_jurnal
                )
            ", [$cutoff]);

            // 2. Verifikasi count
            $inserted = DB::affectingStatement();
            
            // 3. Delete from active
            DB::delete("
                DELETE FROM operasi_jurnal
                WHERE waktu_event < ?
                ORDER BY waktu_event ASC
                LIMIT 50000
            ", [$cutoff]);
        });
        
        Log::info('Jurnal archived', [
            'cutoff' => $cutoff,
            'rows_moved' => $inserted,
        ]);
    }
}
```

## 4. Purge Cadence

### 4.1. Retention Periods

| Data Category       | Active Table | Archive Table | Total Retention |
| ------------------- | ------------ | ------------- | --------------- |
| Active incidents    | 90 hari      | 3 tahun       | 3+ tahun        |
| Closed incidents    | 30 hari      | 3 tahun       | 3+ tahun        |
| Audit trail         | 90 hari      | 5 tahun       | 5 tahun         |

### 4.2. Purge Rules

| Condition                                     | Action              | Exception                          |
| --------------------------------------------- | ------------------- | ---------------------------------- |
| Jurnal insiden aktif > 90 hari                | Pindah ke archive   | —                                  |
| Jurnal insiden ditutup > 30 hari              | Pindah ke archive   | Ada investigasi berjalan            |
| Archive > 5 tahun                             | Hapus permanen      | Hold legal (flag `legal_hold`)     |
| Jurnal terkait surat final > 3 tahun          | Hapus permanen      | Surat masih aktif                   |

### 4.3. Purge Job

```php
class PurgeJurnalArchiveJob implements ShouldQueue
{
    public function handle(): void
    {
        // Soft purge: 5 tahun
        $cutoffLegal = now()->subYears(5);
        // Hard purge: 6 tahun (legal hold override)
        $cutoffHard = now()->subYears(6);

        DB::delete("
            DELETE FROM operasi_jurnal_archive
            WHERE waktu_event < ?
            AND id_jurnal NOT IN (
                SELECT id_referensi FROM operasi_jurnal_archive
                WHERE tabel_referensi = 'legal_hold'
            )
        ", [$cutoffLegal]);
    }
}
```

## 5. Recovery Procedure

### 5.1. Point-in-Time Recovery

Untuk mengembalikan data jurnal dari archive ke active table:

```php
class RecoveryJurnalJob implements ShouldQueue
{
    public function __construct(
        public readonly int $idInsiden,
        public readonly string $reason,
    ) {}

    public function handle(): void
    {
        $rows = DB::select("
            SELECT * FROM operasi_jurnal_archive
            WHERE id_insiden = ?
            ORDER BY waktu_event ASC
        ", [$this->idInsiden]);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('operasi_jurnal')->insert((array) $row);
                DB::table('operasi_jurnal_archive')
                    ->where('id_jurnal', $row->id_jurnal)
                    ->delete();
            }
        });

        Log::warning('Jurnal recovered from archive', [
            'id_insiden' => $this->idInsiden,
            'rows' => count($rows),
            'reason' => $this->reason,
        ]);
    }
}
```

### 5.2. Recovery Scenarios

| Scenario                               | Procedure                                      | SLA     |
| -------------------------------------- | ---------------------------------------------- | ------- |
| Audit investigasi insiden              | Recovery per id_insiden ke active table        | 1 jam   |
| Legal hold / BPK audit                 | Full export dari archive (CSV/JSON)            | 4 jam   |
| Data corruption di active table        | Restore from archive + replay from application log | 2 jam |
| Accidental purge                       | Restore from backup (mariabackup)              | 4 jam   |

### 5.3. Backup Strategy

| Backup Type     | Frequency | Retention | Tool              |
| --------------- | --------- | --------- | ----------------- |
| Logical dump    | Daily     | 7 hari    | `mysqldump --where` per event range |
| Physical backup | Hourly    | 24 jam    | MariaBackup       |
| Archive export  | Monthly   | 1 tahun   | `SELECT INTO OUTFILE` + S3 storage |

## 6. Query Impact Analysis

### 6.1. Before Archive (without indexes)

| Query Pattern            | Rows Scanned | Duration (est) |
| ------------------------ | ------------ | -------------- |
| Jurnal by insiden (detail) | 250K        | >500ms         |
| Jurnal by kategori       | 250K         | >300ms         |
| Audit trail by date range | 250K        | >1s            |

### 6.2. After Archive (with indexes, active = 90 days)

| Query Pattern            | Rows Scanned | Duration (est) |
| ------------------------ | ------------ | -------------- |
| Jurnal by insiden (detail) | <1K         | <5ms           |
| Jurnal by kategori       | <500         | <3ms           |
| Audit trail by date range | <5K         | <20ms          |

### 6.3. Archive-Only Query Patterns

Untuk query historis yang perlu mencakup archive + active:

```php
class JurnalRepository
{
    public function getByInsiden(int $idInsiden, bool $includeArchive = false): Collection
    {
        $query = OperasiJurnal::where('id_insiden', $idInsiden);

        if ($includeArchive) {
            return $query->union(
                DB::table('operasi_jurnal_archive')
                    ->where('id_insiden', $idInsiden)
                    ->select('*')
            )->orderBy('waktu_event')->get();
        }

        return $query->orderBy('waktu_event')->get();
    }
}
```

## 7. Legal & Compliance

| Requirement           | Implementation                       |
| --------------------- | ------------------------------------ |
| Audit trail 5 tahun   | Archive table with 5-year retention  |
| Legal hold            | Flag `legal_hold` bypasses purge     |
| Data deletion request | Hard delete from both tables         |
| Export for BPK        | `SELECT INTO OUTFILE` + S3 zip       |

## 8. Implementation Plan

### Phase 1: Analysis (Sprint 11.5)
1. Buat migration untuk `operasi_jurnal_archive` table
2. Implementasi `ArchiveJurnalJob`
3. Implementasi `PurgeJurnalArchiveJob`
4. Add index `waktu_event` jika belum ada

### Phase 2: Migration (Sprint 12)
5. Run initial archive for data > 90 hari
6. Setup cron schedule (daily archive, weekly verify, monthly partition rotate)
7. Monitoring: archive size, purge count, active table size

### Phase 3: Hardening (Sprint 13)
8. Implementasi recovery procedure
9. Backup automation
10. Load test with 500K archive rows
11. Grafana dashboard: archive metrics

## 9. Risks & Mitigations

| Risk                                      | Impact | Likelihood | Mitigation                                     |
| ----------------------------------------- | ------ | ---------- | ---------------------------------------------- |
| Archive job memblokade active table writes | High   | Low        | Batch in chunks of 50000; use `INSERT ... SELECT` non-locking |
| Recovery terlalu lambat untuk audit       | Medium | Low        | Pre-materialize view for active incident jurnal |
| Legal hold data ikut terpurge             | High   | Very Low   | `legal_hold` flag prevents delete               |
| Archive table partition exhaustion        | Medium | Low        | Auto-add quarterly partitions via event scheduler |
| Query perlu UNION archive + active terus  | Low    | Medium     | Materialized view atau cache layer di repository |

## 10. Migration

```php
// Migration: create archive table
Schema::create('operasi_jurnal_archive', function (Blueprint $table) {
    $table->unsignedBigInteger('id_jurnal');
    $table->unsignedBigInteger('id_insiden');
    $table->unsignedBigInteger('id_pengguna');
    $table->string('kategori_event', 50);
    $table->string('judul_event', 255);
    $table->text('deskripsi_event')->nullable();
    $table->unsignedBigInteger('id_referensi')->nullable();
    $table->string('tabel_referensi', 50)->nullable();
    $table->timestamp('waktu_event');
    $table->timestamp('diarsipkan_pada')->useCurrent();
    $table->boolean('legal_hold')->default(false);

    $table->primary(['id_jurnal', 'waktu_event']);
    $table->index('id_insiden', 'idx_archive_insiden');
    $table->index('waktu_event', 'idx_archive_waktu');
    $table->index('kategori_event', 'idx_archive_kategori');
});
```
