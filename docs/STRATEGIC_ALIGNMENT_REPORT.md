# STRATEGIC ALIGNMENT REPORT

**Date:** 2026-06-20  
**Subject:** Realignment from Offline-First Disaster Platform → Realtime Disaster Operations Platform with Offline Resilience  
**Auditor:** Principal Solution Architect & Technical Program Manager

---

## 1. Status Perubahan

| Aspek | Sebelumnya (OBSOLETE) | Sekarang (SOURCE OF TRUTH) |
|---|---|---|
| Positioning | "Offline-First Disaster Platform" | "Realtime Disaster Operations Platform with Offline Resilience" |
| Konektivitas lapangan | Tidak ada internet — offline adalah mode utama | Internet satelit tersedia — online adalah mode utama |
| Peran offline | Mode operasi normal | Graceful degradation saat koneksi terganggu |
| Kebutuhan utama | Sync, conflict resolution, bootstrap | Realtime visibility, command center, mobilisasi, koordinasi, governance |
| Arsitektur sync | Pull-based (client menarik data via kursor) | Push-based (server mendorong perubahan via event) + pull sebagai fallback |
| Resolusi konflik | Last-Write-Wins + conflict queue manual | Last-Write-Wins cukup (offline sangat jarang) |
| Prioritas pengembangan | S01-S12 semuanya parallel | Sprint ulang berdasarkan Realtime Operations priority |

---

## 2. Asumsi Lama vs Asumsi Baru

| # | Asumsi Lama (DIBATALKAN) | Asumsi Baru (SOURCE OF TRUTH) | Dampak |
|---|---|---|---|
| A1 | Relawan tidak memiliki internet di lapangan — sync adalah satu-satunya cara mendapatkan data | Relawan memiliki internet satelit — API langsung adalah cara utama; sync adalah fallback | Arsitektur sync harus diturunkan prioritasnya; API realtime harus dinaikkan |
| A2 | Data harus di-cache lokal SQLite untuk operasional penuh | Data bisa di-cache lokal, tetapi cache akan di-invalidate saat koneksi tersedia | Cache bukan replika penuh — cukup untuk operasi offline singkat |
| A3 | Conflict resolution adalah fitur kritis (banyak konflik karena lama offline) | Conflict resolution adalah nice-to-have (offline jarang, konflik minimal) | Last-Write-Wins cukup; manual review untuk kasus tepi |
| A4 | Bootstrap adalah operasi berat yang perlu S3 pre-rendered | Bootstrap cukup via API langsung karena bandwidth satelit memadai | S3 snapshot bisa ditunda; prioritaskan API response time |
| A5 | WebSocket / SSE tidak diperlukan (CQRS dan message broker dilarang) | Realtime event broadcast diperlukan untuk command center | Arsitektur blocking terhadap WebSocket harus dicabut |
| A6 | Command center adalah dashboard read-only static (Sprint 12) | Command center adalah pusat kendali live dengan data realtime | Command center harus naik prioritas: dari Sprint 12 ke Sprint 0 |
| A7 | Sync endpoint query count (24 vs 15) adalah masalah P2 | Sync endpoint adalah secondary path — primary path adalah API langsung | Optimasi sync bisa ditunda; optimasi API langsung diprioritaskan |
| A8 | Larangan WebSocket di ARCH_006 | WebSocket/SSE diperlukan untuk realtime | Arsitektur harus diamandemen |
| A9 | Volatile data di-flush dari SQLite setelah sinkron | Data dipertahankan di cache untuk graceful degradation | Retention policy dan invalidation strategy diperlukan |
| A10 | Deployment hanya perlu web server + queue worker | Deployment perlu event server (WebSocket/SSE) | Infrastruktur tambahan diperlukan |

---

## 3. Dampak terhadap Arsitektur

| Area | Dampak |
|---|---|
| **Larangan arsitektur (ARCH_006)** | Larangan WebSocket dan Message Broker HARUS dicabut. SSE minimal diperlukan. |
| **Sync Architecture (RFC-001)** | Dipensiunkan sebagai primary path. Dipertahankan sebagai offline fallback. Prioritas pembangunan diturunkan. |
| **Authorization (4-Layer)** | Tetap relevan — tidak ada perubahan. Scope isolation tetap critical. |
| **State Machine** | Tidak berubah — state machine governance tetap sama. |
| **API Design** | Flat endpoints (bukan nested) menjadi lebih penting untuk mobile access langsung. |
| **Cache Layer** | Diperlukan cache layer untuk API endpoints (Redis atau MariaDB query cache). |
| **Event System** | Diperlukan sistem event internal (Laravel Events + broadcasting) untuk realtime notifikasi. |
| **Database** | Tidak ada perubahan skema. Tapi perlu query optimization untuk direct API access pattern. |

### Perubahan Prioritas Arsitektur

| Prioritas Baru | Komponen | Prioritas Lama |
|---|---|---|
| P0 | API langsung (assessment, sitrep, klaster, mobilisasi, logistik, surat) | P0 |
| P0 | Command Center live (dashboard PWNU/PCNU/Posko/Relawan) | P2 (Sprint 12) |
| P0 | Realtime event broadcast (WebSocket/SSE) | DILARANG |
| P0 | Governance (pleno, surat, eskalasi) — via web | P0 |
| P1 | Offline sync fallback | P0 |
| P1 | Conflict resolution | P1 |
| P2 | S3 Bootstrap snapshot | P1 |
| P2 | Queue-based PDF generation | P1 |
| P3 | Jurnal retention (RFC-004) | P2 |

---

## 4. Dampak terhadap Roadmap

| Area | Dampak |
|---|---|
| **Sprint sequencing** | Command Center (sebelumnya S12) naik ke S0-priority. Sync (sebelumnya P0) turun ke P1. |
| **Sprint 4-6 (Pos Aju, Klaster, Relawan API)** | Tetap relevan — API yang sudah dibangun adalah fondasi untuk realtime. |
| **Sprint 7-10 (Assessment, Sitrep, Pleno, Surat)** | Tetap relevan — governance dan operasi inti tidak berubah. |
| **Sprint 12 (Dashboard)** | HARUS dinaikkan: dari 2 minggu di akhir menjadi fase paralel di awal. |
| **Flutter (M10)** | Perubahan besar: Flutter tidak boleh hanya sync client. Harus jadi realtime client dengan offline fallback. |
| **Phase 16 (Post-Pilot)** | Dari Retrospective → Realtime Infrastructure Build-out |

### Roadmap Shifts

```
SEBELUMNYA:
S01-S03: Auth, Organisasi, Insiden → Foundation
S04-S08: Assessment, Sitrep, Pos Aju, Logistik, Relawan → Domain API
S09-S10: Pleno, Surat → Governance
S11: Feedback & Gap → Evaluation
S12: Dashboard & Command Center → LAST (P2)

SEKARANG:
PHASE 0 (IMMEDIATE): 
  - Realtime Event Infrastructure (SSE/WebSocket)
  - Command Center v1 (live dashboard)
  - API Optimization for direct access
  
PHASE 1 (PILOT READINESS):
  - Assessment API (existing, optimize)
  - Sitrep API (existing, optimize)
  - Mobilisasi API (existing, optimize)
  - Realtime event integration
  
PHASE 2 (REGIONAL):
  - Pleno & Surat (existing, optimize)
  - Logistik & Relawan API
  - Offline sync fallback completion
  - Command Center v2 (with drill-down)
  
PHASE 3 (PROVINCIAL SCALE):
  - Full governance
  - Queue scale-up
  - Jurnal retention
  - DR automation
```

---

## 5. Dampak terhadap Deployment

| Area | Dampak |
|---|---|
| **Infrastruktur** | Perlu tambahan: Redis untuk cache + broadcast, WebSocket server (Laravel Reverb / Soketi / Pusher) |
| **Network** | Bandwidth satelit memadai untuk API calls tapi perlu compression (gzip, JSON pagination) |
| **Monitoring** | Perlu tambahan: WebSocket connection monitoring, event latency, broadcast queue depth |
| **Zero-downtime** | WebSocket disconnect saat deploy perlu graceful handling dengan reconnect logic |
| **Scaling** | WebSocket server perlu horizontal scaling (Redis pub/sub untuk cross-instance broadcast) |
| **Security** | WebSocket connections perlu token authentication (Sanctum via URL parameter atau first message) |

### Komponen Deployment Baru

```
nginx (TLS termination + reverse proxy)
  ├── php-fpm (Laravel web + API)
  ├── RoadRunner / Octane (optional, untuk performance)
  ├── supervisor
  │   ├── queue:default
  │   ├── queue:pdf-generation
  │   └── queue:events (new — untuk broadcast queue)
  ├── Redis (new — untuk cache + broadcast)
  └── MariaDB
```

---

## 6. Dampak terhadap Mobile Strategy

| Area | Dampak |
|---|---|
| **Primary data path** | API langsung (POST/GET) — bukan lagi sync-only |
| **Offline mode** | Graceful degradation: cache last-known-state, retry queue saat reconnect |
| **Sync** | Background sync hanya saat koneksi tidak stabil — bukan mode utama |
| **Realtime** | Flutter perlu SSE/WebSocket client untuk live updates |
| **Command Center mobile** | Flutter perlu live dashboard view (realtime charts, maps, feeds) |
| **Conflict** | Last-Write-Wins cukup — conflict queue jarang diperlukan |
| **Storage** | SQLite di Flutter untuk cache sementara, bukan replika server penuh |

### Perubahan Flutter Architecture

```
SEBELUMNYA (Offline-First):
Flutter App
  ├── SQLite (master data)
  ├── Sync Engine (push/pull)
  ├── Conflict Resolver
  └── Bootstrap Manager

SEKARANG (Realtime-First):
Flutter App
  ├── API Client (primary data path)
  ├── SSE/WebSocket Client (live updates)
  ├── Local Cache (SQLite untuk graceful degradation)
  ├── Retry Queue (untuk request gagal saat offline)
  └── Last-Known-State (display cached data saat offline)
```

---

## 7. Dampak terhadap Observability

| Area | Dampak |
|---|---|
| **API Monitoring** | Dari sync metrics ke API endpoint metrics (latency per endpoint, error rate per endpoint) |
| **Event Monitoring** | Baru: event publish latency, delivery rate, subscriber count |
| **Command Center** | Dari static dashboard ke live dashboard dengan realtime metrics |
| **Satellite Health** | Baru: konektivitas satelit monitoring, latency spike detection |
| **Alerting** | Dari queue backlog → event storm, satellite outage, WebSocket failure |

### Metrik Baru yang Diperlukan

| Metrik | Sumber | Criticality |
|---|---|---|
| API endpoint latency (P50/P95/P99) | Laravel middleware | P0 |
| Event publish-to-deliver latency | WebSocket/SSE | P0 |
| Active WebSocket connections | Reverb/Soketi | P1 |
| SSE reconnection rate | Mobile telemetry | P1 |
| Satellite latency spike | Health endpoint | P1 |
| Command center refresh lag | Dashboard metrics | P2 |

---

## 8. Dampak terhadap Disaster Recovery

| Area | Dampak |
|---|---|
| **WebSocket failure** | Baru: jika WebSocket down, command center harus fallback ke polling dengan backoff |
| **Satellite outage** | Baru: aplikasi harus degrade gracefully ke offline mode dengan last-known-state |
| **Queue backlog** | Baru: event queue backlog — jika broadcast queue menumpuk, prioritas events harus didahulukan |
| **Database recovery** | Tidak berubah — backup/restore script sudah ada dan diverifikasi |
| **RTO/RPO** | RTO untuk WebSocket reconnect: < 30s. RPO untuk event loss: < 1s (buffer in Redis). |

---

## 9. Dokumen yang Perlu Direvisi

| Dokumen | Perubahan |
|---|---|
| `docs/SYSTEM_ARCHITECTURE.md` | Tambahkan WebSocket/SSE sebagai komponen arsitektur; ubah "larangan WebSocket" |
| `docs/RFC-001-OFFLINE-SYNC-V1.md` | Ubah status dari FROZEN menjadi DEPRECATED sebagai primary path; pertahankan sebagai fallback |
| `docs/RFC-003-ASYNC-PDF.md` | Tetap relevan — tidak ada perubahan |
| `docs/RFC-004-JURNAL-RETENTION.md` | Turun prioritas — tidak blocking untuk pilot |
| `docs/SPRINT_PLAN.md` | Restrukturisasi: Command Center naik, offline sync turun |
| `docs/PRODUCTION_READINESS_AUDIT.md` | Tambahkan realtime infrastructure readiness |
| `docs/RISK_REGISTER.md` | Tambahkan risiko baru: WebSocket failure, satellite outage, event storm |
| `docs/PILOT_CHECKLIST.md` | Tambahkan realtime event verification items |
| `docs/PILOT_SUCCESS_CRITERIA.md` | Tambahkan realtime visibility KPI |
| `docs/reports/operations-dashboard.md` | Ubah dari Grafana static → live dashboard |
| `docs/reports/capacity-planning.md` | Tambahkan Redis + WebSocket capacity planning |
| `benchmarks/GATE_APPROVAL_REPORT.md` | Tambahkan gate untuk realtime infrastructure |

---

## 10. Ringkasan Dampak per Stakeholder

| Stakeholder | Dampak |
|---|---|
| **Tech Lead** | Amandemen arsitektur: cabut larangan WebSocket. Prioritas ulang roadmap. |
| **Backend Team** | Bangun event system, SSE endpoints, Redis integration. Optimasi API untuk direct access. |
| **Flutter Team** | Ubah arsitektur dari sync-centric → API-centric + SSE client + offline cache. |
| **Ops/Infra** | Tambah Redis, WebSocket server ke deployment. Update monitoring dan alerting. |
| **Product Owner** | Command center menjadi fitur utama (bukan sprint 12). Realtime visibility adalah selling point. |
| **Security** | WebSocket auth, SSE rate limiting, event validation. |
| **QA** | Test pattern berubah: dari sync test → API latency + event delivery test. |
