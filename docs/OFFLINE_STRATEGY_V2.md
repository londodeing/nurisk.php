# OFFLINE STRATEGY V2 — Offline Resilience (Bukan Offline-First)

**Date:** 2026-06-20  
**Context:** Realignment from Offline-First → Realtime Disaster Operations Platform with Offline Resilience  
**Previous Status:** Offline-First (RFC-001)  
**New Status:** Offline Resilience Layer (deprecated as primary path)

---

## 1. Keputusan Strategis

### ✅ PILIHAN B: Offline-Resilient (BUKAN Offline-First)

**Alasan:**
1. Internet satelit tersedia di lapangan — mayoritas operasi memiliki konektivitas
2. Offline adalah kondisi sementara, bukan default
3. Realtime visibility adalah kebutuhan utama, bukan sync consistency
4. Last-Write-Wins cukup karena konflik jarang terjadi

### Yang Berubah

| Aspek | Offline-First (LAMA) | Offline-Resilient (BARU) |
|---|---|---|
| Mode operasi normal | Sync & Local SQLite | API langsung ke server |
| Mode offline | Normal (default) | Degradasi (fallback) |
| Data path | Pull-based sync → local read | API write → sync sebagai backup |
| Conflict | Sering → manual queue | Jarang → Last-Write-Wins |
| Cache | Full SQLite replica | Selective cache + last-known-state |
| Bootstrap | S3 pre-rendered snapshot | API langsung (with caching) |
| Priority arsitektur | P0 | P2 |

---

## 2. Arsitektur Baru: Offline Resilience Layer

```
┌─────────────────────────────────────────────────────┐
│                 FLUTTER APP                          │
│                                                      │
│  ┌──────────────┐    ┌─────────────────────────┐    │
│  │ API Client   │◄──►│ SSE/WebSocket Client    │    │
│  │ (Primary)    │    │ (Live updates)           │    │
│  └──────┬───────┘    └─────────────────────────┘    │
│         │                                            │
│         ▼                                            │
│  ┌──────────────────────────────────────────┐       │
│  │        Connectivity Detector              │       │
│  │  (Online → API direct | Offline → Cache)  │       │
│  └──────────────┬───────────────────────────┘       │
│                 │                                    │
│         ┌───────┴────────┐                          │
│         ▼                ▼                           │
│  ┌────────────┐   ┌──────────────┐                   │
│  │ Local Cache│   │ Retry Queue │                   │
│  │ (SQLite)   │   │ (Pending    │                   │
│  │            │   │  Requests)  │                   │
│  └────────────┘   └──────┬───────┘                   │
│                          │                            │
│                          ▼                            │
│                   ┌──────────────┐                    │
│                   │ Sync Engine  │                    │
│                   │ (Background) │                    │
│                   └──────────────┘                    │
└─────────────────────────────────────────────────────┘
```

---

## 3. Komponen Baru

### 3.1 Connectivity Detector
- **Fungsi:** Mendeteksi status koneksi secara realtime
- **Implementasi:** Ping ke health endpoint setiap 30 detik
- **State:** `online` / `degraded` (latensi tinggi > 5s) / `offline`
- **Trigger:** Ganti data path saat state berubah

### 3.2 API Client (Primary Path — NEW priority)
- **Fungsi:** Menggantikan sync sebagai primary data path
- **Implementasi:** HTTP client dengan retry (3x, exponential backoff)
- **Cache strategy:** Cache responses di lokal untuk display cepat
- **Idempotency:** Setiap request wajib `request_id` untuk safe retry

### 3.3 Local Cache (Formerly: Full SQLite Replica)
- **Fungsi:** Cache data yang baru diakses untuk display saat offline
- **Bukan:** Full SQLite replica (seperti design lama)
- **Strategy:** 
  - Cache TTL: 1 jam untuk master data (organisasi, jenis bencana)
  - Cache TTL: 15 menit untuk operational data (insiden, assessment)
  - Cache TTL: 5 menit untuk volatile data (status mobilisasi)
  - Max cache size: 50 MB
  - Auto-eviction: LRU saat melebihi batas

### 3.4 Last-Known-State Display
- **Fungsi:** Menampilkan data terakhir yang diketahui saat offline
- **Behavior:**
  - Banner: "Menampilkan data [timestamp] — beberapa data mungkin tidak terkini"
  - Read-only saat offline (tidak bisa create/update)
  - Auto-refresh saat koneksi pulih

### 3.5 Retry Queue
- **Fungsi:** Menyimpan request yang gagal karena offline untuk dikirim ulang
- **Implementasi:** Queue di SQLite, diproses saat koneksi pulih
- **Capacity:** Maks 1000 pending requests
- **Priority:** Recent-first (bukan FIFO)
- **Alert:** Jika queue > 100, notifikasi user "X perubahan menunggu dikirim"

---

## 4. Perubahan pada Sync Infrastructure (Existing)

### Yang Tetap

| Komponen | Alasan |
|---|---|
| Option B++ scope segregation | Critical untuk security — tidak ada data leak antar PCNU |
| Membership versioning | Kritikal untuk revocation — cabut akses langsung berefek |
| Cursor-based pagination | Masih diperlukan untuk initial load & background sync |
| Tombstone tracking | Diperlukan untuk deteksi data yang dihapus |
| Scope isolation | Critical — PCNU A tidak bisa lihat data PCNU B |

### Yang Disederhanakan

| Komponen | Simplifikasi |
|---|---|
| Conflict Resolution | Last-Write-Wins default. Manual queue untuk edge case (conflict queue hanya untuk data governance). |
| Bootstrap | API langsung + response caching. Tidak perlu S3 pre-render untuk pilot. |
| Sync Frequency | Dari setiap action → background setiap 5 menit (saat online) atau saat reconnect (setelah offline). |

### Yang Ditunda

| Komponen | Alasan |
|---|---|
| S3 Bootstrap Snapshot | Bandwidth satelit memadai untuk bootstrap via API. S3 jika diperlukan di Phase 3. |
| Full Offline Conflict Dashboard | Hanya diperlukan jika offline > 1 jam. Last-Write-Wins cukup. |
| Sync Query Optimization (24→15) | Sync bukan primary path. API optimization didahulukan. |

---

## 5. State Machine: Online → Offline → Reconnect

```
[ONLINE]
  │
  ├── API call → success → update cache
  ├── SSE event → update cache
  └── Background sync (every 5 min) → update sync cursors
       │
       ▼ (Connection lost)
[DEGRADED] (latency > 5s or intermittent)
  │
  ├── API call → retry 3x → if fail, queue to Retry Queue
  ├── Display cached data with "stale" banner
  └── Background retry every 30s
       │
       ▼ (Connection lost > 30s)
[OFFLINE]
  │
  ├── All API calls → queue to Retry Queue
  ├── Display Last-Known-State (read-only)
  ├── Show banner: "Luring — data terakhir: [timestamp]"
  └── No create/update allowed (unless cached data with queue)
       │
       ▼ (Connection restored)
[RECONNECT]
  │
  ├── Flush Retry Queue (priority: recent-first)
  ├── Pull latest cursors via background sync
  ├── Invalidate stale cache
  ├── Show notification: "Koneksi pulih — X perubahan telah dikirim"
  └── Resume normal operation (API direct + SSE)
```

---

## 6. Implementasi Plan

| # | Task | Effort | Phase |
|---|---|---|---|
| 1 | Connectivity Detector service | 1 day | Phase 1 |
| 2 | Cache layer (TTL-based, LRU eviction) | 2 days | Phase 1 |
| 3 | Retry Queue (SQLite + flush logic) | 2 days | Phase 1 |
| 4 | Last-Known-State display components | 1 day | Phase 1 |
| 5 | Sync frequency reduction (every 5 min) | 0.5 day | Phase 1 |
| 6 | Conflict resolution simplification → LWW | 1 day | Phase 2 |
| 7 | Bootstrap simplification → API direct | 0.5 day | Phase 2 |
| 8 | Remove S3 snapshot dependency | 0.5 day | Phase 2 |

## 7. Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Offline lebih lama dari expected (> 1 jam) | Medium | Medium | Cache TTL diperpanjang otomatis saat offline berkepanjangan |
| Retry Queue overflow (> 1000) | Low | Low | Batasi max 1000; alert user untuk prioritas manual |
| Stale cache displayed saat online | Medium | Low | Invalidate cache on SSE event |
| Conflict karena concurrent offline writes | Low | Low | Last-Write-Wins cukup — data governance tetap via web |
