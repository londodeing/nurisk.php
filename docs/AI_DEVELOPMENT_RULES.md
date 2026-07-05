# AI_DEVELOPMENT_RULES.md — NURISK

> **Versi:** 1.0.0
> **Tanggal:** 2026-06-16
> **Status:** FREEZE — Tidak boleh diubah tanpa persetujuan Lead Engineer

---

## ⛔ PERINGATAN KERAS

Dokumen ini adalah **panduan wajib** untuk semua AI coding agent yang bekerja di proyek NURISK.
**Melanggar aturan di dokumen ini akan menghasilkan kode yang tidak kompatibel dengan sistem produksi dan HARUS ditulis ulang.**

Tidak ada pengecualian. Tidak ada improvisasi. Tidak ada "pendekatan alternatif yang lebih baik."

---

## 1. Kewajiban Sebelum Coding

Setiap AI agent **WAJIB** membaca dokumen berikut sebelum menulis satu baris kode pun:

| Urutan | Dokumen | Isi |
|--------|---------|-----|
| 1 | `SYSTEM_ARCHITECTURE.md` | Arsitektur yang difreeze (Laravel Monolith, Blade SSR, Bootstrap 5.3) |
| 2 | `DATABASE_CONVENTION.md` | Naming convention dan seluruh enum yang difreeze |
| 3 | `DOMAIN_RULES.md` | Aturan bisnis per domain (operasi, logistik, assessment, dll.) |
| 4 | `STATE_MACHINE.md` | Workflow status yang difreeze, transisi yang diizinkan |
| 5 | `AUTHORIZATION_MATRIX.md` | Matrix otorisasi per role dan per aksi |
| 6 | `MODULE_IMPLEMENTATION_ORDER.md` | Urutan modul yang harus diimplementasi |

**Tidak ada pengecualian.** Jika salah satu dokumen tidak tersedia, hentikan coding dan minta dokumen tersebut.

---

## 2. Aturan Naming WAJIB

### 2.1 Nama Tabel

Nama tabel **HARUS** mengikuti SQL dump secara verbatim — `snake_case`, Bahasa Indonesia.

| ✅ BENAR | ❌ SALAH |
|---------|---------|
| `operasi_insiden` | `incidents` |
| `logistik_stok` | `logistic_stocks` |
| `assessment_utama` | `AssessmentMain` |
| `auth_users` | `users` |
| `operasi_sitrep` | `situation_reports` |
| `laporan_kejadian` | `event_reports` |
| `operasi_surat_keluar` | `letters` |
| `relawan_pendaftaran` | `volunteer_recruitments` |

### 2.2 Nama Kolom

Nama kolom **HARUS** mengikuti SQL dump. Tidak ada translasi ke bahasa Inggris.

| ✅ BENAR | ❌ SALAH |
|---------|---------|
| `id_insiden` | `incident_id` |
| `status_insiden` | `status` |
| `dibuat_pada` | `created_at` |
| `diperbarui_pada` | `updated_at` |
| `dihapus_pada` | `deleted_at` |
| `id_pengguna` | `user_id` |
| `id_pcnu` | `pcnu_id` |
| `nama_bencana` | `disaster_name` |

### 2.3 Referensi Tabel Utama

Daftar tabel yang diakui dalam sistem NURISK (diambil dari SQL dump):

```
aset_master_jenis, aset_master_kategori, aset_master_status, aset_penggunaan, aset_unit
assessment_dampak_manusia, assessment_kebutuhan_mendesak, assessment_utama
auth_keahlian_master, auth_pengguna_keahlian, auth_pengguna_profil, auth_roles, auth_users
bencana_master_jenis
cache, cache_locks
dokumen_surat_paraf, dokumen_surat_tembusan, operasi_surat_keluar
failed_jobs, jobs, job_batches
master_jabatan, pengguna_jabatan
laporan_kejadian
logistik_barang_katalog, logistik_gudang, logistik_kategori, logistik_mutasi,
  logistik_perencanaan, logistik_permintaan, logistik_stok
master_jabatan_penandatangan, master_penerima_manfaat, master_satuan,
  master_surat_jenis, master_surat_template
migrations, model_has_permissions, model_has_roles
operasi_aktivasi, operasi_eskalasi, operasi_insiden, operasi_jurnal,
  operasi_klaster, operasi_klaster_koordinator
operasi_master_indikator, operasi_master_klaster, operasi_mobilisasi_personil,
  operasi_otoritas_kontekstual
operasi_penugasan, operasi_periode, operasi_pleno, operasi_pleno_keputusan,
  operasi_pleno_peserta, operasi_posaju, operasi_posaju_komandan
operasi_sitrep, riwayat_status_insiden
relawan_pendaftaran, relawan_penugasan
sistem_*
```

Tabel di luar daftar ini **TIDAK BOLEH** dibuat tanpa persetujuan eksplisit.

---

## 3. Aturan Model Laravel

### 3.1 Penamaan Model

Konversi nama tabel ke PascalCase, buang prefix ganda yang tidak relevan:

| Tabel | Model |
|-------|-------|
| `operasi_insiden` | `OperasiInsiden` |
| `logistik_stok` | `LogistikStok` |
| `assessment_utama` | `AssessmentUtama` |
| `auth_users` | `AuthUser` |
| `operasi_sitrep` | `OperasiSitrep` |
| `operasi_pleno` | `OperasiPleno` |
| `operasi_surat_keluar` | `DokumenSuratUtama` |
| `laporan_kejadian` | `LaporanKejadian` |
| `logistik_mutasi` | `LogistikMutasi` |
| `relawan_pendaftaran` | `RelawanPendaftaran` |
| `bencana_master_jenis` | `BencanaMasterJenis` |
| `riwayat_status_insiden` | `RiwayatStatusInsiden` |

### 3.2 Deklarasi Wajib di Setiap Model

Setiap Model **WAJIB** mendeklarasikan semua properti berikut secara eksplisit:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NamaTabelModel extends Model
{
    // [WAJIB] Nama tabel nyata — jangan pernah andalkan konvensi default Laravel
    protected $table = 'nama_tabel_nyata';

    // [WAJIB] Primary key eksplisit — tidak menggunakan 'id' default
    protected $primaryKey = 'id_nama';

    // [WAJIB] Timestamp non-standar NURISK
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    // [WAJIB jika ada soft delete] Gunakan SoftDeletes dan deklarasikan kolom
    use SoftDeletes;
    const DELETED_AT = 'dihapus_pada';

    // [WAJIB] Fillable — semua kolom yang boleh diisi via create()/fill()
    protected $fillable = [
        'kolom_satu',
        'kolom_dua',
        // ...
    ];

    // [WAJIB] Cast untuk kolom non-string
    protected $casts = [
        'kolom_boolean' => 'boolean',
        'kolom_datetime' => 'datetime',
        'kolom_json' => 'array',
    ];
}
```

### 3.3 Contoh Lengkap: OperasiInsiden

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasiInsiden extends Model
{
    use SoftDeletes;

    protected $table = 'operasi_insiden';
    protected $primaryKey = 'id_insiden';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'kode_kejadian',
        'id_laporan_asal',
        'id_jenis_bencana',
        'id_pcnu',
        'status_insiden',
        'status_operasi',
        'prioritas',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'is_locked'     => 'boolean',
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
    ];
}
```

### 3.4 Contoh Lengkap: LogistikStok

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogistikStok extends Model
{
    // Tidak ada soft delete pada tabel ini — jangan pakai SoftDeletes
    protected $table = 'logistik_stok';
    protected $primaryKey = 'id_stok';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_barang',
        'id_gudang',
        'jumlah_tersedia',
        'jumlah_minimum',
        'satuan',
    ];

    protected $casts = [
        'jumlah_tersedia' => 'integer',
        'jumlah_minimum'  => 'integer',
    ];
}
```

---

## 4. Aturan Relasi Eloquent

### 4.1 Kewajiban Deklarasi Relasi

Setiap relasi **WAJIB** mendeklarasikan foreign key dan local key secara eksplisit. Jangan pernah mengandalkan konvensi default Laravel karena nama kolom NURISK tidak mengikuti konvensi Laravel.

```php
// ❌ SALAH — mengandalkan konvensi default
public function sitreps()
{
    return $this->hasMany(OperasiSitrep::class);
}

// ✅ BENAR — FK dan local key eksplisit
public function sitreps()
{
    return $this->hasMany(OperasiSitrep::class, 'id_insiden', 'id_insiden');
}
```

### 4.2 Contoh Relasi di OperasiInsiden

```php
// Di Model OperasiInsiden

public function sitreps()
{
    return $this->hasMany(OperasiSitrep::class, 'id_insiden', 'id_insiden');
}

public function assessments()
{
    return $this->hasMany(AssessmentUtama::class, 'id_insiden', 'id_insiden');
}

public function jenisBencana()
{
    return $this->belongsTo(BencanaMasterJenis::class, 'id_jenis_bencana', 'id_jenis');
}

public function riwayatStatus()
{
    return $this->hasMany(RiwayatStatusInsiden::class, 'id_insiden', 'id_insiden');
}

public function jurnal()
{
    return $this->hasMany(OperasiJurnal::class, 'id_insiden', 'id_insiden');
}

public function penugasan()
{
    return $this->hasMany(OperasiPenugasan::class, 'id_insiden', 'id_insiden');
}

public function periode()
{
    return $this->hasOne(OperasiPeriode::class, 'id_insiden', 'id_insiden');
}

public function pleno()
{
    return $this->hasMany(OperasiPleno::class, 'id_insiden', 'id_insiden');
}
```

### 4.3 Contoh Relasi di LogistikMutasi

```php
// Di Model LogistikMutasi

public function barang()
{
    return $this->belongsTo(LogistikBarangKatalog::class, 'id_barang', 'id_barang');
}

public function gudang()
{
    return $this->belongsTo(LogistikGudang::class, 'id_gudang', 'id_gudang');
}

public function pengguna()
{
    return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id');
}
```

---

## 5. Aturan Policy dan Authorization

### 5.1 Prinsip Dasar

- **WAJIB:** Setiap Model HARUS punya Policy yang terdaftar di `AuthServiceProvider`
- **WAJIB:** Setiap method controller HARUS memanggil `$this->authorize()`
- **DILARANG:** `if ($user->id_peran == X)` langsung di controller
- **DILARANG:** Bypass authorization dengan alasan apapun

### 5.2 Registrasi Policy

```php
// app/Providers/AuthServiceProvider.php

protected $policies = [
    OperasiInsiden::class    => OperasiInsidenPolicy::class,
    LogistikStok::class      => LogistikStokPolicy::class,
    AssessmentUtama::class   => AssessmentUtamaPolicy::class,
    OperasiSitrep::class     => OperasiSitrepPolicy::class,
    LaporanKejadian::class   => LaporanKejadianPolicy::class,
    // ... semua model wajib terdaftar
];
```

### 5.3 Contoh Policy: OperasiInsidenPolicy

```php
<?php

namespace App\Policies;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;

class OperasiInsidenPolicy
{
    /**
     * super_admin (1) dan pwnu (2): akses penuh semua insiden
     * pcnu (3): hanya insiden di wilayah PCNU-nya
     * relawan (4) dan publik (5): tidak boleh create/update/delete
     */

    public function viewAny(AuthUser $user): bool
    {
        return in_array($user->id_peran, [1, 2, 3, 4]);
    }

    public function view(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if (in_array($user->id_peran, [1, 2])) {
            return true;
        }
        if ($user->id_peran === 3) {
            return $user->default_scope_id == $insiden->id_pcnu;
        }
        return false;
    }

    public function create(AuthUser $user): bool
    {
        return in_array($user->id_peran, [1, 2, 3]);
    }

    public function update(AuthUser $user, OperasiInsiden $insiden): bool
    {
        if (in_array($user->id_peran, [1, 2])) {
            return true;
        }
        if ($user->id_peran === 3) {
            return $user->default_scope_id == $insiden->id_pcnu;
        }
        return false;
    }

    public function delete(AuthUser $user, OperasiInsiden $insiden): bool
    {
        return in_array($user->id_peran, [1, 2]);
    }

    public function ubahStatus(AuthUser $user, OperasiInsiden $insiden): bool
    {
        // Hanya super_admin dan pwnu yang bisa mengubah status insiden
        return in_array($user->id_peran, [1, 2]);
    }
}
```

### 5.4 Penggunaan di Controller

```php
// ✅ BENAR
public function index()
{
    $this->authorize('viewAny', OperasiInsiden::class);
    // ...
}

public function show(OperasiInsiden $insiden)
{
    $this->authorize('view', $insiden);
    // ...
}

public function update(UpdateInsidenRequest $request, OperasiInsiden $insiden)
{
    $this->authorize('update', $insiden);
    // ...
}

// ❌ SALAH — jangan pernah lakukan ini di controller
public function update(Request $request, OperasiInsiden $insiden)
{
    if (auth()->user()->id_peran == 1) { // DILARANG!
        // ...
    }
}
```

### 5.5 Referensi Role ID

| `id_peran` | Nama Role | Level | Deskripsi |
|-----------|-----------|-------|-----------|
| 1 | `super_admin` | 1 | IT Support & Full System Control |
| 2 | `pwnu` | 2 | Pengguna level PWNU Jawa Tengah |
| 3 | `pcnu` | 3 | Pengguna level PCNU |
| 4 | `relawan` | 4 | Relawan terverifikasi NU |
| 5 | `publik` | 5 | Masyarakat umum/warga pelapor |

**Tidak ada role ke-6 atau lebih.** Jika ada kebutuhan role baru, eskalasikan ke Lead Engineer.

---

## 6. Aturan FormRequest

### 6.1 Kewajiban

- Setiap operasi buat (`store`) dan edit (`update`) **WAJIB** menggunakan `FormRequest` terpisah
- Method `authorize()` **HARUS** diisi dengan logika nyata — bukan `return true;`
- Semua `rules()` harus merujuk tabel nyata dengan `exists:nama_tabel,nama_kolom`

### 6.2 Contoh: StoreInsidenRequest

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OperasiInsiden;

class StoreInsidenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OperasiInsiden::class);
    }

    public function rules(): array
    {
        return [
            'id_jenis_bencana' => ['required', 'exists:bencana_master_jenis,id_jenis'],
            'id_pcnu'          => ['required', 'integer'],
            'id_laporan_asal'  => ['nullable', 'exists:laporan_kejadian,id_laporan'],
            'waktu_mulai'      => ['required', 'date'],
            'prioritas'        => ['required', 'in:rendah,sedang,tinggi,kritis'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_jenis_bencana.required' => 'Jenis bencana wajib dipilih.',
            'waktu_mulai.required'      => 'Waktu mulai insiden wajib diisi.',
            'prioritas.in'             => 'Prioritas harus salah satu: rendah, sedang, tinggi, kritis.',
        ];
    }
}
```

### 6.3 Contoh: UpdateSitrepRequest

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OperasiSitrep;

class UpdateSitrepRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sitrep = $this->route('sitrep');
        return $this->user()->can('update', $sitrep);
    }

    public function rules(): array
    {
        return [
            'judul_sitrep'   => ['required', 'string', 'max:255'],
            'isi_sitrep'     => ['required', 'string'],
            'status_sitrep'  => ['required', 'in:draft,ditinjau,final'],
            'periode_dari'   => ['required', 'date'],
            'periode_sampai' => ['required', 'date', 'after_or_equal:periode_dari'],
        ];
    }
}
```

---

## 7. Aturan Database Transaction

### 7.1 Kapan WAJIB Menggunakan Transaksi

Setiap operasi yang **melibatkan lebih dari satu tabel** WAJIB dibungkus dalam `DB::transaction()`.

Contoh operasi yang wajib transaksi:
- Membuat insiden → sekaligus catat ke `riwayat_status_insiden` dan `operasi_jurnal`
- Mutasi logistik → sekaligus update `logistik_stok` (via trigger/transaksi)
- Aktivasi operasi → sekaligus buat `operasi_periode` dan `operasi_aktivasi`

### 7.2 Contoh: Membuat Insiden Baru

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($request) {
    $insiden = OperasiInsiden::create([
        'id_jenis_bencana' => $request->id_jenis_bencana,
        'id_pcnu'          => $request->id_pcnu,
        'id_laporan_asal'  => $request->id_laporan_asal,
        'prioritas'        => $request->prioritas,
        'waktu_mulai'      => $request->waktu_mulai,
        'status_insiden'   => 'draft',
    ]);

    RiwayatStatusInsiden::create([
        'id_insiden'    => $insiden->id_insiden,
        'status_lama'   => null,
        'status_terbaru' => 'draft',
        'id_pengguna'   => auth()->id(),
        'catatan'       => 'Insiden dibuat dari laporan kejadian.',
    ]);

    OperasiJurnal::create([
        'id_insiden'    => $insiden->id_insiden,
        'kategori_event' => 'laporan',
        'judul_event'   => 'Insiden baru dibuat',
        'deskripsi'     => 'Insiden dibuat oleh ' . auth()->user()->name,
        'id_pengguna'   => auth()->id(),
    ]);

    return $insiden;
});
```

### 7.3 Contoh: Mutasi Logistik

```php
DB::transaction(function () use ($request) {
    // Catat mutasi
    $mutasi = LogistikMutasi::create([
        'id_barang'    => $request->id_barang,
        'id_gudang'    => $request->id_gudang,
        'tipe_mutasi'  => $request->tipe_mutasi, // masuk|keluar|penyesuaian
        'jumlah'       => $request->jumlah,
        'keterangan'   => $request->keterangan,
        'id_pengguna'  => auth()->id(),
    ]);

    // Update stok — HANYA boleh melalui logika ini, bukan update langsung
    $stok = LogistikStok::where('id_barang', $request->id_barang)
        ->where('id_gudang', $request->id_gudang)
        ->lockForUpdate()
        ->firstOrFail();

    if ($request->tipe_mutasi === 'masuk') {
        $stok->increment('jumlah_tersedia', $request->jumlah);
    } elseif ($request->tipe_mutasi === 'keluar') {
        if ($stok->jumlah_tersedia < $request->jumlah) {
            throw new \Exception('Stok tidak mencukupi.');
        }
        $stok->decrement('jumlah_tersedia', $request->jumlah);
    } else {
        // penyesuaian: set langsung
        $stok->update(['jumlah_tersedia' => $request->jumlah]);
    }
});
```

---

## 8. Aturan Query WAJIB

### 8.1 Wajib Eager Loading — Hindari N+1

```php
// ❌ SALAH — menyebabkan N+1 query
$insiden = OperasiInsiden::all();
foreach ($insiden as $i) {
    echo $i->jenisBencana->nama_bencana; // Query baru per iterasi!
    echo $i->sitreps->count();           // Query baru per iterasi!
}

// ✅ BENAR — eager load semua relasi yang dibutuhkan
$insiden = OperasiInsiden::with([
    'jenisBencana',
    'sitreps',
    'penugasan',
    'riwayatStatus',
])->get();
```

### 8.2 Wajib Pagination untuk List

```php
// ❌ SALAH — bisa return ribuan baris
$insiden = OperasiInsiden::with(['jenisBencana'])->get();

// ✅ BENAR
$insiden = OperasiInsiden::with(['jenisBencana'])->paginate(15);

// Di Blade:
{{ $insiden->links() }}
```

### 8.3 Dilarang Query di Blade View

```blade
{{-- ❌ SALAH — query langsung di view --}}
@foreach(OperasiInsiden::all() as $insiden)
    {{ $insiden->status_insiden }}
@endforeach

{{-- ✅ BENAR — data dikirim dari controller --}}
@foreach($insiden as $item)
    {{ $item->status_insiden }}
@endforeach
```

### 8.4 Wajib Scope Wilayah di Query Data Sensitif

Setiap query untuk data insiden, assessment, dan laporan **WAJIB** memfilter berdasarkan scope wilayah pengguna:

```php
// Di Controller atau Service
public function index()
{
    $this->authorize('viewAny', OperasiInsiden::class);

    $user = auth()->user();
    $query = OperasiInsiden::with(['jenisBencana', 'sitreps']);

    // Scope wilayah berdasarkan role
    if ($user->id_peran === 3) { // pcnu — hanya wilayahnya sendiri
        $query->where('id_pcnu', $user->default_scope_id);
    }
    // super_admin (1) dan pwnu (2) — melihat semua, tidak perlu filter

    $insiden = $query->orderByDesc('dibuat_pada')->paginate(15);

    return view('operasi.insiden.index', compact('insiden'));
}
```

### 8.5 Penggunaan selectRaw yang Aman

```php
// ✅ Boleh — dengan binding parameter
$stok = LogistikStok::selectRaw('id_stok, id_barang, jumlah_tersedia')
    ->where('id_gudang', $idGudang)
    ->where('jumlah_tersedia', '>', 0)
    ->get();

// ❌ DILARANG — raw input tanpa binding
$stok = LogistikStok::whereRaw("id_gudang = $idGudang")->get(); // SQL injection!
```

---

## 9. DILARANG KERAS (Larangan Absolut)

Pelanggaran terhadap larangan di bawah ini mengakibatkan **kode wajib ditulis ulang dari awal.**

| No | Larangan | Dampak Pelanggaran |
|----|----------|--------------------|
| 1 | Rename tabel dari SQL dump | Migrasi gagal, FK hancur |
| 2 | Rename kolom dari SQL dump | Query dan relasi gagal |
| 3 | Membuat role baru di luar 5 role PRD | Logika otorisasi rusak |
| 4 | Membuat nilai enum baru yang tidak ada di SQL | Validasi dan state machine rusak |
| 5 | Membuat workflow baru yang tidak ada di `STATE_MACHINE.md` | Alur bisnis tidak valid |
| 6 | Bypass authorization (`return true` di Policy) | Celah keamanan kritis |
| 7 | Query langsung di Blade view | N+1, performa buruk, tidak testable |
| 8 | `UPDATE` langsung ke `logistik_stok.jumlah_tersedia` | Inkonsistensi stok, audit trail hilang |
| 9 | Menggunakan React/Vue SPA, Next.js, Nuxt, atau framework frontend berat | Melanggar arsitektur Blade SSR |
| 10 | Repository pattern tidak perlu / abstraksi berlebihan | Kompleksitas tidak ada manfaatnya |
| 11 | Abstraction layer di luar yang sudah ada di arsitektur | Over-engineering |
| 12 | `SoftDeletes` pada tabel tanpa kolom `dihapus_pada` | Error runtime pada query |
| 13 | Mengubah atau menghapus trigger database yang sudah ada | Inkonsistensi data logistik |
| 14 | Improvisasi struktur tabel atau kolom baru | Data corruption, migrasi konflik |

---

## 10. Format Task Implementasi untuk AI

Setiap task implementasi yang diberikan ke AI **WAJIB** menggunakan format berikut. AI yang menerima task tanpa format ini **HARUS meminta klarifikasi** sebelum mulai coding.

```
DOMAIN: [nama domain — operasi / logistik / assessment / relawan / surat / aset]
MODUL: [nama modul spesifik — contoh: manajemen insiden, mutasi stok, kaji cepat]
TABEL TERKAIT: [daftar tabel yang terlibat, diambil dari SQL dump]
WORKFLOW TERKAIT: [state machine yang berlaku, merujuk STATE_MACHINE.md]
AUTHORIZATION TERKAIT: [role yang boleh akses dan scope wilayah yang berlaku]

OUTPUT YANG DIPERLUKAN:
- [ ] Migration (hanya jika ada perubahan schema — jarang dibutuhkan)
- [ ] Model dengan relasi
- [ ] Policy
- [ ] FormRequest (Store dan Update terpisah)
- [ ] Controller (Resource Controller jika memungkinkan)
- [ ] Route (tambahan di routes/web.php)
- [ ] Blade views (index, create, edit, show)
- [ ] Feature test
```

---

## 11. Urutan Output per Modul

AI **HARUS** menghasilkan output dalam urutan berikut. Jangan skip langkah:

1. **Migration** — hanya jika ada perubahan schema yang disetujui
2. **Model** — lengkap dengan `$table`, `$primaryKey`, timestamp, `$fillable`, `$casts`, relasi
3. **Policy** — semua method yang relevan (viewAny, view, create, update, delete, + custom)
4. **FormRequest** — `StoreXxxRequest` dan `UpdateXxxRequest` terpisah
5. **Controller** — Resource Controller, semua method memanggil `$this->authorize()`
6. **Route** — tambahan di `routes/web.php` dengan middleware `auth` dan grup yang tepat
7. **Blade Views** — `index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
8. **Feature Test** — minimal test untuk happy path dan unauthorized path

---

## 12. Standar Blade View

### 12.1 Struktur Wajib

```blade
{{-- Setiap view WAJIB extend layout master --}}
@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Konten halaman --}}
        </div>
    </div>
</div>
@endsection
```

### 12.2 Form Standar

```blade
{{-- Form Create --}}
<form action="{{ route('operasi.insiden.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="id_jenis_bencana" class="form-label">Jenis Bencana <span class="text-danger">*</span></label>
        <select name="id_jenis_bencana" id="id_jenis_bencana"
            class="form-select @error('id_jenis_bencana') is-invalid @enderror">
            <option value="">-- Pilih Jenis Bencana --</option>
            @foreach($jenisBencana as $jenis)
                <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis_bencana') == $jenis->id_jenis ? 'selected' : '' }}>
                    {{ $jenis->nama_bencana }}
                </option>
            @endforeach
        </select>
        @error('id_jenis_bencana')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

{{-- Form Edit --}}
<form action="{{ route('operasi.insiden.update', $insiden->id_insiden) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- ... field yang sama --}}
</form>

{{-- Form Delete --}}
<form action="{{ route('operasi.insiden.destroy', $insiden->id_insiden) }}" method="POST"
    onsubmit="return confirm('Yakin ingin menghapus insiden ini?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
</form>
```

### 12.3 Larangan di Blade

```blade
{{-- ❌ DILARANG —  framework CSS lain --}}
<link href="https://cdn.tailwindcss.com" rel="stylesheet">

{{-- ❌ DILARANG — framework frontend berat --}}
<div id="app"></div>
<script src="vue.js"></script>

{{-- ❌ DILARANG — query di Blade --}}
@foreach(\App\Models\OperasiInsiden::all() as $insiden)

{{-- ✅ BOLEH — Leaflet.js hanya untuk tampilan peta --}}
<div id="peta" style="height: 400px;"></div>
@push('scripts')
<script>
    var peta = L.map('peta').setView([-7.0, 110.4], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(peta);
</script>
@endpush
```

---

## 13. Referensi Enum yang Difreeze

Gunakan nilai-nilai berikut secara verbatim dalam validasi `FormRequest` dan logika bisnis:

### Status Insiden
```php
'status_insiden' => 'required|in:draft,terverifikasi,respon,pemulihan,selesai,dibatalkan',
```

### Prioritas Insiden
```php
'prioritas' => 'required|in:rendah,sedang,tinggi,kritis',
```

### Status Sitrep
```php
'status_sitrep' => 'required|in:draft,ditinjau,final',
```

### Tipe Mutasi Logistik
```php
'tipe_mutasi' => 'required|in:masuk,keluar,penyesuaian',
```

### Kondisi Fisik Aset
```php
'kondisi_fisik' => 'required|in:baik,rusak_ringan,rusak_berat',
```

### Status Aset
```php
// Nilai integer sesuai aset_master_status
// 1 = Tersedia, 2 = Dalam Tugas, 3 = Perbaikan/Maintenance, 4 = Rusak, 5 = Hilang
'id_status_aset' => 'required|in:1,2,3,4,5',
```

### Peran Otoritas Operasi
```php
'peran_otoritas' => 'required|in:komandan_insiden,trc,relawan,medis,logistik,operator',
```

### Jenis Assessment
```php
'jenis_assessment' => 'required|in:kaji_cepat,pendataan_lanjutan',
```

### Status Akun Pengguna
```php
'status_akun' => 'required|in:menunggu,aktif,nonaktif,suspend',
```

### Default Scope Type
```php
'default_scope_type' => 'required|in:pwnu,pcnu,mwc,ranting,lembaga,banom',
```

### Kategori Bencana
```php
'kategori_bencana' => 'required|in:alam,non_alam,sosial',
```

---

## 14. Catatan Khusus Timestamp Non-Standar

Laravel secara default menggunakan `created_at` dan `updated_at`. NURISK menggunakan `dibuat_pada` dan `diperbarui_pada`. Ini **WAJIB** dideklarasikan di **setiap Model** tanpa pengecualian.

```php
// ✅ WAJIB di setiap Model
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';

// ✅ WAJIB jika tabel memiliki kolom dihapus_pada
use SoftDeletes;
const DELETED_AT = 'dihapus_pada';
```

Konsekuensi jika tidak dideklarasikan:
- `Model::create()` akan mencari kolom `created_at` → **QueryException**
- `Model::withTrashed()` akan mencari kolom `deleted_at` → **data tidak difilter**
- Carbon accessor akan gagal → **TypeError**

---

## 15. Standar Feature Test

Setiap modul **WAJIB** memiliki feature test yang mencakup minimal:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OperasiInsidenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function super_admin_dapat_melihat_daftar_insiden(): void
    {
        $user = AuthUser::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get(route('operasi.insiden.index'));

        $response->assertStatus(200);
        $response->assertViewIs('operasi.insiden.index');
    }

    /** @test */
    public function pcnu_hanya_melihat_insiden_wilayahnya(): void
    {
        $user = AuthUser::factory()->pcnu()->create(['default_scope_id' => 1]);
        OperasiInsiden::factory()->create(['id_pcnu' => 1]);
        OperasiInsiden::factory()->create(['id_pcnu' => 2]);

        $response = $this->actingAs($user)->get(route('operasi.insiden.index'));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('insiden'));
    }

    /** @test */
    public function relawan_tidak_dapat_membuat_insiden(): void
    {
        $user = AuthUser::factory()->relawan()->create();

        $response = $this->actingAs($user)->post(route('operasi.insiden.store'), [
            'id_jenis_bencana' => 1,
            'id_pcnu'          => 1,
            'waktu_mulai'      => now()->toDateString(),
            'prioritas'        => 'sedang',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function publik_tidak_dapat_mengakses_daftar_insiden(): void
    {
        $response = $this->get(route('operasi.insiden.index'));

        $response->assertRedirect(route('login'));
    }
}
```

---

## 16. Checklist Validasi Sebelum Submit Kode

Sebelum menyerahkan hasil coding, AI **WAJIB** memverifikasi checklist berikut:

### Naming
- [ ] Semua nama tabel cocok dengan SQL dump
- [ ] Semua nama kolom cocok dengan SQL dump
- [ ] Tidak ada tabel atau kolom dalam Bahasa Inggris

### Model
- [ ] `protected $table` dideklarasikan
- [ ] `protected $primaryKey` dideklarasikan
- [ ] `const CREATED_AT = 'dibuat_pada'` ada
- [ ] `const UPDATED_AT = 'diperbarui_pada'` ada
- [ ] `use SoftDeletes` + `const DELETED_AT` hanya jika kolom `dihapus_pada` ada
- [ ] Semua relasi menggunakan FK dan local key eksplisit

### Authorization
- [ ] Policy terdaftar di `AuthServiceProvider`
- [ ] Setiap method controller memanggil `$this->authorize()`
- [ ] Tidak ada `if ($user->id_peran == X)` di controller

### Query
- [ ] Semua list menggunakan `paginate()`
- [ ] Semua relasi yang digunakan di-eager-load via `with([])`
- [ ] Tidak ada query di Blade view
- [ ] Scope wilayah diterapkan untuk data sensitif

### Transaksi
- [ ] Operasi multi-tabel dibungkus `DB::transaction()`
- [ ] Mutasi logistik tidak mengupdate `logistik_stok` secara langsung

### Blade
- [ ] Semua view `@extends('layouts.app')`
- [ ] Semua form menggunakan Bootstrap 5.3
- [ ] Form edit menggunakan `@method('PUT')`
- [ ] Form delete menggunakan `@method('DELETE')`
- [ ] Error ditampilkan dengan `@error` / `is-invalid`

### Enum
- [ ] Semua nilai enum sesuai dengan Bagian 13 dokumen ini
- [ ] Tidak ada nilai enum baru yang tidak terdaftar

### Test
- [ ] Happy path tercakup
- [ ] Unauthorized path tercakup
- [ ] Scope wilayah tercakup

---

*Dokumen ini dikelola oleh tim teknikal NURISK. Perubahan hanya boleh dilakukan oleh Lead Engineer dengan review eksplisit.*
