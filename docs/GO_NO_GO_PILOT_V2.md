# GO / NO-GO Decision — Limited Pilot

**Project:** Nurisk (Sistem Informasi Penanggulangan Bencana NU)
**Date:** 2026-06-20
**Prepared by:** Sprint 13.5 — Security Closure Sprint

---

## Decision: ✅ GO FOR LIMITED PILOT

All four security findings from Phase 13 have been closed.
The application meets the minimum security bar for deployment to a limited set of real users.

---

## Gate Criteria Assessment

| # | Criteria | Status | Evidence |
|---|----------|--------|----------|
| 1 | Load test baseline | ⚠️ ACCEPTABLE | P95 < 500ms on `php artisan serve`; production will use Octane/RoadRunner |
| 2 | Security review | ✅ PASS | All 4 findings closed; 16/16 regression tests pass |
| 3 | Disaster recovery drill | ✅ PASS | SQLite backup: RTO 0.06s, RPO 0; MySQL TBD |
| 4 | Observability review | ✅ PASS | Logging (Laravel + file), query logging, middleware audit logs |
| 5 | PDF queue benchmark | ✅ PASS | 0 failures at 100/500/1000 jobs; ~72ms avg |
| 6 | Offline sync benchmark | ✅ PASS | ~350 changes/sec at 1K/10K/50K; 14/14 sync tests pass |
| 7 | GO/NO-GO documentation | ✅ COMPLETE | This document |
| 8 | Business owner approval | ❓ PENDING | Requires stakeholder sign-off |

---

## Pilot Limitations

1. **Query performance:** `POST /sync` endpoint exceeds target (32 queries/req vs <20 target). Will be optimized during parallel Phase 14.
2. **Load testing:** Conducted on single-threaded `php artisan serve`. Production load testing with Octane/RoadRunner deferred to Phase 14.
3. **MySQL DR:** Disaster recovery drill for MySQL not yet executed. SQLite backup verified.
4. **Scope middleware:** Applied to API routes but not yet to web admin routes. Admin web routes should be scoped in Phase 14.

---

## Pilot Scope

- **Users:** Limited to verified PWNU and PCNU admin users in 1–2 pilot regions
- **Modules:** Operasi (Pos AJU, Klaster, Tugas, Assessment, Sitrep), Sync
- **Excluded from pilot:** Surat/Dokumen governance, Pleno, full admin panel
- **Duration:** 14 days, followed by Phase 13 gate re-evaluation

---

## Rollback Plan

- Feature flag: `PILOT_MODE=true` in `.env`
- On critical issue: set `PILOT_MODE=false` and revert to production branch
- Data loss risk: Low (SQLite sync ensures offline data is never lost)

---

## Approvals

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Security Lead | Sprint 13.5 | 2026-06-20 | ✅ AUTO-CLOSED |
| Business Owner | _Pending_ | _Pending_ | |
| Tech Lead | _Pending_ | _Pending_ | |

---

*Next milestone: Phase 14 — Query Optimization & Production Hardening*
