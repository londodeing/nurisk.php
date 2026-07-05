# COMMAND CENTER IMPLEMENTATION PLAN V2

> Revisi sprint setelah redesign decision-first.
> Urutan baru: Foundation → POSKO (frontline) → Relawan + PCNU → PWNU (oversight).

---

## SPRINT 15B — FOUNDATION + POSKO (3 hari)

### Prasyarat Sebelum Coding
- ✅ POSKO ownership solved (pj_posaju — Opsi A, no schema change)
- ✅ Check-in migration ready (`waktu_checkin`, `waktu_checkout` di operasi_penugasan)
- ✅ Widget Logistik dihapus dari semua dashboard
- ✅ COMMAND_CENTER_WIDGET_AUDIT final: KEEP/MERGE/REMOVE/HARMFUL decisions locked

### Foundation Layer (shared semua dashboard)

| File | Est. Jam | Description |
|---|---|---|
| `app/Services/CommandCenter/DecisionQueueService.php` | 3 | Query decision items per role |
| `app/Services/CommandCenter/QuickActionService.php` | 2 | Resolve available actions per role+context |
| `app/Services/CommandCenter/ContactDirectoryService.php` | 2 | Resolve contacts per role |
| `app/Services/CommandCenter/FreshnessService.php` | 1 | Freshness indicator helper |
| `app/Http/Middleware/PoskoScopeMiddleware.php` | 1 | Middleware untuk resolve posko ownership |
| `resources/views/components/decision-queue.blade.php` | 1 | Blade component |
| `resources/views/components/decision-queue-item.blade.php` | 0.5 | Blade component |
| `resources/views/components/quick-action-bar.blade.php` | 1 | Blade component |
| `resources/views/components/quick-action-button.blade.php` | 0.5 | Blade component |
| `resources/views/components/contact-directory.blade.php` | 1 | Blade offcanvas component |
| `resources/views/components/contact-card.blade.php` | 0.5 | Blade component |
| `resources/views/components/freshness-badge.blade.php` | 0.5 | Blade component |
| `resources/views/components/alert-bar.blade.php` | 0.5 | Blade component |
| `resources/views/layouts/command-center.blade.php` | 2 | Main layout (reused all dashboards) |
| `public/js/cc-polling.js` | 2 | Polling manager |
| `public/js/cc-components.js` | 1 | JS untuk Decision Queue, Alert Bar, Contact |
| `database/migrations/xxxx_add_checkin_to_operasi_penugasan.php` | 0.5 | Check-in migration |
| `routes/command-center.php` | 0.5 | Base routes |

**Foundation subtotal: 8 files + 1 migration + 2 JS + routes = 19.5 jam**

### POSKO Dashboard

| File | Est. Jam | Description |
|---|---|---|
| `app/Http/Controllers/CommandCenter/PoskoDashboardController.php` | 2 | SSR + API endpoints |
| `app/Services/CommandCenter/PoskoDashboardService.php` | 3 | Query logic with posko scope |
| `app/Http/Resources/CommandCenter/TugasResource.php` | 0.5 | Resource |
| `app/Http/Resources/CommandCenter/PersonelResource.php` | 0.5 | Resource |
| `app/Http/Resources/CommandCenter/KebutuhanResource.php` | 0.5 | Resource |
| `resources/views/command-center/posko/dashboard.blade.php` | 2 | Main view |
| `resources/views/command-center/posko/partials/_hero-card.blade.php` | 0.5 | Info posko + status |
| `resources/views/command-center/posko/partials/_tugas-panel.blade.php` | 1 | Tugas table + progress |
| `resources/views/command-center/posko/partials/_personel-panel.blade.php` | 1 | Personel with check-in status |
| `resources/views/command-center/posko/partials/_kebutuhan-panel.blade.php` | 0.5 | Kebutuhan relawan |
| `tests/Feature/CommandCenter/PoskoDashboardTest.php` | 2 | Feature test |

**POSKO subtotal: 11 files = 13.5 jam**

### Sprint 15B Total

| Layer | Jam |
|---|---|
| Foundation | 19.5 |
| POSKO | 13.5 |
| **Total** | **33 jam (~4 hari)** |

---

## SPRINT 15C — RELAWAN + PCNU (3 hari)

### Relawan Dashboard

| File | Est. Jam |
|---|---|
| `app/Http/Controllers/CommandCenter/RelawanDashboardController.php` | 1.5 |
| `app/Services/CommandCenter/RelawanDashboardService.php` | 2 |
| `app/Http/Controllers/CommandCenter/RelawanCheckinController.php` | 1 |
| `resources/views/command-center/relawan/dashboard.blade.php` | 1.5 |
| `resources/views/command-center/relawan/partials/_hero-status.blade.php` | 0.5 |
| `resources/views/command-center/relawan/partials/_tugas-saya.blade.php` | 1 |
| `resources/views/command-center/relawan/partials/_insiden-info.blade.php` | 0.5 |
| `tests/Feature/CommandCenter/RelawanDashboardTest.php` | 1.5 |

**Relawan subtotal: 8 files = 9.5 jam**

### PCNU Dashboard

| File | Est. Jam |
|---|---|
| `app/Http/Controllers/CommandCenter/PcnuDashboardController.php` | 2 |
| `app/Services/CommandCenter/PcnuDashboardService.php` | 3 |
| `app/Http/Resources/CommandCenter/InsidenResource.php` | 0.5 |
| `resources/views/command-center/pcnu/dashboard.blade.php` | 2 |
| `resources/views/command-center/pcnu/partials/_hero-row.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_insiden-table.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_tugas-panel.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_kebutuhan-panel.blade.php` | 1 |
| `tests/Feature/CommandCenter/PcnuDashboardTest.php` | 2 |

**PCNU subtotal: 9 files = 13.5 jam**

### Sprint 15C Total

| Layer | Jam |
|---|---|
| Relawan | 9.5 |
| PCNU | 13.5 |
| **Total** | **23 jam (~3 hari)** |

---

## SPRINT 15D — PWNU + INTEGRASI (2 hari)

### PWNU Dashboard

| File | Est. Jam |
|---|---|
| `app/Http/Controllers/CommandCenter/PwnuDashboardController.php` | 2 |
| `app/Services/CommandCenter/PwnuDashboardService.php` | 3 |
| `resources/views/command-center/pwnu/dashboard.blade.php` | 2 |
| `resources/views/command-center/pwnu/partials/_hero-row.blade.php` | 1 |
| `resources/views/command-center/pwnu/partials/_insiden-table.blade.php` | 1 |
| `tests/Feature/CommandCenter/PwnuDashboardTest.php` | 1.5 |

**PWNU subtotal: 6 files = 10.5 jam**

### Integration & Testing

| File | Est. Jam |
|---|---|
| `tests/Feature/CommandCenter/AuthorizationTest.php` | 2 |
| `tests/Feature/CommandCenter/DecisionQueueTest.php` | 1.5 |
| `tests/Feature/CommandCenter/QuickActionTest.php` | 1 |
| `tests/Feature/CommandCenter/ContactDirectoryTest.php` | 1 |
| `tests/Feature/CommandCenter/FreshnessTest.php` | 1 |
| Manual integration testing (4 dashboards, cross-role) | 2 |

**Integration subtotal: 5 test files + manual = 8.5 jam**

### Sprint 15D Total

| Layer | Jam |
|---|---|
| PWNU | 10.5 |
| Integration | 8.5 |
| **Total** | **19 jam (~2.5 hari)** |

---

## TOTAL EFFORT V2

| Sprint | Fokus | Hari | File | Endpoint |
|---|---|---|---|---|
| 15B | Foundation + POSKO | 4 | 19 + migration | 6 |
| 15C | Relawan + PCNU | 3 | 17 | 8 |
| 15D | PWNU + Integration | 2.5 | 11 | 4 |
| **Total** | | **9.5** | **47** | **18** |

> **V1 vs V2:**
> - V1: 7.5 hari, 52 files, PWNU first
> - V2: 9.5 hari, 47 files, POSKO first
> - +2 hari untuk Foundation layer (Decision Queue, Alert, Contact, Freshness)
> - Lebih lama 2 hari, TAPI dashboard sekarang DECISION-ENABLED, bukan read-only

---

## ENDPOINT INVENTORY V2

### Foundation (shared)
| Method | Endpoint | Widget |
|---|---|---|
| GET | `/api/cc/decision-queue` | Decision Queue (all roles) |
| GET | `/api/cc/quick-actions?context=` | Quick Actions (all roles) |
| GET | `/api/cc/contacts?role=` | Contact Directory (all roles) |
| POST | `/api/cc/checkin` | Check-in |
| POST | `/api/cc/checkout` | Check-out |

### POSKO (5)
| Method | Endpoint | Widget |
|---|---|---|
| GET | `/api/cc/posko/summary` | Hero row |
| GET | `/api/cc/posko/tugas` | Tugas panel |
| GET | `/api/cc/posko/personel` | Personel panel (check-in data) |
| GET | `/api/cc/posko/kebutuhan` | Kebutuhan panel |

### PCNU (5)
| Method | Endpoint | Widget |
|---|---|---|
| GET | `/api/cc/pcnu/summary` | Hero row |
| GET | `/api/cc/pcnu/insiden` | Insiden table + sitrep freshness |
| GET | `/api/cc/pcnu/tugas` | Tugas panel |
| GET | `/api/cc/pcnu/kebutuhan` | Kebutuhan relawan panel |

### RELAWAN (4)
| Method | Endpoint | Widget |
|---|---|---|
| GET | `/api/cc/relawan/status` | Hero status + shift |
| GET | `/api/cc/relawan/tugas-saya` | Tugas panel |
| GET | `/api/cc/relawan/insiden` | Info insiden related |

### PWNU (4)
| Method | Endpoint | Widget |
|---|---|---|
| GET | `/api/cc/pwnu/summary` | Hero row |
| GET | `/api/cc/pwnu/insiden` | Insiden per PCNU table |
| GET | `/api/cc/pwnu/kebutuhan` | Sumber daya panel |

### Total Endpoint: 18 (15 unique + 3 shared foundation)

---

## DEFINITION OF DONE CHECKLIST

| Kriteria | Status Target |
|---|---|
| Semua dashboard lolos Decision Review (5 questions test) | ✅ Per-phase validation |
| Tidak ada widget kategori HARMFUL | ✅ Hapus total korban hero, logistik, personel assigned |
| Semua role memiliki Decision Queue | ✅ PWNU: 5 item, PCNU: 6 item, POSKO: 4 item, RELAWAN: 2 item |
| Semua role memiliki Quick Actions | ✅ 11 action types across 4 groups |
| Semua angka memiliki freshness indicator | ✅ FreshnessService + badge component |
| Semua role memiliki Contact Directory | ✅ PWNU: PCNU contacts, PCNU: posko contacts, POSKO: command+logistik, RELAWAN: supervisor+emergency |
| POSKO ownership terdefinisi | ✅ Opsi A — pj_posaju |
| Check-in/check-out implemented | ✅ Migration + controller |
| Logistik widget dihapus | ✅ Dari semua dashboard |
| Implementasi bisa dimulai tanpa redesign tambahan | ✅ Semua desain final |
