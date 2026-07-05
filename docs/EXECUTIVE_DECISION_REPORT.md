# EXECUTIVE DECISION REPORT

**Date:** 2026-06-20  
**Subject:** Strategic Realignment — NURISK Realtime Operations Platform  
**Author:** Principal Solution Architect & Technical Program Manager

---

## 1. Apa yang Dihentikan

### 1.1 Offline-First Sebagai Positioning (DIHENTIKAN)
- **Keputusan:** NURISK tidak lagi diposisikan sebagai "Offline-First Disaster Platform"
- **Alasan:** Internet satelit mengubah asumsi fundamental tentang konektivitas lapangan
- **Yang terjadi:** Semua dokumentasi, roadmap, arsitektur harus direvisi

### 1.2 S3 Bootstrap Snapshot (DITUNDA — BUKAN DIHENTIKAN)
- **Keputusan:** Tidak diimplementasikan untuk pilot
- **Alasan:** Bandwidth satelit memadai untuk bootstrap via API langsung
- **Kapan dievaluasi ulang:** Setelah 500+ concurrent users

### 1.3 RFC-001 Sebagai Primary Data Path (DIPENSIUNKAN)
- **Keputusan:** Sync tidak lagi menjadi primary data path
- **Yang baru:** Sync adalah offline resilience layer — background process, bukan primary data access
- **Konsekuensi:** 24-queries-per-sync adalah acceptable (P2)

### 1.4 Larangan WebSocket di ARCH_006 (DICABUT)
- **Keputusan:** Larangan WebSocket dan Message Broker dicabut
- **Alasan:** Realtime event broadcast adalah kebutuhan utama
- **Yang baru:** SSE untuk Phase 0-1; WebSocket untuk Phase 2+
- **Batasan:** Tidak ada microservice — event broadcast via Redis pub/sub, bukan Kafka/RabbitMQ

### 1.5 Sprint 12 Sebagai Prioritas Rendah (DIHENTIKAN)
- **Keputusan:** Command Center tidak lagi menjadi sprint terakhir
- **Yang baru:** Command Center adalah Phase 0 deliverable — dibangun parallel dengan infrastructure

---

## 2. Apa yang Diprioritaskan

### P0 — Immediate (Phase 0: Weeks 1-2)

| Item | ROI | Alasan |
|---|---|---|
| **Realtime Event Infrastructure** (SSE + Redis + Event classes) | **HIGHEST** | Blocking untuk semua realtime features. Tanpa ini, Command Center dan live updates tidak bisa berfungsi. |
| **Command Center v1** (dashboard + live map + event feed) | **HIGH** | Ini adalah fitur utama yang membedakan NURISK dari sistem lain. Visibilitas realtime adalah selling point. |
| **API Direct Access Optimization** (37 routes) | **HIGH** | Mobile client dan command center butuh API cepat. Tanpa ini, user experience buruk. |
| **Redis Deployment** (infrastructure + config) | **HIGHEST** | Prasyarat untuk SSE, caching, dan event pub/sub. Blocking untuk Phase 0. |

### P1 — High Priority (Phase 1: Weeks 3-6)

| Item | ROI | Alasan |
|---|---|---|
| **Logistik API** | **HIGH** | Blocking untuk pilot — relawan perlu stok, mutasi, permintaan |
| **Relawan API** (verifikasi + shift) | **HIGH** | Diperlukan untuk manajemen relawan di mobile |
| **Flutter SSE Client** | **HIGH** | Mobile client perlu menerima realtime events |
| **Event Monitoring + Health Check** | **MEDIUM** | Blind spot jika events silently drop |
| **Security Hardening** (scope middleware complete) | **HIGH** | Pencegahan data leak |
| **PDF Queue Integration** (surat governance) | **MEDIUM** | Surat tanpa PDF tidak lengkap |

### P2 — Medium Priority (Phase 2: Weeks 7-12)

| Item | ROI | Alasan |
|---|---|---|
| **Governance API** (pleno + surat via mobile) | **HIGH** | Memungkinkan governance dari lapangan |
| **Command Center v2** (drill-down, widgets) | **HIGH** | Meningkatkan utility dashboard |
| **Offline Sync Fallback** (simplified) | **MEDIUM** | Graceful degradation — bukan blocking |
| **Aset API** | **MEDIUM** | Diperlukan untuk regional scale |
| **Pengungsian API** | **MEDIUM** | Diperlukan untuk regional scale |
| **MySQL DR Verification** | **MEDIUM** | Risk mitigation |

### P3 — Low Priority (Phase 3+: Weeks 13-20)

| Item | ROI | Alasan |
|---|---|---|
| **WebSocket Upgrade** | **MEDIUM** | SSE cukup untuk pilot; WebSocket untuk interaktif |
| **Notification System** (push) | **MEDIUM** | Meningkatkan engagement |
| **Jurnal Retention** (RFC-004) | **LOW** | Tidak blocking — data masih kecil |
| **S3 Bootstrap** | **LOW** | Bandwidth satelit memadai |
| **DR Automation** | **MEDIUM** | Mengurangi manual effort |
| **Public Map** | **LOW** | Nice-to-have |

---

## 3. Apa yang Ditunda

| Item | Target Fase | Alasan Penundaan |
|---|---|---|
| Jurnal Retention (RFC-004) | Phase 3+ | Volume data masih < 100K rows |
| S3 Bootstrap Snapshot | Phase 3+ | Bandwidth satelit memadai untuk API langsung |
| WebSocket Upgrade (from SSE) | Phase 2+ | SSE cukup untuk pilot; WebSocket adalah enhancement |
| Notification Push | Phase 3+ | Memerlukan FCM/APNs integration — effort tinggi |
| Public Map | Phase 3+ | Bukan kebutuhan operasional inti |
| Full Conflict Dashboard | Phase 2+ | Last-Write-Wins cukup untuk pilot |
| Aset API | Phase 2 | Tidak blocking untuk pilot |
| Pengungsian API | Phase 2 | Tidak blocking untuk pilot |
| Feedback & Gap Kebutuhan | Phase 2 | Tidak blocking untuk pilot |
| Penetration Testing | Phase 3 | Security audit v2 lulus; pentest untuk regional |

---

## 4. Apa yang Harus Segera Dibangun

Urutan berdasarkan ROI tertinggi:

### Sprint 0 (Next 2 weeks)

| # | Task | ROI Score | Effort | Team |
|---|---|---|---|---|
| 1 | **SSE Endpoint** — `GET /api/v1/events/stream` | 10/10 | 1 day | Backend |
| 2 | **Redis Setup** — Install + config + pub/sub | 10/10 | 0.5 day | Ops |
| 3 | **Event Classes** — Define all event types + payload schema | 9/10 | 1 day | Backend |
| 4 | **Command Center v1** — Summary cards + live map + event feed | 9/10 | 3 days | Full-stack |
| 5 | **API Direct Access** — Audit + optimize all 37 routes | 8/10 | 2 days | Backend |
| 6 | **SSE Auth** — Sanctum integration for event channels | 8/10 | 1 day | Backend |
| 7 | **Broadcast Integration** — Connect domain events to SSE | 8/10 | 2 days | Backend |
| 8 | **Event Monitoring** — Log + health check for event delivery | 7/10 | 1 day | Backend |

**Total Sprint 0:** ~11.5 days (2 weeks with 3 engineers)

### Sprint 1 (Weeks 3-4)

| # | Task | ROI Score | Effort | Team |
|---|---|---|---|---|
| 1 | **Logistik API** — Gudang, stok, mutasi, permintaan | 10/10 | 3 days | Backend |
| 2 | **Relawan API** — Verifikasi, penugasan, shift | 8/10 | 2 days | Backend |
| 3 | **Flutter SSE Client** — Event subscription + UI update | 9/10 | 3 days | Flutter |
| 4 | **Domain Event Integration** — All P0-P1 events | 8/10 | 3 days | Backend |
| 5 | **PDF Queue** — Implement `GenerateSuratPdfJob` | 7/10 | 2 days | Backend |
| 6 | **Security Hardening** — Scope middleware complete | 8/10 | 1 day | Backend |

**Total Sprint 1:** ~14 days (2 weeks with 4 engineers)

---

## 5. Resource Allocation

### Recommended Team Structure

| Role | Count | Focus Area |
|---|---|---|
| Backend (Laravel) | 2 | API optimization, event system, domain APIs |
| Flutter Mobile | 1 | SSE client, offline resilience, API integration |
| DevOps/Infra | 1 | Redis, deployment, monitoring |
| QA | 1 | Event system testing, API performance, regression |

### Budget Impact

| Item | Estimated Cost |
|---|---|
| Redis server (or managed Redis) | $50-200/month |
| Pusher (managed WebSocket, optional) | $0-500/month (free tier for pilot) |
| Laravel Reverb (self-hosted) | $0 (existing server) |
| Additional bandwidth (SSE) | Negligible (HTTP/2 multiplexing) |

---

## 6. Key Metrics untuk Evaluasi

| Metric | Target | Measurement |
|---|---|---|
| Event delivery latency (P0) | < 1s | Server log + client-side timing |
| Command Center page load | < 2s | Browser DevTools |
| API response time (P95) | < 500ms | Sentry / custom middleware |
| SSE connection success rate | > 99% | Health endpoint |
| Event delivery success rate | > 99.9% | Consumer acknowledgment |
| Command Center daily active users | > 50% of pilot users | Analytics |
