# NURISK MOBILE — SYNC ENGINE
## Document 10: Data Synchronization Engine Specification
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide  
**Backend Endpoints**: `POST /api/v1/sync`, `GET /api/v1/sync/state`, `GET /api/v1/sync/status`

---

## 1. OVERVIEW SYNC ENGINE

Sync Engine adalah komponen infrastruktur yang bertanggung jawab menjaga konsistensi data antara Flutter local storage (SQLite) dan backend Laravel. Engine ini beroperasi secara **transparan** di background — user tidak perlu secara manual mengelola sinkronisasi.

**Backend Sync APIs yang Tersedia**:
- `POST /api/v1/sync` — Submit batch sync actions
- `GET /api/v1/sync/state` — Status sync terakhir
- `GET /api/v1/sync/status` — Health check sync engine
- `GET /api/v1/sync/metrics` — Metrics (admin)
- `POST /api/v1/bootstrap` — Full initial sync
- `GET /api/v1/snapshot-download` — Download snapshot lengkap

---

## 2. SYNC MODES

### 2.1 Bootstrap Sync (Full Sync)

**Trigger**: Pertama kali login atau setelah reinstall.  
**Endpoint**: `POST /api/v1/bootstrap`

**Process**:
1. Request snapshot penuh dari server
2. Server mengembalikan data dalam bentuk bundle terkompresi
3. Flutter mengurai dan menyimpan ke SQLite
4. Set `last_sync_timestamp` di Secure Storage
5. Mark bootstrap sebagai selesai

**Data yang Di-bootstrap**:
- Master data (jabatan, bencana, wilayah, institusi)
- Mandate aktif user
- Insiden aktif di territory user
- Posko aktif di territory user
- Penugasan aktif user
- Governance inbox items

**Estimasi Durasi**: 5–30 detik tergantung volume data

---

### 2.2 Delta Sync (Periodik)

**Trigger**: Setiap 15 menit (background isolate)  
**Endpoint**: `POST /api/v1/sync`

**Request Body**:
```json
{
  "last_sync_at": "2026-07-06T10:00:00Z",
  "client_version": "1.2.0",
  "device_uuid": "550e8400-...",
  "scope": {
    "territory_code": "3515",
    "mandate_id": 12
  }
}
```

**Server Response**:
```json
{
  "sync_token": "abc123",
  "changes": {
    "incidents": [...],
    "assignments": [...],
    "notifications": [...]
  },
  "deleted_ids": {
    "incidents": [5, 7],
    "assignments": [12]
  },
  "server_timestamp": "2026-07-06T11:30:00Z"
}
```

**Flutter Process**:
1. Receive delta changes dari server
2. Upsert data baru/updated ke SQLite
3. Soft-delete item yang ada di `deleted_ids`
4. Update `last_sync_timestamp`
5. Trigger UI refresh jika ada perubahan relevan

---

### 2.3 Push Sync (FCM-triggered)

**Trigger**: FCM push notification dari server  
**Latency**: Near real-time (< 5 detik)

**Alur**:
```
Server mendeteksi perubahan penting
    │
    ▼
Kirim FCM push ke device user
(type: sync_required, scope: incidents)
    │
    ▼
Flutter menerima FCM (foreground/background)
    │
    ▼
Trigger targeted delta sync
(hanya untuk scope yang disebutkan di FCM payload)
    │
    ▼
Update SQLite + refresh UI
```

**FCM Push Payload untuk Sync**:
```json
{
  "data": {
    "type": "sync_required",
    "scope": "incidents",
    "resource_id": "123"
  }
}
```

---

### 2.4 Manual Sync (User-initiated)

**Trigger**: User menekan tombol "Sinkronisasi" atau pull-to-refresh.

**Behaviour**:
- Immediate delta sync
- Show loading indicator
- Toast notification saat selesai: "Data berhasil diperbarui"
- Toast notification jika gagal: "Gagal memperbarui. Pastikan koneksi internet tersedia."

---

## 3. UPLOAD QUEUE (WRITE OPERATIONS)

Semua operasi tulis yang gagal karena offline disimpan di queue dan diproses saat online.

### 3.1 Queue Architecture
```
OfflineQueueProcessor (Dart Isolate / Background Service)
    │
    ├── PriorityQueue<OfflineQueueItem>
    │       ├── HIGH priority: submit laporan, update insiden
    │       ├── MEDIUM priority: update status penugasan
    │       └── LOW priority: update profil, GPS coordinates
    │
    └── RetryScheduler
            ├── Exponential backoff: 30s, 1m, 5m, 15m, 1h
            └── Max retries berdasarkan priority
```

### 3.2 Queue Processing Rules
- Queue diproses FIFO dalam setiap priority tier
- HIGH priority selalu diproses sebelum MEDIUM dan LOW
- Hanya 1 item yang diproses bersamaan (sequential, bukan parallel)
- Jika max retries tercapai → item dipindah ke `failed_queue`
- Failed queue memerlukan aksi manual user (lihat/retry/hapus)

### 3.3 Conflict Resolution
| Strategi | Digunakan Untuk |
|----------|----------------|
| **Server Wins** | Data laporan, data insiden (server adalah sumber kebenaran) |
| **Last-Write Wins** | Profil user, status penugasan |
| **Merge** | Data yang dapat digabung (bulk insert) |
| **Drop** | Data GPS yang sudah kadaluarsa (>24 jam) |

---

## 4. MEDIA UPLOAD QUEUE

Media (foto, dokumen) memiliki queue terpisah dari data operasional.

### 4.1 Media Queue Item
```
MediaUploadQueueItem {
  id              : string (UUID)
  local_file_path : string
  entity_type     : string (laporan/insiden/profil)
  entity_id       : int
  mime_type       : string
  file_size_bytes : int
  upload_endpoint : string
  retry_count     : int
  status          : enum (PENDING/UPLOADING/DONE/FAILED)
  uploaded_path   : string? (path di MinIO setelah sukses)
  created_at      : DateTime
  last_attempt_at : DateTime?
}
```

### 4.2 Upload Process
```
User mengambil foto (offline)
    │
    ▼
Simpan foto di local app storage
Tambah ke MediaUploadQueue (status: PENDING)
    │
    ▼
[Saat Online]
    │
    ▼
Compress foto (max 1MB, WebP preferred)
    │
    ▼
Upload ke POST /api/media (multipart)
    │
    ├── [Sukses] → Update entity_id dengan uploaded_path
    │              Clear local temp file
    │              Status: DONE
    │
    └── [Gagal] → Retry dengan backoff
                  Status: FAILED (setelah max retry)
```

### 4.3 Media Queue Behaviour
- Max concurrent upload: **1** (untuk efisiensi bandwidth)
- Hanya upload saat WiFi ATAU data dengan ukuran < 500KB
- Notifikasi ke user jika ada > 3 item pending: "3 foto menunggu dikirim"
- User dapat melihat status upload di Settings → Upload Queue

---

## 5. BACKGROUND SYNC

Flutter Background Sync menggunakan platform-specific mechanisms:

| Platform | Mekanisme | Frekuensi |
|----------|-----------|-----------|
| Android | `WorkManager` (via `flutter_background_service`) | Setiap 15 menit |
| iOS | `Background App Refresh` (BGAppRefreshTask) | Setiap 15 menit (dikontrol OS) |

**Constraint Background Sync**:
- Jalankan hanya jika ada koneksi (WiFi atau cellular)
- Tidak mengganggu performa app di foreground
- Timeout background task: maksimal 30 detik
- Jika background task di-kill OS (iOS), re-schedule untuk berikutnya

---

## 6. SOFT DELETE SYNC

Ketika server menandai data sebagai soft-deleted, Flutter harus merespons dengan benar:

**Server Response untuk Delta Sync**:
```json
{
  "deleted_ids": {
    "incidents": [5, 7],
    "assignments": [12],
    "notifications": [100, 101]
  }
}
```

**Flutter Handling**:
1. Tandai item terkait di SQLite sebagai `deleted_at = now()`
2. Sembunyikan dari UI (filter `WHERE deleted_at IS NULL`)
3. Jangan hapus dari SQLite segera (beri 7 hari grace period)
4. Hapus permanen saat cleanup periodik (7 hari)

---

## 7. SYNC ERROR HANDLING

### 7.1 Error Types & Response
| Error | Penyebab | Respons Flutter |
|-------|---------|----------------|
| Network timeout | Koneksi lambat | Retry 3x, kemudian skip (sync berikutnya) |
| 401 Unauthorized | Token expired | Trigger refresh token flow |
| 409 Conflict | Data konflik | Apply conflict resolution strategy |
| 422 Validation | Data queue invalid | Move ke failed queue, notifikasi user |
| 500 Server Error | Backend error | Retry dengan backoff, tampilkan warning |
| 413 Payload Too Large | File terlalu besar | Compress ulang atau skip |

### 7.2 Failed Sync Notification
Jika sync gagal 3 kali berturut-turut:
```
Push local notification:
"Sinkronisasi gagal. Ketuk untuk mencoba lagi."
```

---

## 8. SYNC STATUS DISPLAY

### 8.1 Sync Indicator di UI
- **Ikon berputar** di header: sedang sinkronisasi
- **Ikon centang hijau**: terakhir sync berhasil
- **Ikon warning kuning**: sync terlambat (> 30 menit)
- **Ikon merah**: sync gagal

### 8.2 Sync Detail Screen (di Settings)
```
╔════════════════════════════════════╗
║ Status Sinkronisasi                ║
╠════════════════════════════════════╣
║ Terakhir berhasil: 5 menit lalu    ║
║ Item tertunda: 2 foto              ║
║ Delta sync berikutnya: 10 menit    ║
║                                    ║
║ [↻ Sinkronisasi Sekarang]          ║
║ [📋 Lihat Antrean Upload]          ║
╚════════════════════════════════════╝
```

---

## 9. SYNC PRIORITY TABLE

| Data | Priority | Sync Mode | Justifikasi |
|------|----------|-----------|-------------|
| Auth token validation | CRITICAL | On-demand | Keamanan |
| Governance inbox | HIGH | Delta + Push | Approval tidak boleh terlambat |
| Active incidents | HIGH | Delta + Push | Keselamatan |
| Active assignments | HIGH | Delta + Push | Operasional lapangan |
| Media upload | HIGH | Upload Queue | Data laporan |
| Mandate data | MEDIUM | Delta periodik | Berubah jarang |
| GPS coordinates | LOW | Upload Queue | Bisa drop jika stale |
| Master data | LOW | Delta periodik | Sangat jarang berubah |
| Audit logs | VERY LOW | On-demand | Historis saja |

---

*Document Status: APPROVED — Backend sync endpoints sudah tersedia di `/api/v1/sync`*
