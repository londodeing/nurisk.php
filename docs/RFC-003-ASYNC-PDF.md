# RFC-003: Asynchronous PDF Generation

**Status:** DRAFT (Under Review)
**Author:** Technical Lead
**Date:** 2026-06-18

## 1. Problem Statement

PDF generation via Dompdf is a synchronous, blocking operation inside the HTTP request lifecycle. Current implementation in `SuratPdfService` executes `finalisasi()` inline, causing:

- Request timeout for surat with many paraf/tembusan (>30s observed)
- No retry mechanism on failure — user sees 500 with no recovery
- No visibility into generation failures — no observability
- Queue worker blocked during PDF generation, reducing throughput

## 2. Scope

Dokumen ini mendefinisikan arsitektur untuk **asynchronous PDF generation** pada modul surat keluar. Meliputi:

- State machine surat yang mengakomodasi async PDF
- Queue driver & job definition
- Failure handling & retry policy
- Idempotency guarantees
- Observability (logging, metrics, alerting)

**Non-Goals:**
- Replace Dompdf — tetap digunakan sebagai rendering engine di queue worker
- Migrasi queue ke sistem eksternal (RabbitMQ/Kafka) — tetap gunakan Laravel queue table
- PDF signing/stamping — di luar scope

## 3. State Machine

### Current State:

```
draft → review_paraf → siap_tanda_tangan → ditandatangani → arsip
```

### Proposed State:

```
draft
  ↓ (kirim review)
review_paraf
  ↓ (semua paraf disetujui)
siap_tanda_tangan
  ↓ (finalisasi → dispatch job)
pdf_pending ──────────────┐
  ↓ (queue worker sukses)  ├→ pdf_failed (retry)
ditandatangani              │
  ↓                         └→ pdf_failed_exhausted (manual)
arsip
```

### State Transition Rules:

| Transition            | Trigger               | Actor              | Validasi                                   |
| --------------------- | --------------------- | ------------------ | ------------------------------------------ |
| `draft → review_paraf` | User klik "Kirim Review" | pcnu/pwnu/super_admin | Surat must have ≥1 paraf                  |
| `review_paraf → siap_tanda_tangan` | Semua paraf disetujui | Otomatis (Observer) | All paraf status = `disetujui`             |
| `siap_tanda_tangan → pdf_pending` | User klik "Finalisasi & TTD" | pwnu/super_admin  | Semua paraf disetujui; status surat = `siap_tanda_tangan` |
| `pdf_pending → ditandatangani` | Queue job sukses | Queue Worker        | PDF tersimpan di storage; hash valid       |
| `pdf_pending → pdf_failed` | Queue job gagal (retry) | Queue Worker       | Exception threshold < max_attempts         |
| `pdf_failed → pdf_pending` | Retry otomatis | Queue Worker        | delay = 30s × attempt^2 (exponential backoff) |
| `pdf_failed_exhausted → draft` | Manual reset | super_admin        | Admin memutuskan untuk regenerate PDF      |

## 4. Queue Architecture

### 4.1. Job Definition

```php
class GenerateSuratPdfJob implements ShouldQueue
{
    public $timeout = 120;      // 2 menit untuk PDF besar
    public $tries = 3;          // max 3 attempts
    public $backoff = [30, 90, 270]; // exponential: 30s, 90s, 270s

    public function __construct(
        public readonly int $idSurat,
        public readonly string $isiSnapshot,
        public readonly string $expectedHash,
    ) {
        $this->onQueue('pdf-generation');
    }

    public function handle(SuratPdfService $pdfService): void
    {
        $surat = DokumenSuratUtama::findOrFail($this->idSurat);

        DB::transaction(function () use ($pdfService, $surat) {
            // 1. Generate PDF
            $path = $pdfService->generatePdf($surat, $this->isiSnapshot);

            // 2. Verify hash (idempotency)
            $actualHash = hash_file('sha256', Storage::disk('public')->path($path));
            if ($actualHash !== $this->expectedHash) {
                throw new PdfHashMismatchException('Hash mismatch: expected ' . $this->expectedHash . ', got ' . $actualHash);
            }

            // 3. Update surat
            $surat->update([
                'file_pdf_path' => $path,
                'hash_dokumen' => $actualHash,
                'status_surat' => 'ditandatangani',
                'waktu_ditandatangani' => now(),
            ]);
        });
    }

    public function failed(\Throwable $e): void
    {
        $surat = DokumenSuratUtama::find($this->idSurat);
        if (!$surat) return;

        $surat->update([
            'status_surat' => 'pdf_failed',
            'catatan_gagal_pdf' => $e->getMessage(),
            'waktu_gagal_pdf' => now(),
        ]);

        Log::error('PDF generation failed', [
            'id_surat' => $this->idSurat,
            'attempt' => $this->attempts(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
```

### 4.2. Queue Driver

- **Primary:** `database` (Laravel queues table) — zero infrastruktur tambahan
- **Worker:** Single process `php artisan queue:work --queue=pdf-generation --tries=3 --timeout=180`
- **Monitoring:** Horizon atau `queue:listen` dengan notifikasi Slack

### 4.3. Queue Table Schema

```sql
ALTER TABLE jobs ADD COLUMN queue_group VARCHAR(50) DEFAULT 'default';
CREATE INDEX idx_jobs_queue_group ON jobs(queue_group);
```

Atau gunakan queue name bawaan Laravel tanpa kolom tambahan.

## 5. Idempotency

PDF generation harus idempoten untuk mencegah duplikasi saat retry:

1. **Job-level:** `GenerateSuratPdfJob` menerima `expectedHash` yang dihitung dari snapshot isi surat. Jika file PDF sudah ada dengan hash yang cocok, job dianggap sukses (no-op).

2. **Surat-level:** Sebelum dispatch, surat harus berstatus `pdf_pending`. Cek di `handle()`:
   ```php
   if ($surat->status_surat !== 'pdf_pending') {
       return; // Already processed or stale job
   }
   ```

3. **Storage-level:** Gunakan path deterministic:
   ```php
   $path = 'surat/pdf/' . $surat->id_surat . '/' . $this->expectedHash . '.pdf';
   ```

## 6. Failure Handling & Retry Policy

| Attempt | Delay  | Action                                  |
| ------- | ------ | --------------------------------------- |
| 1       | 30s    | Retry otomatis                          |
| 2       | 90s    | Retry otomatis                          |
| 3       | 270s   | Final attempt; jika gagal → `pdf_failed_exhausted` |

**Failure causes:**
- Memory exhaustion (Dompdf)
- Disk full
- Storage driver timeout
- Template rendering error

**Alerting:**
- After 3 failures → Slack webhook ke #ops-alerts
- After >5 failures in 1 hour → PagerDuty

**Manual recovery:**
Super admin dapat mereset surat dari `pdf_failed_exhausted` ke `draft` melalui tombol "Regenerate PDF" di halaman show surat. Ini akan:

1. Mengubah status ke `draft`
2. Menghapus file PDF lama (jika ada)
3. Membutuhkan input ulang isi surat oleh user

## 7. Observability

### Logging

```php
Log::channel('pdf')->info('PDF generation started', [
    'id_surat' => $surat->id_surat,
    'job_id' => $this->job?->getJobId(),
    'isi_length' => strlen($this->isiSnapshot),
]);

Log::channel('pdf')->info('PDF generation completed', [
    'id_surat' => $surat->id_surat,
    'path' => $path,
    'hash' => $actualHash,
    'duration_ms' => $duration,
]);
```

### Metrics (Prometheus / Laravel Telescope)

| Metric                      | Type    | Labels                    |
| --------------------------- | ------- | ------------------------- |
| `pdf_generation_total`      | Counter | status=success/failed     |
| `pdf_generation_duration_ms`| Histogram | -                       |
| `pdf_generation_queue_size` | Gauge   | queue=pdf-generation      |

### Dashboard (Grafana)

- PDF generation success rate (24h)
- P50/P95/P99 generation duration
- Queue depth over time
- Failure breakdown by surat

## 8. Implementation Plan

### Sprint 11.4 (RFC Approval)

1. Tambah kolom status `pdf_pending`, `pdf_failed` ke enum `status_surat` di migration
2. Implementasi `GenerateSuratPdfJob`
3. Ubah `SuratPdfService::finalisasi()` untuk dispatch job alih-alih generate langsung
4. Gunakan SuratObserver untuk transisi `review_paraf → siap_tanda_tangan`
5. Tambah route + tombol "Regenerate PDF" untuk super_admin
6. Setup queue worker di .rr.yaml (RoadRunner)

### Sprint 12 (Production Hardening)

7. Tambah unit test untuk state transitions
8. Load test dengan simulasi 100 PDF concurrent
9. Monitoring dashboard
10. Runbook untuk failure scenarios

## 9. Risks & Mitigations

| Risk                           | Impact | Likelihood | Mitigation                                    |
| ------------------------------ | ------ | ---------- | --------------------------------------------- |
| Dompdf OOM pada surat besar    | High   | Medium     | Set memory_limit = 512M di queue worker       |
| Job terputus saat deploy       | Medium | Low        | Gunakan queue:restart + `--stop-when-empty`   |
| Status surat stuck di `pdf_pending` | High | Low    | Cron job untuk timeout detection (>30 menit)  |
| Duplikasi PDF karena retry     | Medium | Low        | Idempotency via hash + status check           |

## 10. Migration

```php
// Migration: add pdf status to surat_keluar
Schema::table('operasi_surat_keluar', function (Blueprint $table) {
    $table->string('catatan_gagal_pdf', 500)->nullable()->after('file_pdf_path');
    $table->timestamp('waktu_gagal_pdf')->nullable()->after('catatan_gagal_pdf');
});

// Update enum — handled via DB::statement
DB::statement("ALTER TABLE operasi_surat_keluar MODIFY COLUMN status_surat ENUM('draft','review_paraf','siap_tanda_tangan','ditandatangani','ditolak','arsip','pdf_pending','pdf_failed') NOT NULL DEFAULT 'draft'");
```
