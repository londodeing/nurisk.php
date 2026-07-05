# COMMAND CENTER IMPLEMENTATION PLAN

> Estimasi implementasi dashboard Command Center dalam 3 sprint.
> Total: 4 hari pengembangan + 2 hari integrasi + 1 hari pengujian = **7 hari kerja**.

---

## SPRINT 15B — PWNU DASHBOARD (3 hari)

### Scope
Dashboard PWNU dengan 10 widget, 6 API endpoint, polling 30s/60s/5min.

### Files to Create

#### 1. Controller & Service Layer
| File | Type | Est. Jam |
|---|---|---|
| `app/Http/Controllers/CommandCenter/PwnuDashboardController.php` | Controller (SSR + API) | 2 |
| `app/Services/CommandCenter/PwnuDashboardService.php` | Service (query logic) | 3 |
| `app/Http/Resources/CommandCenter/InsidenAktifResource.php` | Resource | 0.5 |
| `app/Http/Resources/CommandCenter/TimelineResource.php` | Resource | 0.5 |

#### 2. Blade Views
| File | Est. Jam |
|---|---|
| `resources/views/layouts/command-center.blade.php` | 2 |
| `resources/views/command-center/pwnu/dashboard.blade.php` | 2 |
| `resources/views/command-center/pwnu/partials/_hero-cards.blade.php` | 1 |
| `resources/views/command-center/pwnu/partials/_insiden-table.blade.php` | 1 |
| `resources/views/command-center/pwnu/partials/_timeline.blade.php` | 0.5 |
| `resources/views/command-center/pwnu/partials/_posko-chart.blade.php` | 1 |
| `resources/views/command-center/pwnu/partials/_kebutuhan.blade.php` | 0.5 |
| `resources/views/command-center/pwnu/partials/_peringatan.blade.php` | 0.5 |

#### 3. JavaScript
| File | Est. Jam |
|---|---|
| `public/js/cc-polling.js` | 2 |
| `public/js/cc-charts.js` | 1 |

#### 4. Routes
| File | Est. Jam |
|---|---|
| `routes/command-center.php` (segment PWNU) | 0.5 |

#### 5. Tests
| File | Est. Jam |
|---|---|
| `tests/Feature/CommandCenter/PwnuDashboardTest.php` | 1.5 |

### Effort Summary

| Layer | Files | Jam |
|---|---|---|
| Controller | 1 | 2 |
| Service | 1 | 3 |
| Resources | 2 | 1 |
| Views | 8 | 8.5 |
| JS | 2 | 3 |
| Routes | 1 | 0.5 |
| Tests | 1 | 1.5 |
| **Total** | **16** | **19.5** (~2.5 hari) |

### Deliverable
- `/command-center/pwnu` — dashboard SSR dengan AJAX polling
- 6 API endpoint untuk polling widget
- Layout `command-center.blade.php` (shared untuk sprint berikutnya)

---

## SPRINT 15C — PCNU + POSKO DASHBOARD (3 hari)

### Scope
Dashboard PCNU (12 widget, 6 endpoint) + Dashboard POSKO (8 widget, 5 endpoint).
Reuse layout + polling system dari Sprint 15B.

### Files to Create

#### 1. Controllers & Services
| File | Type | Est. Jam |
|---|---|---|
| `app/Http/Controllers/CommandCenter/PcnuDashboardController.php` | Controller | 2 |
| `app/Http/Controllers/CommandCenter/PoskoDashboardController.php` | Controller | 2 |
| `app/Services/CommandCenter/PcnuDashboardService.php` | Service | 3 |
| `app/Services/CommandCenter/PoskoDashboardService.php` | Service | 2 |
| `app/Http/Resources/CommandCenter/TugasResource.php` | Resource | 0.5 |
| `app/Http/Resources/CommandCenter/MobilisasiResource.php` | Resource | 0.5 |
| `app/Http/Resources/CommandCenter/KebutuhanResource.php` | Resource | 0.5 |

#### 2. Blade Views
| File | Est. Jam |
|---|---|
| `resources/views/command-center/pcnu/dashboard.blade.php` | 2 |
| `resources/views/command-center/pcnu/partials/_hero-cards.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_insiden-table.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_timeline.blade.php` | 0.5 |
| `resources/views/command-center/pcnu/partials/_tugas-table.blade.php` | 1 |
| `resources/views/command-center/pcnu/partials/_mobilisasi.blade.php` | 0.5 |
| `resources/views/command-center/pcnu/partials/_relawan.blade.php` | 0.5 |
| `resources/views/command-center/pcnu/partials/_logistik.blade.php` | 0.5 |
| `resources/views/command-center/pcnu/partials/_peringatan.blade.php` | 0.5 |
| `resources/views/command-center/posko/dashboard.blade.php` | 2 |
| `resources/views/command-center/posko/partials/_hero-cards.blade.php` | 1 |
| `resources/views/command-center/posko/partials/_tugas-table.blade.php` | 1 |
| `resources/views/command-center/posko/partials/_personel-table.blade.php` | 1 |
| `resources/views/command-center/posko/partials/_kebutuhan-relawan.blade.php` | 0.5 |
| `resources/views/command-center/posko/partials/_timeline.blade.php` | 0.5 |

#### 3. Routes (tambah ke routes/command-center.php)
| Segment | Est. Jam |
|---|---|
| Routes PCNU segment | 0.5 |
| Routes POSKO segment | 0.5 |

#### 4. Tests
| File | Est. Jam |
|---|---|
| `tests/Feature/CommandCenter/PcnuDashboardTest.php` | 1.5 |
| `tests/Feature/CommandCenter/PoskoDashboardTest.php` | 1.5 |

### Effort Summary

| Layer | Files | Jam |
|---|---|---|
| Controllers | 2 | 4 |
| Services | 2 | 5 |
| Resources | 3 | 1.5 |
| Views (PCNU) | 9 | 7.5 |
| Views (POSKO) | 6 | 6 |
| Routes | 2 segments | 1 |
| Tests | 2 | 3 |
| **Total** | **26** | **28** (~3.5 hari) |

### Deliverable
- `/command-center/pcnu` — dashboard operational PCNU
- `/command-center/posko` — dashboard posko spesifik
- 11 API endpoint baru (6 PCNU + 5 POSKO)

---

## SPRINT 15D — RELAWAN DASHBOARD + INTEGRASI (1.5 hari)

### Scope
Dashboard Relawan (5 widget, 4 endpoint) + integrasi akhir + pengujian lintas role.

### Files to Create

#### 1. Controller & Service
| File | Est. Jam |
|---|---|
| `app/Http/Controllers/CommandCenter/RelawanDashboardController.php` | 1.5 |
| `app/Services/CommandCenter/RelawanDashboardService.php` | 2 |

#### 2. Blade Views
| File | Est. Jam |
|---|---|
| `resources/views/command-center/relawan/dashboard.blade.php` | 1.5 |
| `resources/views/command-center/relawan/partials/_hero-cards.blade.php` | 0.5 |
| `resources/views/command-center/relawan/partials/_tugas-saya.blade.php` | 1 |
| `resources/views/command-center/relawan/partials/_insiden.blade.php` | 0.5 |
| `resources/views/command-center/relawan/partials/_timeline.blade.php` | 0.5 |

#### 3. Routes (tambah ke routes/command-center.php)
| Segment | Est. Jam |
|---|---|
| Routes RELAWAN segment | 0.5 |

#### 4. Tests
| File | Est. Jam |
|---|---|
| `tests/Feature/CommandCenter/RelawanDashboardTest.php` | 1.5 |
| `tests/Feature/CommandCenter/AuthorizationTest.php` | 1.5 |

### Effort Summary

| Layer | Files | Jam |
|---|---|---|
| Controller | 1 | 1.5 |
| Service | 1 | 2 |
| Views | 5 | 4 |
| Routes | 1 segment | 0.5 |
| Tests | 2 | 3 |
| **Total** | **10** | **11** (~1.5 hari) |

### Deliverable
- `/command-center/relawan` — dashboard relawan
- Seluruh 4 dashboard command center siap pakai

---

## TOTAL EFFORT

| Sprint | Hari | Files | Endpoint | Role |
|---|---|---|---|---|
| 15B | 2.5 | 16 | 6 + layout | PWNU |
| 15C | 3.5 | 26 | 11 | PCNU + POSKO |
| 15D | 1.5 | 10 | 4 | RELAWAN |
| **Total** | **7.5** | **52** | **21** | **4 dashboard** |

---

## DEPENDENCY GRAPH

```
Sprint 15B (PWNU)
  ├── Layout: command-center.blade.php ← base untuk semua
  ├── cc-polling.js ← shared semua dashboard
  ├── cc-charts.js ← shared semua dashboard
  └── PwnuDashboardService ← pola query untuk di-copy ke dashboard lain

Sprint 15C (PCNU + POSKO)
  ├── Bergantung pada: Layout, cc-polling.js, cc-charts.js (dari 15B)
  ├── PcnuDashboardService ← reuse pola query
  └── PoskoDashboardService ← reuse filtered scope

Sprint 15D (RELAWAN)
  ├── Bergantung pada: Layout, cc-polling.js (dari 15B)
  └── RelawanDashboardService ← reuse filtered scope by user
```

---

## FILES NOT CREATED (using existing)

| Component | Existing Location | Reason |
|---|---|---|
| ScopeMiddleware | `app/Http/Middleware/ScopeMiddleware.php` | Already built, only register route |
| RoleMiddleware | `app/Http/Middleware/RoleMiddleware.php` | Already built, only register route |
| AuthorizationContextService | `app/Services/Auth/AuthorizationContextService.php` | Already built, reuse for scope filter |
| AuthUser model | `app/Models/AuthUser.php` | Already built |
| OperasiInsiden model | `app/Models/OperasiInsiden.php` | Already built |
| Semua operasi model | `app/Models/Operasi*` | Already built |

---

## RISK REGISTER

| Risk | Severity | Mitigation |
|---|---|---|
| Query >200ms pada dashboard PCNU | Medium | SQL views untuk agregasi berat, indeks komposit |
| ScopeMiddleware blokir cross-PCNU data untuk PWNU | High | ScopeMiddleware perlu bypass untuk role pwnu (super_admin logic sudah ada) |
| Posko user tidak punya dashboard terpisah | Medium | Gunakan scope_id_pcnu + filter id_posaju dari session |
| Chart.js bundle besar | Low | CDN cached, hanya dimuat sekali |
| Polling 30s banjiri server | Low | throttle:60,1 pada route API + cache query result 30s |
| Data isolation bocor antar role | High | Setiap endpoint harus filter by role + scope. Integration test wajib. |
