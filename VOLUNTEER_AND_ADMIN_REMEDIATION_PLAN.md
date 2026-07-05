# VOLUNTEER AND ADMIN REMEDIATION PLAN

## 1. Existing State
Sistem telah menerapkan tabel `auth_users` kustom dan sistem relawan/penugasan dasar yang mendukung log operasi dasar dan offline sync. Namun, lifecycle flow (statemachine) di banyak tempat tidak ada, tidak konsisten, atau tidak memiliki validasi integritas otoritas.

## 2. Missing Lifecycle
- **User Lifecycle**: Kehilangan status `REGISTERED`, dan `ARCHIVED`.
- **Volunteer Lifecycle**: Kehilangan statemachine komprehensif (`AVAILABLE`, `SICK`, `INACTIVE`, `ARCHIVED`).
- **Assignment Lifecycle**: Kehilangan step krusial `DRAFT`, `ACCEPTED`, `ON_ROUTE`, `ON_SITE`, `CANCELLED`, `REJECTED`.

## 3. Missing Tables
- `master_sertifikasi` & `relawan_sertifikasi` (Kompetensi advance dan sertifikasi medis/SAR).
- `audit_trail_role` (Riwayat pengubahan level/privilege Admin).
- `relawan_kehadiran_harian` (Tabel presensi harian / multi-checkin per penugasan).
- `master_shift` (Standardisasi shift: pagi, siang, malam).

## 4. Missing Validation
- **GAP KRITIS**: Modul Governance (Approval, Paraf Surat, Keputusan Pleno) tidak memvalidasi `status_akun` == `aktif` pada saat persetujuan final dilakukan.
- Tidak ada revokasi token massal secara sistematis saat user diubah menjadi `suspend` atau `nonaktif`.

## 5. Missing Workflow
- **Statemachine Penugasan**: Tidak ada alur terima/tolak penugasan oleh relawan (Assignment Acknowledgment).
- **Statemachine Pembatalan**: Penugasan yang dibatalkan tidak ada alasannya, hanya dihapus.

## 6. Required Migration
- **M1:** Menambah enum/tabel referensi statemachine Assignment di `operasi_penugasan`.
- **M2:** Menambah tabel `master_sertifikasi` dan many-to-many relasinya.
- **M3:** Menambah `deleted_at` di tabel `auth_users`.
- **M4:** Menambah/memperbarui tabel `auth_roles` untuk menyertakan `COMMANDER`, `OPERATOR`, `TRC`, `CLUSTER_COORDINATOR`, `APPROVER`.

## 7. Required Services
- `AssignmentLifecycleService`: Mengelola transisi status Assignment.
- `GovernanceIntegrityService`: Middleware / Check khusus untuk operasi validasi ttd/paraf aktif.

## 8. Required API
- API transisi status Relawan (Sakit, Tidak Aktif).
- API Acknowledge Assignment (Accept/Reject).
- API Attendance Check-in Harian.

## 9. Required UI
- UI Assignment History & Statemachine tracker.
- UI Profile (Upload Foto, Golongan Darah, Kontak Darurat).
- UI Role Management History.

## 10. Sprint Recommendation
- **Sprint 20B**: Security & Governance Hotfix (P0 - Integrity Check Approval).
- **Sprint 20C**: Assignment State Machine & Competency Upgrade (P1).
- **Sprint 20D**: Attendance & Shift Rework (P2).

---

# Final Decision

**NOT READY**

**Alasan Teknis:**
Berdasarkan bukti kode sumber dan tabel database:
1. Validasi persetujuan surat/pleno tidak memverifikasi apakah akun sedang suspend/non-aktif saat itu juga, sehingga cacat integritas secara hukum administratif.
2. Tidak ada Assignment State Machine, yang menyebabkan Mustering/Pengerahan mustahil dilacak secara realtime karena tidak jelas siapa yang sedang `ON_ROUTE`, `ON_SITE`, atau `REJECTED`.
3. Kehilangan master kompetensi krusial seperti Sertifikasi yang mencegah pengerahan presisi.
4. Hilangnya banyak role penting di tabel `auth_roles` seperti COMMANDER dan APPROVER.
