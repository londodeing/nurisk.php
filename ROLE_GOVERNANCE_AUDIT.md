# ROLE_GOVERNANCE_AUDIT

## Domain J — Governance Integrity

### 1. Verifikasi Lifecycle Integrity
**Goal:** Membuktikan bahwa seluruh proses persetujuan dan governance dilakukan oleh orang yang sah dan aktif pada saat tindakan dilakukan.

**Audit Obyek:**
- `operasi_surat_keluar`
- `dokumen_surat_paraf`
- `operasi_pleno_peserta`
- `SuratService` dan `SuratPolicy`

### 2. Hasil Temuan
**Pertanyaan:** Apakah user yang menandatangani (Pleno, Surat, Approval) divalidasi `masih aktif` saat melakukan approval?

**Jawaban:** **TIDAK ADA VALIDASI (GAP KRITIS)**.

Berdasarkan pengecekan source code (misalnya `app/Services/SuratService.php` dan `app/Policies/SuratPolicy.php`), validasi saat menyimpan persetujuan atau paraf hanya mengecek autentikasi user saat ini (`Auth::user()`) atau kepemilikan data (`id_pengguna`).
TIDAK ADA pengecekan apakah status user di database `status_akun` masih `aktif`.

Artinya, jika seorang Approver di-suspend atau di-nonaktifkan, namun sesi JWT/Sanctum belum di-revoke sepenuhnya atau belum logout paksa, mereka dapat tetap menandatangani dokumen operasi darurat. Hal ini melanggar integritas non-repudiasi sistem persuratan (GAP KRITIS).
