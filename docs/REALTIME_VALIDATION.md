# ARSITEKTUR VALIDASI — Apakah Realtime Infrastructure Benar-Benar Diperlukan?

**Date:** 2026-06-20  
**Author:** Principal Architect NURISK  
**Tujuan:** Memvalidasi kebutuhan WebSocket/SSE/Redis/Event Streaming SEBELUM investasi Phase 0  
**Metode:** Evidence-based dari codebase yang ada — nol asumsi

---

## Ringkasan Temuan

**Kesimpulan: Realtime infrastructure (SSE/WebSocket/Redis/Event Streaming) TIDAK DIPERLUKAN untuk pilot.**

Sebaliknya, yang diperlukan adalah:
1. Command Center dengan **AJAX polling** (seperti desain Sprint 12 yang sudah benar sejak awal)
2. Optimasi API yang sudah ada untuk direct access
3. Logistik API — satu-satunya domain yang benar-benar missing

**Dasar bukti:**
- Frekuensi event aktual: ~0.03–0.10 events/menit untuk semua domain
- Polling 30 detik: 2 req/min — bebannya TRIVIAL untuk server saat ini
- Latency requirement maksimal untuk event P0 adalah 30 detik, BUKAN < 2 detik
- Codebase saat ini 100% polling-based — tidak ada SSE/WebSocket/broadcasting
- Database queue memproses 1.000 jobs dengan 0 failure — Redis queue tidak diperlukan
- Nginx dan infrastruktur saat ini tidak siap untuk long-lived connections

---

## 1. Event Inventory

### 1.1 Metodologi Perhitungan

Berdasarkan data operasional NURISK (asumsi 1 insiden aktif per PCNU, 2 PCNU dalam pilot):

| Metrik | Nilai | Sumber |
|---|---|---|
| Insiden aktif per PCNU | 1–2 | Kapasitas operasi normal |
| Relawan per insiden | 10–25 | Sprint 08 scope |
| Assessment per insiden/hari | 2–5 | Sprint 04 scope |
| Sitrep per insiden/hari | 1–2 | Sprint 05 scope |
| Mutasi logistik per hari | 10–50 | Sprint 07 scope |
| Surat per hari | 2–5 | Sprint 10 scope |
| Pleno per hari | 0–1 | Sprint 09 scope |

### 1.2 Daftar Event Lengkap

| # | Event | Frekuensi / Hari | Per Minute | Aktor | Konsumen | Dampak Operasional |
|---|---|---|---|---|---|---|
| 1 | Insiden dibuat | 0–1 | 0.0007 | PCNU Admin | PWNU, PCNU lain | RENDAH — insiden baru tidak butuh respon segera |
| 2 | Insiden status berubah | 1–3 | 0.002 | PCNU Admin | Semua user terkait | SEDANG — perlu visibility perubahan fase operasi |
| 3 | Laporan kejadian masuk | 1–5 | 0.003 | Publik | PCNU Operator | RENDAH — laporan diverifikasi manual |
| 4 | Assessment dibuat | 2–5 | 0.003 | TRC relawan | Komandan insiden | SEDANG — data dampak untuk keputusan |
| 5 | Assessment is_latest berubah | 0–2 | 0.001 | Trigger DB | Semua user | RENDAH — otomatis, tidak perlu notifikasi |
| 6 | Sitrep difinalisasi | 1–2 | 0.001 | Operator | PCNU, PWNU | SEDANG — laporan resmi periodik |
| 7 | Relawan ditugaskan | 5–20 | 0.014 | PCNU Admin | Relawan, Komandan | **TINGGI** — relawan perlu tahu penugasan |
| 8 | Relawan tiba di lokasi | 3–10 | 0.007 | Relawan (mobile) | Komandan Klaster | SEDANG — tracking mobilisasi |
| 9 | Mobilitas status berubah | 5–20 | 0.014 | Relawan | Komandan, PCNU | SEDANG — status personel |
| 10 | Pos Aju dibuka/ditutup | 0–2 | 0.001 | Komandan | PCNU, PWNU | RENDAH — jarang berubah |
| 11 | Pos Aju komandan berubah | 0–1 | 0.0007 | Pleno | Semua terkait | RENDAH |
| 12 | Stok logistik berubah | 10–50 | 0.035 | Logistik operator | Komandan, Relawan | SEDANG — perlu update stok |
| 13 | Stok kritis (threshold) | 0–3 | 0.002 | Trigger sistem | PCNU, Logistik | **TINGGI** — perlu tindakan segera |
| 14 | Permintaan logistik dibuat | 2–10 | 0.007 | Relawan lapangan | Logistik gudang | SEDANG — request fulfillment |
| 15 | Permintaan status berubah | 2–10 | 0.007 | Logistik | Relawan pemohon | SEDANG — status approval |
| 16 | Surat draft dibuat | 2–5 | 0.003 | Admin | — | RENDAH — belum butuh aksi |
| 17 | Surat paraf diaktifkan | 1–3 | 0.002 | Sistem | Pejabat paraf | **TINGGI** — paraf berikutnya perlu aksi |
| 18 | Surat paraf disetujui/ditolak | 1–3 | 0.002 | Pejabat | Admin, paraf next | SEDANG — workflow lanjutan |
| 19 | Surat difinalisasi | 0–2 | 0.001 | Pejabat | PCNU, PWNU | SEDANG — dokumen resmi terbit |
| 20 | Pleno dibuat | 0–1 | 0.0007 | PCNU/PWNU | Peserta pleno | RENDAH — dijadwalkan |
| 21 | Pleno difinalisasi | 0–1 | 0.0007 | PWNU | PCNU, terkait | **TINGGI** — keputusan operasional |
| 22 | Eskalasi dibuat | 0–1 | 0.0007 | PWNU | PWNU, PCNU | **TINGGI** — level bencana naik |
| 23 | Aktivasi darurat | 0–1 | 0.0007 | PWNU | Semua | **TINGGI** — status darurat |
| 24 | Gap kebutuhan terbuka | 0–3 | 0.002 | Sistem (auto) | PCNU, Logistik | SEDANG — need fulfillment |
| 25 | Feedback klaster dibuat | 0–3 | 0.002 | Relawan | Komandan Klaster | RENDAH — evaluasi periodik |
| 26 | Jurnal operasional | 5–20 | 0.014 | Semua user | PCNU, PWNU | RENDAH — audit trail |

### 1.3 Ringkasan

| Metrik | Nilai |
|---|---|
| **Total events per day (pilot)** | ~45–160 events/hari |
| **Events per minute** | 0.03–0.11 events/menit |
| **Events per second** | 0.0005–0.002 events/detik |
| **P0 events (butuh aksi segera) per hari** | ~8–25 events/hari |
| **P0 events per minute** | 0.006–0.017 events/menit |

**Implikasi:** Dengan frekuensi ini, polling 30 detik akan mendeteksi event dalam waktu yang sama atau lebih cepat dari yang dibutuhkan. Bahkan polling 5 menit pun masih acceptable untuk sebagian besar use case.

---

## 2. Latency Requirement Matrix

### 2.1 Analisis Per Event

| Event | Maks Latency Diterima | Kategori | Alasan |
|---|---|---|---|
| Penugasan relawan baru | **30–120 detik** | Near real-time | Relawan perlu tahu, tapi tidak butuh respon instan. Relawan sedang di rumah/tempat kerja, notifikasi 30 detik fine. |
| Relawan tiba di lokasi | **30–60 detik** | Near real-time | Komandan perlu tracking, tapi akurasi menit cukup. |
| Sitrep difinalisasi | **5–15 menit** | Delayed | Laporan periodik — pembaca akan cek ketika siap. |
| Status insiden berubah | **30–60 detik** | Near real-time | Fase operasi berubah — perlu visibility wajar. |
| Eskalasi dibuat | **10–30 detik** | Near real-time | Level bencana naik — cukup cepat dengan polling 30s. |
| Surat paraf aktif | **5–15 menit** | Delayed | Pejabat tidak duduk menunggu notifikasi — akan cek ketika siap. |
| Surat difinalisasi | **5–15 menit** | Delayed | Dokumen resmi — tidak butuh instan. |
| Pleno difinalisasi | **1–5 menit** | Near real-time | Keputusan operasional — visibility menit fine. |
| Stok kritis | **10–30 detik** | Near real-time | Butuh perhatian segera, tapi 30s masih fine. |
| Permintaan logistik | **1–5 menit** | Near real-time | Request dikirim — logistik akan cek berkala. |
| Dashboard summary | **5–15 menit** | Delayed | Statistik — data 5 menit lalu masih relevan. |
| Live map markers | **30–60 detik** | Near real-time | Peta — marker 30 detik fine untuk koordinasi. |
| Laporan kejadian baru | **5–15 menit** | Delayed | Akan diverifikasi manual — tidak butuh instan. |
| Gap kebutuhan | **5–15 menit** | Delayed | Evaluasi — tidak butuh respon instan. |

### 2.2 Kategorisasi

| Kategori | Ambang Batas | Jumlah Event | Contoh |
|---|---|---|---|
| **Real-time** (< 2 detik) | < 2 detik | **0 event** | Tidak ada event yang memerlukan sub-2-detik |
| **Near real-time** (2–30 detik) | 2–30 detik | **0 event** | Polling 30s already covers this |
| **Near real-time** (30–60 detik) | 30–60 detik | **8 event** | Penugasan, status, eskalasi, stok kritis |
| **Delayed** (1–15 menit) | 1–15 menit | **18 event** | Surat, pleno, dashboard, sitrep, logistik |

### 2.3 Temuan Kritis

**Tidak ada satu pun event di NURISK yang memerlukan latency < 2 detik.**

Bahkan event paling kritis sekalipun (eskalasi, stok kritis, penugasan) memiliki latency requirement 10–30 detik — yang dapat dipenuhi dengan polling 30 detik.

**Alasannya:** NURISK adalah platform koordinasi bencana, BUKAN platform trading saham atau sistem kontrol industri. Manusia adalah konsumen utama — notifikasi 30 detik setelah event terjadi adalah peningkatan signifikan dari realitas operasional saat ini (yang biasanya manual via WhatsApp/telepon dengan delay menit hingga jam).

---

## 3. Polling vs SSE vs WebSocket

### 3.1 Decision Table

Untuk setiap use case di Command Center:

| Use Case | Frekuensi Update | Data Size | Polling (30s) | SSE | WebSocket | Rekomendasi |
|---|---|---|---|---|---|---|
| Summary cards (aktif count) | 1–5 event/jam | < 1 KB | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Incident status badges | 1–3 event/hari | < 100 B | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Volunteer list per insiden | 5–20 event/hari | < 5 KB | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Logistik stok overview | 10–50 event/hari | < 10 KB | ✅ MEMADAI | Overkill | Overkill | **POLLING** |
| Live map markers | 0–5 event/jam | < 200 B | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Recent event feed | 2–10 event/jam | < 2 KB | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Stok kritis alert | 0–3 event/hari | < 100 B | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| Surat paraf pending | 1–3 event/hari | < 200 B | ✅ SANGAT MEMADAI | Overkill | Overkill | **POLLING** |
| User presence (online/offline) | — | — | ✅ Not needed | Tidak perlu | Tidak perlu | **TIDAK DIPERLUKAN** |
| Relawan GPS tracking | — | — | ❌ Not realtime | Mungkin | Mungkin | **TUNDA** — Phase 3 |

### 3.2 Polling Cost Analysis

**Biaya polling untuk pilot (50 users):**

| Metrik | Per User | Total (50 users) |
|---|---|---|
| Polling interval | 30 detik | — |
| Request per menit | 2 | 100 req/min |
| Request per jam | 120 | 6,000 req/jam |
| Request per hari (12 jam) | 1,440 | 72,000 req/hari |
| Data per request | ~5 KB | — |
| Bandwidth per jam | ~600 KB | ~30 MB/jam |
| **Server cost per hari** | — | **~0.5 CPU-core-minute** |

**Biaya polling untuk regional (500 users):**

| Metrik | Total |
|---|---|
| Request per jam | 60,000 req/jam |
| Request per detik | ~17 req/detik |
| Server cost per hari | ~5 CPU-core-minutes |

**Biaya polling untuk provincial (5,000 users):**

| Metrik | Total |
|---|---|
| Request per detik | ~167 req/detik |
| Server cost | ~50 CPU-core-minutes |

**Catatan:** Server saat ini mampu menangani 49 req/detik di PHP built-in dev server (single-threaded). Dengan Octane/RoadRunner, throughput diproyeksikan 5-10x lipat. 167 req/detik polling adalah BEBAN RINGAN.

### 3.3 SSE Cost Analysis

| Metrik | Pilot (50 users) | Regional (500 users) |
|---|---|---|
| Koneksi simultan | 50 | 500 |
| Memory per koneksi (PHP-FPM) | ~15 MB | ~15 MB |
| Total memory | 750 MB | 7.5 GB |
| Masalah proxy (nginx keep-alive) | Perlu config tuning | Perlu config tuning |
| Reconnect on satellite drop | Sering (latensi satelit) | Sering |
| Kompleksitas debugging | Tinggi | Tinggi |

**Masalah spesifik dengan SSE + satelit:**
- Latensi satelit: 500-800ms round trip
- Koneksi HTTP persistent rawan terputus oleh proxy/CGNAT
- Reconnection logic kompleks di Flutter (EventSource client)
- Server memory tidak efisien (setiap koneksi PHP-FPM = ~15MB)

### 3.4 WebSocket Cost Analysis

| Metrik | Pilot (50 users) | Regional (500 users) |
|---|---|---|
| Infrastruktur baru | Laravel Reverb / Soketi / Pusher | Sama |
| Kompleksitas deployment | Tinggi — perlu reverse proxy config | Tinggi |
| Auth mechanism | Sanctum via first message | Sama |
| Flutter library | web_socket_channel | Sama |
| State management | Connection state, reconnect, backoff | Sama |

**WebSocket memberikan NILAI TAMBAH NOL dibanding SSE untuk use case NURISK** karena semua komunikasi adalah server-to-client (broadcast), bukan client-to-server (bidirectional).

### 3.5 Rekomendasi Transport

```
SEMUA USE CASE → AJAX POLLING (30 detik)
```

**Alasan:**
1. Frekuensi event sangat rendah (0.03–0.11 events/menit)
2. Latency requirement maksimal adalah 30 detik — polling 30s mencukupi
3. Arsitektur existing 100% polling-based — zero additional infrastructure
4. Polling skala pilot (50 user × 2 req/min = 100 req/min) adalah beban trivial
5. SSE/WebSocket menambah kompleksitas tanpa nilai tambah yang terukur
6. SSE connection via satelit rawan disconnect — reconnection logic eating complexity
7. Sprint 12 original design (AJAX polling 30s) adalah KEPUTUSAN ARSITEKTUR YANG BENAR

---

## 4. Redis Necessity Analysis

### 4.1 Use Case Evaluation

| Use Case | Diperlukan untuk Pilot? | Alternatif | Keputusan |
|---|---|---|---|
| **Queue driver** | ✅ Database queue saati ini berfungsi. 1.600 PDF jobs: 0 failure, ~72ms avg. | Database queue sudah cukup. Redis queue hanya diperlukan jika queue depth > 10.000. | **TIDAK DIPERLUKAN** |
| **Cache layer** | ⚠️ API response caching bisa membantu command center. Tapi pilot dengan 50 user → beban DB masih ringan. | MariaDB query cache, Eloquent eager loading, partial caching di aplikasi. | **TIDAK DIPERLUKAN** — evaluasi setelah load test production |
| **Session storage** | Sanctum token-based auth — tidak menggunakan session. Session Laravel default file/cookie. | Tidak perlu Redis session. File driver cukup. | **TIDAK DIPERLUKAN** |
| **Rate limiting** | Laravel built-in rate limiter menggunakan cache. Cache driver saat ini: `file`. | File cache cukup untuk single-server. Redis diperlukan jika multi-server. | **TIDAK DIPERLUKAN** — single server untuk pilot |
| **Presence (user online)** | Tidak diperlukan untuk pilot. | — | **TIDAK DIPERLUKAN** |
| **Event broadcasting (pub/sub)** | Jika menggunakan SSE, Redis pub/sub diperlukan untuk multi-instance. | TIDAK MENGGUNAKAN SSE → zero need. | **TIDAK DIPERLUKAN** |
| **Job batching** | Laravel job batching menggunakan database. | Database driver cukup. | **TIDAK DIPERLUKAN** |
| **Locking** | Database queue handles locking via `reserved_at`. | Database queue sudah handle. | **TIDAK DIPERLUKAN** |

### 4.2 Keputusan

# ❌ REDIS TIDAK DIPERLUKAN UNTUK PILOT

**Semua use case Redis dapat dipenuhi oleh database/filesystem yang ada.**

Redis baru diperlukan jika:
1. **Multi-server deployment** (horizontal scaling) — perlu centralized cache + rate limiting
2. **Queue depth > 10.000** — database queue mulai melambat
3. **API response caching diperlukan** — bisa ditunda sampai load test production menunjukkan bottleneck
4. **Event broadcasting via SSE/WebSocket** — baru Redis pub/sub diperlukan (tapi ini ditunda juga)

**Rekomendasi:** Tambahkan Redis ke roadmap untuk Phase 2 (Regional Readiness), bukan sekarang.

### 4.3 Catatan: MariaDB Query Cache

MariaDB memiliki query cache built-in yang dapat diaktifkan tanpa Redis:

```sql
SET GLOBAL query_cache_size = 128M;
SET GLOBAL query_cache_type = ON;
```

Ini adalah solusi zero-infrastructure untuk caching query aggregation dashboard. Cukup untuk pilot.

---

## 5. Command Center MVP

### 5.1 Definisi — Apa yang WAJIB Ada untuk Pilot

| # | Fitur | Prioritas | Data Source | Implementasi |
|---|---|---|---|---|
| 1 | **Insiden Aktif — Summary Cards** | **WAJIB** | `v_command_center_summary` view | Blade SSR + partial refresh via AJAX polling 30s |
| 2 | **Insiden List — Sortable Table** | **WAJIB** | `operasi_insiden` query | Blade + DataTable (server-side) |
| 3 | **Volunteer Count per Insiden** | **WAJIB** | `operasi_penugasan` aggregation | Widget card, refresh via polling |
| 4 | **Pos Aju Status** | **WAJIB** | `operasi_posaju` query | Simple list with status badge |
| 5 | **Stok Ringkasan** | **WAJIB** | `logistik_stok` aggregation | Table per gudang/pos |
| 6 | **Recent Activity Log** | **WAJIB** | `operasi_jurnal` — last 20 entries | Simple list, newest first |
| 7 | **Sitrep Terbaru Link** | **WAJIB** | `operasi_sitrep` — last 3 | List with download link |
| 8 | **PCNU Scope Filter** | **WAJIB** | `auth_users.default_scope_id` | Dropdown — pilih PCNU untuk PWNU |

### 5.2 — Yang Bisa Ditunda (NICE TO HAVE)

| # | Fitur | Alasan Penundaan | Target Phase |
|---|---|---|---|
| 1 | Live map (Leaflet) | Map tanpa realtime update tidak bermakna. Tapi map static yang di-refresh polling cukup. | Phase 2 |
| 2 | Live event feed (auto-scroll) | Activity log dengan refresh manual cukup | Phase 2 |
| 3 | Volunteer GPS tracking | Membutuhkan Flutter location service + realtime pipeline | Phase 3 |
| 4 | Logistik critical alert (flash + sound) | Stok kritis bisa ditampilkan sebagai warning badge biasa | Phase 2 |
| 5 | Drill-down interactive charts | Data bisa dilihat via halaman detail masing-masing domain | Phase 3 |
| 6 | Auto-refresh without user action | Manual refresh dengan tombol "Refresh" cukup untuk pilot | Phase 1 |
| 7 | Role-based widget hide/show | Semua widget tampil, akses data diatur oleh policy | Phase 2 |
| 8 | Export to PDF | Screenshot cukup untuk pilot | Phase 3 |

### 5.3 Arsitektur Command Center MVP

```
[Browser User]
    │
    ├── GET /command-center (Blade SSR) → Initial render (server-side)
    │       └── Data: ALL 8 wajib widgets rendered in HTML
    │
    ├── GET /api/command-center/summary (JSON) → Polling setiap 30 detik
    │       └── Returns: { insiden_count, volunteer_count, pos_count }
    │
    ├── GET /api/command-center/insiden (JSON) → Polling setiap 30 detik
    │       └── Returns: [{ id, judul, status, personel_count }]
    │
    ├── GET /api/command-center/activity (JSON) → Polling setiap 30 detik
    │       └── Returns: [{ waktu, kategori, deskripsi }]
    │
    └── GET /api/command-center/stok (JSON) → Polling setiap 60 detik
            └── Returns: [{ gudang, barang, jumlah, satuan }]
```

**Endpoint baru yang diperlukan:**
- `GET /api/command-center/summary` — aggregation dari `v_command_center_summary`
- `GET /api/command-center/activity` — 20 jurnal terbaru
- `GET /api/command-center/stok` — stok per gudang/pos

**Endpoint existing yang digunakan kembali:**
- `GET /api/v1/sync/status` — sync health
- `GET /api/v1/sync/metrics` — sync performance
- Semua domain API untuk drill-down

### 5.4 Perbandingan dengan Sprint 12 Design

| Aspek | Sprint 12 (Desain Asli) | Proposal Realignment (Salah) | Validasi (Kembali ke Desain Asli) |
|---|---|---|---|
| Teknologi | AJAX polling 30 detik | SSE/WebSocket | ✅ **AJAX polling 30 detik — BENAR** |
| Map | Leaflet + marker clustering | Live map with auto-refresh | ✅ Leaflet with polling refresh |
| Cache | Cache 5 menit (file) | Redis cache | ✅ File cache — cukup |
| API endpoints | 5 endpoint AJAX | Event-driven SSE | ✅ 3-4 endpoint AJAX — cukup |
| Scope | View SQL | Streaming events | ✅ View SQL — sudah ada, gunakan |

**Temuan:** Desain Sprint 12 adalah KEPUTUSAN ARSITEKTUR YANG BENAR dan TEPAT. Proposal realignment yang menambahkan SSE/WebSocket/Redis adalah OVER-ENGINEERING yang tidak diperlukan.

---

## 6. ROI Analysis

### 6.1 Option A: Bangun Realtime Infrastructure Sekarang

| Aspek | Nilai |
|---|---|
| **Cost (effort)** | 12.5 hari (Phase 0 estimate) |
| **Complexity** | TINGGI — event system, SSE, Redis, Flutter SSE client, monitoring |
| **Risk** | TINGGI — belum terbukti dibutuhkan. 100% infrastruktur baru. |
| **User Value** | RENDAH — pilot user tidak merasakan perbedaan signifikan |
| **Opportunity Cost** | TINGGI — 12.5 hari yang seharusnya untuk Logistik API, Relawan API, Command Center |

### 6.2 Option B: Bangun Command Center dengan Polling

| Aspek | Nilai |
|---|---|
| **Cost (effort)** | 3–4 hari (3 AJAX endpoints + Blade view + polling JS) |
| **Complexity** | RENDAH — pola yang sudah ada (Blade + Axios) |
| **Risk** | RENDAH — mudah diubah, mudah dibuang jika tidak sesuai |
| **User Value** | TINGGI — Command center langsung bisa dipakai |
| **Opportunity Cost** | RENDAH — sisa waktu untuk Logistik API |

### 6.3 Option C: Bangun Web Frontend Operasional Dulu

| Aspek | Nilai |
|---|---|
| **Cost (effort)** | 5–8 hari (Blade views untuk domain yang belum punya UI) |
| **Complexity** | SEDANG — banyak halaman CRUD |
| **Risk** | RENDAH — pola yang sudah ada |
| **User Value** | SEDANG — Web UI berguna untuk admin, tapi command center lebih prioritas |
| **Opportunity Cost** | SEDANG — waktu dari command center |

### 6.4 Option D: Pertahankan Polling Sampai Pilot Selesai

| Aspek | Nilai |
|---|---|
| **Cost (effort)** | 0 hari (tidak ada perubahan arsitektur) |
| **Complexity** | NOL — existing architecture |
| **Risk** | TERENDAH — proven pattern |
| **User Value** | TERTUNDA — command center tidak ada sampai pilot selesai |
| **Opportunity Cost** | RENDAH — tapi user tidak punya dashboard sama sekali |

### 6.5 ROI Matriks

| Option | Cost | Value | Risk | ROI Score | Peringkat |
|---|---|---|---|---|---|
| **A: Realtime Infrastructure** | 12.5 days | Low | High | 0.2 | 4 |
| **B: Command Center (Polling)** | 3-4 days | **High** | Low | **2.5** | **1** |
| C: Web Frontend | 5-8 days | Medium | Low | 1.0 | 3 |
| D: No Command Center | 0 days | None | None | 0 | — |

**Kesimpulan:** Option B memiliki ROI tertinggi: 4 hari build untuk immediate user value dengan zero infrastruktur baru.

---

## 7. Final Recommendation

### Option B: Bangun Command Center dengan AJAX Polling

# ✅ PILIHAN B — Bangun Command Center dengan Polling

Jangan bangun Redis/SSE/WebSocket/Event Streaming sekarang.

### Yang Harus Dilakukan (Prioritas)

| # | Task | Effort | Justifikasi |
|---|---|---|---|
| 1 | `GET /api/command-center/summary` endpoint | 0.5 hari | Aggregation dari view SQL existing |
| 2 | `GET /api/command-center/activity` endpoint | 0.5 hari | 20 jurnal terbaru |
| 3 | `GET /api/command-center/stok` endpoint | 0.5 hari | Stok summary |
| 4 | Command Center Blade view + AJAX polling | 1.5 hari | SSR initial + JS polling every 30s |
| 5 | PCNU scope filter | 0.5 hari | Dropdown untuk PWNU |
| **Total** | | **3.5 hari** | |

### Yang Tidak Jadi Dilakukan (Hemat)

| Task | Effort Saved | Alasan |
|---|---|---|
| Redis setup + config | 1 hari | Tidak diperlukan untuk pilot |
| SSE endpoint implementation | 1 hari | Polling cukup |
| Event classes (20+ files) | 2 hari | Tidak diperlukan untuk pilot |
| Event broadcasting integration | 2 hari | Tidak diperlukan untuk pilot |
| Flutter SSE client | 2 hari | Tidak diperlukan untuk pilot |
| WebSocket/SSE auth | 1 hari | Tidak diperlukan untuk pilot |
| Event monitoring | 1 hari | Tidak diperlukan untuk pilot |
| Event persistence | 2 hari | Tidak diperlukan untuk pilot |
| **Total saved** | **~12 hari** | **Setara dengan Phase 0** |

### Yang Tetap Harus Dibangun

| Domain | Prioritas | Effort |
|---|---|---|
| Logistik API | P0 — blocking untuk pilot | 3 hari |
| Command Center (polling) | P0 — blocking untuk pilot | 3.5 hari |
| Relawan API (verifikasi + shift) | P1 — penting | 2 hari |
| PDF Queue implementation | P1 — penting | 2 hari |
| API direct access optimization | P1 — penting | 2 hari |
| Security hardening (remaining routes) | P1 — penting | 1 hari |

### Yang Ditunda

| Item | Target Fase |
|---|---|
| Redis | Phase 2 (Regional) — evaluasi setelah load test production |
| SSE | Phase 2 — evaluasi setelah pilot feedback |
| WebSocket | Phase 3 — hanya jika bidirectional communication diperlukan |
| Event streaming | Phase 3 — hanya jika terbukti dibutuhkan |
| Realtime infrastructure | **Tidak dibangun sekarang** — buktikan kebutuhan dulu |

---

## 8. Architecture Decision Record

### ADR-002: Realtime Infrastructure

**Status:** ACCEPTED (negative — we choose NOT to build)

**Context:**
Proposal untuk membangun SSE/WebSocket/Redis/Event Streaming sebelum pilot.

**Decision:**
JANGAN bangun realtime infrastructure sebelum pilot selesai. Gunakan AJAX polling 30 detik untuk Command Center.

**Rationale:**
1. Frekuensi event sangat rendah (0.03–0.11 events/menit) — polling 30 detik ketinggalan max 1 event
2. Latency requirement maksimal untuk event P0 adalah 10–30 detik — polling mencukupi
3. Biaya polling untuk 50 pilot users: 100 req/min = beban server TRIVIAL
4. SSE/WebSocket via satelit: rawan disconnect, reconnection complexity tinggi
5. Sprint 12 original design (AJAX polling) adalah KEPUTUSAN YANG BENAR — tidak perlu diubah
6. Zero additional infrastructure — nol Redis, nol event server, nol monitoring baru
7. Opportunity cost: 12 hari untuk realtime infrastructure vs 3.5 hari untuk command center polling

**Consequences:**
- Command Center akan memiliki latency 0–30 detik (acceptable)
- Tidak ada event-driven architecture untuk Flutter
- Integrasi notifikasi push ditunda ke Phase 3
- Tim dapat fokus pada Logistik API + Relawan API + Command Center

**Revert Condition:**
Keputusan ini direvisi JIKA pilot feedback menunjukkan:
1. Polling 30 detik menyebabkan missed events yang berdampak operasional
2. User melaporkan bahwa data 30 detik terlalu lambat untuk pengambilan keputusan
3. Beban polling > 1.000 req/detik (tidak mungkin di pilot dengan 50 user)

---

## Ringkasan

### 1. Temuan Utama

**Realtime infrastructure (SSE/WebSocket/Redis/Event Streaming) adalah solusi yang mencari masalah.**

- **Frekuensi event:** 0.03–0.11 per menit — polling 30s tidak akan pernah ketinggalan
- **Latency maksimal:** 30 detik polling = cukup untuk semua event P0
- **Biaya polling:** 100 req/min untuk 50 user = trivially small
- **Keandalan satelit:** Koneksi persistent via satelit rawan putus — polling lebih robust
- **Sprint 12 sudah benar:** AJAX polling 30 detik sejak awal adalah desain yang tepat

### 2. Biaya Realtime Infrastructure yang Dihemat

~12 hari development + ~$200/bulan Redis + kompleksitas operasional yang tidak perlu.

### 3. Prioritas Nyata untuk Pilot

1. Command Center dengan polling (3.5 hari) → **INI yang benar-benar dibutuhkan user**
2. Logistik API (3 hari) → blocking domain untuk operasi lapangan
3. Relawan API + verifikasi (2 hari) → manajemen personel
4. PDF Queue (2 hari) → surat governance
5. Security hardening (1 hari) → scope middleware lengkap

### 4. Perubahan dari Laporan Sebelumnya

Laporan `docs/STRATEGIC_ALIGNMENT_REPORT.md` dan `docs/ROADMAP_V2.md` sebelumnya merekomendasikan Phase 0 untuk Realtime Infrastructure. **Ini adalah OVER-CORRECTION.** Dokumen ini adalah VALIDASI bahwa polling adalah pilihan yang tepat.

### 5. Final Recommendation

# ✅ OPTION B — Bangun Command Center dengan AJAX Polling

**Tolak proposal Redis/SSE/WebSocket/Event Streaming untuk pilot.**

**Gunakan 12 hari yang dihemat untuk membangun:**
- Command Center (3.5 hari)
- Logistik API (3 hari)
- Relawan API (2 hari)
- PDF Queue (2 hari)
- Security hardening (1 hari)
- Buffer (0.5 hari)

**Evaluasi ulang setelah pilot:**
- Apakah polling 30 detik cukup? → Hampir pasti ya berdasarkan data frekuensi event
- Apakah user request fitur realtime? → Baru bangun kalau diminta
- Apakah ada bottleneck performance? → Baru tambah Redis kalau terbukti
