# Gate C Re-test — Hasil Eksekusi

**Tanggal:** 18 Juni 2026
**Web Server digunakan:** Laravel Octane (RoadRunner)
**PHP Version:** 8.2.12
**Tool benchmark:** ApacheBench (ab) v2.3

## Konfigurasi Test
| Parameter | Nilai |
|---|---|
| Concurrency | 50 / 100 |
| Total requests | 5.000 / 3.000 |
| Keep-alive | Ya (-k) |
| Endpoint | /api/wilayah/kabupaten & kecamatan |

## Hasil Test 1 (concurrency 50) - Kabupaten
| Metrik | Nilai | Target Dev | Status |
|---|---|---|---|
| Requests/sec | 1749.69 | > 1000 | ✅ PASS |
| P95 latency | 38ms | < 500ms | ✅ PASS |
| Failed requests | 0 | 0 | ✅ PASS |
| Non-2xx responses | 0 | 0 | ✅ PASS |

## Hasil Test 2 (concurrency 100) - Kabupaten
| Metrik | Nilai | Target Dev | Status |
|---|---|---|---|
| Requests/sec | 1661.17 | > 1000 | ✅ PASS |
| P95 latency | 100ms | < 500ms | ✅ PASS |
| Failed requests | 0 | 0 | ✅ PASS |
| Non-2xx responses | 0 | 0 | ✅ PASS |

## Hasil Test 3 (concurrency 50) - Kecamatan
| Metrik | Nilai | Target Dev | Status |
|---|---|---|---|
| Requests/sec | 1359.62 | > 1000 | ✅ PASS |
| P95 latency | 57ms | < 500ms | ✅ PASS |
| Failed requests | 0 | 0 | ✅ PASS |
| Non-2xx responses | 0 | 0 | ✅ PASS |

## Catatan Lingkungan
Web server digunakan: Laravel Octane (RoadRunner) pada port 8080.
Uji produksi sesungguhnya: WAJIB diulang di Nginx + PHP-FPM dedicated server multi-worker sebelum go-live untuk menembus target produksi murni (5000 RPS).

## Verdict Gate C
- [x] ✅ PASS (lingkungan dev) — arsitektur terbukti valid, uji produksi pending
- [ ] ❌ FAIL — catat error di bawah
