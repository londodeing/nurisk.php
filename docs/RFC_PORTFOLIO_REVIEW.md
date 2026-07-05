# RFC PORTFOLIO REVIEW

**Date:** 2026-06-20  
**Reviewer:** Principal Solution Architect  
**Context:** Strategic realignment to Realtime Disaster Operations Platform with Offline Resilience

---

## Executive Summary

| RFC | Old Status | Recommended Status | Priority Change |
|---|---|---|---|
| RFC-001: Offline Sync V1 | FROZEN (Baseline) | **REVISE** — demote to fallback path | P0 → P2 |
| RFC-003: Async PDF | DRAFT (Under Review) | **KEEP** — no change needed | P1 → P1 |
| RFC-004: Jurnal Retention | DRAFT (Under Review) | **DEFER** — not blocking for pilot | P2 → P3 |

---

## RFC-001: Offline Sync V1

### Current Status
FROZEN — approved baseline architecture. 4 mitigations (Fix-1 through Fix-4) defined and implemented. 14/14 sync tests passing. 24 queries/sync request (vs 15 target).

### Evaluation

| Kriteria | Penilaian |
|---|---|
| Masih relevan? | **Sebagian** — sebagai offline fallback, tetap relevan. Sebagai primary data path, TIDAK relevan. |
| Prioritas berubah? | **Ya** — diturunkan dari P0 (primary) menjadi P1/P2 (fallback). |
| Harus direvisi? | **Ya** — scope dipersempit menjadi offline resilience layer. Bagian Option B++ (scope segregation, membership versioning) tetap valid. Bagian bootstrap via S3 dapat diturunkan prioritasnya. |
| Harus dipensiunkan? | **Tidak** — komponen ini masih diperlukan untuk graceful degradation. |
| Harus dipecah? | **Ya** — pisahkan menjadi: (1) Offline Resilience Protocol dan (2) Bootstrap Snapshot Strategy. Bagian Conflict Resolution cukup Last-Write-Wins. |

### Rekomendasi Perubahan

1. **Status baru:** REVISE — dipertahankan sebagai *offline resilience layer*, bukan *primary data path*
2. **Yang dipertahankan:**
   - Option B++ scope segregation (critical untuk security)
   - Membership versioning (critical untuk revocation)
   - Cursor-based pagination (masih diperlukan untuk initial load)
   - Conflict queue (disederhanakan → Last-Write-Wins)
3. **Yang diubah:**
   - S3 Bootstrap → turun prioritas (post-pilot). Bootstrap via API langsung cukup.
   - Conflict resolution → Last-Write-Wins default. Manual queue untuk edge case saja.
   - Sync query optimization (24 → target) → ditunda. Fokus ke API langsung.
4. **Batasan baru (non-goals yang diaktifkan):**
   - WebSocket untuk realtime PUSH — sync tidak perlu push
   - API langsung adalah primary path — sync hanya fallback reads
   - Flutter sync engine hanya berjalan saat background check koneksi
5. **Prioritas implementasi sisa:** P2 (setelah realtime infrastructure, command center, dan governance domain selesai)

---

## RFC-003: Async PDF

### Current Status
DRAFT — under review. Defines queue-based PDF generation for surat keluar. State machine: `siap_tanda_tangan → pdf_pending → ditandatangani / pdf_failed`. Uses database queue with 3 retries and exponential backoff.

### Evaluation

| Kriteria | Penilaian |
|---|---|
| Masih relevan? | **Ya** — PDF generation tetap synchronous-blocking. Queue diperlukan. |
| Prioritas berubah? | **Tidak** — tetap P1. Surat governance adalah prioritas untuk pilot. |
| Harus direvisi? | **Tidak** — desain sudah solid. Namun perlu implementasi tambahan untuk dukungan realtime notifikasi (user dapat notifikasi saat PDF selesai). |
| Harus dipensiunkan? | **Tidak** |
| Harus dipecah? | **Tidak** |

### Rekomendasi Minor

1. **Status baru:** KEEP — APPROVED for implementation
2. **Tambahan:** Integrasi dengan event system — ketika PDF selesai/faile, broadcast event ke user via SSE
3. **Prioritas:** P1 — implementasi di fase pilot readiness bersama governance domain

---

## RFC-004: Jurnal Retention

### Current Status
DRAFT — under review. Defines monthly archive for `operasi_jurnal` table. Time-based partitioning. Archive + purge jobs. 90-day active retention, 5-year archive.

### Evaluation

| Kriteria | Penilaian |
|---|---|
| Masih relevan? | **Ya, tapi bukan sekarang** — volume data jurnal saat ini masih kecil (< 100K rows). Retention hanya diperlukan setelah 1-2 tahun operasi. |
| Prioritas berubah? | **Ya** — diturunkan dari P2 menjadi P3. Tidak blocking untuk pilot atau regional. |
| Harus direvisi? | **Tidak** — desain sudah matang. Cukup defer implementasi. |
| Harus dipensiunkan? | **Tidak** |
| Harus dipecah? | **Tidak** |

### Rekomendasi

1. **Status baru:** DEFER — implementasi setelah operasi 1 tahun
2. **Kondisi trigger implementasi:** Ketika `operasi_jurnal` mencapai 500K rows ATAU query time > 500ms
3. **Prioritas:** P3

---

## Summary Table

| RFC | New Status | Priority | Effort | Notes |
|---|---|---|---|---|
| RFC-001: Offline Sync V1 | **REVISE** | P2 (was P0) | 2-3 weeks | Persempit scope jadi offline resilience. Option B++ scope segregation tetap critical. |
| RFC-003: Async PDF | **KEEP** | P1 | 1 week | APPROVED. Tambah notifikasi via event system. |
| RFC-004: Jurnal Retention | **DEFER** | P3 | 1 week | Trigger: 500K rows atau query > 500ms. |

### RFC-002?
Tidak ada RFC-002 dalam repository. Gap ini perlu diisi dengan RFC untuk Realtime Event Infrastructure.

### RFC Baru yang Diperlukan

| RFC | Topik | Priority | Proposed Status |
|---|---|---|---|
| RFC-005 | Realtime Event Infrastructure (SSE/WebSocket) | P0 | DRAFT |
| RFC-006 | Command Center Live Dashboard Architecture | P0 | DRAFT |
| RFC-007 | Mobile API Direct Access Pattern (non-sync) | P0 | DRAFT |
