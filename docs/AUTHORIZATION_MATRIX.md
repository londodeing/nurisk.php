# AUTHORIZATION_MATRIX.md — NURISK

> **Versi:** 1.0.0  
> **Tanggal:** 2026-06-16  
> **Status:** Final — PRD Freeze  
> **Berlaku untuk:** Laravel Monolith NURISK, Blade SSR, MySQL InnoDB

---

## 1. Konsep Authorization 4 Lapis

Sistem otorisasi NURISK dibangun di atas 4 lapis yang saling melengkapi. Setiap lapis memiliki tanggung jawab yang berbeda dan tidak boleh dipertukarkan fungsinya.

```
┌─────────────────────────────────────────────────────────┐
│  LAPIS 1: Role Global          (auth_roles)             │
│  WHO are you in the system?                             │
├─────────────────────────────────────────────────────────┤
│  LAPIS 2: Jabatan Organisasi   (master_jabatan +        │
│           pengguna_jabatan)                             │
│  WHAT is your structural position?                      │
├─────────────────────────────────────────────────────────┤
│  LAPIS 3: Scope Wilayah        (auth_users.             │
│           default_scope_type + default_scope_id)        │
│  WHERE is your data jurisdiction?                       │
├─────────────────────────────────────────────────────────┤
│  LAPIS 4: Assignment Operasional (operasi_penugasan.    │
│           peran_otoritas)                               │
│  WHAT temporary authority do you hold in an incident?   │
└─────────────────────────────────────────────────────────┘
```

---

### Lapis 1: Role Global (`auth_roles`)

Role global adalah identitas permanen pengguna dalam sistem. Didefinisikan di tabel `auth_roles` dan ditetapkan ke pengguna via tabel `model_has_roles` (Spatie Laravel Permission). Role bersifat **frozen** — tidak boleh ditambah, dikurangi, atau dimodifikasi tanpa persetujuan arsitektural.

| `auth_roles.name` | `level_otoritas` | Deskripsi |
|---|---|---|
| `super_admin` | 1 | IT Support & Full System Control. Tidak ada batasan scope. Akses seluruh data dan konfigurasi sistem. |
| `pwnu` | 2 | Pengguna level PWNU Jawa Tengah. Akses seluruh PCNU se-Jawa Tengah. |
| `pcnu` | 3 | Pengguna level PCNU. Akses dibatasi oleh `auth_users.default_scope_id` (id cabang). |
| `relawan` | 4 | Relawan terverifikasi NU. Akses operasional terbatas pada insiden yang ditugaskan. |
| `publik` | 5 | Masyarakat umum / warga pelapor. Hanya dapat membuat `laporan_kejadian` dan melihat public dashboard. |

> **PENTING:** Jabatan struktural seperti Admin, Komandan TRC, Anggota TRC, dan Operator **bukan role global**. Mereka disimpan di tabel `master_jabatan` dan `pengguna_jabatan`. Role global hanya menjawab "siapa kamu di sistem?", bukan "apa jabatanmu di organisasi?".

---

### Lapis 2: Jabatan Organisasi (`master_jabatan` + `pengguna_jabatan`)

Jabatan adalah posisi struktural dalam organisasi yang bersifat dinamis — dapat berubah tanpa mengubah role global pengguna.

**Tabel `master_jabatan`** menyimpan master daftar jabatan:
- Pimpinan
- Admin
- Komandan TRC
- Anggota TRC
- Operator
- dst.

**Tabel `pengguna_jabatan`** menyimpan relasi pengguna ↔ jabatan beserta periode aktifnya:
- Jabatan aktif ditentukan berdasarkan kolom tanggal aktif (harus dalam rentang waktu aktif)
- Satu pengguna dapat memiliki riwayat jabatan berbeda
- Jabatan digunakan untuk menentukan siapa yang berwenang menandatangani dokumen (`operasi_surat_keluar`, `dokumen_surat_paraf`, `dokumen_surat_tembusan`)
- Data jabatan aktif pengguna digunakan di `master_jabatan_penandatangan`

**Relasi dengan otorisasi:**
- Jabatan **tidak menggantikan** role global dalam gate/policy
- Jabatan digunakan sebagai kondisi tambahan, misalnya: hanya pengguna dengan jabatan aktif tertentu yang boleh menandatangani surat
- Policy cek jabatan dilakukan via query ke `pengguna_jabatan` dengan filter tanggal aktif

---

### Lapis 3: Scope Wilayah (`auth_users.default_scope_type` + `auth_users.default_scope_id`)

Scope wilayah menentukan jurisdiksi data pengguna. Setiap query yang menyentuh data insiden, logistik, aset, relawan, dan assessment **wajib** difilter berdasarkan scope ini.

| `default_scope_type` | Keterangan |
|---|---|
| `pwnu` | Lingkup provinsi PWNU Jawa Tengah |
| `pcnu` | Lingkup cabang PCNU tertentu |
| `mwc` | Lingkup Majelis Wakil Cabang |
| `ranting` | Lingkup ranting |
| `lembaga` | Lingkup lembaga khusus |
| `banom` | Lingkup badan otonom NU |

**Aturan scope per role:**

| Role | Perilaku Scope |
|---|---|
| `super_admin` | Tidak ada batasan scope. Akses semua data. |
| `pwnu` | Akses semua data seluruh PCNU di Jawa Tengah (semua `id_pcnu`). |
| `pcnu` | Hanya akses data dengan `id_pcnu = auth_users.default_scope_id`. |
| `relawan` | Akses data terbatas pada insiden di mana ia memiliki entri aktif di `operasi_penugasan`. |
| `publik` | Akses hanya pada endpoint publik dan data milik sendiri (`laporan_kejadian` yang ia buat). |

**Validasi scope di Policy:**
```php
// Contoh validasi scope PCNU di InsidenPolicy
public function view(User $user, OperasiInsiden $insiden): bool
{
    if ($user->hasRole('super_admin') || $user->hasRole('pwnu')) {
        return true;
    }

    if ($user->hasRole('pcnu')) {
        return $insiden->id_pcnu === $user->default_scope_id;
    }

    if ($user->hasRole('relawan')) {
        return OperasiPenugasan::where('id_insiden', $insiden->id)
            ->where('id_pengguna', $user->id)
            ->whereNull('waktu_selesai')
            ->exists();
    }

    return false;
}
```

---

### Lapis 4: Assignment Operasional (`operasi_penugasan.peran_otoritas`)

Lapis ini memberikan **otoritas sementara** kepada pengguna selama insiden aktif. Otoritas ini incident-based dan berakhir otomatis saat kolom `operasi_penugasan.waktu_selesai` diisi.

| `peran_otoritas` (ENUM) | Deskripsi |
|---|---|
| `komandan_insiden` | Kendali penuh atas operasi insiden yang bersangkutan |
| `trc` | Tim Reaksi Cepat — akses lapangan dan assessment |
| `relawan` | Relawan umum — akses terbatas ke jurnal dan sitrep |
| `medis` | Klaster kesehatan — akses assessment medis dan klaster |
| `logistik` | Klaster logistik — akses mutasi dan permintaan logistik |
| `operator` | Operator sistem lapangan — akses data entry operasional |

**Sifat otoritas ini:**
- **SEMENTARA** — hanya berlaku selama `waktu_selesai IS NULL` di `operasi_penugasan`
- **INCIDENT-BASED** — terikat pada `id_insiden` di `operasi_penugasan`
- Tidak mempengaruhi `auth_roles` atau scope wilayah permanen pengguna
- Cross-region: relawan dari PCNU-A dapat ditugaskan di insiden PCNU-B tanpa mengubah `auth_users.id_unit`

**Cek assignment aktif di Policy:**
```php
protected function getActiveAssignment(User $user, int $idInsiden): ?OperasiPenugasan
{
    return OperasiPenugasan::where('id_insiden', $idInsiden)
        ->where('id_pengguna', $user->id)
        ->whereNull('waktu_selesai')
        ->first();
}
```

---

## 2. Matrix Akses Lengkap

Keterangan kolom:
- **✔** — Diizinkan tanpa kondisi tambahan
- **✘** — Dilarang
- **✔ (scope)** — Diizinkan hanya jika `id_pcnu = auth_users.default_scope_id`
- **✔ (ditugaskan)** — Diizinkan hanya jika ada entri aktif di `operasi_penugasan` untuk insiden terkait
- **✔ (jabatan)** — Diizinkan hanya jika memiliki jabatan aktif yang relevan di `pengguna_jabatan`

| Aksi | `super_admin` | `pwnu` | `pcnu` | `relawan` | `publik` |
|---|:---:|:---:|:---:|:---:|:---:|
| **Pelaporan** | | | | | |
| Buat `laporan_kejadian` | ✔ | ✔ | ✔ | ✔ | ✔ |
| Validasi `laporan_kejadian` | ✔ | ✔ | ✔ | ✘ | ✘ |
| Lihat daftar `laporan_kejadian` | ✔ | ✔ | ✔ (scope) | ✔ (milik sendiri) | ✔ (milik sendiri) |
| **Manajemen Insiden** | | | | | |
| Buat `operasi_insiden` | ✔ | ✔ | ✔ | ✘ | ✘ |
| Edit `operasi_insiden` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Transisi status insiden | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Lihat command center insiden | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| Lihat public dashboard | ✔ | ✔ | ✔ | ✔ | ✔ |
| Eskalasi insiden (`operasi_eskalasi`) | ✔ | ✔ | ✘ | ✘ | ✘ |
| **Assessment** | | | | | |
| Buat `assessment_utama` | ✔ | ✔ | ✔ | ✔ (ditugaskan) | ✘ |
| Edit `assessment_utama` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan, milik sendiri) | ✘ |
| Lihat `assessment_utama` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| Buat `assessment_dampak_manusia` | ✔ | ✔ | ✔ | ✔ (ditugaskan) | ✘ |
| Buat `assessment_kebutuhan_mendesak` | ✔ | ✔ | ✔ | ✔ (ditugaskan) | ✘ |
| **Sitrep** | | | | | |
| Buat `operasi_sitrep` | ✔ | ✔ | ✔ | ✔ (ditugaskan) | ✘ |
| Edit `operasi_sitrep` (status: draft) | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan, milik sendiri) | ✘ |
| Finalisasi sitrep (status: final) | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Lihat `operasi_sitrep` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| **Pleno** | | | | | |
| Buat `operasi_pleno` | ✔ | ✔ | ✔ | ✘ | ✘ |
| Edit `operasi_pleno` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Finalisasi pleno | ✔ | ✔ | ✘ | ✘ | ✘ |
| Buat `operasi_pleno_keputusan` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Lihat `operasi_pleno` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| **Surat Menyurat** | | | | | |
| Buat `operasi_surat_keluar` | ✔ | ✔ | ✔ | ✘ | ✘ |
| Edit `operasi_surat_keluar` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Tanda tangan surat | ✔ | ✔ | ✔ (jabatan) | ✘ | ✘ |
| Tambah `dokumen_surat_paraf` | ✔ | ✔ | ✔ (jabatan) | ✘ | ✘ |
| Tambah `dokumen_surat_tembusan` | ✔ | ✔ | ✔ | ✘ | ✘ |
| **Logistik** | | | | | |
| Lihat `logistik_stok` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| Mutasi logistik (`logistik_mutasi`) | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| Buat `logistik_permintaan` | ✔ | ✔ | ✔ (scope) | ✔ (ditugaskan) | ✘ |
| Approve `logistik_permintaan` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Buat `logistik_perencanaan` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `logistik_gudang` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `logistik_barang_katalog` | ✔ | ✔ | ✘ | ✘ | ✘ |
| **Aset** | | | | | |
| Lihat `aset_unit` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Manage aset (`aset_unit`) | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `aset_master_jenis` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `aset_master_kategori` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `aset_penggunaan` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| **Relawan** | | | | | |
| Daftar relawan (`relawan_pendaftaran`) | ✔ | ✔ | ✔ | ✔ | ✘ |
| Approve relawan | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Penugasan relawan (`operasi_penugasan`) | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Lihat `relawan_penugasan` | ✔ | ✔ | ✔ (scope) | ✔ (milik sendiri) | ✘ |
| **Operasi** | | | | | |
| Kelola `operasi_klaster` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `operasi_klaster_koordinator` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `operasi_posaju` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `operasi_mobilisasi_personil` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Buat `operasi_jurnal` | ✔ | ✔ | ✔ | ✔ (ditugaskan) | ✘ |
| Kelola `operasi_aktivasi` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| **Master Data & Administrasi** | | | | | |
| Kelola `auth_users` | ✔ | ✔ (scope) | ✔ (scope, terbatas) | ✘ | ✘ |
| Kelola `auth_roles` | ✔ | ✘ | ✘ | ✘ | ✘ |
| Kelola `bencana_master_jenis` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `master_surat_template` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `master_satuan` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `master_jabatan` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `pengguna_jabatan` | ✔ | ✔ | ✔ (scope) | ✘ | ✘ |
| Kelola `operasi_master_klaster` | ✔ | ✔ | ✘ | ✘ | ✘ |
| Kelola `operasi_master_indikator` | ✔ | ✔ | ✘ | ✘ | ✘ |

---

## 3. Scope Wilayah Rules

### 3.1 Aturan Dasar

1. **`super_admin`**: Tidak ada filter scope. Semua query tanpa kondisi wilayah.
2. **`pwnu`**: Akses semua `id_pcnu`. Filter: tidak diperlukan filter PCNU spesifik, namun tetap dibatasi pada data dalam lingkup Jawa Tengah.
3. **`pcnu`**: Filter wajib `id_pcnu = auth_users.default_scope_id` pada setiap query.
4. **`relawan`**: Filter berdasarkan `operasi_penugasan.id_pengguna = auth()->id()` dan `waktu_selesai IS NULL`.
5. **`publik`**: Filter berdasarkan `laporan_kejadian.id_pelapor = auth()->id()` (jika terautentikasi) atau token laporan.

### 3.2 Implementasi Scope di Eloquent

```php
// app/Traits/HasWilayahScope.php
trait HasWilayahScope
{
    public function scopeByWilayah(Builder $query, User $user): Builder
    {
        if ($user->hasRole('super_admin') || $user->hasRole('pwnu')) {
            return $query;
        }

        if ($user->hasRole('pcnu')) {
            return $query->where('id_pcnu', $user->default_scope_id);
        }

        // Relawan: scope ditentukan via operasi_penugasan
        if ($user->hasRole('relawan')) {
            $insidenIds = OperasiPenugasan::where('id_pengguna', $user->id)
                ->whereNull('waktu_selesai')
                ->pluck('id_insiden');
            return $query->whereIn('id_insiden', $insidenIds);
        }

        // Publik: tidak ada akses ke query ini
        return $query->whereRaw('1 = 0');
    }
}
```

### 3.3 Validasi Scope di Policy

Setiap Policy **wajib** melakukan validasi scope sebelum mengizinkan akses. Urutan pengecekan:

```
1. Apakah user super_admin?     → return true
2. Apakah user pwnu?            → return true (atau cek kondisi spesifik)
3. Apakah user pcnu?            → cek id_pcnu === default_scope_id
4. Apakah user relawan?         → cek operasi_penugasan aktif
5. Apakah user publik?          → return false (kecuali aksi spesifik)
```

### 3.4 Tabel-Tabel yang Wajib Difilter Scope

| Tabel | Kolom Scope | Kondisi |
|---|---|---|
| `operasi_insiden` | `id_pcnu` | `= auth_users.default_scope_id` |
| `laporan_kejadian` | `id_pcnu` | `= auth_users.default_scope_id` |
| `assessment_utama` | via `id_insiden` → `operasi_insiden.id_pcnu` | join |
| `operasi_sitrep` | via `id_insiden` → `operasi_insiden.id_pcnu` | join |
| `operasi_pleno` | via `id_insiden` → `operasi_insiden.id_pcnu` | join |
| `logistik_stok` | `id_gudang` → `logistik_gudang.id_pcnu` | join |
| `logistik_mutasi` | via `id_gudang` → `logistik_gudang.id_pcnu` | join |
| `logistik_permintaan` | via `id_gudang` → `logistik_gudang.id_pcnu` | join |
| `aset_unit` | `id_pcnu` atau `id_unit` terkait | `= auth_users.default_scope_id` |
| `relawan_pendaftaran` | `id_pcnu` | `= auth_users.default_scope_id` |
| `operasi_penugasan` | via `id_insiden` → `operasi_insiden.id_pcnu` | join |
| `operasi_surat_keluar` | via `id_insiden` | join |

---

## 4. Cross-Region Rules

### 4.1 Definisi Cross-Region

Cross-region terjadi ketika pengguna (relawan atau personil) dari satu PCNU ditugaskan ke insiden yang dikelola oleh PCNU lain.

### 4.2 Aturan Tidak Berubah

- `auth_users.id_unit` **TIDAK BERUBAH** saat cross-region assignment
- `auth_users.default_scope_type` dan `auth_users.default_scope_id` **TIDAK BERUBAH**
- Role global pengguna **TIDAK BERUBAH**
- Organisasi asal relawan tetap tercatat di `auth_users`

### 4.3 Mekanisme Cross-Region

Otoritas lintas wilayah diatur **eksklusif** melalui dua tabel:

**1. `operasi_penugasan`** — Assignment utama relawan ke insiden:
- `id_insiden`: insiden tujuan (dapat berbeda PCNU dengan pengguna)
- `id_pengguna`: id relawan/personil yang ditugaskan
- `peran_otoritas`: enum otoritas dalam insiden tersebut
- `waktu_selesai`: NULL = aktif, diisi = selesai

**2. `operasi_otoritas_kontekstual`** — Otoritas tambahan kontekstual:
- Digunakan untuk memberikan akses spesifik di luar assignment standar
- Berlaku hanya selama insiden berlangsung

**Alur cek cross-region di Policy:**
```php
public function createSitrep(User $user, OperasiInsiden $insiden): bool
{
    // Pengguna dari PCNU berbeda tetap bisa buat sitrep jika ditugaskan
    if ($user->hasRole('relawan')) {
        return OperasiPenugasan::where('id_insiden', $insiden->id)
            ->where('id_pengguna', $user->id)
            ->whereIn('peran_otoritas', ['trc', 'komandan_insiden', 'operator'])
            ->whereNull('waktu_selesai')
            ->exists();
    }

    // PCNU: hanya scope sendiri
    if ($user->hasRole('pcnu')) {
        return $insiden->id_pcnu === $user->default_scope_id;
    }

    return $user->hasRole('super_admin') || $user->hasRole('pwnu');
}
```

### 4.4 Batasan Cross-Region

- Relawan cross-region **TIDAK** dapat mengakses data logistik gudang PCNU lain kecuali eksplisit ada `operasi_penugasan` dengan `peran_otoritas = logistik`
- Relawan cross-region **TIDAK** dapat mengubah status insiden di PCNU tujuan
- Relawan cross-region **TIDAK** dapat membuat `operasi_pleno` di PCNU tujuan

---

## 5. Escalation Authority

### 5.1 Definisi Eskalasi

Eskalasi adalah proses formal di mana PWNU mengambil alih pengelolaan insiden yang sebelumnya berada di bawah kendali PCNU.

### 5.2 Syarat Eskalasi

1. Hanya role `pwnu` (dan `super_admin`) yang dapat memulai eskalasi
2. Eskalasi **wajib** melalui pleno terlebih dahulu — `operasi_eskalasi.id_pleno` tidak boleh NULL
3. Pleno terkait harus berstatus final sebelum eskalasi dapat dikonfirmasi
4. Eskalasi dicatat di tabel `operasi_eskalasi`

### 5.3 Efek Eskalasi

Setelah eskalasi dikonfirmasi:
- Pengguna dengan role `pwnu` mendapat akses penuh ke insiden tersebut (melampaui batasan scope normal PCNU)
- PCNU asal tetap dapat melihat data insiden (read-only) setelah eskalasi
- Status insiden dapat diubah oleh `pwnu` tanpa batasan scope
- Semua aksi tulis di insiden yang sudah dieskalasi hanya bisa dilakukan oleh `pwnu` dan `super_admin`

### 5.4 Cek Eskalasi di Policy

```php
protected function isEscalated(OperasiInsiden $insiden): bool
{
    return OperasiEskalasi::where('id_insiden', $insiden->id)
        ->whereNotNull('id_pleno')
        ->where('status', 'dikonfirmasi')
        ->exists();
}

public function update(User $user, OperasiInsiden $insiden): bool
{
    if ($user->hasRole('super_admin')) return true;
    if ($user->hasRole('pwnu')) return true;

    if ($this->isEscalated($insiden)) {
        // Insiden sudah dieskalasi ke PWNU, PCNU hanya bisa lihat
        return false;
    }

    return $user->hasRole('pcnu') && $insiden->id_pcnu === $user->default_scope_id;
}
```

---

## 6. Implementasi Laravel

### 6.1 Policy

Setiap domain model wajib memiliki Policy yang terdaftar di `AuthServiceProvider`. Policy adalah lokasi tunggal untuk logika otorisasi — **tidak boleh ada logika otorisasi di Controller atau Service**.

**Daftar Policy wajib:**

| Policy | Model | Keterangan |
|---|---|---|
| `InsidenPolicy` | `OperasiInsiden` | Akses, edit, transisi status, eskalasi |
| `SitrepPolicy` | `OperasiSitrep` | Buat, edit, finalisasi |
| `PlanoPolicy` | `OperasiPleno` | Buat, edit, finalisasi |
| `SuratPolicy` | `DokumenSuratUtama` | Buat, tanda tangan, paraf |
| `LogistikPolicy` | `LogistikMutasi`, `LogistikPermintaan` | Mutasi, approve |
| `AsetPolicy` | `AsetUnit` | CRUD aset unit |
| `AssessmentPolicy` | `AssessmentUtama` | Buat, edit assessment |
| `RelawanPolicy` | `RelawanPendaftaran` | Approve, penugasan |
| `LaporanPolicy` | `LaporanKejadian` | Buat, validasi |
| `OperasiPenugasanPolicy` | `OperasiPenugasan` | Buat, tutup penugasan |

**Struktur Policy standar:**

```php
<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Operasi\OperasiInsiden;
use App\Models\Operasi\OperasiPenugasan;
use App\Models\Operasi\OperasiEskalasi;

class InsidenPolicy
{
    /**
     * Super admin bypass semua cek.
     * Gunakan fitur before() agar tidak perlu mengulang di setiap method.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null; // Lanjut ke method spesifik
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['pwnu', 'pcnu', 'relawan']);
    }

    public function view(User $user, OperasiInsiden $insiden): bool
    {
        if ($user->hasRole('pwnu')) return true;

        if ($user->hasRole('pcnu')) {
            return $insiden->id_pcnu === $user->default_scope_id;
        }

        if ($user->hasRole('relawan')) {
            return OperasiPenugasan::where('id_insiden', $insiden->id)
                ->where('id_pengguna', $user->id)
                ->whereNull('waktu_selesai')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['pwnu', 'pcnu']);
    }

    public function update(User $user, OperasiInsiden $insiden): bool
    {
        if ($user->hasRole('pwnu')) return true;

        if ($this->isEscalated($insiden)) return false;

        return $user->hasRole('pcnu') && $insiden->id_pcnu === $user->default_scope_id;
    }

    public function escalate(User $user, OperasiInsiden $insiden): bool
    {
        return $user->hasRole('pwnu');
    }

    protected function isEscalated(OperasiInsiden $insiden): bool
    {
        return OperasiEskalasi::where('id_insiden', $insiden->id)
            ->where('status', 'dikonfirmasi')
            ->exists();
    }
}
```

### 6.2 Gate

Gate digunakan untuk aksi spesifik yang tidak terikat langsung pada satu Model.

```php
// app/Providers/AuthServiceProvider.php

Gate::define('finalize-sitrep', function (User $user, OperasiSitrep $sitrep) {
    if ($user->hasRole('super_admin') || $user->hasRole('pwnu')) return true;
    return $user->hasRole('pcnu') 
        && $sitrep->insiden->id_pcnu === $user->default_scope_id;
});

Gate::define('escalate-insiden', function (User $user) {
    return $user->hasRole('pwnu');
});

Gate::define('finalize-pleno', function (User $user, OperasiPleno $pleno) {
    return $user->hasRole('super_admin') || $user->hasRole('pwnu');
});

Gate::define('approve-logistik', function (User $user, LogistikPermintaan $permintaan) {
    if ($user->hasRole('super_admin') || $user->hasRole('pwnu')) return true;
    return $user->hasRole('pcnu')
        && $permintaan->gudang->id_pcnu === $user->default_scope_id;
});

Gate::define('sign-surat', function (User $user, DokumenSuratUtama $surat) {
    // Harus memiliki jabatan aktif yang sesuai
    return PenggunaJabatan::where('id_pengguna', $user->id)
        ->where('is_aktif', true)
        ->whereIn('id_jabatan', $surat->jabatanPenandatangan->pluck('id_jabatan'))
        ->exists();
});
```

### 6.3 Middleware

```php
// routes/web.php

// Route admin sistem (super_admin only)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::resource('master-roles', MasterRoleController::class);
    Route::resource('master-template', MasterTemplateController::class);
});

// Route PWNU (pwnu + super_admin)
Route::middleware(['auth', 'role:super_admin,pwnu'])->prefix('pwnu')->group(function () {
    Route::resource('insiden', InsidenController::class);
    Route::post('insiden/{insiden}/eskalasi', [EskalasiController::class, 'store']);
    Route::resource('pleno', PlanoController::class);
});

// Route PCNU (pcnu + pwnu + super_admin)
Route::middleware(['auth', 'role:super_admin,pwnu,pcnu'])->prefix('pcnu')->group(function () {
    Route::resource('insiden', InsidenController::class);
    Route::resource('logistik', LogistikController::class);
    Route::resource('aset', AsetController::class);
    Route::resource('relawan', RelawanController::class);
});

// Route relawan (relawan + pcnu + pwnu + super_admin)
Route::middleware(['auth', 'role:super_admin,pwnu,pcnu,relawan'])->prefix('operasional')->group(function () {
    Route::resource('assessment', AssessmentController::class);
    Route::resource('sitrep', SitrepController::class);
    Route::resource('jurnal', JurnalController::class);
});

// Route publik (terautentikasi, semua role)
Route::middleware(['auth'])->group(function () {
    Route::resource('laporan', LaporanKejadianController::class)->only(['create', 'store', 'show']);
});

// Route publik (tanpa auth)
Route::get('/dashboard', [PublicDashboardController::class, 'index'])->name('dashboard.public');
```

### 6.4 FormRequest Authorization

Setiap FormRequest **wajib** mengimplementasikan method `authorize()`. Dilarang mengembalikan `true` tanpa logika otorisasi.

```php
<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreSitrepRequest extends FormRequest
{
    /**
     * WAJIB: authorize() harus berisi logika nyata.
     * DILARANG: return true; tanpa kondisi.
     */
    public function authorize(): bool
    {
        $insiden = $this->route('insiden');

        return $this->user()->can('createSitrep', $insiden);
    }

    public function rules(): array
    {
        return [
            'id_insiden'      => ['required', 'exists:operasi_insiden,id'],
            'periode'         => ['required', 'string'],
            'narasi_situasi'  => ['required', 'string'],
            'status'          => ['required', 'in:draft,ditinjau,final'],
        ];
    }
}
```

---

## 7. Aturan Keras

### 7.1 DILARANG

| Larangan | Alasan |
|---|---|
| `if ($user->role == 'admin')` di Controller | Role harus dicek via Spatie `hasRole()`, bukan kolom langsung |
| `return true;` tanpa kondisi di `authorize()` | Bypass otorisasi menciptakan celah keamanan |
| Membuat role baru di luar 5 role PRD | Role sudah di-freeze, penambahan harus melalui review arsitektural |
| Bypass authorization dengan comment `// TODO: add auth later` | Authorization bukan fitur opsional, wajib ada sejak awal |
| Logika scope di Controller | Scope harus di Policy atau Eloquent Scope, bukan di Controller |
| Akses langsung `auth_users.id_peran` sebagai string untuk cek role | Gunakan `$user->hasRole()` dari Spatie |
| Menghapus/menonaktifkan middleware auth untuk "kemudahan testing" | Gunakan factory user dengan role yang sesuai untuk testing |

### 7.2 WAJIB

| Kewajiban | Implementasi |
|---|---|
| `$this->authorize('aksi', $model)` di setiap method Controller yang menulis data | Di atas semua logika bisnis |
| Policy untuk setiap Model yang memiliki aksi tulis | Terdaftar di `AuthServiceProvider` |
| Scope validation di setiap query yang menyentuh data wilayah | Via Eloquent Scope atau kondisi eksplisit di Policy |
| Gunakan `before()` di Policy untuk `super_admin` bypass | Agar tidak mengulang cek di setiap method |
| Cek `waktu_selesai IS NULL` di setiap cek assignment relawan | Assignment yang sudah selesai tidak memberikan otoritas |
| Catat semua aksi eskalasi di `operasi_eskalasi` | Audit trail eskalasi wajib ada |
| Validasi jabatan aktif via `pengguna_jabatan` untuk aksi tanda tangan | Jabatan yang sudah tidak aktif tidak berwenang |

### 7.3 Pola Implementasi Controller yang Benar

```php
// ✔ BENAR: authorize sebelum logika bisnis
public function store(StoreSitrepRequest $request): RedirectResponse
{
    // authorize() sudah dijalankan via FormRequest
    // Jika perlu cek tambahan:
    $insiden = OperasiInsiden::findOrFail($request->id_insiden);
    $this->authorize('createSitrep', $insiden);

    $sitrep = OperasiSitrep::create($request->validated());

    return redirect()->route('sitrep.show', $sitrep)->with('success', 'Sitrep berhasil dibuat.');
}

// ✘ SALAH: cek role langsung di controller
public function store(Request $request): RedirectResponse
{
    if (auth()->user()->role === 'pcnu') { // DILARANG
        // ...
    }
    // ...
}

// ✘ SALAH: skip authorization
public function store(Request $request): RedirectResponse
{
    // TODO: tambahkan auth nanti  // DILARANG
    $sitrep = OperasiSitrep::create($request->all());
    return redirect()->back();
}
```

---

## 8. Referensi Tabel Kritis

| Tabel | Peran dalam Otorisasi |
|---|---|
| `auth_roles` | Master role global (5 role freeze) |
| `auth_users` | `default_scope_type`, `default_scope_id` untuk scope wilayah |
| `model_has_roles` | Relasi pengguna ↔ role (Spatie) |
| `model_has_permissions` | Permission granular jika diperlukan (Spatie) |
| `master_jabatan` | Master jabatan struktural organisasi |
| `pengguna_jabatan` | Relasi pengguna ↔ jabatan + periode aktif |
| `operasi_penugasan` | Assignment operasional + `peran_otoritas` (Lapis 4) |
| `operasi_otoritas_kontekstual` | Otoritas tambahan kontekstual per insiden |
| `operasi_eskalasi` | Rekam jejak eskalasi insiden PCNU → PWNU |

---

*Dokumen ini adalah referensi teknikal final untuk implementasi otorisasi sistem NURISK. Setiap deviasi dari dokumen ini harus melalui review arsitektural dan dicatat sebagai RFC (Request for Change).*
