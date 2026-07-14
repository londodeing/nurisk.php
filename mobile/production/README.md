# NURISK MOBILE — README
## Production Documentation Index

**Proyek**: NURISK ERP Disaster Management — Flutter Client  
**Fase**: F0 (Pre-Production Documentation)  
**Paradigma**: PUBLIC FIRST (v2.0)  
**Status**: COMPLETE — Revision Applied

---

> **📜 CONSTITUTION**  
> Semua keputusan arsitektur mengacu pada:  
> **[FLUTTER_APPLICATION_ARCHITECTURE.md](./FLUTTER_APPLICATION_ARCHITECTURE.md)**

---

## Dokumen Inti (Constitution & Review)

| Dokumen | Deskripsi | Status |
|---------|-----------|--------|
| [FLUTTER_APPLICATION_ARCHITECTURE.md](./FLUTTER_APPLICATION_ARCHITECTURE.md) | **Constitution** — Sumber kebenaran tunggal arsitektur Flutter | ✅ FINAL |
| [PARADIGM_SHIFT_REVIEW.md](./PARADIGM_SHIFT_REVIEW.md) | Review & Impact Matrix perubahan paradigma | ✅ FINAL |

---

## Dokumen Teknis (15 Dokumen)

| # | Dokumen | Deskripsi | Status |
|---|---------|-----------|--------|
| 01 | [MOBILE_ARCHITECTURE.md](./01_MOBILE_ARCHITECTURE.md) | Arsitektur, stack teknologi, routing | ⚠️ Perlu revisi minor |
| 02 | [AUTHENTICATION_DOMAIN.md](./02_AUTHENTICATION_DOMAIN.md) | Spesifikasi domain autentikasi | ⚠️ Perlu revisi minor |
| 03 | [AUTHENTICATION_API_MAPPING.md](./03_AUTHENTICATION_API_MAPPING.md) | Kontrak API autentikasi + gap list | ⚠️ Perlu tambah public API |
| 04 | [AUTHENTICATION_UI_FLOW.md](./04_AUTHENTICATION_UI_FLOW.md) | State machine autentikasi | ✅ REVISED v2.0 |
| 05 | [GOVERNANCE_DOMAIN.md](./05_GOVERNANCE_DOMAIN.md) | Domain governance | ✅ Valid (minor note) |
| 06 | [GOVERNANCE_API_MAPPING.md](./06_GOVERNANCE_API_MAPPING.md) | Kontrak API governance | ✅ Valid |
| 07 | [ROLE_BASED_NAVIGATION.md](./07_ROLE_BASED_NAVIGATION.md) | Navigasi per role | ✅ REVISED v2.0 |
| 08 | [MOBILE_PERMISSION_MATRIX.md](./08_MOBILE_PERMISSION_MATRIX.md) | Matriks permission | ⚠️ Perlu tambah GUEST row |
| 09 | [OFFLINE_FIRST_STRATEGY.md](./09_OFFLINE_FIRST_STRATEGY.md) | Strategi offline | ⚠️ Perlu pisahkan public/governance cache |
| 10 | [SYNC_ENGINE.md](./10_SYNC_ENGINE.md) | Spesifikasi sync engine | ⚠️ Perlu tambah public sync |
| 11 | [MEDIA_STRATEGY.md](./11_MEDIA_STRATEGY.md) | Strategi media | ✅ Valid (minor note) |
| 12 | [DESIGN_SYSTEM.md](./12_DESIGN_SYSTEM.md) | Design system tokens | ⚠️ Perlu public nav components |
| 13 | [SCREEN_INVENTORY.md](./13_SCREEN_INVENTORY.md) | Inventaris screen | ✅ REVISED v2.0 |
| 14 | [DATA_FLOW.md](./14_DATA_FLOW.md) | Alur data Flutter ↔ Laravel | ⚠️ Perlu tambah public flow |
| 15 | [EXECUTIVE_DECISION.md](./15_EXECUTIVE_DECISION.md) | Go/No-Go Sprint F1 | ⚠️ Perlu update blockers |

**Legend**: ✅ Selesai/Valid | ⚠️ Perlu revisi | 🔴 Obsolete/Rewrite needed

---

## Sprint F1 Go/No-Go Checklist

### Backend Blockers
- [ ] B-01: `GET /api/auth/me/permissions` — Structured permission list
- [ ] B-02: `GET /api/governance/inbox` — Aggregated governance inbox
- [ ] B-03: `GET /api/governance/inbox/count` — Inbox badge count
- [ ] B-04: `GET /api/governance/mandates/me/active` — Active mandate of current user
- [ ] B-05: `POST /api/auth/fcm-token` — Store FCM device token
- [ ] B-06: `DELETE /api/auth/fcm-token` — Remove FCM token on logout

### Public Layer Readiness (NEW — v2.0)
- [ ] `GET /api/public/dashboard` ✅ Ada
- [ ] `GET /api/public/incident/{id}/detail` ✅ Ada
- [ ] `GET /api/laporan/peta` ✅ Ada
- [ ] `POST /api/laporan` ✅ Ada
- [ ] `GET /api/weather/forecast` ✅ Ada
- [ ] `GET /api/external/bmkg/gempa` ✅ Ada
- [ ] `GET /api/internal/weather/*` ✅ Ada

**Public Layer Backend: ✅ SIAP** — Semua public endpoints sudah ada.  
**Governance Layer Backend: ❌ MENUNGGU** — B-01 s/d B-06 harus selesai dulu.

---

*Dokumen ini adalah blueprint resmi sebelum Sprint Flutter dimulai.*  
*Seluruh programmer Flutter wajib membaca CONSTITUTION dan semua dokumen sebelum menulis satu baris kode.*


---

## Daftar Dokumen

| # | Dokumen | Deskripsi | Status |
|---|---------|-----------|--------|
| 01 | [MOBILE_ARCHITECTURE.md](./01_MOBILE_ARCHITECTURE.md) | Arsitektur, stack teknologi, routing, offline strategy | ✅ APPROVED |
| 02 | [AUTHENTICATION_DOMAIN.md](./02_AUTHENTICATION_DOMAIN.md) | Spesifikasi domain autentikasi lengkap | ✅ APPROVED |
| 03 | [AUTHENTICATION_API_MAPPING.md](./03_AUTHENTICATION_API_MAPPING.md) | Kontrak API autentikasi + gap list | ✅ DRAFT |
| 04 | [AUTHENTICATION_UI_FLOW.md](./04_AUTHENTICATION_UI_FLOW.md) | State machine dan flow diagram autentikasi | ✅ APPROVED |
| 05 | [GOVERNANCE_DOMAIN.md](./05_GOVERNANCE_DOMAIN.md) | Spesifikasi domain governance lengkap | ✅ DRAFT |
| 06 | [GOVERNANCE_API_MAPPING.md](./06_GOVERNANCE_API_MAPPING.md) | Kontrak API governance + gap list | ✅ DRAFT |
| 07 | [ROLE_BASED_NAVIGATION.md](./07_ROLE_BASED_NAVIGATION.md) | Menu, drawer, FAB per role | ✅ APPROVED |
| 08 | [MOBILE_PERMISSION_MATRIX.md](./08_MOBILE_PERMISSION_MATRIX.md) | Matriks permission Role × Feature | ✅ DRAFT |
| 09 | [OFFLINE_FIRST_STRATEGY.md](./09_OFFLINE_FIRST_STRATEGY.md) | Strategi offline, cache, queue | ✅ APPROVED |
| 10 | [SYNC_ENGINE.md](./10_SYNC_ENGINE.md) | Spesifikasi sync engine | ✅ APPROVED |
| 11 | [MEDIA_STRATEGY.md](./11_MEDIA_STRATEGY.md) | Strategi integrasi Enterprise Media Layer | ✅ APPROVED |
| 12 | [DESIGN_SYSTEM.md](./12_DESIGN_SYSTEM.md) | Design system: warna, tipografi, komponen | ✅ APPROVED |
| 13 | [SCREEN_INVENTORY.md](./13_SCREEN_INVENTORY.md) | Inventaris seluruh screen per sprint | ✅ APPROVED |
| 14 | [DATA_FLOW.md](./14_DATA_FLOW.md) | Diagram alur data Flutter ↔ Laravel ↔ DB | ✅ APPROVED |
| 15 | [EXECUTIVE_DECISION.md](./15_EXECUTIVE_DECISION.md) | Evaluasi Go/No-Go Sprint F1 | ✅ FINAL |

---

## Sprint F1 Go/No-Go Checklist

Backend harus menyelesaikan item berikut sebelum Flutter Sprint F1 dimulai:

- [ ] B-01: `GET /api/auth/me/permissions` — Structured permission list
- [ ] B-02: `GET /api/governance/inbox` — Aggregated governance inbox
- [ ] B-03: `GET /api/governance/inbox/count` — Inbox badge count
- [ ] B-04: `GET /api/governance/mandates/me/active` — Active mandate of current user
- [ ] B-05: `POST /api/auth/fcm-token` — Store FCM device token
- [ ] B-06: `DELETE /api/auth/fcm-token` — Remove FCM token on logout
- [ ] B-07: OpenAPI/Swagger Contract documentation

**Status**: ❌ BELUM SIAP — Awaiting backend gap closure

---

*Dokumen ini adalah blueprint resmi sebelum Sprint Flutter dimulai.*  
*Seluruh programmer Flutter wajib membaca semua dokumen sebelum menulis satu baris kode.*
