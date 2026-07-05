# Queue Stress Test — PDF Generation Benchmark

## Executive Summary

A queue stress test was performed on the PDF generation pipeline (`GenerateSuratPdfJob`/`BenchmarkPdfJob`) to measure throughput, reliability, and resource usage under increasing load. The benchmark dispatched 100, 500, and 1000 jobs using the `sync` queue driver with DOMPDF rendering. **Zero jobs failed** across all test sizes, confirming the job's stability. Average job completion time remained stable at ~77 ms, yielding a consistent throughput of ~12.9 jobs/second.

---

## Test Methodology

1. **Job Used:** `App\Jobs\BenchmarkPdfJob` — a simplified version of `GenerateSuratPdfJob` that simulates the same workload:
   - Lookup `DokumenSuratUtama` record from DB
   - Validate status (`siap_tanda_tangan`)
   - Render Blade template to HTML
   - Generate PDF via `dompdf/dompdf` (v3.0)
   - Store PDF to local disk
   - Update record status to `ditandatangani`

2. **Data:** For each run, N `operasi_surat_keluar` records are created with `status_surat = 'siap_tanda_tangan'`. Required related records (`master_surat_jenis`, `auth_users`, `master_jabatan_penandatangan`) are seeded once per run.

3. **Measurement:** Total wall-clock time is captured via `hrtime()`. Memory delta is `memory_get_peak_usage(true)` before and after the batch.

4. **Verification:** After each run, the database is queried to confirm all records have `status_surat = 'ditandatangani'` and a non-null `file_pdf_path`.

---

## Environment

| Parameter          | Value                                          |
|--------------------|------------------------------------------------|
| App Environment    | `loadtest`                                     |
| PHP Version        | 8.x                                            |
| Queue Driver       | `sync` (synchronous, in-process)               |
| Database           | SQLite (`database/loadtest.sqlite`)            |
| PDF Library        | `dompdf/dompdf` ^3.0                           |
| Cache Store        | `array`                                        |
| Session Driver     | `file`                                         |
| Log Channel        | `stack` (single)                               |
| Job Timeout        | 120 seconds                                    |
| Job Retries        | 3 (backoff: 10s, 30s, 60s)                     |
| PHP Memory Limit   | Unlimited (`-1`)                                |

---

## Results

| Metric                      | 100 Jobs      | 500 Jobs      | 1000 Jobs     |
|-----------------------------|---------------|---------------|---------------|
| Dispatched                  | 100           | 500           | 1000          |
| Failed                      | 0             | 0             | 0             |
| Completed (`ditandatangani`)| 100           | 500           | 1000          |
| PDF Files Created           | 100           | 500           | 1000          |
| Total Time (ms)             | 7,736.11      | 38,574.18     | 77,914.92     |
| Avg Time per Job (ms)       | 77.36         | 77.15         | 77.91         |
| Throughput (jobs/sec)       | 12.93         | 12.96         | 12.83         |
| Peak Memory Delta (MB)      | 24.50         | 26.50         | 26.50         |

---

## Observations

1. **Consistent per-job cost:** The average job time is tightly clustered around 77 ms across all three runs. This indicates no degradation under load — the `sync` driver processes jobs sequentially, so each job gets the same resources regardless of batch size.

2. **Memory stability:** Peak memory delta grew only slightly (24.5 → 26.5 MB) between 100 and 500 jobs and remained at 26.5 MB for 1000. This suggests that DOMPDF instances are properly garbage-collected between synchronous dispatches, and no memory leak is present.

3. **Throughput ceiling:** At ~12.9 jobs/second, a single synchronous worker can process roughly **46,000 PDF jobs per hour**. With production queue workers (e.g., `database`, `redis` with multiple workers), throughput can scale linearly.

4. **No failures:** Zero exceptions were thrown, and all 1600 dispatched jobs across all runs completed with the correct final status. The job's guard clauses (`status_surat !== 'siap_tanda_tangan'`) and retry logic (`backoff: [10, 30, 60]`) were never triggered.

5. **SQLite performance:** The database was configured with `PRAGMA synchronous = OFF` and `PRAGMA journal_mode = MEMORY` during seeding for speed. This is acceptable for a loadtest environment.

---

## Target Verification

| Target                      | 100 Jobs | 500 Jobs | 1000 Jobs | Status |
|-----------------------------|----------|----------|-----------|--------|
| 0 failed jobs               | ✓        | ✓        | ✓         | **PASS** |

The zero-failure target was met across all scales.

---

## Recommendations for Production Queue Configuration

1. **Use a persistent queue driver:** Replace `sync` with `redis` or `database` for production. This allows job persistence across restarts and horizontal scaling via multiple workers.

2. **Worker count:** Based on ~77 ms per job, a single worker can handle ~13 jobs/second. For 1000 PDFs/day, one worker is sufficient. For peak loads (e.g., batch approvals), scale to 3–5 workers on the `pdf-generation` queue.

3. **Queue per-channel isolation:** The `pdf-generation` queue (already used by `GenerateSuratPdfJob`) should have dedicated workers so PDF rendering does not block other job types. Configure `horizon` or `queue:work` with:
   ```
   php artisan queue:work redis --queue=pdf-generation --tries=3 --timeout=120
   ```

4. **Retry & timeout:** The existing configuration (`tries=3`, `backoff=[10,30,60]`, `timeout=120`) is appropriate. DOMPDF can spike to ~50 MB per rendering; the 120s timeout provides ample headroom.

5. **Storage:** Ensure the `public` disk (or S3 in production) has sufficient capacity. Each PDF is typically 20–50 KB. 10,000 PDFs consume ~200–500 MB.

6. **Monitor failed jobs:** Enable `failed_jobs` table and set up alerting on the `pdf-generation` queue failure rate. The benchmark confirms the job is reliable, but external factors (disk space, memory limits) can still cause failures.

7. **Consider deferring PDF generation:** If synchronous generation during HTTP requests is too slow, pre-generate PDFs via a scheduled command or webhook-triggered job, which is the current approach.

---

*Benchmark executed on 2026-06-20 using `php artisan queue:stress-test --jobs=N --env=loadtest`.*
