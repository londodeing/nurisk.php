# NURISK — PUBLIC DASHBOARD KPI SPECIFICATION
## Document 20: Backend Contract & Business Rules
**Version**: 1.0.0 | **Status**: PENDING REVIEW | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OBJECTIVE

Dokumen ini adalah kontrak *Backend-for-Frontend* (BFF) untuk endpoint KPI Dashboard Publik NURISK. 
Tujuan utamanya adalah memastikan bahwa KPI hanya merepresentasikan **Single Source of Trusted Information**. Public Dashboard sama sekali tidak membaca tabel `operasi_insiden` mentah, melainkan mengandalkan service agregasi khusus di backend.

---

## 2. INCIDENT LIFE CYCLE

Sistem ini memaksakan siklus hidup insiden sebagai berikut:
1. `REPORTED` (Laporan baru masuk, belum disaring)
2. `TRIAGED` (Sedang dipilah)
3. `VERIFIED` (Laporan valid / bukan hoax)
4. `ASSESSED` (Telah dilakukan asesmen lapangan)
5. `ACTIVE` (Operasi tanggap darurat sedang berjalan)
6. `RESOLVED` (Penanganan selesai)
7. `CLOSED` (Terdokumentasi & selesai penuh)

**Aturan Emas Public KPI:**
Data yang belum berstatus minimal `VERIFIED` **DILARANG KERAS** masuk ke dalam agregasi publik untuk mencegah penyebaran hoax dan spam.

---

## 3. DEFINISI KPI (4 CARD UTAMA)

Dashboard hanya akan menampilkan 4 KPI yang bermakna bagi publik. Tidak menampilkan angka akumulatif pendaftar/relawan yang *vanity* (sekadar hiasan).

### KPI 1: Kejadian Aktif
- **Label UI**: "Kejadian Aktif"
- **Definisi Bisnis**: Jumlah insiden yang saat ini sedang dalam status tanggap darurat (`ACTIVE`) pada waktu berjalan.
- **SQL Source**: `SELECT count(id) FROM operasi_insiden WHERE status = 'ACTIVE'`

### KPI 2: Kejadian Tervalidasi
- **Label UI**: "Kejadian Tervalidasi"
- **Definisi Bisnis**: Total insiden yang terbukti valid dari awal tahun hingga saat ini (Year-To-Date) atau All Time.
- **SQL Source**: `SELECT count(id) FROM operasi_insiden WHERE status IN ('VERIFIED', 'ASSESSED', 'ACTIVE', 'RESOLVED', 'CLOSED')`

### KPI 3: Wilayah Terdampak
- **Label UI**: "Wilayah Terdampak"
- **Definisi Bisnis**: Jumlah entitas Kabupaten/Kota yang saat ini masih memiliki kejadian aktif (menghindari spam titik kejadian berulang).
- **SQL Source**: `SELECT count(DISTINCT kode_wilayah) FROM operasi_insiden WHERE status = 'ACTIVE'`

### KPI 4: Relawan Bertugas
- **Label UI**: "Relawan Bertugas"
- **Definisi Bisnis**: Total relawan yang saat ini telah dimobilisasi dan statusnya sedang bertugas (Deployed) pada Misi/Insiden yang aktif. Bukan jumlah total pendaftar akun aplikasi.
- **SQL Source**: (Dari Mission Engine) `SELECT count(DISTINCT relawan_id) FROM operasi_penugasan WHERE status = 'DEPLOYED'`

---

## 4. API CONTRACT SPECIFICATION

Backend Laravel WAJIB menyediakan endpoint agregasi agar perhitungan tidak terjadi di sisi mobile.

- **Endpoint**: `GET /api/public/dashboard/kpi`
- **Auth**: None (Public)
- **Response Format**:
```json
{
  "status": "success",
  "message": "KPI data retrieved successfully",
  "data": {
    "active_incidents": 9,
    "verified_incidents": 356,
    "impacted_regions": 18,
    "deployed_volunteers": 247,
    "last_updated": "2026-07-06T21:45:00+07:00"
  }
}
```

---

## 5. CACHING STRATEGY (BACKEND & MOBILE)

### 5.1 Backend Caching (Redis/File)
- Endpoint ini berpotensi dipanggil ratusan ribu kali secara serentak (DDoS alamiah).
- Backend **TIDAK BOLEH** menjalankan query SQL (`count()`) untuk setiap *request*.
- Backend harus menyimpan hasil `count` tersebut dalam Cache (contoh: `Redis`) dengan **TTL 60 detik (1 menit)**.
- Query SQL hanya berjalan ketika cache Redis kedaluwarsa.

### 5.2 Mobile Caching (Flutter SQLite)
- **Modul**: `Dashboard KPI Module` (F1.7)
- **Cache TTL**: 2 Menit (Stale-While-Revalidate).
- Mobile App melakukan fetch ke `/api/public/dashboard/kpi`.
- Hasil disimpan di `dashboard_table.dart`.
- Jika koneksi putus, Mobile merender nilai terakhir dari SQLite beserta badge *"Offline - Pembaruan Terakhir: HH:MM"*.
