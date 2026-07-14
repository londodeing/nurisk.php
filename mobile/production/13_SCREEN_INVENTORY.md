# NURISK MOBILE — SCREEN INVENTORY
## Document 13: Screen Inventory & Scope
**Version**: 2.0.0 | **Status**: REVISED — PUBLIC FIRST | **Domain**: Platform-Wide

> **⚠️ PARADIGMA BERUBAH (v2.0)**  
> Inventaris sekarang dibagi dua layer: **PUBLIC SCREENS** dan **GOVERNANCE SCREENS**.  
> Referensi: `FLUTTER_APPLICATION_ARCHITECTURE.md` Article 2 & 3.

---

## LEGENDA

| Symbol | Arti |
|--------|------|
| F1 | Sprint F1 (Public Layer + Auth + Governance MVP) |
| F2 | Sprint F2 (Operasional) |
| F3 | Sprint F3 (Logistik + Media full) |
| F4 | Sprint F4 (Advanced features) |
| 🔐 | Wajib auth |
| 🔓 | Publik (tidak perlu auth) |

---

# ═══════════════════════════════════════
# PUBLIC LAYER SCREENS (Tidak perlu login)
# ═══════════════════════════════════════

## P0. CORE & SPLASH

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P0.01 | Splash | `/` | F1 | 🔓 | Loading init → /p/home |

---

## P1. PUBLIC HOME & DASHBOARD

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P1.01 | Public Home | `/p/home` | F1 | 🔓 | KPI bencana, feed kejadian, cuaca, mini map |
| P1.02 | Public Map | `/p/map` | F1 | 🔓 | Peta interaktif insiden, posko, shelter |
| P1.03 | Statistik Publik | `/p/statistik` | F2 | 🔓 | Data statistik bencana |

---

## P2. KEJADIAN & BENCANA (Publik)

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P2.01 | Daftar Kejadian | `/p/incidents` | F1 | 🔓 | Feed kejadian terbaru, filter jenis |
| P2.02 | Detail Kejadian | `/p/incidents/:id` | F1 | 🔓 | Detail insiden: lokasi, dampak, kebutuhan |
| P2.03 | Submit Laporan | `/p/lapor` | F1 | 🔓 | Form laporan kejadian + foto (publik) |
| P2.04 | Peta Laporan | `/p/laporan/peta` | F1 | 🔓 | Peta sebaran laporan masyarakat |
| P2.05 | Konfirmasi Laporan | `/p/lapor/sukses` | F1 | 🔓 | Halaman sukses submit laporan |

---

## P3. CUACA & PERINGATAN DINI

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P3.01 | Prakiraan Cuaca | `/p/cuaca` | F1 | 🔓 | Cuaca hari ini, prakiraan, risiko |
| P3.02 | Cuaca Detail Jam | `/p/cuaca/hourly` | F2 | 🔓 | Cuaca per jam |
| P3.03 | Alert Gempa (BMKG) | `/p/cuaca/gempa` | F1 | 🔓 | Gempa terbaru dari BMKG |
| P3.04 | Risiko Cuaca | `/p/cuaca/risiko` | F2 | 🔓 | Level risiko cuaca wilayah |

---

## P4. REGISTRASI & AKUN PUBLIK

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P4.01 | Daftar Relawan | `/p/daftar-relawan` | F1 | 🔓 | Form registrasi relawan baru |
| P4.02 | Daftar Anggota | `/p/daftar-anggota` | F1 | 🔓 | Form registrasi anggota NU |
| P4.03 | Pilih Jenis Daftar | `/p/daftar` | F1 | 🔓 | Pilih relawan atau anggota |
| P4.04 | Menunggu Verifikasi | `/p/daftar/pending` | F1 | 🔓 | Status menunggu persetujuan |
| P4.05 | Akun (belum login) | `/p/account` | F1 | 🔓 | Halaman masuk / daftar |
| P4.06 | Login | `/auth/login` | F1 | 🔓 | Form login no_hp + password |
| P4.07 | Lupa Password | `/auth/forgot-password` | F2 | 🔓 | Reset password (backend Sprint F2) |
| P4.08 | Input OTP | `/auth/otp` | F2 | 🔓 | Verifikasi OTP |
| P4.09 | Reset Password | `/auth/reset-password` | F2 | 🔓 | Input password baru |

---

## P5. INFORMASI PUBLIK

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| P5.01 | Tentang NU Peduli | `/p/tentang` | F1 | 🔓 | Profil organisasi NU Peduli |
| P5.02 | Nomor Darurat | `/p/darurat` | F1 | 🔓 | Kontak darurat per wilayah |
| P5.03 | FAQ | `/p/faq` | F2 | 🔓 | Pertanyaan umum |
| P5.04 | Kontak | `/p/kontak` | F2 | 🔓 | Formulir kontak |
| P5.05 | Artikel | `/p/artikel` | F3 | 🔓 | Artikel kebencanaan |
| P5.06 | Detail Artikel | `/p/artikel/:slug` | F3 | 🔓 | Detail artikel |
| P5.07 | Donasi Lazisnu | `/p/donasi` | F3 | 🔓 | Link / info donasi |

---

# ══════════════════════════════════════════
# GOVERNANCE LAYER SCREENS (Wajib login)
# ══════════════════════════════════════════

## G0. AUTH & MANDATE SCREENS

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G0.01 | Mandate Picker | `/g/mandate-picker` | F1 | 🔐 | Pilih posisi aktif (jika > 1) |
| G0.02 | Lock Screen | `/g/lock` | F1 | 🔐 | PIN/Biometric re-auth |
| G0.03 | Permission Denied (403) | `/g/403` | F1 | 🔐 | No permission state |
| G0.04 | Bootstrap Sync | `/g/bootstrap` | F1 | 🔐 | First-time governance data loading |

---

## G1. GOVERNANCE DASHBOARD

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G1.01 | Governance Dashboard (Relawan) | `/g/dashboard` | F1 | 🔐 | Dashboard role relawan |
| G1.02 | Governance Dashboard (Operator) | `/g/dashboard` | F1 | 🔐 | Dashboard operasional |
| G1.03 | Governance Dashboard (PWNU/SA) | `/g/dashboard` | F1 | 🔐 | Dashboard command center |
| G1.04 | Command Center Map | `/g/dashboard/peta` | F2 | 🔐 | Full-screen peta governance |

---

## G2. PROFIL & PENGATURAN

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G2.01 | Profil | `/g/profile` | F1 | 🔐 | Profil lengkap user |
| G2.02 | Edit Profil | `/g/profile/edit` | F1 | 🔐 | Edit nama, foto, dll |
| G2.03 | Pengaturan | `/g/settings` | F1 | 🔐 | Pengaturan umum |
| G2.04 | Pengaturan — Keamanan | `/g/settings/security` | F1 | 🔐 | PIN, biometric, timeout |
| G2.05 | Pengaturan — Notifikasi | `/g/settings/notifications` | F1 | 🔐 | Preferensi notif |
| G2.06 | Pengaturan — Posisi Aktif | `/g/settings/mandate` | F1 | 🔐 | Ganti posisi aktif |
| G2.07 | Pengaturan — Upload Queue | `/g/settings/upload-queue` | F2 | 🔐 | Status media upload |
| G2.08 | Pengaturan — Sinkronisasi | `/g/settings/sync` | F1 | 🔐 | Status dan manual sync |
| G2.09 | Pengaturan — Perangkat | `/g/settings/devices` | F2 | 🔐 | Daftar device aktif |
| G2.10 | Tentang Aplikasi | `/g/settings/about` | F1 | 🔐 | Versi, lisensi |
| G2.11 | Ganti Password | `/g/settings/change-password` | F2 | 🔐 | Ganti password |

---

## G3. GOVERNANCE — MANDATE & APPROVAL

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G3.01 | Governance Inbox | `/g/governance/inbox` | F1 | 🔐 | Semua item approval |
| G3.02 | Inbox Detail | `/g/governance/inbox/:id` | F1 | 🔐 | Detail item approval |
| G3.03 | Mandate List | `/g/governance/mandates` | F1 | 🔐 | Daftar mandate user |
| G3.04 | Mandate Detail | `/g/governance/mandates/:id` | F1 | 🔐 | Detail mandate + SK + Node |
| G3.05 | Delegation List | `/g/governance/delegations` | F1 | 🔐 | Daftar delegasi |
| G3.06 | Create Delegation | `/g/governance/delegations/create` | F1 | 🔐 | Form delegasi baru |
| G3.07 | Delegation Detail | `/g/governance/delegations/:id` | F1 | 🔐 | Detail delegasi |
| G3.08 | Org Tree | `/g/governance/org-tree` | F1 | 🔐 | Struktur organisasi |
| G3.09 | Node Detail | `/g/governance/nodes/:id` | F1 | 🔐 | Detail node + jabatan |
| G3.10 | SK List | `/g/governance/sks` | F2 | 🔐 | Daftar SK |
| G3.11 | SK Detail | `/g/governance/sks/:id` | F2 | 🔐 | Detail SK |
| G3.12 | Audit Trail | `/g/governance/audit-trail` | F2 | 🔐 | Riwayat governance |
| G3.13 | Pleno List | `/g/governance/pleno` | F3 | 🔐 | Daftar pleno |
| G3.14 | Pleno Detail | `/g/governance/pleno/:id` | F3 | 🔐 | Detail pleno |

---

## G4. SURAT & PARAF

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G4.01 | Surat List | `/g/surat` | F2 | 🔐 | Daftar surat |
| G4.02 | Surat Detail | `/g/surat/:id` | F2 | 🔐 | Detail surat + alur paraf |
| G4.03 | Create Surat | `/g/surat/create` | F2 | 🔐 | Form buat surat baru |
| G4.04 | Edit Surat | `/g/surat/:id/edit` | F2 | 🔐 | Edit surat (draft only) |
| G4.05 | Paraf Inbox | `/g/surat/paraf` | F2 | 🔐 | Surat menunggu paraf |
| G4.06 | Paraf Review | `/g/surat/:id/paraf` | F2 | 🔐 | Review dan tanda paraf |

---

## G5. NOTIFIKASI INTERNAL

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G5.01 | Notification List | `/g/notifications` | F1 | 🔐 | Notifikasi internal |
| G5.02 | Notification Detail | `/g/notifications/:id` | F1 | 🔐 | Detail notifikasi |

---

## G6. LAPORAN KEJADIAN (Admin View)

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G6.01 | Laporan List | `/g/laporan` | F2 | 🔐 | Daftar laporan masuk |
| G6.02 | Laporan Detail | `/g/laporan/:id` | F2 | 🔐 | Detail + validasi |
| G6.03 | Laporan Peta Admin | `/g/laporan/peta` | F2 | 🔐 | Peta sebaran laporan |

---

## G7. INSIDEN OPERASIONAL

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G7.01 | Insiden List | `/g/operasi/insiden` | F2 | 🔐 | Daftar insiden aktif |
| G7.02 | Create Insiden | `/g/operasi/insiden/create` | F2 | 🔐 | Form buat insiden |
| G7.03 | Insiden Detail | `/g/operasi/insiden/:id` | F2 | 🔐 | Detail insiden |
| G7.04 | Edit Insiden | `/g/operasi/insiden/:id/edit` | F2 | 🔐 | Edit insiden |
| G7.05 | Insiden Timeline | `/g/operasi/insiden/:id/timeline` | F3 | 🔐 | Timeline kejadian |
| G7.06 | Assessment | `/g/operasi/insiden/:id/assessment` | F2 | 🔐 | Input assessment dampak |
| G7.07 | Sitrep | `/g/operasi/insiden/:id/sitrep` | F2 | 🔐 | Situation Report harian |

---

## G8. POSKO (POSAJU)

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G8.01 | Posko List | `/g/operasi/posko` | F2 | 🔐 | Daftar posko aktif |
| G8.02 | Posko Detail | `/g/operasi/posko/:id` | F2 | 🔐 | Dashboard posko |
| G8.03 | Create Posko | `/g/operasi/posko/create` | F2 | 🔐 | Form buat posko |
| G8.04 | Posko Klaster | `/g/operasi/posko/:id/klaster` | F2 | 🔐 | Klaster di posko |

---

## G9. PENUGASAN & MOBILISASI

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G9.01 | Penugasan List | `/g/operasi/penugasan` | F2 | 🔐 | Daftar penugasan |
| G9.02 | Penugasan Saya | `/g/operasi/penugasan/saya` | F1 | 🔐 | Penugasan aktif milik saya |
| G9.03 | Penugasan Detail | `/g/operasi/penugasan/:id` | F2 | 🔐 | Detail penugasan |
| G9.04 | Create Penugasan | `/g/operasi/penugasan/create` | F2 | 🔐 | Form penugasan baru |
| G9.05 | Mobilisasi List | `/g/operasi/mobilisasi` | F2 | 🔐 | Daftar mobilisasi |
| G9.06 | Mobilisasi Detail | `/g/operasi/mobilisasi/:id` | F2 | 🔐 | Detail + approve |
| G9.07 | Create Mobilisasi | `/g/operasi/mobilisasi/create` | F2 | 🔐 | Form mobilisasi baru |

---

## G10. RELAWAN (Governance View)

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G10.01 | Relawan List | `/g/relawan` | F2 | 🔐 | Daftar relawan wilayah |
| G10.02 | Relawan Detail | `/g/relawan/:id` | F2 | 🔐 | Profil relawan |
| G10.03 | Status Ketersediaan | `/g/relawan/status` | F1 | 🔐 | Update status saya |

---

## G11. ASET

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G11.01 | Aset List | `/g/aset` | F3 | 🔐 | Daftar aset |
| G11.02 | Aset Detail | `/g/aset/:id` | F3 | 🔐 | Detail aset |
| G11.03 | Aset Tersedia | `/g/aset/tersedia` | F3 | 🔐 | Aset siap pakai |
| G11.04 | Create Aset | `/g/aset/create` | F3 | 🔐 | Form tambah aset |

---

## G12. LOGISTIK

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G12.01 | Logistik List | `/g/logistik` | F3 | 🔐 | Daftar stok logistik |
| G12.02 | Logistik Detail | `/g/logistik/:id` | F3 | 🔐 | Detail stok |
| G12.03 | Permintaan Logistik | `/g/logistik/minta` | F3 | 🔐 | Form permintaan barang |

---

## G13. MEDIA INTERNAL

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| G13.01 | Media Gallery | `/g/media` | F2 | 🔐 | Galeri media entitas |
| G13.02 | Media Full View | `/g/media/:id` | F2 | 🔐 | Full screen preview |
| G13.03 | Upload Queue | `/g/media/upload-queue` | F2 | 🔐 | Status upload queue |

---

## SUMMARY (REVISED)

| Layer | Sprint | Jumlah Screen | Focus |
|-------|--------|--------------|-------|
| **PUBLIC** | F1 | ~18 screen | Splash, Home, Map, Kejadian, Lapor, Cuaca, Auth, Info |
| **PUBLIC** | F2+ | ~12 screen | Statistik, Artikel, Cuaca detail |
| **GOVERNANCE** | F1 | ~25 screen | Dashboard, Mandate, Inbox, Profile, Settings |
| **GOVERNANCE** | F2 | ~35 screen | Insiden, Laporan, Posko, Penugasan |
| **GOVERNANCE** | F3 | ~15 screen | Logistik, Aset, Media |
| **GOVERNANCE** | F4 | ~10 screen | Pleno, Advanced features |
| **TOTAL** | — | **~115 screen** | Full Public ERP Mobile |

> ⚠️ Sprint F1 sekarang mencakup **Public Layer penuh** + **Governance MVP**. Ini adalah perbedaan signifikan dari v1.0 yang hanya menargetkan governance MVP.

---

*Document Status: REVISED v2.0 — Public First paradigm. Referensi: FLUTTER_APPLICATION_ARCHITECTURE.md*


---

## 1. ONBOARDING & AUTHENTICATION

| # | Screen Name | Route | Sprint | Auth | Keterangan |
|---|-------------|-------|--------|------|------------|
| 1.01 | Splash | `/` | F1 | 🔓 | Loading init + routing |
| 1.02 | Login | `/login` | F1 | 🔓 | Form login no_hp + password |
| 1.03 | Registration (Jenis) | `/register` | F1 | 🔓 | Pilih jenis pendaftaran |
| 1.04 | Registration (Relawan) | `/register/relawan` | F1 | 🔓 | Form daftar relawan |
| 1.05 | Registration (Anggota) | `/register/anggota` | F1 | 🔓 | Form daftar anggota |
| 1.06 | Registration Pending | `/register/pending` | F1 | 🔓 | Menunggu persetujuan |
| 1.07 | Mandate Picker | `/mandate-picker` | F1 | 🔐 | Pilih posisi aktif |
| 1.08 | Lock Screen | `/lock` | F1 | 🔐 | PIN/Biometric re-auth |
| 1.09 | Permission Denied (403) | `/403` | F1 | 🔐 | No permission state |
| 1.10 | Forgot Password | `/forgot-password` | F2 | 🔓 | Request reset (backend belum ada) |
| 1.11 | OTP Verification | `/otp` | F2 | 🔓 | Input OTP |
| 1.12 | Reset Password | `/reset-password` | F2 | 🔓 | Input password baru |

---

## 2. DASHBOARD

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 2.01 | Dashboard (Relawan) | `/dashboard` | F1 | Dashboard role relawan |
| 2.02 | Dashboard (Operator/PCNU) | `/dashboard` | F1 | Dashboard operasional |
| 2.03 | Dashboard (PWNU/Super Admin) | `/dashboard` | F1 | Dashboard command center |
| 2.04 | Command Center Map | `/dashboard/peta` | F2 | Full-screen peta insiden |
| 2.05 | Bootstrap Sync | `/bootstrap` | F1 | First-time data loading |

---

## 3. PROFILE & SETTINGS

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 3.01 | Profile | `/profile` | F1 | Profil lengkap user |
| 3.02 | Edit Profile | `/profile/edit` | F1 | Edit nama, foto, dll |
| 3.03 | Settings | `/settings` | F1 | Pengaturan umum |
| 3.04 | Settings — Keamanan | `/settings/security` | F1 | PIN, biometric, timeout |
| 3.05 | Settings — Notifikasi | `/settings/notifications` | F1 | Preferensi notif |
| 3.06 | Settings — Posisi Aktif | `/settings/mandate` | F1 | Ganti posisi aktif |
| 3.07 | Settings — Upload Queue | `/settings/upload-queue` | F2 | Status media upload |
| 3.08 | Settings — Sinkronisasi | `/settings/sync` | F1 | Status dan manual sync |
| 3.09 | Settings — Perangkat | `/settings/devices` | F2 | Daftar device aktif |
| 3.10 | Tentang Aplikasi | `/settings/about` | F1 | Versi, lisensi |
| 3.11 | Change Password | `/settings/change-password` | F2 | Ganti password |

---

## 4. GOVERNANCE

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 4.01 | Governance Inbox | `/governance/inbox` | F1 | Semua item approval + notif |
| 4.02 | Inbox Detail — Approval | `/governance/inbox/:id` | F1 | Detail item approval |
| 4.03 | Mandate List | `/governance/mandates` | F1 | Daftar semua mandate user |
| 4.04 | Mandate Detail | `/governance/mandates/:id` | F1 | Detail mandate + SK + Node |
| 4.05 | Delegation List | `/governance/delegations` | F1 | Daftar delegasi |
| 4.06 | Create Delegation | `/governance/delegations/create` | F1 | Form buat delegasi baru |
| 4.07 | Delegation Detail | `/governance/delegations/:id` | F1 | Detail delegasi |
| 4.08 | Org Tree | `/governance/org-tree` | F1 | Struktur organisasi (tree view) |
| 4.09 | Node Detail | `/governance/nodes/:id` | F1 | Detail node + jabatan |
| 4.10 | SK List | `/governance/sks` | F2 | Daftar SK |
| 4.11 | SK Detail | `/governance/sks/:id` | F2 | Detail SK |
| 4.12 | Audit Trail | `/governance/audit-trail` | F2 | Riwayat tindakan governance |
| 4.13 | Pleno List | `/governance/pleno` | F3 | Daftar pleno/musyawarah |
| 4.14 | Pleno Detail | `/governance/pleno/:id` | F3 | Detail pleno |

---

## 5. SURAT (DOKUMEN DIGITAL)

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 5.01 | Surat List | `/surat` | F2 | Daftar surat |
| 5.02 | Surat Detail | `/surat/:id` | F2 | Detail surat + alur paraf |
| 5.03 | Create Surat | `/surat/create` | F2 | Form buat surat baru |
| 5.04 | Edit Surat | `/surat/:id/edit` | F2 | Edit surat (draft only) |
| 5.05 | Paraf Inbox | `/surat/paraf` | F2 | Surat yang perlu diparaf saya |
| 5.06 | Paraf Review | `/surat/:id/paraf` | F2 | Review dan tanda paraf |

---

## 6. NOTIFIKASI

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 6.01 | Notification List | `/notifications` | F1 | Semua notifikasi |
| 6.02 | Notification Detail | `/notifications/:id` | F1 | Detail notifikasi |

---

## 7. LAPORAN KEJADIAN

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 7.01 | Laporan List | `/laporan` | F2 | Daftar laporan masuk |
| 7.02 | Submit Laporan | `/laporan/create` | F1 | Form laporan kejadian (+ foto) |
| 7.03 | Laporan Detail | `/laporan/:id` | F2 | Detail laporan + validasi |
| 7.04 | Laporan Peta | `/laporan/peta` | F2 | Peta sebaran laporan |

---

## 8. INSIDEN OPERASIONAL

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 8.01 | Insiden List | `/insiden` | F2 | Daftar insiden aktif |
| 8.02 | Create Insiden | `/insiden/create` | F2 | Form buat insiden baru |
| 8.03 | Insiden Detail | `/insiden/:id` | F2 | Detail insiden lengkap |
| 8.04 | Edit Insiden | `/insiden/:id/edit` | F2 | Edit insiden |
| 8.05 | Insiden Timeline | `/insiden/:id/timeline` | F3 | Timeline kejadian |
| 8.06 | Assessment | `/insiden/:id/assessment` | F2 | Input assessment dampak |
| 8.07 | Sitrep | `/insiden/:id/sitrep` | F2 | Situation Report harian |

---

## 9. POSKO (POSAJU)

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 9.01 | Posko List | `/posko` | F2 | Daftar posko aktif |
| 9.02 | Posko Detail | `/posko/:id` | F2 | Dashboard posko |
| 9.03 | Create Posko | `/posko/create` | F2 | Form buat posko |
| 9.04 | Posko Aktivasi | `/posko/:id/aktivasi` | F2 | Aktifkan posko |
| 9.05 | Posko Klaster | `/posko/:id/klaster` | F2 | Daftar klaster di posko |

---

## 10. PENUGASAN & MOBILISASI

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 10.01 | Penugasan List | `/penugasan` | F2 | Daftar penugasan |
| 10.02 | Penugasan Saya | `/penugasan/saya` | F1 | Penugasan aktif milik saya |
| 10.03 | Penugasan Detail | `/penugasan/:id` | F2 | Detail penugasan |
| 10.04 | Create Penugasan | `/penugasan/create` | F2 | Form penugasan baru |
| 10.05 | Mobilisasi List | `/mobilisasi` | F2 | Daftar mobilisasi |
| 10.06 | Mobilisasi Detail | `/mobilisasi/:id` | F2 | Detail mobilisasi + approve |
| 10.07 | Create Mobilisasi | `/mobilisasi/create` | F2 | Form mobilisasi baru |

---

## 11. RELAWAN

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 11.01 | Relawan List | `/relawan` | F2 | Daftar relawan wilayah |
| 11.02 | Relawan Detail | `/relawan/:id` | F2 | Profil relawan |
| 11.03 | Relawan Status | `/relawan/status` | F1 | Update status ketersediaan saya |

---

## 12. ASET

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 12.01 | Aset List | `/aset` | F3 | Daftar aset |
| 12.02 | Aset Detail | `/aset/:id` | F3 | Detail aset |
| 12.03 | Aset Tersedia | `/aset/tersedia` | F3 | Aset siap pakai |
| 12.04 | Create Aset | `/aset/create` | F3 | Form tambah aset |

---

## 13. LOGISTIK

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 13.01 | Logistik List | `/logistik` | F3 | Daftar stok logistik |
| 13.02 | Logistik Detail | `/logistik/:id` | F3 | Detail stok |
| 13.03 | Permintaan Logistik | `/logistik/minta` | F3 | Form permintaan barang |

---

## 14. MEDIA

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 14.01 | Media Gallery | `/media` | F2 | Galeri media entitas |
| 14.02 | Media Full View | `/media/:id` | F2 | Full screen preview |
| 14.03 | Upload Progress | `/media/upload` | F2 | Status upload queue |

---

## 15. CUACA & DATA PUBLIK

| # | Screen Name | Route | Sprint | Keterangan |
|---|-------------|-------|--------|-----------|
| 15.01 | Prakiraan Cuaca | `/cuaca` | F2 | Cuaca wilayah aktif (BMKG) |
| 15.02 | Alert BMKG | `/cuaca/gempa` | F2 | Gempa terbaru |

---

## SUMMARY

| Sprint | Jumlah Screen | Focus |
|--------|--------------|-------|
| F1 | ~20 screen | Auth, Mandate, Inbox, Dashboard MVP |
| F2 | ~35 screen | Operasional (Insiden, Laporan, Posko) |
| F3 | ~15 screen | Logistik, Aset, Media lanjutan |
| F4 | ~10 screen | Advanced (Pleno, Sitrep, Desktop) |
| **Total** | **~80 screen** | Full ERP Mobile |

---

*Document Status: APPROVED — Scope F1 telah dikonfirmasi sebagai prioritas pertama*
