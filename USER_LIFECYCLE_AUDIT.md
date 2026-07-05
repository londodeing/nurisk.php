# USER_LIFECYCLE_AUDIT

## Domain A — User Lifecycle

### 1. Proses Akun
- **Bagaimana akun dibuat**: Melalui instansiasi model `AuthUser` dan tabel custom `auth_users`.
- **Bagaimana akun diaktifkan**: Pengaturan field `status_akun` menjadi `aktif`.
- **Bagaimana akun dinonaktifkan**: Pengaturan field `status_akun` menjadi `nonaktif`.
- **Bagaimana akun diblokir**: Pengaturan field `status_akun` menjadi `suspend`.
- **Bagaimana akun dihapus**: Tidak ada mekanisme soft-delete (`deleted_at`/`dihapus_pada`) di tabel `auth_users`, sehingga penghapusan berarti hard-delete atau tidak diizinkan.
- **Bagaimana akun berpindah role**: Melalui perubahan kolom `id_peran` pada `auth_users`, namun terdapat potensi out-of-sync dengan Spatie Permissions (tabel `model_has_roles`).

### 2. Verifikasi Status Lifecycle
Status lifecycle pengguna diverifikasi berdasarkan tabel `auth_users`:

- `REGISTERED` — **MISSING**
- `PENDING_VERIFICATION` — **PARTIAL** (terdapat status `menunggu`)
- `ACTIVE` — **IMPLEMENTED** (terdapat status `aktif`)
- `SUSPENDED` — **IMPLEMENTED** (terdapat status `suspend`)
- `DISABLED` — **IMPLEMENTED** (terdapat status `nonaktif`)
- `ARCHIVED` — **MISSING**

### Kesimpulan & GAP
Terdapat dua sistem User/Role yang berjalan paralel (Laravel default `users` vs NURISK custom `auth_users`). Lifecycle belum lengkap secara statemachine.

**Gap Classification:** P0 = Harus diperbaiki sebelum produksi.
