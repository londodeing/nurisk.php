# AUTH_IMPLEMENTATION_AUDIT.md — Audit Teknis Implementasi Autentikasi NURISK
# Laporan Audit Keamanan Pasca-AUTH-011 — Principal Software Architect

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Status: **FROZEN & VERIFIED** (Pemberlakuan Keras Tanpa Pengecualian)

---

## 1. PEMETAAN AKTUAL IMPLEMENTASI KODE VS SQL v37 FROZEN

Berikut adalah hasil audit silang (*cross-audit*) antara berkas yang telah dideploy dengan tabel fisik database NURISK:

### A. Kompatibilitas Tabel & Model
* **Model `AuthRole` (`app/Models/AuthRole.php`)**:
  * ✅ Mengarah ke tabel `auth_roles` (PK: `id_peran`). Timestamps dinonaktifkan (`public $timestamps = false`).
* **Model `AuthUser` (`app/Models/AuthUser.php`)**:
  * ✅ Mengarah ke tabel `auth_users` (PK: `id_pengguna`).
  * ✅ Timestamps dipetakan ke `dibuat_pada` dan `diperbarui_pada`.
  * ✅ Kolom hashing password diarahkan ke `kata_sandi` melalui override `getAuthPassword()`.

### B. Alur Autentikasi (Service Layer)
* **`AuthenticationService` (`app/Services/Auth/AuthenticationService.php`)**:
  * ✅ Menggunakan login berbasis `no_hp` dan verifikasi hash `kata_sandi`.
  * ✅ Memblokir pengguna jika status akun tidak aktif (`status_akun !== 'aktif'`).
  * ✅ Memperbarui kolom `terakhir_masuk` secara aman saat login sukses.

---

## 2. AUDIT TERHADAP MATRIKS OTORISASI (`AUTHORIZATION_MATRIX.md`)

Setiap kali pengguna berhasil login ke dalam sistem, otorisasi multidimensi ditentukan dari:
1. **Role Global**: Didapat langsung dari relasi `AuthUser::peran()` (via kolom `id_peran` yang menunjuk ke `auth_roles`).
2. **Jabatan Aktif**: Harus diambil secara berkala dari tabel transaksional `pengguna_jabatan` di mana `status_aktif = 1` dan `berakhir_pada IS NULL` / `berakhir_pada > NOW()`.
3. **Scope Wilayah**: Dibaca langsung dari `default_scope_type` dan `default_scope_id` pada model `AuthUser`.
4. **Assignment Operasional**: Ditentukan dari keberadaan record aktif di tabel `operasi_penugasan` untuk insiden terkait.

---

## 3. KEPUTUSAN ARSITEKTUR: SESSION PAYLOAD STRATEGY
* **Masalah**: Laravel secara default hanya menyimpan ID pengguna (`id_pengguna`) di session data payload. Mengambil role, scope, dan jabatan aktif langsung dari kueri database pada setiap HTTP Request (melalui model `AuthUser`) akan menyebabkan masalah performa (*query overhead*).
* **Solusi Terpilih**:
  * Menyimpan **Role Global** (`nama_peran` & `level_otoritas`) dan **Scope Wilayah** (`default_scope_type` & `default_scope_id`) ke dalam **Session State** saat proses login sukses di `LoginController`.
  * Untuk **Jabatan Aktif** dan **Assignment Lapangan**, data tetap dibaca real-time dari database karena sifatnya yang dinamis dan sensitif terhadap transisi status operasional.

---

## 4. IDENTIFIKASI GAP & TINGKAT RISIKO

### A. Level Kritis (High Risk)
* **Gap 01: Penyalahgunaan Auth Provider di Middleware Guard**:
  * *Deskripsi*: Belum ada middleware yang secara aktif memvalidasi scope wilayah regional dan membatasi akses URL berdasarkan `default_scope_id`.
  * *Tingkat Risiko*: **Kritis**.
  * *Rekomendasi Tindakan*: Buat middleware custom `ScopeEnclosure` untuk mengisolasi request PCNU/MWC.

### B. Level Menengah (Medium Risk)
* **Gap 02: Sinkronisasi Status Sesi Pasca-Suspend**:
  * *Deskripsi*: Jika admin men-suspend pengguna yang sedang login aktif, pengguna tersebut masih bisa berselancar di sistem sebelum session-nya kedaluwarsa.
  * *Tingkat Risiko*: **Menengah**.
  * *Rekomendasi Tindakan*: Tambahkan middleware `CheckAccountStatus` yang memvalidasi `status_akun == 'aktif'` di setiap request.

---

## 5. KESIMPULAN KESIAPAN SPRINT

### **STATUS: READY**

**Alasan Teknis**:
1. Fondasi autentikasi (`AUTH-001` s.d. `AUTH-011`) telah terpasang dengan aman tanpa merusak SQL v37 Frozen.
2. Keputusan arsitektur penyimpanan session payload telah didefinisikan.
3. Langkah berikutnya adalah membangun **`AUTH-012` (Middleware Role & Scope)** sebelum menyentuh antarmuka Dashboard.
