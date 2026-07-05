# FILE_STORAGE_RULES.md — NURISK

> Dokumen ini mendefinisikan aturan penyimpanan file di sistem NURISK.
> Semua referensi kolom mengacu pada SQL schema production.

---

## 1. Storage Driver

| Environment  | Driver       | Konfigurasi                         |
|--------------|--------------|-------------------------------------|
| Local/Dev    | `local`      | `storage/app/public/`               |
| Staging      | `local`      | `storage/app/public/`               |
| Production   | `s3` atau `local` | Konfigurasi via `.env`         |

**Konfigurasi di `config/filesystems.php`:**
```php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    'local' => [
        'driver' => 'local',
        'root'   => storage_path('app/private'),
        'serve'  => true,
        'throw'  => false,
    ],
    'public' => [
        'driver'     => 'local',
        'root'       => storage_path('app/public'),
        'url'        => env('APP_URL') . '/storage',
        'visibility' => 'public',
        'throw'      => false,
    ],
    's3' => [
        'driver'   => 's3',
        'key'      => env('AWS_ACCESS_KEY_ID'),
        'secret'   => env('AWS_SECRET_ACCESS_KEY'),
        'region'   => env('AWS_DEFAULT_REGION'),
        'bucket'   => env('AWS_BUCKET'),
        'url'      => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'throw'    => false,
    ],
],
```

**Konfigurasi `.env`:**
```env
FILESYSTEM_DISK=public           # default: public (local)
# Untuk production S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=...
# AWS_SECRET_ACCESS_KEY=...
# AWS_DEFAULT_REGION=ap-southeast-1
# AWS_BUCKET=nurisk-storage
```

> [!IMPORTANT]
> Setelah mengubah ke disk `public`, wajib jalankan `php artisan storage:link` untuk membuat symlink `public/storage → storage/app/public`.

---

## 2. Struktur Folder Upload

Semua file upload disimpan di dalam `storage/app/public/` dengan subfolder berdasarkan domain:

```
storage/app/public/
├── insiden/          # Foto laporan kejadian — kolom: laporan_kejadian.photo_path
├── assessment/       # Dokumen & foto assessment — kolom: assessment_utama (jika ada file)
├── sitrep/           # PDF sitrep final — kolom: operasi_sitrep.file_pdf_path
├── pleno/            # Dokumen pleno (notulen, lampiran)
├── surat/            # PDF surat resmi — kolom: operasi_surat_keluar (file path)
├── logistik/         # Bukti mutasi logistik — kolom: logistik_mutasi (jika ada attachment)
├── pengungsian/      # Foto dan dokumen pengungsian
├── aset/             # Foto kondisi aset — kolom: aset_unit (jika ada foto kondisi)
└── temp/             # File sementara — dibersihkan otomatis setelah 24 jam
```

### Mapping Folder ke Kolom Database

| Folder          | Tabel DB                  | Kolom File Path              |
|-----------------|---------------------------|------------------------------|
| `insiden/`      | `laporan_kejadian`        | `photo_path`                 |
| `sitrep/`       | `operasi_sitrep`          | `file_pdf_path`              |
| `surat/`        | `operasi_surat_keluar`     | *(kolom file path di tabel)* |
| `aset/`         | `aset_unit`               | *(kolom foto kondisi)*       |
| `temp/`         | —                         | File sementara, tidak disimpan ke DB |

> [!NOTE]
> Untuk tabel yang belum memiliki kolom file path di schema saat ini (assessment, pleno, logistik, pengungsian), dokumentasikan sebagai **mismatch** dan tambahkan kolom tersebut via migration sebelum fitur upload diaktifkan.

---

## 3. Naming Convention File

**Format nama file:**
```
YYYYMMDD_[slug-entitas]_[8-karakter-uuid].ext
```

**Aturan penamaan:**
- Semua huruf **lowercase**
- Spasi diganti tanda hubung (`-`)
- UUID 8 karakter pertama untuk memastikan uniqueness
- Ekstensi sesuai MIME type sebenarnya (deteksi via `$file->getMimeType()`)
- Tidak ada karakter khusus selain huruf, angka, tanda hubung, dan underscore

### Contoh Nama File per Domain

| Domain       | Contoh Nama File                                   |
|--------------|----------------------------------------------------|
| Foto insiden | `insiden/20260610_banjir-semarang_a3f2b7c1.jpg`    |
| PDF sitrep   | `sitrep/20260610_sitrep-001-insiden-42_b7c1d9e3.pdf` |
| PDF surat    | `surat/20260611_st-001-2026_d9e3f1a4.pdf`          |
| Assessment   | `assessment/20260610_kaji-cepat-insiden-42_f1a4c2b3.jpg` |
| Foto aset    | `aset/20260612_genset-12_e5f6a7b8.jpg`             |

### Helper Fungsi Generate Nama File

```php
/**
 * Generate nama file sesuai konvensi NURISK.
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string $slugEntitas  Contoh: 'banjir-semarang', 'sitrep-001-insiden-42'
 * @return string              Contoh: '20260610_banjir-semarang_a3f2b7c1.jpg'
 */
function generateNamaFile(\Illuminate\Http\UploadedFile $file, string $slugEntitas): string
{
    $tanggal   = now()->format('Ymd');
    $slug      = Str::slug($slugEntitas);
    $uuidPendek = substr(str_replace('-', '', Str::uuid()->toString()), 0, 8);
    $ekstensi  = $file->getClientOriginalExtension();

    return "{$tanggal}_{$slug}_{$uuidPendek}.{$ekstensi}";
}
```

---

## 4. Validasi Upload

Semua upload wajib divalidasi di Form Request sebelum diproses. Gunakan rule berikut sesuai jenis file.

### 4.1 Foto / Gambar

```php
// Form Request — validasi foto laporan kejadian
// Kolom terkait: laporan_kejadian.photo_path
'photo_path' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:5120',  // 5 MB
],
```

### 4.2 Dokumen PDF

```php
// Form Request — validasi PDF sitrep atau surat
// Kolom terkait: operasi_sitrep.file_pdf_path, operasi_surat_keluar
'file_pdf_path' => [
    'nullable',
    'file',
    'mimes:pdf',
    'max:10240',  // 10 MB
],
```

### 4.3 Spreadsheet (Import Logistik)

```php
// Form Request — validasi file import data logistik
'file_import' => [
    'nullable',
    'file',
    'mimes:xlsx,xls,csv',
    'max:5120',  // 5 MB
],
```

### 4.4 Tabel MIME Type yang Diizinkan

| Jenis File          | Ekstensi yang Diizinkan     | Max Ukuran |
|---------------------|-----------------------------|------------|
| Foto / Gambar       | `jpg`, `jpeg`, `png`, `webp`| 5 MB       |
| Dokumen             | `pdf`                       | 10 MB      |
| Spreadsheet (import)| `xlsx`, `xls`, `csv`        | 5 MB       |

### 4.5 Ekstensi yang DILARANG

> [!CAUTION]
> Ekstensi berikut **dilarang keras** diterima oleh sistem dalam kondisi apapun:

```
exe, sh, bash, php, php3, php4, php5, phtml,
py, rb, pl, js, ts, jar, war, bat, cmd,
dll, so, msi, dmg, apk, ipa
```

Implementasikan denylist di Form Request sebagai lapisan validasi tambahan:

```php
// Validasi tambahan di Form Request
use Illuminate\Validation\Rules\File;

'file_pdf_path' => [
    'nullable',
    File::types(['pdf'])->max(10 * 1024),
],

// Atau manual check di after() hook
public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        $file = $this->file('file_pdf_path');
        if ($file) {
            $ekstensiDilarang = ['exe', 'sh', 'php', 'py', 'rb', 'js', 'bat'];
            if (in_array(strtolower($file->getClientOriginalExtension()), $ekstensiDilarang)) {
                $validator->errors()->add('file_pdf_path', 'Tipe file tidak diizinkan.');
            }
        }
    });
}
```

---

## 5. Penyimpanan File ke Disk

### 5.1 Proses Upload dan Simpan Path ke Database

```php
// Contoh di InsidenController atau LaporanKejadianService
public function simpanFotoInsiden(
    \Illuminate\Http\UploadedFile $foto,
    string $slugInsiden
): string {
    $namaFile = generateNamaFile($foto, $slugInsiden);
    $path = $foto->storeAs('insiden', $namaFile, 'public');

    // $path = 'insiden/20260610_banjir-semarang_a3f2b7c1.jpg'
    // Simpan $path ke kolom laporan_kejadian.photo_path
    return $path;
}
```

### 5.2 Simpan dan Update Kolom Database

```php
// Di LaporanKejadianService::simpan()
$laporan = LaporanKejadian::create([
    'id_insiden'  => $idInsiden,
    'id_pengguna' => auth()->id(),
    'deskripsi'   => $data['deskripsi'],
    'photo_path'  => null, // default null dulu
]);

if ($request->hasFile('photo_path')) {
    $path = $request->file('photo_path')
        ->storeAs('insiden', generateNamaFile($request->file('photo_path'), 'laporan-' . $laporan->id_laporan), 'public');

    $laporan->update(['photo_path' => $path]);
}
```

---

## 6. Generate PDF

### 6.1 Library

Gunakan salah satu dari:
- `barryvdh/laravel-dompdf` — untuk dokumen sederhana (tabel, teks)
- `knplabs/knp-snappy` (wkhtmltopdf) — untuk dokumen dengan layout kompleks

### 6.2 Trigger Generate PDF

| Entitas           | Trigger                                        | Kolom Simpan Path              |
|-------------------|------------------------------------------------|-------------------------------|
| Sitrep            | `operasi_sitrep.status_sitrep` → `'final'`     | `operasi_sitrep.file_pdf_path` |
| Surat resmi       | Status surat → FINALIZED (paraf lengkap)       | *(kolom di `operasi_surat_keluar`)* |

### 6.3 Implementasi Generate PDF Sitrep

```php
// Di SitrepService::finalisasiSitrep()
use Barryvdh\DomPDF\Facade\Pdf;

public function finalisasiSitrep(int $idSitrep): OperasiSitrep
{
    $sitrep = OperasiSitrep::with(['insiden', 'pembuat'])->findOrFail($idSitrep);

    // Generate PDF dari Blade template
    $pdf = Pdf::loadView('pdf.sitrep', ['sitrep' => $sitrep]);

    // Nama file sesuai konvensi
    $namaFile = now()->format('Ymd') . '_sitrep-' . $sitrep->id_sitrep . '-insiden-' . $sitrep->id_insiden
        . '_' . substr(str_replace('-', '', Str::uuid()), 0, 8) . '.pdf';

    $path = 'sitrep/' . $namaFile;

    // Simpan ke disk 'public'
    Storage::disk('public')->put($path, $pdf->output());

    // Update status dan path di database
    $sitrep->update([
        'status_sitrep' => 'final',
        'file_pdf_path' => $path,
    ]);

    return $sitrep->fresh();
}
```

### 6.4 Blade Template PDF

Lokasi template PDF:
```
resources/views/pdf/
├── sitrep.blade.php    # Template PDF operasi_sitrep
└── surat.blade.php     # Template PDF operasi_surat_keluar
```

Template PDF wajib memuat:
- **sitrep.blade.php**: id_sitrep, id_insiden, nama_insiden, periode, ringkasan situasi, data korban dari `assessment_dampak_manusia`, kebutuhan dari `assessment_kebutuhan_mendesak`, tanda tangan komandan
- **surat.blade.php**: nomor surat, tanggal, perihal, isi, daftar paraf dari `dokumen_surat_paraf`, daftar tembusan dari `dokumen_surat_tembusan`

---

## 7. Akses File

### 7.1 Aturan Akses per Jenis File

| Jenis File                          | Akses                    | Mekanisme                              |
|-------------------------------------|--------------------------|----------------------------------------|
| Foto insiden (publik map)           | Tanpa autentikasi         | `Storage::url($path)` via disk `public` |
| PDF sitrep                          | Auth + Policy check       | Route protected, download via controller |
| PDF surat resmi                     | Auth + Policy check       | Route protected, download via controller |
| Dokumen assessment                  | Auth + Policy check       | Route protected                        |
| Foto aset                           | Auth minimal (relawan)    | Route protected                        |

### 7.2 Generate URL File

```php
// File publik (foto insiden untuk peta)
$url = Storage::disk('public')->url($laporan->photo_path);
// Hasil: https://nurisk.example.com/storage/insiden/20260610_banjir-semarang_a3f2b7c1.jpg

// File internal (download PDF sitrep) — melalui controller
// Route: GET /sitrep/{idSitrep}/download-pdf
public function downloadPdf(int $idSitrep): \Illuminate\Http\Response
{
    $sitrep = OperasiSitrep::findOrFail($idSitrep);
    $this->authorize('download', $sitrep); // Policy check

    if (!$sitrep->file_pdf_path || !Storage::disk('public')->exists($sitrep->file_pdf_path)) {
        abort(404, 'File PDF tidak ditemukan.');
    }

    return Storage::disk('public')->download($sitrep->file_pdf_path);
}
```

### 7.3 Larangan Akses Langsung

- **DILARANG** expose path storage secara langsung ke user (misalnya menampilkan `/var/www/nurisk/storage/...` di response)
- **DILARANG** menyimpan file di `public/` root Laravel (gunakan `storage/app/public/` + symlink)
- Selalu gunakan `Storage::url()` atau `Storage::disk()->download()` — bukan path fisik

---

## 8. Cleanup File Sementara

### 8.1 Aturan Cleanup `temp/`

- File di folder `temp/` dihapus otomatis setelah **24 jam**
- Cleanup dilakukan via Laravel Scheduler (Artisan Command)

```php
// app/Console/Commands/CleanupTempStorage.php
class CleanupTempStorage extends Command
{
    protected $signature   = 'storage:cleanup-temp';
    protected $description = 'Hapus file sementara di storage/app/public/temp/ yang berumur > 24 jam';

    public function handle(): void
    {
        $files = Storage::disk('public')->files('temp');

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);

            if (now()->timestamp - $lastModified > 86400) { // 24 jam dalam detik
                Storage::disk('public')->delete($file);
                $this->info("Dihapus: {$file}");
            }
        }
    }
}
```

```php
// routes/console.php atau bootstrap/app.php (Laravel 11+)
Schedule::command('storage:cleanup-temp')->daily();
```

### 8.2 Aturan Soft Delete dan File Fisik

| Kondisi                                         | Tindakan terhadap File Fisik       |
|-------------------------------------------------|------------------------------------|
| Entitas di-soft-delete (`dihapus_pada` diisi)   | **Pertahankan file** — jangan hapus |
| Entitas di-hard-delete (purge permanen)         | Hapus file fisik setelah konfirmasi |
| Upload file gagal di tengah proses              | Hapus file yang sudah terupload (rollback) |

```php
// Contoh: soft delete LaporanKejadian TIDAK menghapus foto
// Di LaporanKejadian model — pastikan tidak ada hook yang menghapus file
protected static function booted(): void
{
    // TIDAK ada Storage::delete() di event deleting/deleted
    // File dipertahankan untuk keperluan audit
}
```

> [!WARNING]
> Jangan pernah menambahkan `Storage::delete()` di event `deleting` atau `deleted` model yang menggunakan soft delete. File terkait entitas yang di-soft-delete harus dipertahankan sebagai bagian dari jejak audit.

---

## 9. Ringkasan Aturan Cepat

| Aspek                  | Aturan                                                              |
|------------------------|---------------------------------------------------------------------|
| Driver default         | `public` (local) untuk dev/staging; `s3` untuk production          |
| Folder upload          | Sesuai subfolder domain di `storage/app/public/`                   |
| Naming file            | `YYYYMMDD_[slug]_[uuid8].ext`, lowercase, tanpa spasi              |
| Max ukuran foto        | 5 MB (`jpg`, `jpeg`, `png`, `webp`)                                |
| Max ukuran PDF         | 10 MB (`pdf`)                                                      |
| Generate URL           | `Storage::url($path)` atau via controller download                  |
| Akses file internal    | Wajib auth + policy — jangan expose langsung                        |
| Akses foto publik      | Boleh tanpa auth (untuk peta publik)                               |
| Cleanup temp           | Otomatis setiap 24 jam via `storage:cleanup-temp` scheduler        |
| Soft delete + file     | File fisik **dipertahankan** — tidak ikut dihapus                  |
| File terlarang         | `exe`, `sh`, `php`, `py`, `js`, `bat`, dan sejenisnya diblokir     |
