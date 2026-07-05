# ARCH-003B — AUTHORIZATION AUDIT

## Framework Otorisasi 4-Lapis
Menurut `AUTHORIZATION_MATRIX.md`, setiap request ke Domain Assessment wajib divalidasi dengan 4 lapis perlindungan. Implementasi saat ini pada `AssessmentPolicy` tidak lengkap.

### Lapis 1: Global Role
- **Kepatuhan**: Sebagian. `AssessmentPolicy` menggunakan `$authCtx->hasAnyRole()` tetapi logika kombinasi dengan scope wilayah belum kokoh.

### Lapis 2: Jabatan Organisasi
- **Kepatuhan**: NA (Domain assessment tidak memerlukan pengecekan Tanda Tangan Dokumen saat ini, sehingga Lapis 2 belum relevan untuk operasi CRUD utama assessment).

### Lapis 3: Scope Jurisdiction
- **Kepatuhan**: Ada pelanggaran.
- **Logika Seharusnya**: PCNU hanya dapat melihat dan membuat assessment pada insiden yang berada di jurisdiksinya (`insiden.id_pcnu == auth_users.default_scope_id`).
- **Implementasi Saat Ini**: `$this->authCtx()->getScopeId() === $insiden->id_pcnu` sudah ada di `bolehAksesInsiden`, ini CUKUP untuk Lapis 3.

### Lapis 4: Operational Assignment
- **Kepatuhan**: GAGAL.
- **Logika Seharusnya**: Pengguna dengan role `relawan` hanya diizinkan membuat/melihat assessment JIKA mereka memiliki entri aktif di `operasi_penugasan` untuk insiden tersebut. Khusus untuk pembuatan assessment, `peran_otoritas` mereka di insiden tersebut idealnya adalah `trc` atau peran yang relevan.
- **Implementasi Saat Ini**: Komentar `// Additional roles logic could be added here for Komandan Posko / Relawan` menunjukkan bahwa relawan **selalu ditolak** karena tidak ada validasi Lapis 4.

## Audit Matrix

| Role | Action | Expected Behavior | Current Behavior | Status |
| --- | --- | --- | --- | --- |
| `super_admin` | Create Assessment | Allowed | Allowed | COMPLIANT |
| `pwnu` | Create Assessment | Allowed | Allowed | COMPLIANT |
| `pcnu` (In-Scope) | Create Assessment | Allowed | Allowed | COMPLIANT |
| `pcnu` (Out-of-Scope)| Create Assessment | Denied | Denied | COMPLIANT |
| `relawan` (Assigned TRC)| Create Assessment | Allowed | Denied | VIOLATION |
| `relawan` (Unassigned)| Create Assessment | Denied | Denied | COMPLIANT |

## Rekomendasi Hardening
1. Implementasi pengecekan `OperasiPenugasan` untuk relawan di `AssessmentPolicy`.
2. Gunakan method Helper dari `BaseNuriskPolicy` jika ada, atau panggil langsung query pencarian penugasan aktif untuk Lapis 4.
