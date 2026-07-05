# RISK REGISTER V2 — Realtime Operations Platform

**Date:** 2026-06-20  
**Previous Version:** docs/RISK_REGISTER.md (Phase 13)  
**Context:** Realignment to Realtime Disaster Operations Platform + New risk categories

---

## 1. Risk Revalidation Summary

| Original ID | Risk | Previous Severity | New Severity | Status Change | Rationale |
|---|---|---|---|---|---|
| CR-01 | API routes have no role/scope middleware | Critical | 🔴 CRITICAL | UNCHANGED | Still not fully mitigated |
| CR-02 | Sanctum tokens never expire | Critical | 🟢 RESOLVED | ✅ FIXED | 30-day expiry implemented |
| HR-01 | Sync endpoint 32 queries/request | High | 🟡 REDUCED to P2 | ⬇️ IMPROVED | Reduced to 24; sync is now secondary path |
| HR-02 | Mass assignment in AuthUser | High | 🟢 RESOLVED | ✅ FIXED | Sensitive fields removed from $fillable |
| HR-03 | No automated backup verification | High | 🟡 REDUCED to Medium | ⬇️ IMPROVED | Backup script exists but no automation |
| HR-04 | Built-in PHP server in load test | High | 🟡 REDUCED to Medium | ⬇️ IMPROVED | Octane/RoadRunner deployment ready |
| HR-05 | No token revocation on deactivation | High | 🟢 RESOLVED | ✅ FIXED | AuthUserObserver implemented |
| MR-01 | Role middleware flat string comparison | Medium | 🟢 RESOLVED | ✅ FIXED | RoleMiddleware uses hierarchical level |
| MR-02 | Correlation ID not propagated | Medium | 🟡 UNCHANGED | — | Still pending |
| MR-03 | No request duration logging | Medium | 🔴 INCREASED | ⬆️ WORSE | Now critical for realtime SLA monitoring |
| MR-04 | No slow query logging | Medium | 🟡 UNCHANGED | — | Still pending |
| MR-05 | Queue job duration not tracked | Medium | 🟡 UNCHANGED | — | Still pending |
| MR-06 | MySQL backup not tested | Medium | 🟡 UNCHANGED | — | Still pending |
| MR-07 | X-Request-ID dead code | Low | 🟡 UNCHANGED | — | Low priority |
| LR-01 | CORS max_age=0 | Low | 🟡 UNCHANGED | — | Low priority |
| LR-02 | Sanctum token_prefix empty | Low | 🟢 RESOLVED | ✅ FIXED | Token prefix configured |
| LR-03 | No per-endpoint rate limiting | Low | 🟡 UNCHANGED | — | Low priority |
| LR-04 | Index write overhead | Low | 🟡 UNCHANGED | — | Acceptable |
| LR-05 | Soft-delete filtering not indexed | Low | 🟡 UNCHANGED | — | Acceptable |

---

## 2. Risks No Longer Relevant

| Original ID | Risk | Reason for Removal |
|---|---|---|
| HR-02 | Mass assignment in AuthUser | ✅ Fixed — sensitive fields removed from $fillable |
| HR-05 | Token revocation on deactivation | ✅ Fixed — AuthUserObserver implemented |
| CR-02 | Sanctum tokens never expire | ✅ Fixed — 30-day expiry implemented |
| MR-01 | Role middleware flat string comparison | ✅ Fixed — hierarchical levels used |
| LR-02 | Sanctum token_prefix empty | ✅ Fixed — prefix configured |

---

## 3. Risks with Reduced Severity

| Original ID | Risk | Old → New Severity | Reason |
|---|---|---|---|
| HR-01 | Sync endpoint query count | High → P2 (Medium) | Sync is secondary path now; 24 queries acceptable |
| HR-03 | No automated backup verification | High → Medium | Backup script exists; automation deferred |
| HR-04 | Built-in PHP dev server load test | High → Medium | RoadRunner deployment ready |

---

## 4. Risks with Increased Severity

| Original ID | Risk | Old → New Severity | Reason |
|---|---|---|---|
| MR-03 | No request duration logging | Medium → 🔴 HIGH | Critical for monitoring realtime API SLA (P95 < 500ms). Without this, we cannot verify realtime performance targets. |

---

## 5. New Risks (Post-Realignment)

### CRITICAL

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|---|---|---|---|---|---|
| **NR-C01** | **WebSocket/SSE infrastructure not implemented** — realtime events cannot be delivered to command center or mobile clients | All realtime features blocked; Command Center cannot provide live updates | **CERTAIN** | Phase 0 must deliver SSE before any pilot. Redis + SSE endpoint in Week 1. | Backend Team |
| **NR-C02** | **API endpoints not optimized for direct access** — current endpoints designed for sync client, not direct mobile access | Mobile users experience high latency or incomplete data | High | Audit all 37 API routes. Add eager loading, pagination, caching. Optimize query patterns. | Backend Team |
| **NR-C03** | **Mobile client has no SSE/WebSocket capability** — Flutter app cannot receive realtime events | Realtime features invisible to mobile users — defeats purpose of platform | High | Flutter team must implement SSE client in Phase 0-1. | Flutter Team |
| **NR-C04** | **No Redis infrastructure in deployment** — no cache, no pub/sub, no session management | Cannot support realtime events at scale. All features degraded. | **CERTAIN** | Add Redis to deployment. Update nginx/supervisor/backup configs. | Ops Team |

### HIGH

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|---|---|---|---|---|---|
| **NR-H01** | **Realtime event storm** — too many events overwhelm SSE/WebSocket and Redis pub/sub | Events delayed or dropped; command center freezes | Medium | Implement per-channel throttling. Priority-based event queue. Max 100 P0 events/sec. | Backend Team |
| **NR-H02** | **Satellite outage** — internet satelit terputus saat operasi berlangsung | Semua fitur realtime tidak tersedia. Harus fallback ke offline mode. | Medium | Offline resilience layer harus siap. Last-Known-State display. Retry queue with exponential backoff. | Mobile Team |
| **NR-H03** | **Queue backlog untuk event broadcast** — event queue menumpuk, delivery delayed | Real-time menjadi near-real-time (menit, bukan detik) | Medium | Priority queue: P0 events skip queue, direct Redis publish. Queue hanya untuk P2-P3 events. | Backend Team |
| **NR-H04** | **Command center widget slow load (> 3s)** — aggregasi data multi-domain lambat | User experience buruk; dashboard ditinggalkan | High | Implement Redis caching untuk aggregasi. Warm cache on event. Avoid N+1 queries. | Backend Team |
| **NR-H05** | **Logistik API belum dibangun** — blocking path untuk pilot | Relawan tidak bisa request barang; stok tidak terpantau | High | Prioritaskan Logistik API di Phase 1. Ini adalah domain P0 untuk pilot. | Backend Team |
| **NR-H06** | **Governance API (Pleno/Surat) not available for mobile** — hanya via Web | Mobile users cannot participate in governance workflow | Medium | Phase 2 deliverable. Pilot dapat berjalan dengan governance via Web sementara. | Backend Team |
| **NR-H07** | **No realtime event monitoring** — tidak bisa detect event delivery failure | Blind spot: events silently dropped; command center shows stale data | High | Implement event health check: setiap event broadcast dicatat + consumer acknowledgment. | Ops Team |
| **NR-H08** | **Notification flooding** — terlalu banyak notifikasi P0 events | User overwhelmed; notifikasi diabaikan | Medium | Group notifications. Rate limit per user (max 1/min per event type). Do not disturb mode. | Mobile Team |

### MEDIUM

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|---|---|---|---|---|---|
| **NR-M01** | **SSE connection limit reached** — each browser/mobile opens persistent connection | New users cannot receive events | Medium | SSE connections are lightweight. Estimate: 1000 concurrent = ~100MB RAM. Monitor and scale. | Ops Team |
| **NR-M02** | **Event schema version mismatch** — backend pushes new event format, old clients crash | Client-side errors on event parsing | Medium | Version field in all events. Backward-compatible payload. Clients ignore unknown fields. | Both Teams |
| **NR-M03** | **Command center data accuracy** — live data tidak match dengan database (cache stale) | Wrong decisions based on stale data | Medium | Cache TTL max 30s for operational data. Event-based cache invalidation. | Backend Team |
| **NR-M04** | **Mobile offline cache inconsistency** — conflicting data between local cache and server after reconnect | User sees wrong data briefly after reconnect | Medium | Invalidate all cache on reconnect. Show stale banner until fresh data loaded. | Mobile Team |

### LOW

| ID | Risk | Impact | Likelihood | Mitigation | Owner |
|---|---|---|---|---|---|
| **NR-L01** | **Browser SSE reconnection storm** — semua browser reconnect bersamaan setelah server restart | Spike pada Redis + PHP-FPM | Low | Exponential backoff (1s, 2s, 4s, 8s, max 30s). Jitter. | Frontend Team |
| **NR-L02** | **Event log storage growth** — menyimpan semua events untuk replay | Disk usage increases | Low | Event retention: 7 days in Redis, 30 days in database. Auto-purge. | Ops Team |
| **NR-L03** | **Geolocation privacy concern** — personel location tracking via GPS | Privacy issue with relawan | Low | Opt-in location sharing. Data visible only to Komandan. Purge after insiden closed. | Legal / Product |

---

## 6. Risk Summary

| Severity | Old Count | New Count | Change |
|---|---|---|---|
| Critical | 2 | 6 | +4 (NR-C01, NR-C02, NR-C03, NR-C04) |
| High | 5 | 10 | +5 (3 old reduced, 5 new, 1 increased) |
| Medium | 7 | 10 | +3 (1 reduced, 5 new) |
| Low | 5 | 7 | +2 (5 old + 2 new, 2 removed) |
| Resolved | — | 5 | All fixed items |
| **Total Active** | **19** | **33** | **+14 new risks** |

---

## 7. Critical Path to Pilot

Risks that MUST be resolved before pilot:

| Priority | Risk ID | Resolution | Effort |
|---|---|---|---|
| P0 | NR-C01 | Implement SSE endpoint + Redis pub/sub | Phase 0 (Week 1) |
| P0 | NR-C04 | Deploy Redis + configure stack | Phase 0 (Week 1) |
| P0 | NR-C02 | Optimize 37 API routes for direct access | Phase 0-1 (2 weeks) |
| P1 | NR-C03 | Flutter SSE client implementation | Phase 1 (Week 3) |
| P1 | NR-H05 | Build Logistik API | Phase 1 (Week 3-4) |
| P1 | NR-H04 | Command Center caching strategy | Phase 1 (Week 4) |
| P1 | NR-H07 | Event monitoring + health check | Phase 1 (Week 4) |
| P1 | CR-01 | Complete scope middleware on remaining routes | Phase 1 (Week 3) |
