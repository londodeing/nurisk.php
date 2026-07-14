# NURISK MOBILE — MEDIA STRATEGY
## Document 11: Enterprise Media Layer Integration Strategy
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Media  
**Backend**: Enterprise Media Layer (MinIO + Laravel + WebP/Thumbnail Pipeline)

---

## 1. OVERVIEW

NURISK Enterprise Media Layer adalah sistem penyimpanan media yang sudah selesai dibangun di backend. Sistem ini menggunakan MinIO sebagai object storage, dengan pipeline konversi otomatis (Thumbnail + WebP) via Laravel Queue.

**Karakteristik Penting yang Harus Dipahami Flutter**:
1. URL media bersifat **Presigned Temporary** (berlaku 15 menit) — bukan URL permanen
2. Bucket MinIO bersifat **private** — akses direct tanpa presigned URL akan mendapat 403
3. Server menyimpan file original + thumbnail + WebP secara otomatis
4. Flutter harus meng-handle expired URL (403 → re-fetch URL baru)

---

## 2. UPLOAD STRATEGY

### 2.1 Upload Endpoint
```
POST /api/media
Content-Type: multipart/form-data
Authorization: Bearer {token}

Form fields:
  file          : File (required)
  entity_type   : string (laporan/insiden/profil/aset) (required)
  entity_id     : int (required)
  visibility    : string (public/private) (optional, default: public)
```

### 2.2 Upload Flow — Online

```
User tap [Tambah Foto]
    │
    ▼
Image Picker (kamera / galeri)
    │
    ▼
Pre-upload Processing:
  ├── Resize: max 1920px (sisi terpanjang)
  ├── Compress: target < 1MB
  ├── Format: JPEG (biarkan server yang konversi ke WebP)
  └── Generate local thumbnail untuk preview instan
    │
    ▼
POST /api/media (multipart)
    │
    ├── [Loading] → Tampilkan progress indicator
    │
    ├── [Sukses 201]
    │       │
    │       ▼
    │   Simpan media_id + path ke entity lokal
    │   Tampilkan thumbnail dari server
    │
    └── [Gagal]
            │
            ▼
        Cek jenis error:
          [Network] → Masukkan ke MediaUploadQueue
          [413 Too Large] → Compress lebih lanjut, retry
          [422 Validation] → Tampilkan pesan error ke user
```

### 2.3 Upload Flow — Offline Queue

```
User tap [Tambah Foto] (kondisi offline)
    │
    ▼
Simpan file ke local app storage:
/app_documents/media_queue/{uuid}.jpg
    │
    ▼
Tambah ke MediaUploadQueue (SQLite):
{
  id: "uuid",
  local_file_path: "/app/.../uuid.jpg",
  entity_type: "laporan",
  entity_id: 42,
  status: "PENDING"
}
    │
    ▼
Tampilkan di UI dengan placeholder "Menunggu Upload"
    │
    ▼
[Saat Online] → Auto-proses queue → Upload → Update entity
```

### 2.4 Kompresi & Format

| Parameter | Value | Alasan |
|-----------|-------|--------|
| Max resolution | 1920x1080 | Cukup untuk dokumentasi bencana |
| Target file size | < 1MB sebelum upload | Hemat bandwidth |
| Output format | JPEG | Kompatibilitas luas |
| WebP | Dikonversi oleh server | Server yang mengoptimalkan |
| Thumbnail | Dibuat oleh server | Konsisten ukurannya |

---

## 3. DOWNLOAD & PREVIEW STRATEGY

### 3.1 Presigned URL Lifecycle

```
Flutter request media URL
    │
    ▼
GET /api/media/{id}
    │
    ▼
Server mengembalikan presigned URL (berlaku 15 menit)
    │
    ▼
Flutter load gambar dari presigned URL
    │
    ▼
[Jika URL expired (403)]
    │
    ├── Re-fetch dari GET /api/media/{id}
    ├── Dapatkan presigned URL baru
    └── Retry load gambar
```

### 3.2 URL Caching Strategy

**Problem**: Presigned URL hanya berlaku 15 menit. Jika di-cache lebih lama, akan expired.

**Solusi**: Cache presigned URL dengan TTL **10 menit** (lebih pendek dari server-side TTL):

```
MediaUrlCache {
  media_id : int
  url       : string
  cached_at : DateTime
  expires_at : DateTime (cached_at + 10 menit)
}
```

**Alur di Flutter**:
1. Check `MediaUrlCache` untuk `media_id` yang diminta
2. Jika ada dan belum expired → gunakan URL tersebut
3. Jika tidak ada atau sudah expired → fetch dari `GET /api/media/{id}` → cache hasilnya
4. Jika URL mengembalikan 403 saat load → clear cache → fetch ulang

### 3.3 Image Widget Behaviour

```
CachedNetworkImage(
  imageUrl: presignedUrl,
  placeholder: ShimmerPlaceholder,
  errorWidget: BrokenImagePlaceholder,
  cacheManager: MediaCacheManager (custom),
  httpHeaders: {"Authorization": "Bearer {token}"}
)
```

**Error Widget** (ketika URL expired atau 404):
- Tampilkan icon 🖼️ dengan teks "Foto tidak tersedia"
- Tombol "Muat Ulang" untuk trigger re-fetch URL

---

## 4. THUMBNAIL STRATEGY

Backend secara otomatis membuat thumbnail saat upload. Flutter harus menggunakan thumbnail untuk:
- List view (card di list screen)
- Gallery grid view
- Attachment preview di form

**Thumbnail vs Full-size**:
| Use Case | Yang Digunakan |
|----------|----------------|
| Card di list | Thumbnail (hemat bandwidth) |
| Full-screen preview | Full WebP atau JPEG original |
| Gallery grid | Thumbnail |
| Attachment di form | Thumbnail |

**Naming Convention Thumbnail** (dari backend):
- Thumbnail: `thumb_{randomstring}.jpg`
- WebP: `webp_{randomstring}.webp`
- Original: `{randomstring}.jpg`

---

## 5. WEBP SUPPORT

Server mengkonversi semua gambar ke WebP secara otomatis di background queue.

**Flutter WebP Support**:
- Flutter native mendukung WebP di Android dan iOS
- Gunakan format WebP dari server jika tersedia (lebih kecil ~30% dari JPEG)
- Fallback ke JPEG jika WebP belum selesai diproses (konversi async)

**Alur Selection**:
```
Check media conversions dari API response:
  └── Jika ada "webp" conversion → gunakan URL WebP
      Jika belum ada → gunakan URL original
      Jika thumbnail ada → gunakan thumbnail untuk preview
```

---

## 6. REPLACE MEDIA

**Endpoint**: `POST /api/media/{id}/replace`

**Use Case**: 
- Update foto profil
- Koreksi foto laporan yang salah

**Flutter Flow**:
1. User tap [Ganti Foto]
2. Image picker
3. Compress
4. `POST /api/media/{id}/replace`
5. Clear URL cache untuk `media_id` tersebut
6. Refresh tampilan gambar

---

## 7. DELETE MEDIA

**Endpoint**: `DELETE /api/media/{id}`

**Flutter Behaviour saat Delete**:
1. Konfirmasi dialog: "Yakin ingin menghapus foto ini?"
2. `DELETE /api/media/{id}`
3. Hapus URL dari `MediaUrlCache`
4. Hapus referensi dari entity lokal
5. Update UI (hide image widget)

**⚠️ Catatan Arsitektur**: Soft delete di server menghapus record tapi file MinIO tetap dipertahankan selama masa retensi (kebijakan backend). Flutter tidak perlu khawatir soal ini.

---

## 8. STREAMING (FUTURE)

**Status**: Future sprint. Belum diimplementasikan di backend.

**Rencana**: Untuk video dokumentasi bencana, gunakan HLS streaming via MinIO.

---

## 9. MEDIA CACHE MANAGEMENT

### 9.1 Disk Cache Limits
| Cache Type | Max Size | TTL File |
|------------|----------|----------|
| Thumbnail cache | 50MB | 7 hari |
| Full-size cache | 200MB | 3 hari |
| Temp upload files | Tidak terbatas | Hapus setelah upload sukses |

### 9.2 Cache Eviction
- LRU (Least Recently Used) — file yang paling lama tidak diakses dihapus pertama
- Otomatis run saat storage app > 80% dari limit
- User dapat clear cache manual dari Settings

### 9.3 Implementation Stack
```
cachedNetworkImage: untuk rendering
path_provider: untuk path local storage
MediaCacheManager: custom class wrapper
  ├── SQLite: metadata URL + expire
  ├── FileSystem: file cache (via cached_network_image)
  └── MediaUrlCache: presigned URL cache
```

---

## 10. BACKGROUND UPLOAD MONITORING

User dapat memantau status upload yang sedang berjalan:

```
Settings → Upload Queue
╔══════════════════════════════════════╗
║ Antrean Upload Media                 ║
╠══════════════════════════════════════╣
║ ✅ foto_laporan_001.jpg  — Terkirim  ║
║ ⏳ foto_laporan_002.jpg  — Mengunggah║
║ 🔴 foto_insiden_001.jpg  — Gagal    ║
║                          [Coba lagi] ║
╚══════════════════════════════════════╝
```

**Item States**:
| Status | Icon | Aksi yang Tersedia |
|--------|------|-------------------|
| PENDING | ⏰ | Batalkan |
| UPLOADING | ⏳ | — |
| DONE | ✅ | — |
| FAILED | 🔴 | Coba lagi / Hapus |

---

## 11. TEMPORARY URL EXPIRED — HANDLING MATRIX

| Scenario | HTTP Response | Flutter Action |
|---------|---------------|----------------|
| URL belum expired | 200 OK | Tampilkan gambar normal |
| URL expired (< 10 menit sejak fetch) | 403 | Re-fetch URL dari `/api/media/{id}` |
| Media dihapus dari server | 404 | Tampilkan broken image placeholder |
| Network error | — | Tampilkan placeholder + retry button |
| Media belum selesai diproses | 202 / empty | Tampilkan loading, retry setelah 3 detik |

---

*Document Status: APPROVED — Bergantung pada Enterprise Media Layer yang sudah selesai*
