# NURISK MOBILE — OFFLINE-FIRST STRATEGY
## Document 09: Offline-First Data Strategy
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide

---

## 1. PRINSIP OFFLINE-FIRST

NURISK beroperasi di lapangan bencana dimana koneksi internet tidak dapat diandalkan. Filosofi "Offline-First" berarti:

> **Aplikasi harus dapat berfungsi secara bermakna bahkan tanpa koneksi. Koneksi internet meningkatkan kualitas data, bukan syarat fungsionalitas.**

**Hierarki prioritas**:
1. **Keselamatan dan operasional** tidak boleh terhenti karena koneksi
2. **Data kritikal** harus selalu tersedia dari cache
3. **Tindakan penting** di-queue dan diproses saat kembali online
4. **Approval dan Signature** adalah satu-satunya pengecualian (wajib online)

---

## 2. KLASIFIKASI DATA

### 2.1 Data yang BOLEH Offline

Data ini dapat dibaca dari cache lokal tanpa memerlukan koneksi:

| Data | TTL Cache | Prioritas Sync | Storage |
|------|-----------|----------------|---------|
| Profil user | 24 jam | Rendah | SQLite |
| Mandate aktif | 12 jam | Tinggi | SQLite + Secure |
| Struktur organisasi | 48 jam | Rendah | SQLite |
| Node & territory | 48 jam | Rendah | SQLite |
| SK (metadata) | 24 jam | Rendah | SQLite |
| Master data jabatan | 72 jam | Rendah | SQLite |
| Master jenis bencana | 72 jam | Rendah | SQLite |
| Master wilayah (kab/kec/desa) | 7 hari | Sangat rendah | SQLite |

### 2.2 Data yang HARUS Cache (Update Sering)

Data ini berubah lebih sering dan wajib disinkronkan secara periodik:

| Data | TTL Cache | Refresh Trigger | Storage |
|------|-----------|-----------------|---------|
| Insiden aktif | 30 menit | FCM push / manual | SQLite |
| Penugasan aktif | 15 menit | FCM push / manual | SQLite |
| Posko aktif | 30 menit | FCM push / manual | SQLite |
| Sitrep terbaru | 1 jam | Manual | SQLite |
| Stok logistik | 30 menit | Manual | SQLite |
| Notifikasi unread | 5 menit | FCM push | SQLite |
| Governance inbox | 10 menit | FCM push | SQLite |
| Aset tersedia | 1 jam | Manual | SQLite |
| Cuaca (BMKG) | 30 menit | Periodik | SQLite |

### 2.3 Data yang TIDAK BOLEH Offline (Online Only)

Operasi ini **wajib online**. Jika offline, tampilkan pesan dan tunda sampai koneksi tersedia:

| Operasi | Alasan |
|---------|--------|
| **Login / Logout** | Autentikasi memerlukan validasi server |
| **Approve / Reject** | Keputusan governance harus real-time dan tercatat |
| **Digital Paraf** | Signature harus tercatat dengan timestamp server |
| **Emergency Override** | Tindakan darurat perlu audit trail instan |
| **Delegation baru** | Perubahan kewenangan harus real-time |
| **Device token refresh** | Keamanan tidak boleh dikompromikan |
| **Download SK/Dokumen resmi** | File besar, tidak di-pre-cache |

---

## 3. OFFLINE QUEUE (WRITE OPERATIONS)

Operasi tulis yang tidak memerlukan respons real-time dapat di-queue dan diproses saat online:

| Operasi | Queue Priority | Max Retry | Conflict Strategy |
|---------|---------------|-----------|------------------|
| Submit laporan kejadian | TINGGI | 10x | Server wins |
| Upload foto | TINGGI | 5x | Append (tidak replace) |
| Update status penugasan | SEDANG | 5x | Last-write wins |
| Input sitrep | SEDANG | 5x | Server wins |
| Kirim koordinat GPS | RENDAH | 3x | Drop if >24 jam |
| Update profil | RENDAH | 3x | Last-write wins |

### 3.1 Queue Item Schema
```
OfflineQueueItem {
  id            : string (UUID)
  operation     : enum (CREATE / UPDATE / UPLOAD)
  endpoint      : string
  method        : enum (POST / PUT / PATCH)
  payload       : json
  media_paths   : List<string>?
  priority      : enum (HIGH / MEDIUM / LOW)
  retry_count   : int (default 0)
  max_retries   : int
  created_at    : DateTime
  next_retry_at : DateTime
  status        : enum (PENDING / PROCESSING / FAILED / DONE)
  error_message : string?
}
```

---

## 4. OFFLINE STATE INDICATORS

Flutter harus secara aktif mengkomunikasikan status koneksi dan data kepada user.

### 4.1 Connectivity Banner
Selalu tampilkan di bagian atas app jika offline:
```
╔══════════════════════════════════════════╗
║ 🔴 Tidak ada koneksi • Data dari cache   ║
╚══════════════════════════════════════════╝
```

Saat kembali online:
```
╔══════════════════════════════════════════╗
║ 🟢 Terhubung kembali • Sinkronisasi...   ║
╚══════════════════════════════════════════╝
```
(Hilang otomatis setelah 3 detik)

### 4.2 Data Freshness Indicator
Di setiap halaman data penting, tampilkan:
```
Terakhir diperbarui: 2 jam lalu [↻ Perbarui]
```

### 4.3 Pending Queue Badge
Jika ada item di queue yang belum terkirim:
```
[Ikon sync berputar + badge angka] di navigation bar
```

---

## 5. CACHE INVALIDATION STRATEGY

### 5.1 TTL-Based (Waktu)
Setiap data cache memiliki TTL. Saat TTL habis, data ditandai sebagai `stale`:
- Data stale tetap ditampilkan (tidak langsung dihapus)
- Background refresh dimulai otomatis
- Jika refresh gagal (offline), tetap tampilkan data stale dengan indikator

### 5.2 Event-Based
Data di-invalidate segera setelah menerima FCM push yang relevan:
| FCM Event | Data yang Di-invalidate |
|-----------|------------------------|
| `incident_updated` | Cache insiden terkait |
| `mandate_updated` | Cache mandate user |
| `assignment_updated` | Cache penugasan terkait |
| `sync_required` | Full re-fetch sesuai scope |

### 5.3 Manual (User-Initiated)
User dapat memaksa refresh via:
- Pull-to-refresh di list screen
- Tombol "Perbarui" di detail screen
- Menu "Sinkronisasi Sekarang" di Settings

---

## 6. OFFLINE DATA STORAGE SCHEMA (SQLite — Drift)

### 6.1 Tabel Core Cache
```
cache_user_profile
  id, user_id, data_json, cached_at, expires_at

cache_mandates
  id, user_id, mandate_id, data_json, cached_at, expires_at

cache_incidents
  id, incident_id, data_json, territory_code, cached_at, expires_at

cache_notifications
  id, notification_id, user_id, data_json, is_read, cached_at
```

### 6.2 Tabel Offline Queue
```
offline_queue
  id (UUID), operation, endpoint, method, payload_json,
  priority, retry_count, max_retries, created_at,
  next_retry_at, status, error_message

media_upload_queue
  id (UUID), file_path, remote_path, entity_type, entity_id,
  mime_type, size_bytes, retry_count, status, created_at
```

### 6.3 Tabel Master Data (Long-lived)
```
master_jenis_bencana, master_wilayah_kabupaten,
master_wilayah_kecamatan, master_wilayah_desa,
master_jabatan, master_institusi
```

---

## 7. BOOTSTRAP SYNC (First Login)

Saat pertama kali login, lakukan full sync sebelum menampilkan Dashboard:

```
Login Sukses
    │
    ▼
Bootstrap Sync Screen
"Mempersiapkan data Anda..."
    │
    ├── [Phase 1] Fetch user profile + mandate
    ├── [Phase 2] Fetch master data (jabatan, bencana, wilayah)
    ├── [Phase 3] Fetch active incidents (by territory)
    ├── [Phase 4] Fetch active posko + penugasan
    └── [Phase 5] Fetch governance inbox count
    │
    ▼
Simpan semua ke SQLite
    │
    ▼
Dashboard
```

**Progress Display**: Linear progress bar dengan teks fase aktif.
**Error Handling**: Jika fase gagal (network) → retry 3x → jika tetap gagal → lanjutkan dengan data parsial + warning.

---

## 8. OFFLINE SCENARIOS & RESPONSE

### Scenario A: User membuka app dalam kondisi offline
- Splash → cek token ada → validasi gagal (offline)
- Gunakan cache token sebagai valid (jika dalam TTL 24 jam)
- Load Dashboard dari cache penuh
- Tampilkan banner offline

### Scenario B: Koneksi terputus di tengah penggunaan
- Semua GET request → serve dari cache
- Semua POST/PUT yang di-queue → simpan ke offline_queue
- Semua POST/PUT yang tidak di-queue → tampilkan error + retry button
- Banner offline muncul di atas app

### Scenario C: Koneksi kembali saat ada queue pending
- Trigger background sync (upload queue + delta sync)
- Tampilkan progress toast: "Mengirim data yang tertunda..."
- Jika berhasil → toast sukses + clear queue item
- Jika gagal → retry sesuai jadwal

### Scenario D: Koneksi sangat lambat (timeout)
- Jika request timeout → serve dari cache
- Tampilkan indikator "Koneksi lambat"
- Tidak langsung tampilkan error kecuali cache kosong

---

*Document Status: APPROVED*
