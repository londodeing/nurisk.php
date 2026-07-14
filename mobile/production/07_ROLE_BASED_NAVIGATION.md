# NURISK MOBILE — ROLE-BASED NAVIGATION
## Document 07: Role-Based Navigation Matrix
**Version**: 2.0.0 | **Status**: REVISED — PUBLIC FIRST | **Domain**: Platform-Wide

> **⚠️ PARADIGMA BERUBAH (v2.0)**  
> Navigasi sekarang terbagi menjadi dua sistem: **Public Navigation** dan **Governance Navigation**.  
> Guest/masyarakat menggunakan Public Navigation tanpa login.  
> Referensi: `FLUTTER_APPLICATION_ARCHITECTURE.md` Article 4.

---

## 1. ROLES DI NURISK

Berdasarkan model `AuthUser` dan field `id_peran`:

| Role Key | Label UI | Level | Entry Point |
|----------|---------|-------|-------------|
| `guest` | Masyarakat / Guest | 5 (Public) | Public Layer |
| `relawan` | Relawan / Voluntir | 4 | Public + Governance terbatas |
| `operator` | Operator | 3 | Governance |
| `pcnu` | Admin PCNU | 2 | Governance |
| `pwnu` | Admin PWNU | 1 | Governance |
| `super_admin` | Super Administrator | 0 (Tertinggi) | Governance Full |

**Catatan**: Selain role, **jabatan dari mandate aktif** juga mempengaruhi menu yang muncul (misal: Relawan dengan mandate sebagai Koordinator Logistik mendapat menu tambahan).

---

## 2. DUA SISTEM NAVIGASI

### 2.1 PUBLIC BOTTOM NAVIGATION (Semua user, tanpa login)

```
Public Bottom Nav (5 tab):
  [🏠 Beranda]  [🗺️ Peta]  [📸 Lapor]  [ℹ️ Info]  [👤 Akun]
```

| Tab | Route | Deskripsi | Auth |
|-----|-------|-----------|------|
| Beranda | `/p/home` | Dashboard publik, KPI, feed bencana, cuaca | ❌ |
| Peta | `/p/map` | Peta interaktif insiden, posko, shelter | ❌ |
| Lapor | `/p/lapor` | Submit laporan kejadian (publik) | ❌ |
| Info | `/p/info` | Nomor darurat, FAQ, tentang NU Peduli, donasi | ❌ |
| Akun | `/p/account` | Login / profil jika sudah login | ❌ |

**Behaviour Tab Akun**:
- Jika belum login: tampilkan halaman dengan tombol "Masuk" dan "Daftar Relawan"
- Jika sudah login: tampilkan ringkasan profil + mandate aktif + shortcut ke Governance

---

### 2.2 GOVERNANCE BOTTOM NAVIGATION (User login + mandate)

```
Governance Bottom Nav (5 tab, tergantung role):
  [🏠 Dashboard]  [🚨 Insiden]  [✅ Inbox]  [🔔 Notif]  [👤 Profil]
```

| Tab | Route | Keterangan | Role |
|-----|-------|-----------|------|
| Dashboard | `/g/dashboard` | Governance dashboard | Semua |
| Insiden | `/g/operasi/insiden` | Manajemen insiden | Operator+ |
| Inbox | `/g/governance/inbox` | Approval & paraf | Yang punya mandate |
| Notif | `/g/notifications` | Notifikasi internal | Semua |
| Profil | `/g/profile` | Profil + ganti mandate | Semua |

---

### 2.3 Transisi Public ↔ Governance Navigation

```
[User di Public Nav]
  │ Tap tab Akun → Masuk
  │ Atau tap fitur yang memerlukan auth
  ▼
[Login Flow]
  ▼
[Mandate Picker jika > 1 mandate]
  ▼
[Bottom Nav ganti ke Governance Nav]

[User di Governance Nav]
  │ Tap Profil → Keluar
  ▼
[Logout Flow]
  ▼
[Bottom Nav ganti ke Public Nav]
[Public state tetap hidup]
```

---

## 3. BOTTOM NAVIGATION PER ROLE

### 3.1 Guest / Masyarakat (Tidak Login)

**Navigation System**: PUBLIC

| Icon | Label | Route |
|------|-------|-------|
| 🏠 | Beranda | `/p/home` |
| 🗺️ | Peta | `/p/map` |
| 📸 | Lapor | `/p/lapor` |
| ℹ️ | Info | `/p/info` |
| 👤 | Akun | `/p/account` |

---

## 4. NAVIGATION DRAWER

Drawer berisi menu lengkap yang difilter berdasarkan permission.

### 3.1 Header Drawer (Semua Role)
```
╔══════════════════════════════╗
║  [Foto Profil]               ║
║  Ahmad Fauzi                 ║
║  Koordinator Logistik        ║
║  PCNU Sidoarjo               ║
╚══════════════════════════════╝
```

### 3.2 Menu Drawer per Role

#### Super Admin — Full Menu
```
GOVERNANCE
  ├── Mandat & Posisi
  ├── Struktur Organisasi
  ├── Delegasi
  ├── SK (Surat Keputusan)
  └── Audit Trail

OPERASIONAL
  ├── Insiden Aktif
  ├── Posko (POSAJU)
  ├── Klaster
  ├── Penugasan
  └── Mobilisasi

DATA
  ├── Relawan
  ├── Aset
  ├── Logistik
  └── Laporan Masuk

SURAT & DOKUMEN
  ├── Surat Keluar
  └── Inbox Paraf

ADMINISTRASI
  ├── Manajemen Pengguna
  ├── Persetujuan Pendaftaran
  └── Pengaturan Sistem

LAINNYA
  ├── Notifikasi
  ├── Pengaturan
  └── Tentang Aplikasi
```

#### Admin PWNU / PCNU — Governance-heavy
```
GOVERNANCE
  ├── Mandat & Posisi
  ├── Struktur Organisasi
  ├── Delegasi
  └── Audit Trail

OPERASIONAL
  ├── Insiden Aktif
  ├── Posko (POSAJU)
  └── Penugasan

DATA
  ├── Relawan (wilayah saya)
  ├── Aset (wilayah saya)
  └── Laporan Masuk

SURAT & DOKUMEN
  ├── Surat Keluar
  └── Inbox Paraf

LAINNYA
  ├── Notifikasi
  └── Pengaturan
```

#### Operator — Operasi-heavy
```
OPERASIONAL
  ├── Insiden Aktif
  ├── Laporan Masuk
  ├── Klaster
  └── Penugasan

DATA
  ├── Aset
  └── Logistik

LAINNYA
  ├── Notifikasi
  └── Pengaturan
```

#### Relawan (Tanpa Mandate Tinggi)
```
SAYA
  ├── Tugas Saya
  ├── Profil & Keahlian
  └── Status Ketersediaan

LAPORAN
  └── Buat Laporan Kejadian

LAINNYA
  ├── Notifikasi
  └── Pengaturan
```

#### Komandan Posko (Relawan dengan mandate jabatan)
```
POSKO
  ├── Dashboard Posko
  ├── Tim Relawan
  ├── Jurnal Harian (Sitrep)
  └── Assessment Dampak

PENUGASAN
  ├── Penugasan Aktif
  └── Buat Penugasan

LOGISTIK
  ├── Stok Posko
  └── Permintaan Barang

LAINNYA
  ├── Notifikasi
  └── Pengaturan
```

---

## 4. QUICK ACTIONS (Floating Action Button / Action Sheet)

Quick Action adalah tindakan yang paling sering dilakukan dan dapat diakses dari Dashboard dengan satu tap.

### 4.1 Super Admin
| Aksi | Icon | Route |
|------|------|-------|
| Buat Insiden | ➕🚨 | `/insiden/buat` |
| Approve Inbox | ✅📋 | `/governance/inbox` |
| Lihat Peta | 🗺️ | `/peta` |

### 4.2 Admin PWNU / PCNU
| Aksi | Icon |
|------|------|
| Proses Approval | ✅ |
| Lihat Peta Insiden | 🗺️ |
| Kirim Laporan | 📋 |

### 4.3 Komandan Posko
| Aksi | Icon |
|------|------|
| Buat Penugasan | 👥➕ |
| Input Sitrep Harian | 📝 |
| Minta Logistik | 📦 |

### 4.4 Relawan
| Aksi | Icon |
|------|------|
| Laporkan Kejadian | 📸 |
| Ubah Status Ketersediaan | 🔄 |
| Lihat Tugas Aktif | ✅ |

---

## 5. DASHBOARD WIDGETS

Widget yang muncul di Dashboard disesuaikan per role.

### 5.1 Super Admin Dashboard
| Widget | Keterangan |
|--------|-----------|
| Command Center Summary | Insiden aktif, posko aktif, relawan deployed |
| Governance Inbox Count | Badge jumlah approval menunggu |
| Alert Insiden Baru | List insiden baru 24 jam terakhir |
| Peta Insiden Aktif | Mini map dengan pin insiden |
| Blank Spot Warning | Wilayah tanpa coverage |

### 5.2 Admin PWNU / PCNU Dashboard
| Widget | Keterangan |
|--------|-----------|
| Insiden Wilayah | Insiden aktif di wilayah saya |
| Governance Inbox | Approval menunggu (count badge) |
| Relawan Aktif | Jumlah relawan deployed |
| Alert Cuaca (BMKG) | Peringatan cuaca ekstrem wilayah |

### 5.3 Komandan Posko Dashboard
| Widget | Keterangan |
|--------|-----------|
| Info Posko | Nama, status, lokasi |
| Tim Hari Ini | Relawan yang bertugas |
| Stok Logistik Kritis | Warning jika stok < threshold |
| Tugas Aktif | Progress penugasan |

### 5.4 Relawan Dashboard
| Widget | Keterangan |
|--------|-----------|
| Status Saya | Ketersediaan (ready/deployed/rest) |
| Tugas Aktif | Penugasan aktif saat ini |
| Notifikasi | Pemanggilan/pemberitahuan terbaru |

---

## 6. PERMISSION GUARD PER ROUTE

| Route | Izin yang Diperlukan |
|-------|---------------------|
| `/governance/mandates` | `governance.mandate.view` |
| `/governance/delegations` | `governance.delegation.view` |
| `/governance/approvals` | `governance.approval.view` |
| `/admin` | Role: `super_admin` |
| `/insiden/buat` | `operasi.incident.create` |
| `/penugasan/buat` | `operasi.mission.create` |
| `/surat/paraf` | `governance.surat.sign` |
| `/emergency-override` | `governance.emergency_override` |

---

## 7. MANDATE-BASED MENU AUGMENTATION

Ketika user memiliki mandate aktif, menu tambahan muncul berdasarkan jabatan:

| Jabatan di Mandate | Menu Tambahan |
|-------------------|--------------|
| Komandan Posko | Dashboard Posko, Jurnal, Assessment |
| Koordinator Klaster | Klaster Management, Penugasan Klaster |
| Koordinator Logistik | Manajemen Stok, Distribusi |
| Ketua PCNU/PWNU | Full Governance menu |
| Penandatangan | Inbox Paraf Surat |

---

*Document Status: APPROVED — Perlu dikalibrasi ulang saat permission endpoint (G-05) sudah tersedia*
