# ROADMAP V2 — Realtime Disaster Operations Platform

**Date:** 2026-06-20  
**Context:** Realignment from Offline-First → Realtime-First with Offline Resilience

---

## Phase Overview

```
PHASE 0 (Weeks 1-2) — Realtime Infrastructure
├── Deliverable: Event system, SSE, Redis, Command Center v1

PHASE 1 (Weeks 3-6) — Pilot Readiness  
├── Deliverable: All domains functional via API + realtime

PHASE 2 (Weeks 7-12) — Regional Readiness
├── Deliverable: Governance, logging, offline fallback, command center v2

PHASE 3 (Weeks 13-20) — Provincial Scale
├── Deliverable: Queue scale, DR automation, full coverage
```

---

## Completed (Already Built)

### Auth & Authorization Infrastructure
- [x] Login via `no_hp` + `kata_sandi` — sanctum token
- [x] Role middleware (RoleMiddleware)
- [x] Scope middleware (ScopeMiddleware)  
- [x] 5 roles: super_admin, pwnu, pcnu, relawan, publik
- [x] Device API (list, revoke, logout-all)
- [x] Sanctum token expiry (30 days)
- [x] AuthUserObserver (revoke on deactivation)
- [x] Rate limiting (60/min API, 10/min login)

### Domain API (Built — need optimization for direct access)
- [x] Assessment API (`POST /api/v1/assessment`)
- [x] Klaster API (`POST /api/v1/klaster`)
- [x] Mobilisasi API (`POST /api/v1/mobilisasi`)
- [x] Penugasan API (`POST /api/v1/penugasan`)
- [x] Sitrep API (`POST /api/v1/sitrep`)
- [x] Offline sync infrastructure
- [x] Conflict resolution

### Governance (Web UI)
- [x] Pleno (CRUD + finalisasi + voting)
- [x] Surat (CRUD + paraf + PDF generation)
- [x] Eskalasi
- [x] Aktivasi
- [x] Jurnal service

### Production Hardening
- [x] 63 physical migrations
- [x] 11 new indexes
- [x] Backup/restore scripts
- [x] Deployment scripts (nginx, supervisor, php-fpm, logrotate)
- [x] Sentry integration
- [x] Health endpoint
- [x] Correlation ID middleware

### Benchmarks & Audits
- [x] Load test baseline
- [x] PDF queue benchmark (0 failures @ 1000 jobs)
- [x] Offline sync benchmark (~376 changes/sec)
- [x] Disaster recovery drill (RTO 0.06s, RPO 0)
- [x] Security review v2
- [x] Conflict analysis design
- [x] Capacity planning
- [x] Operations dashboard design
- [x] Pilot success criteria

---

## Phase 0: Realtime Infrastructure (Weeks 1-2)

### Priority: P0 — BLOCKER for all downstream

| # | Task | Effort | Depends On |
|---|---|---|---|
| 0.1 | Install & configure Redis for cache + broadcast | 0.5 day | Infrastructure |
| 0.2 | Implement SSE endpoint for realtime events | 1 day | 0.1 |
| 0.3 | Design event types & payload schema | 1 day | — |
| 0.4 | Implement Laravel Event classes for domain events | 2 days | 0.3 |
| 0.5 | Integrate broadcasting: AssessmentCreated, InsidenUpdated, etc. | 1 day | 0.2, 0.4 |
| 0.6 | Command Center v1: summary dashboard with AJAX polling | 2 days | 0.1 |
| 0.7 | Command Center v1: Leaflet live map with incident markers | 2 days | 0.6 |
| 0.8 | API optimization — direct access pattern (non-sync) | 2 days | — |
| 0.9 | WebSocket/SSE authentication (Sanctum integration) | 1 day | 0.2 |
| **Total** | | **12.5 days** | |

### Deliverables
- Redis operational for cache + broadcast
- SSE endpoint: `GET /api/v1/events/stream` (authenticated, per-scope)
- Event classes for: InsidenCreated, InsidenUpdated, AssessmentCreated, SitrepFinalized, PenugasanCreated, SuratFinalized, EskalasiCreated, PlenoFinalized, PosAjuStatusChanged
- Command Center v1: summary cards + live map + recent events feed
- API direct access optimized (eager loading, caching, pagination)
- Deployment infrastructure updated (Redis + Reverb/Soketi configs)

### Definition of Done
- [ ] `GET /api/v1/events/stream` returns SSE events for authenticated user scope
- [ ] Redis pub/sub works for cross-instance broadcast
- [ ] Command Center v1 loads < 2s with 10 insiden aktif
- [ ] Live map shows incident markers with clustering
- [ ] Events feed refreshes automatically without page reload
- [ ] API endpoints return correct data without relying on sync
- [ ] Deployment scripts include Redis + supervisord event worker

---

## Phase 1: Pilot Readiness (Weeks 3-6)

### Priority: P0-P1

| # | Task | Effort | Depends On |
|---|---|---|---|
| 1.1 | Assessment API — direct access optimization + realtime events | 1 day | Phase 0 |
| 1.2 | Sitrep API — direct access optimization + realtime events | 1 day | Phase 0 |
| 1.3 | Klaster API — realtime progress events | 1 day | Phase 0 |
| 1.4 | Mobilisasi API — realtime status events (depart/arrive/finish) | 1 day | Phase 0 |
| 1.5 | Penugasan API — realtime assignment events | 1 day | Phase 0 |
| 1.6 | Pos Aju API — realtime position/status updates | 2 days | Phase 0 |
| 1.7 | Logistik API — stok check + mutasi + realtime alerts | 3 days | 1.6 |
| 1.8 | Relawan API — pendaftaran, verifikasi, penugasan | 2 days | 1.5 |
| 1.9 | Queue: PDF generation integration with governance | 2 days | 1.7 |
| 1.10 | Pilot telemetry — `nurisk:aggregate-metrics` (DONE) | 0 | ✅ |
| 1.11 | Security hardening — scope middleware on remaining routes | 1 day | — |
| 1.12 | Pilot checklist verification | 1 day | 1.1-1.11 |
| **Total** | | **16 days** | |

### Deliverables
- All domain APIs functional via direct access + sync fallback
- Realtime events integrated into all domain operations
- Logistik API (gudang, stok, mutasi, permintaan)
- Relawan API (kebutuhan, pendaftaran, verifikasi, shift)
- PDF generation integrated with queue
- All pilot checklist items verified

### Definition of Done
- [ ] All 37 API routes return correct, optimized responses
- [ ] Domain events broadcast via SSE within 500ms of action
- [ ] Logistik: stok check, mutasi, permintaan via API
- [ ] Relawan: end-to-end flow via API
- [ ] PDF: generation via queue without blocking HTTP
- [ ] Pilot checklist: 100% items verified ✅
- [ ] Load test: P95 < 500ms for all endpoints
- [ ] Security scan: zero P0/P1 findings

---

## Phase 2: Regional Readiness (Weeks 7-12)

### Priority: P1-P2

| # | Task | Effort | Depends On |
|---|---|---|---|
| 2.1 | Command Center v2 — drill-down per insiden, widgets | 3 days | Phase 0 |
| 2.2 | Command Center v2 — live volunteer status map | 2 days | Phase 0 |
| 2.3 | Command Center v2 — logistics overview dashboard | 2 days | 1.7 |
| 2.4 | Governance: Pleno via API (not just web) | 2 days | Phase 0 |
| 2.5 | Governance: Surat API (draft, paraf, finalize via API) | 3 days | Phase 0 |
| 2.6 | Governance: Eskalasi + Aktivasi via API | 1 day | 2.4 |
| 2.7 | Feedback Klaster API | 1 day | 1.3 |
| 2.8 | Gap Kebutuhan API | 1 day | 2.7 |
| 2.9 | Aset API (pinjam, kembali, double-booking guard) | 3 days | 1.7 |
| 2.10 | Pengungsian API (pos, sensus harian, penerima manfaat) | 2 days | 1.6 |
| 2.11 | Offline sync fallback completion | 3 days | Phase 0 |
| 2.12 | Conflict resolution simplification → Last-Write-Wins | 1 day | 2.11 |
| 2.13 | MySQL DR verification | 1 day | — |
| 2.14 | Production load test (Octane/RoadRunner + MySQL) | 2 days | Phase 0 |
| 2.15 | Regional deployment prerequisites | 3 days | 2.1-2.14 |
| **Total** | | **30 days** | |

### Deliverables
- Full Command Center with drill-down capability
- Governance APIs (pleno, surat, eskalasi via mobile)
- Feedback & Gap Kebutuhan APIs
- Aset & Pengungsian APIs
- Offline sync as fallback (not primary)
- MySQL DR verified
- Production load test passed

### Definition of Done
- [ ] Command Center: live map, drill-down, volunteer status, logistics overview
- [ ] Governance APIs functional (pleno create → surat finalize via mobile)
- [ ] Offline sync: background process, Last-Write-Wins, retry queue
- [ ] Aset: no double-booking, full lifecycle via API
- [ ] Pengungsian: pos CRUD, sensus harian, penerima manfaat
- [ ] Load test: P95 < 500ms at 250 VU on production hardware
- [ ] MySQL DR: RTO < 30 min, RPO < 1 hour verified
- [ ] ALL 18 domains have API coverage

---

## Phase 3: Provincial Scale (Weeks 13-20)

### Priority: P2-P3

| # | Task | Effort | Depends On |
|---|---|---|---|
| 3.1 | Jurnal retention (RFC-004 implementation) | 2 days | — (deferred) |
| 3.2 | Queue scale-up: Redis queue driver | 2 days | Phase 0 |
| 3.3 | WebSocket upgrade: Laravel Reverb / Soketi | 3 days | Phase 0 |
| 3.4 | Realtime event persistence + replay | 2 days | 3.3 |
| 3.5 | Notification system (in-app + push) | 3 days | 3.3 |
| 3.6 | DR automation (weekly drill, auto-verify) | 2 days | — |
| 3.7 | Command Center v3 — full Grafana + custom dashboard | 3 days | Phase 2 |
| 3.8 | Public map + laporan publik via API | 2 days | 1.3 |
| 3.9 | Performance tuning & query optimization pass | 3 days | — |
| 3.10 | Security audit v3 | 2 days | — |
| 3.11 | Penetration testing | 3 days | 3.10 |
| 3.12 | Documentation & training materials | 5 days | — |
| **Total** | | **30 days** | |

### Deliverables
- Full realtime event system (WebSocket upgrade)
- Notification system (in-app + push)
- DR automation
- Command Center v3 (production-ready)
- Public map + laporan publik
- Security audit v3 + penetration test pass

### Definition of Done
- [ ] WebSocket: full duplex, reconnect, event persistence
- [ ] Notifications: delivered within 5s of event
- [ ] DR: automated weekly drill, RTO < 5 min
- [ ] Public map: accessible without login, no sensitive data leak
- [ ] Security: zero critical findings, penetration test pass
- [ ] All runbooks verified by ops team
- [ ] Training docs delivered to PWNU/PCNU trainers

---

## Dependency Matrix

```
PHASE 0 (Realtime Infrastructure)
  ├── Redis + SSE + Event System
  │
  ├── PHASE 1 (Pilot Readiness)
  │   ├── Domain APIs + Realtime Events
  │   ├── Logistik + Relawan
  │   ├── PDF Queue
  │   └── Pilot Checklist
  │
  ├── PHASE 2 (Regional Readiness)
  │   ├── Command Center v2
  │   ├── Governance APIs
  │   ├── Feedback + Gap + Aset + Pengungsian
  │   ├── Offline Sync Fallback
  │   └── Load Test + DR Verify
  │
  └── PHASE 3 (Provincial Scale)
      ├── WebSocket Upgrade
      ├── Notifications
      ├── DR Automation
      ├── Security Audit v3
      └── Training
```

## Prioritas vs Sprint Plan Lama

| Sprint Lama | Topik | Status Baru | Fase Baru |
|---|---|---|---|
| S01 | Autentikasi | ✅ COMPLETED | — |
| S02 | Organisasi | ✅ COMPLETED | — |
| S03 | Insiden | ✅ COMPLETED | — |
| S04 | Assessment | ⚠️ BUILT — perlu optimasi API | Phase 1 |
| S05 | Sitrep | ⚠️ BUILT — perlu optimasi API | Phase 1 |
| S06 | Pos Aju | ⚠️ BUILT — perlu realtime events | Phase 1 |
| S07 | Logistik | ❌ NOT BUILT | Phase 1 |
| S08 | Relawan | ⚠️ BUILT — perlu verifikasi + shift | Phase 1 |
| S09 | Pleno | ✅ COMPLETED (Web) — perlu API | Phase 2 |
| S10 | Surat | ✅ COMPLETED (Web) — perlu API | Phase 2 |
| S11 | Feedback & Gap | ❌ NOT BUILT | Phase 2 |
| S12 | Command Center | ❌ NOT BUILT | Phase 0 |
| — | Realtime Events | ❌ NOT BUILT (BARU) | Phase 0 |
| — | Aset | ❌ NOT BUILT | Phase 2 |
| — | Pengungsian | ❌ NOT BUILT | Phase 2 |
