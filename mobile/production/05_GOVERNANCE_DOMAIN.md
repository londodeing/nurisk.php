# NURISK MOBILE — GOVERNANCE DOMAIN
## Document 05: Governance Domain Specification
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Governance  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OVERVIEW

Domain Governance adalah inti dari sistem NURISK. Governance Engine mengelola seluruh struktur organisasi, kewenangan, dan alur persetujuan digital. Di mobile, domain Governance berfungsi sebagai **antarmuka field-level** bagi pejabat organisasi untuk:
- Melihat posisi dan kewenangan mereka
- Memproses persetujuan digital
- Mendelegasikan wewenang
- Memantau alur governance organisasi

**Prinsip**: Semua keputusan governance (approval, rejection, delegation) **hanya dapat dilakukan dalam kondisi online**. Cache hanya untuk read operations.

---

## 2. STRUKTUR ORGANISASI (ORGANIZATIONAL TREE)

### 2.1 Hierarki Governance NURISK

```
Governance Engine
    │
    ├── OrgInstitution      (PWNU, PCNU, LCNU, LPBI, Lazisnu, dll)
    │       │
    │       └── OrgStructureLevel  (Level 1=PWNU, 2=PWNU-Dept, 3=PCNU, dst)
    │
    ├── OrgNode             (Instance konkret: "PCNU Sidoarjo", "LPBI Surabaya")
    │       │
    │       ├── territory_code  → Kode wilayah (e.g., "3515" = Kab. Sidoarjo)
    │       └── OrgNodePosition → OrgPosition + OrgNode (pivot)
    │
    ├── OrgPosition         (Jabatan: Ketua, Sekretaris, Koordinator, dll)
    │       │
    │       └── OrgFunction     (Fungsi: Approve SPK, Sign Surat, dll)
    │
    ├── OrgMandate          (Penugasan resmi user ke NodePosition)
    │       │
    │       ├── sk_id         → SK (Surat Keputusan) dasar
    │       ├── user_id       → User yang menerima mandate
    │       └── tanggal_mulai/berakhir
    │
    ├── OrgDelegation       (Pelimpahan mandate sementara)
    │
    └── OrgAuthority        (Kewenangan spesifik yang dimiliki posisi)
```

---

## 3. FITUR GOVERNANCE MOBILE

### 3.1 CURRENT POSITION (Posisi Saat Ini)

**Definisi**: Informasi posisi aktif yang dipilih dari Mandate Picker.

**Data yang Ditampilkan**:
- Nama Jabatan (dari OrgPosition)
- Nama Node (dari OrgNode)
- Nama Institusi (dari OrgInstitution)
- Wilayah (territory_code → nama kabupaten)
- Periode berlaku (tanggal_mulai — tanggal_berakhir atau "Tidak Terbatas")
- Nomor SK

**Lokasi di UI**:
- Card/chip di header Dashboard
- Halaman Settings → Posisi Aktif
- Header di setiap halaman Governance

**Aksi yang Tersedia**:
- Tap untuk melihat detail
- "Ganti Posisi" jika user memiliki > 1 mandate

---

### 3.2 CURRENT MANDATE (Mandat Aktif)

**Definisi**: Mandat yang sedang aktif, termasuk seluruh kewenangan yang diturunkan darinya.

**Data yang Di-Cache**:
```
ActiveMandate {
  id              : int
  sk_nomor        : string
  node_id         : int
  node_name       : string
  position_id     : int
  position_name   : string
  territory_code  : string
  territory_name  : string
  tanggal_mulai   : date
  tanggal_berakhir: date?
  status          : string (aktif/expired/delegated)
  functions       : List<GovernanceFunction>
  authorities     : List<OrgAuthority>
}
```

**Cache Strategy**: 
- Di-fetch saat login dan saat mandate berpindah
- Re-fetch setiap 12 jam
- Re-fetch saat menerima notifikasi `mandate_updated`

---

### 3.3 CURRENT TERRITORY (Wilayah Aktif)

**Definisi**: Wilayah administrasi yang menjadi scope operasional mandate aktif.

**Mapping**: `territory_code` → nama wilayah dari tabel/endpoint wilayah

**Implikasi di UI**:
- Filter incident map: hanya tampilkan insiden di wilayah ini
- Filter laporan: hanya tampilkan laporan dari wilayah ini
- Tampilkan info wilayah di header (kecamatan, kabupaten)

---

### 3.4 CURRENT AUTHORITY (Kewenangan Aktif)

**Definisi**: Daftar kewenangan spesifik yang dimiliki user pada posisi aktif.

**Sumber**: `GET /api/governance/authorities?node_id={id}`

**Authority Types** (berdasarkan kode di backend):
| Authority Code | Kewenangan |
|---------------|-----------|
| `approve_spk` | Menyetujui Surat Perintah Kerja |
| `approve_mission` | Menyetujui penugasan misi |
| `approve_asset` | Menyetujui penggunaan aset |
| `approve_mobilisasi` | Menyetujui mobilisasi relawan |
| `sign_surat` | Menandatangani surat resmi |
| `emergency_override` | Tindakan darurat tanpa approval |
| `delegate` | Mendelegasikan kewenangan |

---

### 3.5 GOVERNANCE INBOX

**Definisi**: Kotak masuk untuk semua item yang memerlukan tindakan governance — approval, signature, notifikasi.

**Sub-categories**:
| Tab | Konten |
|-----|--------|
| Semua | Semua item masuk |
| Menunggu | Menunggu aksi dari saya |
| Diproses | Sedang dalam proses (multi-level) |
| Selesai | Sudah diproses (approved/rejected) |

**Item Types dalam Inbox**:
- Permintaan Persetujuan SPK
- Permintaan Paraf Surat
- Permintaan Persetujuan Misi
- Permintaan Persetujuan Aset
- Notifikasi Mandat Akan Berakhir
- Notifikasi Delegasi Diterima

**UI Pattern**:
```
╔══════════════════════════════════╗
║ [Semua] [Menunggu] [Selesai]     ║
╠══════════════════════════════════╣
║ ● Persetujuan SPK               ║
║   Dari: Ahmad Fauzi             ║
║   PCNU Sidoarjo • 2 jam lalu    ║
║   [Lihat Detail]                ║
╠══════════════════════════════════╣
║ ● Paraf Surat                   ║
║   Surat: 001/PCNU-SDA/VII/2026  ║
║   Dari: Sekretaris              ║
╚══════════════════════════════════╝
```

---

### 3.6 DIGITAL APPROVAL

**Definisi**: Proses persetujuan digital untuk berbagai jenis dokumen/keputusan.

**Jenis Approval yang Didukung**:

#### 3.6.1 Task Approval
- Sumber: `GET /api/v1/operasi/tugas` (status: awaiting approval)
- Aksi: Approve / Reject dengan catatan
- Berlaku untuk: Koordinator ke atas

#### 3.6.2 SPK Approval (Surat Perintah Kerja)
- Sumber: (endpoint yang akan dikonfirmasi)
- Aksi: Approve / Reject / Request Revision
- Berlaku untuk: Pejabat yang memiliki authority `approve_spk`

#### 3.6.3 Mission Approval
- Sumber: `GET /api/v1/penugasan` (status: awaiting approval)
- Aksi: Approve / Reject
- Berlaku untuk: Komandan Posko ke atas

#### 3.6.4 Asset Approval
- Sumber: `GET /api/aset` (status: approval pending)
- Aksi: Approve penggunaan / Reject
- Berlaku untuk: Yang memiliki authority `approve_asset`

#### 3.6.5 Incident Approval (Eskalasi)
- Sumber: `GET /api/v1/insiden` (status: pending escalation)
- Aksi: Approve eskalasi / Redirect ke unit lain
- Berlaku untuk: Koordinator Klaster ke atas

#### 3.6.6 Mobilisasi Approval
- Sumber: `GET /api/v1/mobilisasi` (status: pending)
- Aksi: `POST /api/v1/mobilisasi/{uuid}/approve`
- Berlaku untuk: Yang memiliki authority `approve_mobilisasi`

---

### 3.7 DIGITAL SIGNATURE (PARAF)

**Definisi**: Penandatanganan digital dokumen surat resmi melalui mekanisme paraf bertingkat.

**Alur Paraf Surat**:
```
Surat dibuat (Operator/Admin)
    │
    ▼
Diajukan paraf ke pejabat
(POST /api/v1/surat/{id}/ajukan-paraf)
    │
    ▼
Pejabat menerima notifikasi di Governance Inbox
    │
    ▼
Pejabat membuka detail surat di mobile
    │
    ▼
Review konten surat
    │
    ▼
PATCH /api/v1/surat/paraf/{paraf}
(dengan status: disetujui / ditolak)
    │
    ├── [Disetujui]
    │       │
    │       ▼
    │   Status surat update
    │   (jika multi-paraf, lanjut ke next)
    │
    └── [Ditolak]
            │
            ▼
        Surat dikembalikan ke pembuat
```

**Catatan**: Digital Signature **wajib online**. Tidak ada offline queuing untuk signature.

---

### 3.8 EMERGENCY OVERRIDE

**Definisi**: Mekanisme tindakan darurat yang memungkinkan pejabat tertentu mengambil keputusan tanpa melalui alur approval normal.

**Syarat**:
- User memiliki authority `emergency_override`
- Hanya berlaku dalam konteks insiden aktif
- Setiap penggunaan Emergency Override **tercatat di Audit Log** secara otomatis

**Aksi yang Tersedia**:
- Aktivasi Posko tanpa persetujuan normal
- Mobilisasi relawan segera tanpa approval chain
- Akuisisi aset darurat

**UI Pattern**: Tombol merah terkunci, membutuhkan konfirmasi dua langkah (tap → konfirmasi dialog dengan teks "DARURAT")

---

### 3.9 DELEGATION (DELEGASI)

**Definisi**: Melimpahkan sebagian atau seluruh kewenangan mandate kepada pengguna lain untuk periode tertentu.

**Sumber Data**: `GET /api/governance/delegations`

**Membuat Delegasi** (`POST /api/governance/delegations`):
```json
{
  "mandat_asal_id": 12,
  "mandat_pengganti_id": 15,
  "mulai": "2026-07-10",
  "selesai": "2026-07-17",
  "jenis": "penuh"
}
```

**Jenis Delegasi**:
| Jenis | Keterangan |
|-------|-----------|
| `penuh` | Seluruh kewenangan dilimpahkan |
| `sebagian` | Hanya kewenangan tertentu |
| `tugas` | Untuk tugas spesifik saja |

**Status Delegasi**:
| Status | Tampilan |
|--------|---------|
| Aktif | Badge hijau |
| Menunggu | Badge kuning |
| Selesai | Badge abu-abu |
| Dibatalkan | Badge merah |

---

### 3.10 MANDATE SWITCHING

**Definisi**: Perpindahan antar mandate oleh user yang memiliki lebih dari satu mandate aktif.

**Lokasi di UI**: 
- Settings → "Ganti Posisi"
- Header Dashboard → tap Current Position

**Alur**:
1. Tap "Ganti Posisi"
2. Tampilkan Mandate Picker (list semua mandate aktif)
3. User pilih mandate baru
4. Konfirmasi: "Anda akan berpindah ke [Jabatan] di [Node]. Lanjutkan?"
5. Jika ya:
   - Clear cache operasional
   - Re-fetch permissions
   - Update `active_mandate_id` di Secure Storage
   - Rebuild navigation
   - Tampilkan Dashboard dengan context baru

---

### 3.11 MANDATE EXPIRY NOTIFICATION

**Definisi**: Notifikasi proaktif ketika mandate akan berakhir.

**Timeline Notifikasi**:
| Waktu Sebelum Expire | Notifikasi |
|---------------------|-----------|
| 30 hari | Push notification ringan |
| 7 hari | Push notification + Inbox item |
| 1 hari | Push notification kritikal + badge merah di profil |
| Expired | Dialog saat buka app: "Mandat Anda telah berakhir" |

**Saat Mandate Expired**:
- Jika user masih menggunakan mandate yang expired → tampilkan dialog
- Jika ada mandate lain → redirect ke Mandate Picker
- Jika tidak ada mandate lain → tampilkan screen "Hubungi Administrator"

---

### 3.12 MULTIPLE POSITION

**Definisi**: User dapat memiliki lebih dari satu mandate aktif secara bersamaan (misal: Ketua PCNU dan Koordinator Logistik Provinsi).

**Implikasi Flutter**:
- Mandate Picker muncul setiap kali login jika > 1 mandate
- Switching mandate dapat dilakukan kapan saja
- Notifikasi dikumpulkan dari semua mandate, namun difilter berdasarkan mandate aktif

---

### 3.13 GOVERNANCE AUDIT TRAIL

**Definisi**: Log seluruh tindakan governance yang dapat dilihat oleh pejabat berwenang.

**Sumber Data**: `GET /api/governance/audit-logs`

**Filter**:
- Berdasarkan periode
- Berdasarkan jenis tindakan
- Berdasarkan user/pejabat

**Tampilan**:
- Timeline vertikal dengan timestamp
- Icon sesuai jenis tindakan
- Nama aktor + jabatan

---

### 3.14 SK (SURAT KEPUTUSAN) MANAGEMENT

**Definisi**: SK adalah dasar hukum setiap mandate. User dapat melihat SK yang mendasari mandate mereka.

**Endpoint**: `GET /api/governance/sks`

**Info yang Ditampilkan**:
- Nomor SK
- Tanggal SK
- Status (aktif/tidak aktif)
- Download (jika ada lampiran — via Enterprise Media Layer)

---

### 3.15 STRUCTURE LEVELS & NODES

**Definisi**: Tampilan struktur organisasi dalam bentuk pohon (tree view).

**Endpoint**: `GET /api/governance/structure-levels`

**Tampilan Mobile**:
- Expandable tree view
- Filter berdasarkan wilayah/territory
- Tap node untuk melihat pejabat aktif di node tersebut

---

## 4. OFFLINE BEHAVIOUR — GOVERNANCE

| Fitur | Offline Behaviour |
|-------|------------------|
| Lihat mandate aktif | ✅ Dari cache |
| Lihat struktur org | ✅ Dari cache (snapshot) |
| Approve / Reject | ❌ Wajib online |
| Paraf surat | ❌ Wajib online |
| Delegation baru | ❌ Wajib online |
| Emergency override | ❌ Wajib online |
| Audit trail | ✅ Dari cache |
| SK download | ❌ Perlu koneksi untuk download, bisa preview dari cache |

---

## 5. GOVERNANCE NOTIFICATION TYPES

| Tipe Notifikasi | FCM Topic | Aksi di Flutter |
|----------------|-----------|-----------------|
| `approval_requested` | `mandate_{id}` | Buka Governance Inbox |
| `approval_approved` | `mandate_{id}` | Tampilkan sukses toast |
| `approval_rejected` | `mandate_{id}` | Tampilkan notifikasi reject |
| `delegation_received` | `user_{id}` | Tampilkan Inbox item |
| `mandate_expiring_7d` | `user_{id}` | Tampilkan reminder |
| `mandate_expired` | `user_{id}` | Dialog paksa saat buka app |
| `paraf_requested` | `mandate_{id}` | Buka Surat detail |
| `emergency_override_used` | `admin` | Alert ke super admin |

---

*Document Status: DRAFT — Endpoint untuk SPK Approval dan Task Approval perlu dikonfirmasi dengan backend*
