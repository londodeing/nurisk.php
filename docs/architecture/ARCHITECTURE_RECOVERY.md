# Architecture Recovery Report

## Structure

Each endpoint is mapped through 11 layers:

| # | Layer | What | Status |
|---|-------|------|--------|
| 1 | **DB** | Tables/views queried | ✅/❌/⚠ |
| 2 | **Repository** | Class + method | ✅/❌/⚠ |
| 3 | **Service** | Business logic class | ✅/❌/⚠ |
| 4 | **Composer** | Scene composer (legacy SDUI or Runtime Screen) | ✅/❌/⚠ |
| 5 | **Runtime Screen** | Runtime Screen builder (if migrated) | ✅/❌/⚠ |
| 6 | **Certification** | Certification path (Laravel RuntimeCertificationEngine / FlutterCertificationEngine) | ✅/❌/⚠ |
| 7 | **Serializer** | Serializer used (SduiSerializer / raw array) | ✅/❌/⚠ |
| 8 | **Endpoint** | Controller + route | ✅/❌/⚠ |
| 9 | **Flutter Parser** | How Flutter parses the HTTP response | ✅/❌/⚠ |
| 10 | **Widget Registry** | How UI components are resolved (SduiRegistry / WidgetFactory / WidgetRegistry) | ✅/❌/⚠ |
| 11 | **Action** | How user actions are dispatched | ✅/❌/⚠ |

**Status legend:**
- ✅ = Clean, production-ready, no issues
- ⚠ = Works but has known gaps or technical debt
- ❌ = Missing, broken, or wrong implementation
- ⛔ = Blocking—prevents the endpoint from functioning

---

## Endpoint Inventory

### 1. `GET /api/account/home` — Account Workspace

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1 | **DB** | ✅ | `auth_users`, `auth_roles`, `auth_pengguna_profil`, `pengguna_jabatan`, `auth_pengguna_keahlian`, `auth_keahlian_master`, `operasi_penugasan`, `operasi_insiden`, `bencana_master_jenis`, `operasi_klaster`, `operasi_master_klaster`, `operasi_sitrep`, `organisasi_pcnu` |
| 2 | **Repository** | ❌ | No repository class. Queries are direct SQL in `AccountHomeService`. No Eloquent ORM. |
| 3 | **Service** | ✅ | `App\Services\Sdui\Runtime\Screens\AccountHomeService` |
| 4 | **Composer** | ⚠ | Two parallel composers exist: `AkunSceneComposer` (legacy, implements SceneComposer interface, 791 lines) and `AccountWorkspaceScreen` (new, Runtime domain model). Both are active. |
| 5 | **Runtime Screen** | ✅ | `AccountWorkspaceScreen::build()` — builds ScreenNode → SectionNode → ComponentNode → RenderNode |
| 6 | **Certification** | ✅ | `RuntimeCertificationEngine::certify()` on Laravel side; `FlutterCertificationEngine` on Flutter side. |
| 7 | **Serializer** | ✅ | `SduiSerializer::serialize()` — maps ScreenNode→ListView, SectionNode→Column, RenderNode→kind |
| 8 | **Endpoint** | ✅ | `AccountHomeController@index` — wired to `AccountHomeService`, returns NSS 1.0 envelope |
| 9 | **Flutter Parser** | ✅ | `AccountHomeData.fromJson()` → `FlutterCertificationEngine` → `AccountHomeData` |
| 10 | **Widget Registry** | ⛔ | Serializer outputs `Component` type nodes with `componentId` (e.g., `ProfileCard`, `ActionList`). `SduiRegistry` does NOT know these types. `WidgetFactory` does. `SduiRenderer` uses `SduiRegistry`, not `WidgetFactory`. **Result: runtime "Unsupported Component" errors.** |
| 11 | **Action** | ⚠ | Actions defined in `AccountWorkspaceScreen` use `RenderNode` with action maps. Dispatcher routes `navigate` correctly. Missing: pull-to-refresh does not use `RuntimeActionDispatcher` — uses Riverpod directly. |

**Summary:** 6/11 layers ✅, 2 ⚠, 2 ❌/⛔. The blocking issue is Layer 10: the serializer and renderer use incompatible registries.

---

### 2. `GET /api/public/dashboard/config` — Public Dashboard

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1 | **DB** | ✅ | `operasi_insiden`, `bencana_master_jenis`, `weather_snapshots`, plus projection service caches |
| 2 | **Repository** | ❌ | No repository. `AnalyticsProjectionService`, `WeatherProjectionService`, `IncidentProjectionService` are direct-query services. |
| 3 | **Service** | ⚠ | `DashboardHomeService` (new, NSS wrapper) + `PublicDashboardComposer` (data builder). Parallel paths: legacy returns `{screen, layout, nodes}`; runtime returns `{schema_version, scene_id, version, ttl_seconds, root}`. |
| 4 | **Composer** | ✅ | `PublicDashboardComposer::compose()` — builds raw array tree with SDUI primitives |
| 5 | **Runtime Screen** | ❌ | No Runtime domain model. Composer returns raw arrays, not ScreenNode. |
| 6 | **Certification** | ⚠ | No Laravel-side certification. Flutter-side certification (`FlutterCertificationEngine`) runs on the envelope. |
| 7 | **Serializer** | ❌ | No serializer. Composer returns raw arrays directly. SduiSerializer is not used. |
| 8 | **Endpoint** | ✅ | `PublicDashboardApiController@config` — `?runtime=1` returns NSS envelope, legacy returns old format |
| 9 | **Flutter Parser** | ⚠ | Two parsers: legacy path uses `ConfigModel.fromJson()` → `SduiNode.fromJson()` on `nodes[0]`; runtime path uses `SduiRemoteScreen` → `FlutterCertificationEngine`. Feature flag `kUseRuntimeDashboard` controls which. |
| 10 | **Widget Registry** | ✅ | Current composer outputs only SDUI primitives (`Card`, `Text`, `Icon`, `Row`, `Container`, `ListView`). All are registered in `SduiRegistry`. `WidgetRegistry` components (`kpi_section`, `weather_card`) are NOT emitted by current composer. |
| 11 | **Action** | ⚠ | Actions in tree are `navigate` type → handled by `RuntimeActionDispatcher`. Pull-to-refresh and retry still use Riverpod directly. Notification bell is no-op. |

**Summary:** 4/11 ✅, 4 ⚠, 3 ❌. Dashboard works visually but has fragmented architecture (parallel legacy/runtime, no certification on server side, no repository layer).

---

### 3. `GET /api/dashboard/home` — Dashboard Home (role-aware)

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1 | **DB** | ❓ | Unknown — need to read `DashboardHomeController` |
| 2 | **Repository** | ❓ | Unknown |
| 3 | **Service** | ❓ | `DashboardHomeService` exists but structure unknown |
| 4 | **Composer** | ❓ | Unknown |
| 5 | **Runtime Screen** | ❓ | Unknown |
| 6 | **Certification** | ❓ | Unknown |
| 7 | **Serializer** | ❓ | Unknown |
| 8 | **Endpoint** | ⚠ | `DashboardHomeController@index` — exists but not yet analyzed |
| 9 | **Flutter Parser** | ❌ | No Flutter consumer known — likely unused or to-be-migrated |
| 10 | **Widget Registry** | ❓ | Unknown |
| 11 | **Action** | ❓ | Unknown |

**Summary:** Pending audit.

---

### 4. `GET /api/public/incident/list` — Public Incident List

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. Route exists: `Api\PublicIncidentApiController@index`. |

---

### 5. `GET /api/public/mission/list` — Public Mission List

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. Route exists: `Api\PublicMissionListController@index`. |

---

### 6. `GET /api/bff/dashboard` — BFF Composite Dashboard

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. Route exists: `Bff\DashboardBffController@index`. |

---

### 7. `GET /api/public/dashboard/trc/queue` — TRC Assessment Queue

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. Route exists: `TrcDashboardApiController@queue`. |

---

### 8. `GET /api/public/news` — News Feed

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. |

---

### 9. `GET /api/public/resources` — Resource Directory

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1–11 | **All** | ❓ | Not yet audited. |

---

### 10. Auth & User Endpoints

| Endpoint | Controller | Status |
|----------|------------|--------|
| `POST /api/auth/login` | `AuthenticationApiController@login` | ❓ |
| `POST /api/auth/register/{jenis}` | `AuthenticationApiController@register` | ❓ |
| `POST /api/auth/mandate` | `AuthApiController@selectMandate` | ❓ |
| `POST /api/auth/pin/set` | `AuthApiController@setPin` | ❓ |
| `POST /api/auth/pin/verify` | `AuthApiController@verifyPin` | ❓ |

---

### 11. Command Center Endpoints

| Endpoint | Controller | Status |
|----------|------------|--------|
| `GET /api/command-center/insiden-aktif` | `CommandCenterApiController@insidenAktif` | ❓ |
| `GET /api/command-center/jurnal-terbaru` | `CommandCenterApiController@jurnalTerbaru` | ❓ |
| `GET /api/command-center/statistik` | `CommandCenterApiController@statistik` | ❓ |
| `GET /api/command-center/stok-kritis` | `CommandCenterApiController@stokKritis` | ❓ |

---

## Cross-Cutting Issues

### A. Widget Registry Fragmentation

Three separate widget resolution systems exist:

| System | Used By | Scope |
|--------|---------|-------|
| `SduiRegistry` | `SduiRenderer` | SDUI primitives: `Container`, `Row`, `Column`, `Text`, `Icon`, `Card`, etc. ~20 types |
| `WidgetFactory` | Account workspace (via `ComponentNode` → `componentId`) | Domain components: `ProfileCard`, `ActionList`, `KpiSection` |
| `WidgetRegistry` | Public Dashboard (via config `widgets` array) | Feature widgets: `kpi_section`, `weather_card`, `incident_feed`, `cta_volunteer`, etc. |

None of these are unified. The serializer outputs one format, the renderer expects another.

### B. Repository Layer

No endpoint uses a formal Repository pattern. Every service either:
- Runs direct SQL queries (`AccountHomeService`)
- Uses in-line Eloquent queries (`PublicDashboardComposer`)
- Calls projection services that wrap direct queries

### C. Certification Duplication

| Side | Engine | Purpose |
|------|--------|---------|
| Laravel | `RuntimeCertificationEngine` | Validates domain model (ScreenNode → SectionNode → ComponentNode → RenderNode) |
| Flutter | `FlutterCertificationEngine` | Validates JSON envelope (schema, registry, actions, properties, states) |

These operate independently on different representations. No end-to-end certification that traces from DB → JSON → Flutter render.

### D. Action Architecture

| System | Where | Status |
|--------|-------|--------|
| `RuntimeActionDispatcher` | Flutter, SduiScreen | Handles `navigate`, `submit`, `reload`, `toast`, `custom` |
| Pull-to-refresh | Flutter, PublicDashboardScreen | Uses `configProvider.notifier().refresh()` — bypasses dispatcher |
| Error retry | Flutter, AccountHomeScreen | Uses provider `refresh()` — bypasses dispatcher |
| Notification bell | Flutter, PublicDashboardScreen | No-op `() {}` |

---

## Priority Audit Queue

1. `AccountHomeController` — fully mapped above, blocker at Layer 10
2. `PublicDashboardApiController` — mapped above, four gaps
3. `DashboardHomeController`
4. `PublicIncidentApiController`
5. `PublicMissionListController`
6. `TrcDashboardApiController`
7. `BffDashboardController`
8. `NewsController`
9. `CommandCenterApiController`
10. Auth controllers (login, register, mandate, PIN)

---

## Template for New Endpoint Audit

```markdown
### N. `{METHOD} /api/{path}` — {Description}

| # | Layer | Status | Detail |
|---|-------|--------|--------|
| 1 | **DB** | ❓ | |
| 2 | **Repository** | ❓ | |
| 3 | **Service** | ❓ | |
| 4 | **Composer** | ❓ | |
| 5 | **Runtime Screen** | ❓ | |
| 6 | **Certification** | ❓ | |
| 7 | **Serializer** | ❓ | |
| 8 | **Endpoint** | ❓ | |
| 9 | **Flutter Parser** | ❓ | |
| 10 | **Widget Registry** | ❓ | |
| 11 | **Action** | ❓ | |
```
