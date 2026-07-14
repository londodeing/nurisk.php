# NURISK MOBILE — EXECUTIVE DECISION
## Document 15: Sprint F1 Readiness Evaluation
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION — KEPUTUSAN EKSEKUTIF  
**Author**: Enterprise Mobile Solution Architect  
**Tanggal Evaluasi**: 2026-07-06

---

## RINGKASAN EKSEKUTIF

Setelah melakukan analisis menyeluruh terhadap backend NURISK (Laravel 12, MySQL, MinIO, Redis, Laravel Queue), seluruh API yang tersedia, model domain, dan kebutuhan Flutter untuk Domain Authentication dan Governance, berikut adalah keputusan resmi:

---

## ❌ FLUTTER BELUM SIAP MEMASUKI SPRINT F1

**Keputusan ini bersifat obyektif, berbasis bukti, dan dapat diubah jika gap ditutup.**

---

## ALASAN KEPUTUSAN

### 🔴 BLOCKER #1 — Tidak Ada Endpoint Permission Terstruktur (P0)

**Gap**: Endpoint `GET /api/auth/me` mengembalikan data user, profil, dan peran — namun **tidak mengembalikan daftar permission yang terstruktur** (e.g., `governance.approval.create`, `operasi.incident.create`).

**Dampak**:
- Flutter tidak dapat membangun RBAC navigation tanpa tahu permission apa yang dimiliki user
- Semua komponen PermissionGuard, route redirect, dan widget visibility **tidak bisa diimplementasi**
- Seluruh dokumen `08_MOBILE_PERMISSION_MATRIX.md` menjadi tidak executable

**Gap Endpoint**: `GET /api/auth/me/permissions` — **BELUM ADA**

**Estimasi Backend**: 1–2 hari

---

### 🔴 BLOCKER #2 — Tidak Ada Governance Inbox Endpoint (P0)

**Gap**: Tidak ada endpoint aggregasi untuk Governance Inbox. Flutter memerlukan satu endpoint yang mengembalikan semua item pending (approval, paraf surat, delegasi, notifikasi) dalam satu response.

**Dampak**:
- Layar Governance Inbox (Screen 4.01) tidak bisa dibangun
- Badge count untuk bottom navigation tidak bisa ditampilkan
- Alur paling kritikal di Sprint F1 (Approval) tidak dapat dimulai

**Gap Endpoint**: 
- `GET /api/governance/inbox` — **BELUM ADA**
- `GET /api/governance/inbox/count` — **BELUM ADA**

**Estimasi Backend**: 2–3 hari

---

### 🔴 BLOCKER #3 — Tidak Ada Endpoint Mandate Aktif User Login (P0)

**Gap**: Saat ini, untuk mendapatkan mandate aktif user yang login, Flutter harus memanggil `GET /api/governance/mandates?user_id={id}` — namun ini menggunakan `user_id` yang berarti Flutter harus sudah tahu `id_pengguna` dari `GET /api/auth/me`, lalu query ulang. Ini adalah 2 sequential requests yang ineffisien.

**Yang Dibutuhkan**: `GET /api/governance/mandates/me/active` — endpoint singkat yang mengembalikan mandate aktif dari user yang sedang login berdasarkan Bearer Token.

**Dampak**:
- Mandate Picker Screen tidak bisa dibangun dengan efisien
- Session initialization akan lambat (2 serial requests)

**Gap Endpoint**: `GET /api/governance/mandates/me/active` — **BELUM ADA**

**Estimasi Backend**: 0.5 hari

---

### 🔴 BLOCKER #4 — Tidak Ada FCM Token Storage Endpoint (P0)

**Gap**: Backend tidak memiliki endpoint untuk menyimpan FCM token device. Tanpa ini, push notification (notifikasi approval, mandate expiry, sync required) tidak dapat dikirim ke device yang tepat.

**Gap Endpoint**:
- `POST /api/auth/fcm-token` — **BELUM ADA**
- `DELETE /api/auth/fcm-token` — **BELUM ADA**

**Estimasi Backend**: 0.5 hari

---

### 🟡 CONCERN #1 — Generic Approval Action Endpoint Tidak Ada (P1)

**Gap**: Setiap jenis approval (mobilisasi, surat, penugasan) memiliki endpoint berbeda. Tidak ada unified approval endpoint. Flutter akan memerlukan routing logic yang kompleks untuk menentukan endpoint mana yang harus dipanggil berdasarkan item type.

**Rekomendasi**: Buat `POST /api/governance/approvals/{id}/approve` dan `POST /api/governance/approvals/{id}/reject` sebagai facade — atau dokumentasikan mapping yang jelas per item type.

---

### 🟡 CONCERN #2 — Forgot Password Flow Belum Ada (P1)

**Gap**: Endpoint reset password via OTP belum ada di backend. Ini dibutuhkan untuk Sprint F2, namun screen inventori sudah memasukkan layar ini.

**Aksi**: Tandai layar Forgot Password sebagai placeholder di Sprint F1. Implementasi di Sprint F2 setelah backend siap.

---

### 🟡 CONCERN #3 — Presigned URL Short TTL (15 Menit) — Handled

**Status**: INI BUKAN BLOCKER. Sudah didokumentasikan strategi caching dan expired URL handling di `11_MEDIA_STRATEGY.md`. Flutter dapat mengimplementasi ini.

---

### 🟢 YANG SUDAH SIAP

Berikut yang sudah **siap digunakan Flutter** tanpa perubahan backend:

| # | Komponen | Status |
|---|---------|--------|
| 1 | Login (`POST /api/auth/login`) | ✅ Siap |
| 2 | Logout (`POST /api/auth/logout`) | ✅ Siap |
| 3 | Fetch Profile (`GET /api/auth/me`) | ✅ Siap |
| 4 | Device Token Refresh | ✅ Siap |
| 5 | Device Management (list/logout-all/delete) | ✅ Siap |
| 6 | Register Relawan/Anggota | ✅ Siap |
| 7 | Mandate List & Detail | ✅ Siap (butuh /me/active) |
| 8 | Delegation CRUD | ✅ Siap |
| 9 | Node, Position, Institution list | ✅ Siap |
| 10 | SK List & Detail | ✅ Siap |
| 11 | Structure Levels | ✅ Siap |
| 12 | Audit Trail | ✅ Siap |
| 13 | Surat & Paraf endpoints | ✅ Siap |
| 14 | Sync Engine API (`/api/v1/sync`) | ✅ Siap |
| 15 | Enterprise Media Layer | ✅ Siap |
| 16 | Org Tree endpoints | ✅ Siap |

---

## ROADMAP MENUJU SPRINT F1

### Phase 0 — Backend Gap Closure (Estimasi: 3–5 hari kerja)

| # | Task | Owner | Estimasi | Blocker? |
|---|------|-------|----------|---------|
| B-01 | Implement `GET /api/auth/me/permissions` | Backend | 2 hari | YES |
| B-02 | Implement `GET /api/governance/inbox` | Backend | 2 hari | YES |
| B-03 | Implement `GET /api/governance/inbox/count` | Backend | 0.5 hari | YES |
| B-04 | Implement `GET /api/governance/mandates/me/active` | Backend | 0.5 hari | YES |
| B-05 | Implement `POST /api/auth/fcm-token` | Backend | 0.5 hari | YES |
| B-06 | Implement `DELETE /api/auth/fcm-token` | Backend | 0.5 hari | YES |
| B-07 | Finalize approval action endpoints | Backend | 1 hari | NO (P1) |
| B-08 | Document permission code map | Backend | 0.5 hari | YES |

**Total Estimasi Gap Closure**: 5–7 hari kerja

---

### Phase 0.5 — OpenAPI Contract (Rekomendasi — Paralel dengan Phase 0)

Seperti yang diusulkan di awal, buatkan **OpenAPI/Swagger Contract** sebelum Flutter mulai development:

```yaml
# Contoh OpenAPI spec
openapi: "3.0.0"
info:
  title: NURISK Mobile API
  version: "1.0.0"
paths:
  /api/auth/login:
    post:
      requestBody: ...
      responses:
        200:
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/LoginResponse'
```

**Manfaat**:
- Tim Flutter dan Backend dapat bekerja paralel dengan kontrak yang stabil
- Validasi otomatis request/response di development
- Dokumentasi selalu up-to-date

**Tools**: Laravel L5-Swagger atau Scribe untuk auto-generate dari kode existing

---

### Phase 1 — Sprint F1 Kickoff (Setelah Gap Ditutup)

**Scope Sprint F1** (≈2–3 minggu):
1. Authentication screens (Login, Splash, Mandate Picker, Lock Screen)
2. Profile screen
3. Dashboard MVP (per role)
4. Governance Inbox (list + approve/reject)
5. Mandate + Delegation screens
6. Notifikasi dasar
7. Offline cache + sync engine MVP
8. Push notification (FCM integration)
9. Settings (Keamanan, Sync, Posisi)

---

## KEPUTUSAN AKHIR

```
╔══════════════════════════════════════════════════════════════╗
║                    KEPUTUSAN EKSEKUTIF                        ║
╠══════════════════════════════════════════════════════════════╣
║                                                               ║
║   FLUTTER SPRINT F1 : ❌ BELUM DAPAT DIMULAI                 ║
║                                                               ║
║   Alasan:                                                     ║
║   4 blocker endpoint belum tersedia di backend                ║
║                                                               ║
║   Kondisi Go/No-Go Sprint F1:                                 ║
║   ✅ B-01 (permissions endpoint)    DONE                      ║
║   ✅ B-02 (governance inbox)        DONE                      ║
║   ✅ B-03 (inbox count)             DONE                      ║
║   ✅ B-04 (mandates/me/active)      DONE                      ║
║   ✅ B-05 (fcm-token store)         DONE                      ║
║                                                               ║
║   Jika seluruh checklist di atas terpenuhi:                   ║
║   → SPRINT F1 DIIZINKAN DIMULAI                               ║
║                                                               ║
║   Estimasi: 5–7 hari kerja dari sekarang                      ║
╚══════════════════════════════════════════════════════════════╝
```

---

## SKOR KESIAPAN

| Domain | Backend Readiness | Flutter Documentation | Overall |
|--------|------------------|----------------------|---------|
| Authentication | 85% (gap: FCM, permissions) | ✅ Lengkap | 🟡 85% |
| Governance | 70% (gap: inbox, active-mandate) | ✅ Lengkap | 🟡 70% |
| Media | 100% (Production Ready) | ✅ Lengkap | ✅ 100% |
| Sync Engine | 95% | ✅ Lengkap | ✅ 95% |
| Offline Strategy | N/A (client-side) | ✅ Lengkap | ✅ 100% |
| Design System | N/A (client-side) | ✅ Lengkap | ✅ 100% |
| **TOTAL** | **80%** | **✅ 100%** | **🟡 80%** |

**Target untuk Go**: Backend readiness ≥ 95% untuk domain Authentication dan Governance.

---

*Document Status: FINAL — Keputusan ini wajib di-review kembali setelah backend gap B-01 hingga B-05 selesai*
