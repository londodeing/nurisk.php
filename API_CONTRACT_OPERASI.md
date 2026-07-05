# Kontrak API Domain Operasi (Frozen)

Dokumen ini mendefinisikan secara resmi kontrak JSON API untuk domain **Operasi** (Pos Aju, Klaster, dan Tugas) di sistem NURISK. 

> [!IMPORTANT]
> Kontrak ini bersifat **FROZEN** (beku). Seluruh field di bawah ini tidak boleh diubah, dihapus, atau diganti namanya tanpa melalui mekanisme Architecture Decision Record (ADR) atau persetujuan tertulis resmi.

---

## 1. Pos Aju (Posaju)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 1,
  "nama": "Pos Aju Utama",
  "status": "aktif",
  "insiden": {
    "id": 5,
    "nama": "INS-2026-001"
  },
  "koordinat": {
    "lat": -6.12345678,
    "lng": 106.12345678
  },
  "alamat_lokasi": "Jalan Raya Jepara No. 12",
  "penanggung_jawab": {
    "id": 2
  },
  "waktu_diaktifkan": "2026-06-16T13:46:53Z",
  "diperpanjang_hingga": "2026-06-23T13:46:53Z",
  "waktu_ditutup": null,
  "alasan_penutupan": null,
  "dibuat_pada": "2026-06-16T13:46:53Z"
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Pos Aju (`id_posaju`) | Primary Key |
| `nama` | String / Null | Nama Posko Lapangan | Diambil dari `nama_posaju` |
| `status` | String | Status alur pos komando lapangan | ENUM: `direncanakan`, `aktif`, `diperpanjang`, `ditutup` |
| `insiden` | Object / Null | Relasi insiden kebencanaan terkait | Eager-loaded (opsional) |
| `koordinat` | Object | Representasi latitude & longitude | Memiliki sub-field `lat` dan `lng` |
| `alamat_lokasi` | String / Null | Alamat lengkap fisik pos komando | |
| `penanggung_jawab`| Object / Null | Pj Pos Aju (`pj_posaju`) | Eager-loaded (opsional), memuat `id` pengguna |
| `waktu_diaktifkan`| ISO-8601 String | Waktu aktivasi Pos Aju | Format UTC/ISO-8601 |
| `diperpanjang_hingga`| ISO-8601 String | Batas akhir waktu perpanjangan | Format UTC/ISO-8601 |
| `waktu_ditutup` | ISO-8601 String | Waktu penutupan resmi Pos Aju | Format UTC/ISO-8601 |
| `alasan_penutupan`| String / Null | Alasan penutupan Pos Aju | Tampil hanya jika status = `ditutup` |
| `dibuat_pada` | ISO-8601 String | Waktu record dibuat | Format UTC/ISO-8601 |

---

## 2. Klaster (Klaster)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 1,
  "nama": "Klaster Penyelamatan / TRC",
  "status": "aktif",
  "prioritas": "tinggi",
  "progress": 65.5,
  "dibutuhkan": true,
  "target_cakupan": "Seluruh kecamatan terdampak",
  "indikator_keberhasilan": "Distribusi merata",
  "insiden": {
    "id": 5,
    "nama": "INS-2026-001"
  },
  "waktu_aktivasi": "2026-06-16T13:46:53Z"
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Operasi Klaster (`id_operasi_klaster`) | Primary Key |
| `nama` | String | Nama Klaster Kebencanaan NU | Ditranslasikan otomatis dari `id_klaster` (1-6) |
| `status` | String | Status operasional klaster | ENUM: `nonaktif`, `aktif`, `selesai` |
| `prioritas` | String | Skala prioritas respon klaster | ENUM: `rendah`, `sedang`, `tinggi`, `kritis` |
| `progress` | Float | Progres kinerja dalam persen | Range: `0.0` s.d `100.0` |
| `dibutuhkan` | Boolean | Apakah klaster ini aktif/dibutuhkan | |
| `target_cakupan` | String / Null | Rencana target geografis/wilayah | |
| `indikator_keberhasilan` | String / Null | Parameter keberhasilan klaster | |
| `insiden` | Object / Null | Relasi insiden terkait | Eager-loaded (opsional) |
| `waktu_aktivasi` | ISO-8601 String | Waktu klaster diaktifkan | Format UTC/ISO-8601 |

---

## 3. Tugas (Tugas)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 1,
  "judul": "Membagikan logistik beras",
  "status": "rencana",
  "target_indikator": "100 KK terlayani",
  "progress": 0.0,
  "klaster": {
    "id": 1,
    "status": "aktif",
    "prioritas": "tinggi"
  },
  "posaju": {
    "id": 1,
    "nama": "Pos Aju Utama"
  },
  "pelaksana": {
    "id": 2
  },
  "dibuat_pada": "2026-06-16T13:46:53Z"
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Tugas (`id_tugas`) | Primary Key |
| `judul` | String | Deskripsi singkat penugasan | Diambil dari `judul_tugas` |
| `status` | String | Status pengerjaan tugas | ENUM: `rencana`, `berjalan`, `tertunda`, `selesai` |
| `target_indikator`| String / Null | Parameter target tugas | |
| `progress` | Float | Progres pengerjaan tugas | Range: `0.0` s.d `100.0` |
| `klaster` | Object / Null | Relasi klaster operasional penaung | Eager-loaded (opsional) |
| `posaju` | Object / Null | Relasi Pos Aju tempat koordinasi | Eager-loaded (opsional) |
| `pelaksana` | Object / Null | Relasi ke user pelaksana tugas | Eager-loaded (opsional) |
| `dibuat_pada` | ISO-8601 String | Waktu tugas dideklarasikan | Format UTC/ISO-8601 |
