# PERFORMANCE_RULES.md — NURISK

> Dokumen ini mendefinisikan aturan performa wajib di sistem NURISK.
> Semua contoh kode mengacu pada nama tabel dan kolom nyata dari SQL schema production.

---

## 1. Larangan Performa (Hard Rules)

Pelanggaran terhadap aturan berikut harus diperbaiki sebelum merge ke branch `main`.

| No | Larangan                                                                 |
|----|--------------------------------------------------------------------------|
| 1  | **DILARANG** menulis query Eloquent/DB di dalam Blade view               |
| 2  | **DILARANG** N+1 query — wajib eager loading                             |
| 3  | **DILARANG** `SELECT *` pada tabel besar — gunakan `select()` spesifik   |
| 4  | **DILARANG** load semua data tanpa pagination pada list view              |
| 5  | **DILARANG** kalkulasi analytics kompleks secara real-time tanpa cache    |
| 6  | **DILARANG** eager loading berlebihan — jangan load relasi yang tidak dipakai di view |
| 7  | **DILARANG** `->all()` atau `->get()` tanpa filter/limit pada tabel besar |
| 8  | **DILARANG** loop `foreach` yang memicu query Eloquent di dalamnya        |

---

## 2. Wajib Eager Loading

Gunakan `with()` hanya untuk relasi yang benar-benar dirender di view.

### BENAR ✅
```php
// Eager load hanya relasi yang dibutuhkan di halaman list insiden
$insiden = OperasiInsiden::select(
        'id_insiden', 'id_pcnu', 'id_jenis_bencana',
        'nama_insiden', 'status_insiden', 'dibuat_pada'
    )
    ->with([
        'jenisBencana:id_jenis,nama_bencana',
        'penugasan:id_incident_assignment,id_insiden,id_pengguna',
    ])
    ->where('status_insiden', '!=', 'selesai')
    ->paginate(15);
```

### SALAH ❌
```php
// Load semua relasi tanpa filter — menyebabkan memory spike
$insiden = OperasiInsiden::with([
    'jenisBencana',
    'sitreps',
    'penugasan',
    'klaster',
    'posaju',
    'jurnal',
    'assessment',
])->get(); // tanpa pagination
```

### Daftar Relasi Eager Load per Context

| Context / Halaman                     | Relasi yang Boleh Di-eager-load                                         |
|---------------------------------------|-------------------------------------------------------------------------|
| List insiden (`index`)                | `jenisBencana:id_jenis,nama_bencana`, `penugasan` (count saja)          |
| Detail insiden (`show`)               | `jenisBencana`, `sitreps`, `klaster.koordinator`, `posaju`              |
| List logistik stok                    | `katalog:id_katalog,nama_barang_standar,id_satuan`, `katalog.satuan`    |
| Dashboard command center              | Tidak ada eager load — gunakan query agregasi terpisah                  |
| List permintaan logistik              | `katalog:id_katalog,nama_barang_standar`, `pemohon:id,name`             |
| Detail pleno                          | `peserta.pengguna:id,name`, `keputusan`                                 |
| List sitrep                           | `insiden:id_insiden,nama_insiden`, `pembuat:id,name`                    |

---

## 3. Indexing Wajib

Index berikut **harus ada** di database production. Verifikasi dengan `SHOW INDEX FROM <tabel>` sebelum deploy.

### Index Tabel Operasi

| Tabel                     | Kolom                        | Tipe Index |
|---------------------------|------------------------------|------------|
| `operasi_insiden`         | `id_pcnu`                    | INDEX       |
| `operasi_insiden`         | `status_insiden`             | INDEX       |
| `operasi_insiden`         | `dibuat_pada`                | INDEX       |
| `operasi_sitrep`          | `id_insiden`                 | INDEX       |
| `operasi_sitrep`          | `status_sitrep`              | INDEX       |
| `riwayat_status_insiden`  | `id_insiden`                 | INDEX       |
| `operasi_jurnal`          | `id_insiden`                 | INDEX       |
| `operasi_jurnal`          | `waktu_event`                | INDEX       |
| `operasi_penugasan`       | `id_insiden`                 | INDEX       |
| `operasi_penugasan`       | `id_pengguna`                | INDEX       |

### Index Tabel Logistik

| Tabel               | Kolom            | Tipe Index |
|---------------------|------------------|------------|
| `logistik_mutasi`   | `id_stok`        | INDEX       |
| `logistik_mutasi`   | `waktu_mutasi`   | INDEX       |
| `logistik_stok`     | `id_posaju`      | INDEX       |
| `logistik_stok`     | `id_gudang`      | INDEX       |
| `logistik_permintaan` | `id_insiden`   | INDEX       |
| `logistik_permintaan` | `status_permintaan` | INDEX  |

### Index Tabel Lainnya

| Tabel                  | Kolom          | Tipe Index |
|------------------------|----------------|------------|
| `assessment_utama`     | `id_insiden`   | INDEX       |
| `assessment_utama`     | `is_latest`    | INDEX       |
| `laporan_kejadian`     | `is_valid`     | INDEX       |
| `laporan_kejadian`     | `dibuat_pada`  | INDEX       |
| `auth_users`           | `status_akun`  | INDEX       |
| `relawan_pendaftaran`  | `id_pengguna`  | INDEX       |
| `aset_penggunaan`      | `id_aset`      | INDEX       |

> [!IMPORTANT]
> Semua foreign key otomatis diindeks oleh InnoDB. Index tambahan di atas adalah untuk kolom non-FK yang sering digunakan di klausa `WHERE`, `ORDER BY`, dan `GROUP BY`.

---

## 4. Pagination Wajib

Semua endpoint yang menampilkan list data **wajib** menggunakan pagination.

| Ukuran Dataset           | Method Pagination           | Keterangan                          |
|--------------------------|------------------------------|--------------------------------------|
| List data umum           | `->paginate(15)`             | Tampilkan nomor halaman              |
| List sederhana/filter    | `->simplePaginate(20)`       | Hanya prev/next, lebih ringan        |
| Export / batch proses    | `->chunk(100, fn($batch)…)` | Proses batch tanpa load semua di RAM |
| API response             | `->paginate(20)`             | Sertakan `meta.pagination` di JSON   |

### BENAR ✅
```php
// Controller InsidentController@index
$insiden = OperasiInsiden::select('id_insiden', 'nama_insiden', 'status_insiden', 'dibuat_pada')
    ->with(['jenisBencana:id_jenis,nama_bencana'])
    ->orderByDesc('dibuat_pada')
    ->paginate(15);

return view('insiden.index', compact('insiden'));
```

### SALAH ❌
```php
// Load semua insiden ke memori — DILARANG
$insiden = OperasiInsiden::all();
$insiden = OperasiInsiden::get();
$insiden = OperasiInsiden::where('status_insiden', 'respon')->get(); // tanpa paginate
```

---

## 5. Query Optimization

### 5.1 Select Kolom Spesifik untuk List

```php
// List stok logistik di pos aju — pilih kolom minimal yang dibutuhkan view
$stok = LogistikStok::select('id_stok', 'id_posaju', 'id_katalog', 'jumlah_tersedia', 'jumlah_minimum')
    ->with([
        'katalog:id_katalog,nama_barang_standar,id_satuan',
        'katalog.satuan:id_satuan,singkatan',
    ])
    ->where('id_posaju', $posaju->id_posaju)
    ->get();
```

### 5.2 Gunakan `withCount` untuk Agregasi Sederhana

```php
// Hitung jumlah sitrep per insiden tanpa load koleksi sitrep
$insiden = OperasiInsiden::withCount('sitreps')
    ->where('status_insiden', 'respon')
    ->orderByDesc('dibuat_pada')
    ->paginate(15);

// Di Blade: {{ $item->sitreps_count }}
```

### 5.3 Gunakan `DB::select()` untuk Query Agregasi Kompleks

Untuk query yang melibatkan banyak JOIN dan GROUP BY, gunakan raw SQL via `DB::select()` dan simpan hasilnya ke cache:

```php
// Aggregasi summary dashboard — jangan lakukan ini di Blade
$summary = DB::select("
    SELECT
        oi.status_insiden,
        COUNT(oi.id_insiden) AS jumlah_insiden,
        SUM(am.jumlah_jiwa_terdampak) AS total_jiwa
    FROM operasi_insiden oi
    LEFT JOIN assessment_utama am
        ON am.id_insiden = oi.id_insiden AND am.is_latest = 1
    WHERE oi.dihapus_pada IS NULL
    GROUP BY oi.status_insiden
");
```

### 5.4 Hindari Query di Loop

```php
// SALAH ❌ — N+1 query
foreach ($insiden as $item) {
    $item->sitreps; // query baru per iterasi
}

// BENAR ✅ — eager loading sebelum loop
$insiden = OperasiInsiden::with('sitreps:id_sitrep,id_insiden,status_sitrep')
    ->paginate(15);

foreach ($insiden as $item) {
    $item->sitreps; // sudah di-load, tidak ada query tambahan
}
```

### 5.5 Hindari Eloquent di Blade

```blade
{{-- SALAH ❌ — query di Blade --}}
@foreach(OperasiInsiden::all() as $insiden)
    ...
@endforeach

{{-- BENAR ✅ — data dikirim dari Controller --}}
@foreach($insiden as $item)
    ...
@endforeach
```

---

## 6. Caching

### 6.1 Data yang WAJIB Di-cache

Cache data master yang jarang berubah. Gunakan key yang konsisten.

```php
// bencana_master_jenis — TTL 1 jam
$jenisBencana = Cache::remember('bencana_master_jenis', 3600, fn() =>
    BencanaMasterJenis::select('id_jenis', 'nama_bencana', 'kategori_bencana')
        ->orderBy('nama_bencana')
        ->get()
);

// master_satuan — TTL 1 jam
$satuan = Cache::remember('master_satuan', 3600, fn() =>
    MasterSatuan::select('id_satuan', 'nama_satuan', 'singkatan')
        ->orderBy('nama_satuan')
        ->get()
);

// auth_roles — TTL 6 jam (sangat jarang berubah)
$roles = Cache::remember('auth_roles', 21600, fn() =>
    Role::select('id', 'name', 'guard_name')
        ->get()
);

// logistik_kategori — TTL 2 jam
$kategoriLogistik = Cache::remember('logistik_kategori', 7200, fn() =>
    LogistikKategori::select('id_kategori', 'nama_kategori')
        ->orderBy('nama_kategori')
        ->get()
);
```

### 6.2 Data yang DILARANG Di-cache

| Data                                | Alasan                                             |
|-------------------------------------|----------------------------------------------------|
| `logistik_stok.jumlah_tersedia`     | Berubah real-time saat mutasi                      |
| `operasi_insiden.status_insiden`    | Berubah saat transisi status                       |
| `assessment_utama` (latest)         | Berubah saat assessment baru dibuat                |
| `operasi_sitrep` list               | Berubah saat sitrep baru di-submit                 |
| Data yang terkait dengan auth user  | Berbeda per pengguna, tidak boleh di-cache global  |

### 6.3 Invalidasi Cache

Invalidasi cache wajib dilakukan saat data master berubah:

```php
// Di Observer atau Service saat BencanaMasterJenis berubah
Cache::forget('bencana_master_jenis');

// Invalidasi semua cache master sekaligus (admin action)
Cache::flush(); // hanya jika ada isolasi cache per tag/prefix
```

### 6.4 Konfigurasi Driver Cache

```env
# .env
CACHE_STORE=redis        # gunakan redis jika tersedia
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Fallback jika Redis tidak tersedia
CACHE_STORE=file
```

> [!TIP]
> Gunakan Redis sebagai cache driver di production untuk TTL yang akurat dan dukungan tag. Jika Redis tidak tersedia, `file` cache masih lebih baik daripada tidak cache sama sekali.

---

## 7. Dashboard & Command Center Performance

### 7.1 Strategi Refresh Data

- Command center **menggunakan AJAX polling setiap 30 detik** — bukan WebSocket permanen
- Endpoint polling: `GET /api/dashboard/summary` — kembalikan JSON ringkas
- Data yang di-poll: jumlah insiden aktif, jumlah personel terdeployment, status klaster aktif
- **Jangan poll endpoint yang query-nya berat** — pre-compute dengan scheduled job jika perlu

```javascript
// Contoh polling di Blade + vanilla JS
setInterval(function () {
    fetch('/dashboard/summary-json')
        .then(res => res.json())
        .then(data => updateDashboard(data));
}, 30000); // 30 detik
```

### 7.2 Marker Peta (Leaflet.js)

- Hanya tampilkan marker insiden dengan `status_insiden` IN (`respon`, `pemulihan`)
- Jangan load marker untuk insiden `selesai` atau `dibatalkan`
- Batasi data marker: `id_insiden`, `nama_insiden`, `latitude`, `longitude`, `status_insiden` saja

```php
// Controller — endpoint GeoJSON untuk Leaflet
$markers = OperasiInsiden::select('id_insiden', 'nama_insiden', 'latitude', 'longitude', 'status_insiden')
    ->whereIn('status_insiden', ['respon', 'pemulihan'])
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### 7.3 Agregasi Dashboard

Hindari kalkulasi on-the-fly di Blade. Gunakan query teroptimasi di Controller atau pre-compute via scheduled job:

```php
// Scheduled Command: berjalan setiap 5 menit
// Simpan hasil ke cache
Cache::put('dashboard_summary', [
    'insiden_aktif'   => OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])->count(),
    'insiden_draft'   => OperasiInsiden::where('status_insiden', 'draft')->count(),
    'relawan_aktif'   => OperasiMobilisasiPersonil::where('status', 'aktif')->count(),
], 300); // cache 5 menit
```

---

## 8. Tabel Referensi Performa Cepat

| Situasi                            | Solusi Wajib                              |
|------------------------------------|-------------------------------------------|
| List data > 50 baris               | `paginate(15)` atau `paginate(20)`        |
| Relasi 1-to-many di list           | `with(['relasi:kolom1,kolom2'])`          |
| Jumlah anak (count relasi)         | `withCount('relasi')`                     |
| Query agregasi (SUM, COUNT, GROUP) | `DB::select()` + cache                   |
| Data master (jarang berubah)       | `Cache::remember()` dengan TTL           |
| Data real-time (stok, status)      | Tidak di-cache, query langsung            |
| Refresh dashboard                  | AJAX polling 30 detik                     |
| Marker peta                        | Filter hanya insiden `respon`/`pemulihan` |
| Export data besar                  | `->chunk(100)` + Queue job               |
