# Kontrak API Domain Relawan (Frozen)

Dokumen ini mendefinisikan secara resmi kontrak JSON API untuk domain **Relawan** (Pendaftaran, Penugasan, dan Profil) di sistem NURISK.

> [!IMPORTANT]
> Kontrak ini bersifat **FROZEN** (beku). Seluruh field di bawah ini tidak boleh diubah, dihapus, atau diganti namanya tanpa melalui mekanisme Architecture Decision Record (ADR) atau persetujuan tertulis resmi.

---

## 1. Pendaftaran Relawan (Pendaftaran)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 1,
  "status": "seleksi",
  "motivasi": "Ingin mengabdi untuk kemanusiaan",
  "catatan_verifikator": null,
  "waktu_daftar": "2026-06-16T13:46:53Z",
  "waktu_penugasan_dimulai": null,
  "waktu_penugasan_selesai": null,
  "kebutuhan": {
    "id": 2,
    "judul_posisi": "Evakuator TRC"
  },
  "relawan": {
    "id": 3,
    "nama": "Ahmad Relawan"
  }
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Pendaftaran (`id_pendaftaran`) | Primary Key |
| `status` | String | Status pendaftaran rekrutmen | ENUM: `dibuka`, `seleksi`, `diterima`, `ditugaskan`, `selesai`, `ditolak` |
| `motivasi` | String / Null | Motivasi singkat relawan mendaftar | Diambil dari `motivasi_singkat` |
| `catatan_verifikator` | String / Null | Catatan dari verifikator penyeleksi | |
| `waktu_daftar` | ISO-8601 String | Waktu pendaftaran dilakukan | Format UTC/ISO-8601 |
| `waktu_penugasan_dimulai` | ISO-8601 String / Null | Waktu mulai aktif penugasan | Format UTC/ISO-8601 |
| `waktu_penugasan_selesai` | ISO-8601 String / Null | Waktu selesai penugasan | Format UTC/ISO-8601 |
| `kebutuhan` | Object / Null | Rujukan kebutuhan operasional | Eager-loaded (opsional) |
| `relawan` | Object / Null | Pengguna relawan yang mendaftar | Eager-loaded (opsional) |

---

## 2. Penugasan Relawan (Penugasan)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 1,
  "peran": "Dapur Umum",
  "status_aktif": true,
  "tgl_mulai": "2026-06-17",
  "tgl_selesai": null,
  "posaju": {
    "id": 1,
    "nama": "Pos Aju Relawan"
  }
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Penugasan Relawan (`id_penugasan_relawan`) | Primary Key |
| `peran` | String / Null | Peran lapangan relawan | Diambil dari `peran_lapangan` |
| `status_aktif` | Boolean | Status penugasan aktif atau selesai | `true` (aktif) atau `false` (selesai) |
| `tgl_mulai` | Date String | Tanggal mulai aktif bertugas | Format `YYYY-MM-DD` |
| `tgl_selesai` | Date String / Null | Tanggal selesai aktif bertugas | Format `YYYY-MM-DD` |
| `posaju` | Object / Null | Pos Aju tempat relawan ditugaskan | Eager-loaded (opsional) |

---

## 3. Profil Relawan (Profil)

### Struktur JSON Response (Single Resource)

```json
{
  "id": 3,
  "nik": "3201010101010001",
  "nama": "Ahmad Relawan",
  "email": "ahmad@relawan.nu",
  "id_desa_domisili": "3201010001",
  "keahlian": [
    {
      "id": 1,
      "nama": "Navigasi Darat"
    },
    {
      "id": 2,
      "nama": "P3K"
    }
  ]
}
```

### Detail Field

| Field Name | Tipe Data | Deskripsi | Constraint / Nilai |
| :--- | :--- | :--- | :--- |
| `id` | Integer | ID Pengguna (`id_pengguna`) | Primary Key (sama dengan ID user) |
| `nik` | String / Null | Nomor Induk Kependudukan | Maks 20 karakter |
| `nama` | String | Nama lengkap relawan | Diambil dari `nama_lengkap` |
| `email` | String | Email kontak relawan | |
| `id_desa_domisili` | String / Null | Kode wilayah desa domisili | Kode BPS / Kemendagri 10 digit |
| `keahlian` | Array of Objects | Daftar keahlian tersinkronisasi | Diambil dari pivot keahlian |
