# ADMIN_LIFECYCLE_AUDIT

## Domain I — Administrator Lifecycle

### 1. Ketersediaan Role
Berdasarkan `database/seeders/AuthRoleSeeder.php` dan tabel `auth_roles`, daftar role yang diverifikasi:

| Role Diminta | Status Sistem Saat Ini |
|---|---|
| SUPERADMIN | IMPLEMENTED (`super_admin`) |
| PWNU | IMPLEMENTED (`pwnu`) |
| PCNU | IMPLEMENTED (`pcnu`) |
| COMMANDER | **MISSING** |
| OPERATOR | **MISSING** |
| TRC | **MISSING** |
| CLUSTER_COORDINATOR | **MISSING** |
| APPROVER | **MISSING** |

### 2. Mekanisme Lifecycle Role
- **Pemberian Role**: Dilakukan dengan mengubah nilai `id_peran` pada tabel `auth_users`. Namun, ini memiliki gap sinkronisasi dengan Spatie Permission (`model_has_roles`).
- **Pencabutan Role**: Mekanisme yang sama dengan pemberian role (update `id_peran`).
- **Perubahan Role**: Tidak terdapat tabel histori `pengguna_jabatan` yang mengikat role secara ketat di sisi otorisasi teknis.
- **Audit Trail**: **MISSING**. Tidak ada log yang mencatat siapa yang memberikan, mencabut, atau mengubah role seorang administrator.
