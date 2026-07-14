# NURISK — PUBLIC INCIDENT FEED SPECIFICATION
## Document 21: Backend Contract & Privacy Rules
**Version**: 1.0.0 | **Status**: PENDING REVIEW | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OBJECTIVE

Tujuan dari kontrak ini adalah memisahkan dengan tegas antara data operasional insiden (internal ERP) dengan umpan publik (Public Feed). 

Public Feed harus ringan, cepat (cached), dan mutlak terbebas dari kebocoran informasi sensitif seperti data pelapor, nomor telepon, dan asessment internal. 

Dokumen ini menjadi acuan mutlak bagi tim Backend Laravel sebelum endpoint Publik dibuat, dan bagi tim Flutter sebelum modul *Latest Incident* (Sprint F1.9) diimplementasikan.

---

## 2. PRIVACY & SECURITY RULES

Sebagai aplikasi Enterprise Disaster Management, data mentah `operasi_insiden` mengandung informasi rahasia.

### 🔴 FIELD BLACKLIST (HARUS DIHAPUS DARI RESPONSE)
Endpoint Publik **DILARANG KERAS** mengekspos:
- Identitas & kontak pelapor (Nama, Nomor HP, Email, NIK).
- Alamat kejadian yang terlalu spesifik/presisi koordinat rumah (Kecuali untuk peta agregat dengan resolusi rendah).
- *Internal Notes*, Catatan Komandan, atau Hasil *Assessment* Internal.
- Dokumen pendukung operasional (SPK, Surat Tugas, Approval Status, Mandate).
- Foto asli/raw tanpa *thumbnail* atau *watermark*.

### 🟢 FIELD WHITELIST (IIZINKAN UNTUK RESPONSE PUBLIK)
Endpoint Publik **HANYA BOLEH** mengekspos:
- ID unik aman (contoh: `INC-2026-00123`).
- Judul kejadian (Disunting/terverifikasi).
- Kategori bencana (Banjir, Longsor, Gempa).
- *Severity* / Keparahan (Low, Medium, High).
- Status penanganan (Active, Resolved, Closed).
- Waktu kejadian (*occurred_at*).
- Lokasi generik (Kabupaten/Kecamatan).
- URL Thumbnail (bukan raw path).
- Status Verifikasi (`verified = true`).

---

## 3. INCIDENT LIFECYCLE MAPPING

Sama halnya dengan *Dashboard KPI*, umpan insiden publik hanya akan merender insiden yang berstatus minimal `VERIFIED`.
```sql
-- FILTER WAJIB DI BACKEND
WHERE status IN ('VERIFIED', 'ASSESSED', 'ACTIVE', 'RESOLVED', 'CLOSED')
```
Insiden dengan status `REPORTED` (baru masuk), `TRIAGED` (penyaringan), atau `REJECTED` (hoax) **TIDAK AKAN MUNCUL**.

---

## 4. ENDPOINT SPECIFICATIONS (BFF)

### 4.1 Get Latest Incidents Feed (Summary List)
**Request:**
`GET /api/public/incidents`

**Query Parameters Diizinkan:**
- `page` (default: 1)
- `limit` (maksimal: 20)
- `district` (kode kabupaten)
- `category` (enum bencana)
- `status` (filter spesifik misal: ACTIVE)
*Catatan: Tidak ada pencarian teks bebas (free-text search) atau eager loading relasi besar.*

**Caching Strategy:**
- Redis Cache TTL: **60 detik** di backend.
- Flutter Cache: Stale-While-Revalidate (Drift SQLite).

**Media Strategy:**
- Kolom foto harus dirender melalui Enterprise Media Layer dengan konversi ke WebP dan resolusi thumbnail (misal: 320x240 WebP) untuk menghemat bandwidth mobile publik.

**Response Schema (JSON):**
```json
{
  "data": [
    {
      "id": "INC-2026-00123",
      "title": "Banjir di Kecamatan Karanganyar",
      "category": "BANJIR",
      "severity": "HIGH",
      "status": "ACTIVE",
      "occurred_at": "2026-07-06T09:30:00Z",
      "district": "Kabupaten Demak",
      "thumbnail": "https://cdn.nurisk.local/media/incidents/INC-123/thumb_320x240.webp",
      "verified": true
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "total": 92
  }
}
```

---

### 4.2 Get Incident Detail (Public View)
**Request:**
`GET /api/public/incidents/{id}`

**Response Schema (JSON):**
*Backend hanya mengirimkan detail tanpa menyingkap data internal.*
```json
{
  "data": {
    "id": "INC-2026-00123",
    "headline": "Banjir di Kecamatan Karanganyar merendam 3 Desa",
    "description": "Banjir bandang akibat tanggul jebol di sungai wulan...",
    "category": "BANJIR",
    "status": "ACTIVE",
    "occurred_at": "2026-07-06T09:30:00Z",
    "location": {
      "district": "Kabupaten Demak",
      "province": "Jawa Tengah"
    },
    "media": [
      {
        "url": "https://cdn.nurisk.local/media/incidents/INC-123/full_1.webp",
        "caption": "Tanggul jebol"
      }
    ],
    "timeline": [
      {
        "time": "2026-07-06T10:00:00Z",
        "event": "Tim SAR diturunkan"
      }
    ],
    "public_advice": "Warga diharap menjauhi aliran sungai",
    "emergency_contacts": [
      {"name": "Posko BPBD", "phone": "0811xxxx"}
    ]
  }
}
```

---
*Kontrak ini mengunci API Publik agar tetap berada di luar boundary data rahasia ERP operasional.*
