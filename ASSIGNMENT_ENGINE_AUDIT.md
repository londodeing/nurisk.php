# ASSIGNMENT_ENGINE_AUDIT

## Domain E — Assignment Engine

### 1. Dokumentasi Proses
- **Pembuat Assignment**: Kolom `ditugaskan_oleh` di tabel `operasi_penugasan`.
- **Penerima Assignment**: Relawan yang tertaut via `id_pengguna` di `operasi_penugasan` atau tabel `relawan_penugasan`.
- **Penyelesaian Assignment**: Kolom `waktu_selesai` menandai akhir penugasan, namun tidak ada state khusus.
- **Pembatalan Assignment**: **MISSING** (Hanya ada mekanisme delete permanen/soft delete, namun tidak ada statemachine pembatalan logis).

### 2. Verifikasi Status Penugasan
Tidak terdapat enumerasi (enum) statemachine di tabel `operasi_penugasan` selain string default `aktif`.
- `DRAFT` — **MISSING**
- `ASSIGNED` — **PARTIAL** (terwakili oleh record yang dibuat)
- `ACCEPTED` — **MISSING**
- `ON_ROUTE` — **MISSING**
- `ON_SITE` — **MISSING**
- `COMPLETED` — **MISSING** (Hanya berupa tanggal selesai)
- `CANCELLED` — **MISSING**
- `REJECTED` — **MISSING**

---

## Domain F — Assignment History

**Apakah histori assignment tersimpan permanen?**
**IMPLEMENTED**. Tabel `operasi_penugasan` dan `relawan_penugasan` menggunakan fitur Soft Deletes Laravel (`dihapus_pada`). Relawan dapat dilihat riwayat penugasannya di masa lalu berdasarkan query dengan `withTrashed()`.
Sistem BISA menjawab "Relawan A pernah bertugas dimana selama 5 tahun terakhir?" secara terstruktur.

---

## Domain G — Attendance & Check-In

**Verifikasi Kemampuan:**
- **Check in Posko**: **IMPLEMENTED** (Kolom `waktu_checkin`, `lokasi_checkin`).
- **Check out Posko**: **IMPLEMENTED** (Kolom `waktu_checkout`, `lokasi_checkout`).
- **Riwayat kehadiran**: **MISSING** (Data check-in diikat langsung di tabel `operasi_penugasan` yang berarti 1 tugas = 1 checkin, sistem tidak mendukung riwayat harian).
- **Perhitungan jam tugas**: **PARTIAL** (Dapat dikalkulasi dari waktu mulai-selesai atau checkin-checkout global penugasan, bukan akumulasi shift).

---

## Domain H — Shift Management

**Tabel:** `relawan_shift`
**Verifikasi Kemampuan:**
- Terdapat `waktu_mulai` dan `waktu_selesai`.
- `SHIFT PAGI`, `SHIFT SIANG`, `SHIFT MALAM`: **MISSING** (Tidak ada konvensi master shift).
- Rotasi shift otomatis: **MISSING**.
