# NURISK GLOBAL DATA ISOLATION AUDIT

## Executive Summary
Audit keamanan arsitektural komprehensif membuktikan bahwa sistem NURISK mengalami **CRITICAL ARCHITECTURAL FAILURE**. Celah keamanan isolasi data (*Data Leak*) yang awalnya ditemukan pada domain M10 Mobilisasi bukan hanya masalah modul spesifik, melainkan gejala dari cacat sistemik pada seluruh infrastruktur Offline Sync. Arsitektur yang ada gagal menegakkan batas otorisasi Lapis 3 (Wilayah PCNU) dan Lapis 4 (Penugasan) pada *Read Layer* secara masif.

## Affected Domains
| Domain | REST Data Leak | Sync Data Leak |
|---|---|---|
| Assessment | Aman (Wajib `uuid_insiden` & scope) | **TERDAMPAK (Global Leak)** |
| Sitrep | Aman (Wajib `uuid_insiden` & scope) | **TERDAMPAK (Global Leak)** |
| Klaster | Aman (Wajib `uuid_insiden` & scope) | **TERDAMPAK (Global Leak)** |
| Penugasan | Aman (Wajib `uuid_insiden` & scope) | **TERDAMPAK (Global Leak)** |
| Mobilisasi | **TERDAMPAK (IDOR via Index)** | **TERDAMPAK (Global Leak)** |

*Kesimpulan: REST API Leak hanya terjadi di M10 (Domain Failure), namun Offline Sync Leak menembus 100% domain operasional (Systemic Failure).*

## Critical Findings
1. **REST API Data Leak (Domain M10):** Endpoint `index()` pada `MobilisasiApiController` membiarkan parameter `uuid_insiden` bersifat opsional dan gagal memfilter query builder dengan `AuthorizationContextService`. Berbeda dengan controller domain lain yang mengunci relasi pada satu insiden yang terotorisasi.
2. **Global Offline Sync Data Leak (Systemic ARCH-006):** Endpoint `POST /api/v1/sync` di `SyncApiController` mengeksekusi Pull Sync *murni* berdasarkan kriteria matematis `cursor_value > client_cursor`. Endpoint ini **sama sekali tidak memiliki layer otorisasi / query scoping** (tidak peduli apakah user PCNU atau Relawan, data tetap ditarik tanpa filter batas wilayah/insiden).

## Data Leak Scenarios
**Skenario 1 (REST API M10):**
Pengguna dengan level PCNU (contoh: PCNU Surabaya) melakukan *request* HTTP GET ke `/api/v1/mobilisasi`. Karena query tidak difilter, pengguna tersebut akan menerima daftar seluruh mobilisasi dari PCNU cabang lain secara nasional.

**Skenario 2 (Offline Sync — Bencana Skala Penuh):**
Seorang Relawan biasa (Lapis 4) *login* dan melakukan sinkronisasi *offline* melalui aplikasi Flutter. Aplikasi memanggil `POST /api/v1/sync`. Server merespons dengan **seluruh rekaman data** `assessment`, `sitrep`, `klaster`, `penugasan`, dan `mobilisasi` dari seluruh Indonesia yang diperbarui. Perangkat lokal relawan tersebut kini menyimpan data rahasia seluruh negara di dalam SQLite perangkatnya.

## Offline Sync Risk
- **Total Loss of Confidentiality:** Seluruh dokumen sensitif korban dan operasional organisasi bocor secara horizontal ke setiap perangkat aplikasi (*Zero Data Isolation*).
- **Client OOM & Bandwidth Exhaustion:** Aplikasi Flutter akan kehabisan memori (*Out Of Memory*) dan ruang penyimpanan karena harus mengunduh data skala nasional yang sama sekali tidak ada hubungannya dengan penugasan *user*.

## Root Cause
1. **A. Bug M10:** Kelalaian pada saat mendesain method `index()` di `MobilisasiApiController`, yang tidak meniru pola keharusan filter `uuid_insiden` yang sudah diterapkan di API Assessment dan Penugasan.
2. **B. Bug Seluruh Arsitektur NURISK:** Keputusan desain yang cacat pada `ADR_OFFLINE_SYNC_GOVERNANCE.md` dan implementasi awal `SyncApiController`. Arsitektur Sync menduplikasi alur transmisi data namun gagal menghubungkan *query layer* dari tabel `sync_cursors` dengan *AuthorizationContextService* (NURISK Multi-Scope Security). Akibatnya, sistem menganggap log kursor sebagai entitas *publicly broadcastable*.

## Blast Radius
Radius kebocoran adalah **100% Data Operasional**.
Setiap perangkat (Mobile Device) yang memiliki *token* API sah dapat mengekstraksi seluruh:
- Assessment Utama
- Operasi Sitrep
- Operasi Klaster
- Operasi Penugasan
- Operasi Mobilisasi
Milik seluruh unit PCNU dan PWNU di sistem tanpa halangan.

## Risk Score (0-100)
**100 / 100**

## Final Verdict
**CRITICAL ARCHITECTURAL FAILURE**

Seluruh mekanisme arsitektur Offline Sync perlu didesain dan ditulis ulang untuk menerapkan penyaringan *Cursor* dan *Tombstone* berdasarkan otorisasi Lapis 3 (Wilayah PCNU) dan Lapis 4 (Penugasan) milik masing-masing *mobile device user*.
