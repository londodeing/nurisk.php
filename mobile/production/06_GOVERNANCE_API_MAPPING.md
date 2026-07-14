# NURISK MOBILE — GOVERNANCE API MAPPING
## Document 06: Governance API Contract
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Governance  
**Base URL**: `/api/governance/` dan `/api/v1/`

---

## KONVENSI

| Simbol | Arti |
|--------|------|
| 🔐 | Wajib auth (Bearer Token) |
| ✅ Cache | Bisa di-cache lokal |
| ❌ Cache | Tidak boleh di-cache |
| ⚠️ | Endpoint ada, contract belum final |
| ❌ Endpoint | Endpoint belum ada, perlu dibuat |

---

## A. MANDATE ENDPOINTS

### `GET /api/governance/mandates` 🔐

**Fungsi**: List semua mandate.  
**Digunakan untuk**: Mandate Picker, Settings posisi

**Query Parameters**:
| Param | Tipe | Keterangan |
|-------|------|-----------|
| `user_id` | int | Filter per user |
| `per_page` | int | Default 15 |

**Response 200**:
```json
{
  "data": {
    "data": [
      {
        "id": 12,
        "sk_id": 5,
        "user_id": 42,
        "node_position_id": 8,
        "tanggal_mulai": "2026-01-01",
        "tanggal_berakhir": null,
        "status": "aktif",
        "sk": {
          "id": 5,
          "nomor": "SK/001/PWNU/2026",
          "tanggal": "2026-01-01"
        },
        "user": {
          "id_pengguna": 42,
          "profil": { "nama_lengkap": "Ahmad Fauzi" }
        }
      }
    ],
    "meta": { "total": 1 }
  }
}
```

**Caching**: ✅ 12 jam, SQLite  
**Retry**: 3x backoff  
**Timeout**: 20s  
**Offline**: Serve dari cache

---

### `POST /api/governance/mandates` 🔐

**Fungsi**: Membuat mandate baru (admin only).  
**Digunakan oleh**: Admin PWNU/PCNU via Dashboard web (bukan mobile direct)

**Request**:
```json
{
  "sk_id": 5,
  "user_id": 42,
  "node_position_id": 8,
  "tanggal_mulai": "2026-01-01",
  "tanggal_berakhir": null
}
```

**Response 201**: `{ "message": "Mandat dibuat.", "data": {...} }`  
**Caching**: ❌  
**Timeout**: 20s  
**Offline**: ❌ Tidak tersedia offline

---

### `GET /api/governance/mandates/{id}` 🔐

**Fungsi**: Detail satu mandate dengan relasi lengkap.

**Response 200**:
```json
{
  "data": {
    "id": 12,
    "tanggal_mulai": "2026-01-01",
    "tanggal_berakhir": null,
    "status": "aktif",
    "sk": { "nomor": "SK/001/PWNU/2026" },
    "nodePosition": {
      "id": 8,
      "position": { "id": 3, "name": "Koordinator", "level": 2 },
      "node": {
        "id": 15,
        "name": "PCNU Sidoarjo",
        "territory_code": "3515",
        "institution": { "name": "PCNU" },
        "structureLevel": { "name": "Cabang", "level": 3 }
      }
    }
  }
}
```

**Caching**: ✅ 12 jam  
**Timeout**: 15s  
**Offline**: Serve dari cache

---

### `PUT/PATCH /api/governance/mandates/{id}` 🔐

**Fungsi**: Update mandate (admin only).  
**Offline**: ❌

---

### `DELETE /api/governance/mandates/{id}` 🔐

**Fungsi**: Hapus mandate (admin only).  
**Offline**: ❌

---

## B. NODE ENDPOINTS

### `GET /api/governance/nodes` 🔐

**Fungsi**: List semua node organisasi.  
**Digunakan untuk**: Struktur org tree, filter wilayah

**Query Parameters**:
| Param | Keterangan |
|-------|-----------|
| `institution_id` | Filter per institusi |
| `structure_level_id` | Filter per level |
| `territory_code` | Filter per wilayah |

**Response 200**:
```json
{
  "data": [
    {
      "id": 15,
      "name": "PCNU Sidoarjo",
      "territory_code": "3515",
      "status": "aktif",
      "institution": { "id": 2, "name": "PCNU" },
      "structureLevel": { "id": 3, "name": "Cabang", "level": 3 }
    }
  ]
}
```

**Caching**: ✅ 24 jam (jarang berubah)  
**Timeout**: 20s  
**Offline**: Serve dari cache

---

### `GET /api/governance/nodes/{id}` 🔐

**Fungsi**: Detail satu node.  
**Caching**: ✅ 24 jam

---

### `POST /api/governance/nodes` 🔐 (Admin only)
### `PUT/PATCH /api/governance/nodes/{id}` 🔐 (Admin only)
### `DELETE /api/governance/nodes/{id}` 🔐 (Admin only)

**Offline**: ❌ semua write operations

---

## C. POSITION ENDPOINTS

### `GET /api/governance/positions` 🔐

**Fungsi**: Daftar semua jabatan (OrgPosition).  
**Digunakan untuk**: Admin form, mandate management

**Response 200**:
```json
{
  "data": [
    { "id": 1, "name": "Ketua", "level": 1 },
    { "id": 2, "name": "Wakil Ketua", "level": 2 },
    { "id": 3, "name": "Koordinator", "level": 3 }
  ]
}
```

**Caching**: ✅ 48 jam (sangat jarang berubah)  
**Timeout**: 15s  
**Offline**: Serve dari cache

---

### `POST/PUT/DELETE /api/governance/positions/{id}` 🔐 (Admin only)

---

## D. NODE-POSITION PIVOT ENDPOINTS

### `POST /api/governance/node-positions` 🔐

**Fungsi**: Menetapkan jabatan ke node tertentu.  
**Request**: `{ "node_id": 15, "position_id": 3 }`  
**Offline**: ❌

---

### `DELETE /api/governance/node-positions/{id}` 🔐

**Fungsi**: Melepas jabatan dari node.  
**Offline**: ❌

---

## E. FUNCTION ENDPOINTS

### `GET /api/governance/functions` 🔐

**Fungsi**: Daftar fungsi governance (GovernanceFunction) yang tersedia.  
**Digunakan untuk**: Assign authority ke jabatan

**Response 200**:
```json
{
  "data": [
    { "id": 1, "name": "Approve SPK", "code": "approve_spk" },
    { "id": 2, "name": "Sign Surat", "code": "sign_surat" },
    { "id": 3, "name": "Emergency Override", "code": "emergency_override" }
  ]
}
```

**Caching**: ✅ 48 jam

---

### `POST /api/governance/function-authorities` 🔐

**Fungsi**: Menetapkan function ke authority.  
**Offline**: ❌

---

### `DELETE /api/governance/function-authorities/{id}` 🔐

**Offline**: ❌

---

## F. AUTHORITY ENDPOINTS

### `GET /api/governance/authorities` 🔐

**Fungsi**: Daftar kewenangan (OrgAuthority) yang ada.

**Response 200**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Approve Mobilisasi PCNU",
      "code": "approve_mobilisasi",
      "scope": "pcnu",
      "functions": [{ "id": 1, "code": "approve_spk" }]
    }
  ]
}
```

**Caching**: ✅ 12 jam  
**Timeout**: 15s

---

### `GET /api/governance/authorities/{id}` 🔐
### `POST /api/governance/authorities` 🔐 (Admin)
### `PUT/PATCH /api/governance/authorities/{id}` 🔐 (Admin)
### `DELETE /api/governance/authorities/{id}` 🔐 (Admin)

---

## G. DELEGATION ENDPOINTS

### `GET /api/governance/delegations` 🔐

**Fungsi**: List semua delegasi yang terkait dengan user.

**Response 200**:
```json
{
  "data": {
    "data": [
      {
        "id": 3,
        "mandat_asal_id": 12,
        "mandat_pengganti_id": 15,
        "mulai": "2026-07-10",
        "selesai": "2026-07-17",
        "jenis": "penuh",
        "created_at": "2026-07-08T08:00:00Z"
      }
    ]
  }
}
```

**Caching**: ✅ 1 jam  
**Timeout**: 15s  
**Offline**: Serve dari cache (read only)

---

### `POST /api/governance/delegations` 🔐

**Fungsi**: Membuat delegasi baru.

**Request**:
```json
{
  "mandat_asal_id": 12,
  "mandat_pengganti_id": 15,
  "mulai": "2026-07-10",
  "selesai": "2026-07-17",
  "jenis": "penuh"
}
```

**Validation**:
| Field | Rule |
|-------|------|
| `mandat_asal_id` | required, exists:org_mandates,id |
| `mandat_pengganti_id` | required, exists:org_mandates,id |
| `mulai` | required, date |
| `selesai` | nullable, date, after:mulai |
| `jenis` | nullable, string, max:50 |

**Possible Errors**:
| HTTP | Skenario |
|------|---------|
| 422 | Mandate tidak ada atau invalid |
| 403 | Tidak punya authority delegate |

**Caching**: ❌  
**Timeout**: 20s  
**Offline**: ❌ Wajib online

---

### `GET /api/governance/delegations/{id}` 🔐
### `PUT/PATCH /api/governance/delegations/{id}` 🔐
### `DELETE /api/governance/delegations/{id}` 🔐

---

## H. SK (SURAT KEPUTUSAN) ENDPOINTS

### `GET /api/governance/sks` 🔐

**Fungsi**: List semua SK.

**Response 200**:
```json
{
  "data": {
    "data": [
      {
        "id": 5,
        "nomor": "SK/001/PWNU/2026",
        "tanggal": "2026-01-01",
        "judul": "Pengangkatan Pengurus PCNU Sidoarjo",
        "status": "aktif"
      }
    ]
  }
}
```

**Caching**: ✅ 24 jam  
**Timeout**: 15s

---

### `GET /api/governance/sks/{id}` 🔐
### `POST /api/governance/sks` 🔐 (Admin)
### `PUT/PATCH /api/governance/sks/{id}` 🔐 (Admin)
### `DELETE /api/governance/sks/{id}` 🔐 (Admin)

---

## I. STRUCTURE LEVEL ENDPOINTS

### `GET /api/governance/structure-levels` 🔐

**Fungsi**: Daftar level struktur organisasi (PWNU=1, PCNU=2, dst).

**Response 200**:
```json
{
  "data": [
    { "id": 1, "name": "PWNU", "level": 1 },
    { "id": 2, "name": "Departemen PWNU", "level": 2 },
    { "id": 3, "name": "PCNU", "level": 3 }
  ]
}
```

**Caching**: ✅ 48 jam

---

## J. INSTITUTION ENDPOINTS

### `GET /api/governance/institutions` 🔐

**Fungsi**: Daftar institusi NU (PWNU, PCNU, LCNU, LPBI, Lazisnu).

**Response 200**:
```json
{
  "data": [
    { "id": 1, "name": "PWNU", "type": "induk" },
    { "id": 2, "name": "PCNU", "type": "cabang" },
    { "id": 3, "name": "LPBI", "type": "lembaga" }
  ]
}
```

**Caching**: ✅ 48 jam

---

### `POST /api/governance/institutions` 🔐 (Admin)
### `PUT/PATCH /api/governance/institutions/{id}` 🔐 (Admin)
### `DELETE /api/governance/institutions/{id}` 🔐 (Admin)

---

## K. PLANO ENDPOINTS (Meeting/Musyawarah)

### `GET /api/v1/insiden/{insiden}/pleno` 🔐

**Fungsi**: List sesi pleno/musyawarah dalam konteks insiden.

**Caching**: ✅ 30 menit

---

### `POST /api/v1/insiden/{insiden}/pleno` 🔐

**Fungsi**: Membuat sesi pleno baru.  
**Offline**: ❌

---

### `GET /api/v1/insiden/{insiden}/pleno/{pleno}` 🔐

**Fungsi**: Detail satu pleno.

---

### `PUT /api/v1/insiden/{insiden}/pleno/{pleno}` 🔐

**Fungsi**: Update pleno.

---

### `DELETE /api/v1/insiden/{insiden}/pleno/{pleno}` 🔐

**Fungsi**: Hapus pleno.

---

### `POST /api/v1/insiden/{insiden}/pleno/{pleno}/finalisasi` 🔐

**Fungsi**: Finalisasi pleno (lock dari perubahan).  
**Offline**: ❌

---

### `POST /api/v1/insiden/{insiden}/pleno/{pleno}/keputusan` 🔐

**Fungsi**: Tambah keputusan dalam pleno.  
**Offline**: ❌

---

### `POST /api/v1/insiden/{insiden}/pleno/{pleno}/peserta` 🔐

**Fungsi**: Tambah peserta pleno.

---

## L. SURAT (DIGITAL SIGNATURE) ENDPOINTS

### `GET /api/v1/surat` 🔐

**Fungsi**: List surat yang terkait dengan user (termasuk yang perlu paraf).

**Query Parameters**:
| Param | Keterangan |
|-------|-----------|
| `status` | Filter status (draft/menunggu_paraf/final) |
| `per_page` | Default 15 |

**Response 200**:
```json
{
  "data": [
    {
      "id": 1,
      "nomor_surat": "001/PCNU-SDA/VII/2026",
      "perihal": "Permohonan Bantuan Logistik",
      "status": "menunggu_paraf",
      "created_at": "2026-07-06T08:00:00Z"
    }
  ]
}
```

**Caching**: ✅ 15 menit  
**Offline**: Serve dari cache (read only)

---

### `GET /api/v1/surat/{id}` 🔐

**Fungsi**: Detail surat lengkap dengan alur paraf.

**Caching**: ✅ 15 menit

---

### `PATCH /api/v1/surat/paraf/{paraf}` 🔐

**Fungsi**: Memberikan atau menolak paraf pada surat.

**Request**:
```json
{
  "status": "disetujui",
  "catatan": "Disetujui untuk diteruskan"
}
```

**Possible Errors**:
| HTTP | Skenario |
|------|---------|
| 403 | Bukan giliran paraf Anda |
| 404 | Paraf tidak ditemukan |
| 422 | Status tidak valid |

**Caching**: ❌  
**Timeout**: 20s  
**Offline**: ❌ Wajib online

---

### `POST /api/v1/surat/{id}/ajukan-paraf` 🔐

**Fungsi**: Mengajukan surat ke pejabat untuk diparaf.  
**Offline**: ❌

---

### `POST /api/v1/surat/{id}/finalisasi` 🔐

**Fungsi**: Finalisasi surat (jika semua paraf terpenuhi).  
**Offline**: ❌

---

## M. AUDIT LOG ENDPOINTS

### `GET /api/governance/audit-logs` 🔐

**Fungsi**: Riwayat tindakan governance untuk audit.

**Query Parameters**:
| Param | Keterangan |
|-------|-----------|
| `start_date` | Tanggal mulai filter |
| `end_date` | Tanggal akhir filter |
| `action_type` | Tipe tindakan |
| `user_id` | Filter per user |

**Response 200**:
```json
{
  "data": [
    {
      "id": 100,
      "user_id": 42,
      "action": "approve",
      "target_type": "mobilisasi",
      "target_id": "uuid-xxx",
      "metadata": { "reason": "Mendesak" },
      "created_at": "2026-07-06T10:00:00Z"
    }
  ]
}
```

**Caching**: ✅ 1 jam (data historis, tidak real-time)  
**Timeout**: 20s

---

## N. GAP LIST — GOVERNANCE

| # | Endpoint yang Dibutuhkan | Priority |
|---|-------------------------|---------|
| G-10 | `GET /api/governance/mandates/me/active` — Mandate aktif user login | P0 F1 |
| G-11 | `GET /api/governance/mandates/{id}/authorities` — Authority dari mandate | P0 F1 |
| G-12 | `GET /api/governance/inbox` — Aggregated governance inbox | P0 F1 |
| G-13 | `GET /api/governance/inbox/count` — Jumlah item menunggu (badge) | P0 F1 |
| G-14 | `POST /api/governance/approvals/{id}/approve` — Generic approval action | P1 F2 |
| G-15 | `POST /api/governance/approvals/{id}/reject` — Generic rejection | P1 F2 |
| G-16 | `GET /api/governance/notifications` — Notifikasi governance | P1 F2 |

> **PENTING**: G-10, G-11, G-12, G-13 adalah **blocker** untuk Sprint F1. Tanpa ini, Flutter tidak dapat menampilkan mandate dan authority yang benar untuk user yang login.

---

## O. TIMEOUT MATRIX — GOVERNANCE

| Endpoint | Timeout | Max Retry | Cache TTL |
|---------|---------|-----------|----------|
| GET mandates | 20s | 3 | 12 jam |
| GET mandate detail | 15s | 3 | 12 jam |
| POST delegation | 20s | 0 | — |
| GET audit logs | 20s | 2 | 1 jam |
| PATCH paraf | 20s | 0 | — |
| POST finalisasi | 20s | 0 | — |
| GET nodes | 20s | 3 | 24 jam |
| GET institutions | 15s | 2 | 48 jam |
| GET structure-levels | 15s | 2 | 48 jam |

---

*Document Status: DRAFT — Gap G-10 hingga G-13 harus dikerjakan backend sebelum Sprint F1 dimulai*
