# AI_EXECUTION_WORKFLOW.md — Protokol Eksekusi AI Agent
# Alur Kerja dan Validasi Mandiri — Technical Lead & Architect

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Status: FROZEN (Aturan Wajib Tanpa Pengecualian)

---

## ⛔ PROTOKOL UTAMA: EKSEKUSI TUNGGAL (SINGLE-TASK PROTOCOL)

1. AI Agent **DILARANG** mengerjakan lebih dari 1 (satu) Atomic Task dalam satu sesi pengerjaan.
2. AI Agent **DILARANG** melanjutkan ke task berikutnya sebelum task aktif saat ini berhasil dibuat kodenya, diuji (pass testing), dan disetujui.
3. Setiap tugas harus menghasilkan kode produksi yang bersih dan unit/feature test yang lulus.

---

## 🔄 ALUR EKSEKUSI 6 LANGKAH

### Langkah 1: Membaca & Memahami Spesifikasi
Sebelum menulis kode apa pun, AI Agent wajib membaca dokumen-dokumen berikut untuk konteks:
* `docs/SYSTEM_ARCHITECTURE.md` (Arsitektur dasar)
* `docs/DOMAIN_RULES.md` (Logika bisnis spesifik domain)
* `docs/AI_DEVELOPMENT_RULES.md` (Pedoman larangan coding)
* `docs/ATOMIC_TASK_BACKLOG.md` (Mencari ID task aktif saat ini, misal: `AUTH-001`)

### Langkah 2: Implementasi Kode Terfokus
* Kerjakan **hanya** apa yang tertulis di target file output dari task tersebut.
* **ATURAN DATABASE-FIRST**: Jangan membuat migrasi Laravel baru untuk tabel yang sudah ada di SQL Dump Frozen. Hubungkan langsung model Eloquent ke tabel database yang sudah ada dengan menentukan `$table`, `$primaryKey`, `$timestamps` (menggunakan kolom `dibuat_pada` dan `diperbarui_pada`), serta properti `$incrementing` yang sesuai.

### Langkah 3: Penulisan Uji Fitur & Uji Unit (Testing)
* Buat berkas pengujian terkait untuk task tersebut di bawah direktori `tests/`.
* Tulis minimal dua skenario:
  * **Skenario Sukses (Happy Path)**.
  * **Skenario Gagal/Otorisasi (Unhappy & Unauthorized Path)**.

### Langkah 4: Validasi & Self-Review (DoD Checklist)
Sebelum mengakhiri turn, AI Agent wajib memvalidasi kodenya terhadap standar kualitas:
* Tidak ada query SQL langsung di dalam file Blade.
* Semua input transaksional divalidasi via Form Request.
* Seluruh operasi multi-tabel dibungkus di dalam `DB::transaction()`.
* Jalankan pengujian lokal dengan PHPUnit: `./vendor/bin/phpunit` dan pastikan berwarna **hijau (PASS)**.

### Langkah 5: Memperbarui Status Proyek (`PROJECT_STATUS.md`)
Perbarui baris progress modul dan sprint yang sedang dikerjakan pada [PROJECT_STATUS.md](file:///home/londo/nurisk/docs/PROJECT_STATUS.md) dan tandai checklist tugas di [MODULE_CHECKLIST.md](file:///home/londo/nurisk/docs/MODULE_CHECKLIST.md) menjadi `[x]`.

### Langkah 6: Menghentikan Pekerjaan & Menyerahkan Turn
* Hentikan eksekusi.
* Laporkan hasil pengerjaan (nama berkas yang dimodifikasi, status testing, link berkas) secara singkat kepada USER.
* **DILARANG** memicu task baru secara otomatis. Tunggu instruksi atau persetujuan tertulis dari USER.

---

## 🛠️ CONTOH IMPLEMENTASI DATABASE-FIRST (SINKRON SQL V37)

Saat menulis model Laravel, hindari asumsi nama kolom bahasa Inggris. Gunakan pemetaan berikut untuk keselarasan dengan database riil NURISK:

```python
# Contoh struktur model Laravel yang benar (Database-First):
class AuthUser extends Authenticatable
{
    protected $table = 'auth_users';
    protected $primaryKey = 'id_pengguna';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    
    protected $fillable = [
        'username',
        'kata_sandi',
        'email',
        'status_akun',
        'id_peran',
        'default_scope_type',
        'default_scope_id'
    ];
}
```
*(Catatan: Jangan gunakan $table->timestamps() di migrasi karena kolom bawaan Laravel default ke created_at/updated_at, sedangkan NURISK menggunakan dibuat_pada/diperbarui_pada).*
