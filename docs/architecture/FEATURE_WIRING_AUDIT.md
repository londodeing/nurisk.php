# Feature Wiring Audit — Account Workspace

## Legend

| Status | Meaning |
|--------|---------|
| ✅ | Connected — data flows DB→Service→Screen→Serializer→Flutter correctly |
| ⚠️ | Partial — has data source but action/endpoint is missing or broken |
| ❌ | Missing — hardcoded, placeholder, or not wired |
| 🔵 | Untestable — depends on external state that can't be verified in current environment |

---

## 1. IdentitySection (`IdentitySection.php`)

### Data Sources

| # | Feature | PHP Source | DB Table/Column | Files | Status |
|---|---------|-----------|----------------|-------|--------|
| 1.1 | Inisial Avatar (2 chars) | `generateInisial($namaLengkap)` → `$inisial` | `auth_pengguna_profil.nama_lengkap` | `AccountHomeService:47-58`, `IdentitySection:207-216` | ✅ |
| 1.2 | Nama Lengkap | `$profil['nama_lengkap'] ?? $profil['no_hp']` | `auth_pengguna_profil.nama_lengkap` | `AccountHomeService:47-58`, `IdentitySection:48` | ✅ |
| 1.3 | Role Title | `ucwords(str_replace('_', ' ', $profil['nama_peran']))` | `auth_roles.nama_peran` | `AccountHomeService:47-58`, `IdentitySection:50` | ✅ |
| 1.4 | Scope Text | `resolveScopeText($profil, $penugasan)` | `auth_users.default_scope_id` + `organisasi_pcnu.nama_pcnu` | `IdentitySection:218-234` | ✅ |
| 1.5 | Jabatan Aktif | `$jabatanAktif['nama_jabatan']` | `master_jabatan.nama_jabatan` via `pengguna_jabatan` | `AccountHomeService:61-74`, `IdentitySection:141-153` | ✅ |
| 1.6 | Keahlian (max 4 chips) | `array_slice($keahlian, 0, 4)` | `auth_keahlian_master.nama_keahlian` via `auth_pengguna_keahlian` | `AccountHomeService:76-86`, `IdentitySection:156-191` | ✅ |
| 1.7 | Toggle Ketersediaan | `$profil['is_tersedia']` | `auth_users.is_tersedia` | `IdentitySection:99-135` | ⚠️ |

### Actions

| # | Action | Type | Target | Status |
|---|--------|------|--------|--------|
| A1 | Guest card tap → login | `navigate` | `/login` | ❌ **Broken** — GoRouter route is `/auth/login`, not `/login` |
| A2 | Toggle ketersediaan | `action` | `profil.toggle_tersedia` | ❌ **No backend handler** — `CustomActionHandler` needs `endpoint` field |

### Issues

- **A1**: Guest card target `/login` doesn't match GoRouter route `/auth/login` — has been fixed in this session (changed to `/auth/login`), but needs Flutter rebuild to take effect.
- **A2**: `profil.toggle_tersedia` action has **no handler**. `CustomActionHandler` dispatches by `action_type` string. The `ActionResolver` (`action_resolver.dart:48`) only knows about `navigate`, `submit`, `reload`, `toast`, and `action`. The toggle action's payload `action_type: profil.toggle_tersedia` would hit `CustomActionHandler`, which calls `context.httpClient.post(endpoint, ...)`. But the action payload has **no `endpoint` field** — it only has `action_type` and `id_pengguna`. So `CustomActionHandler.execute()` would throw `FormatException: endpoint required`.

---

## 2. StatusOperasionalSection (`StatusOperasionalSection.php`)

### Data Sources

| # | Feature | PHP Source | DB Table/Column | Files | Status |
|---|---------|-----------|----------------|-------|--------|
| 2.1 | Penugasan list | `$penugasan` | `operasi_penugasan` + `operasi_insiden` + `bencana_master_jenis` | `AccountHomeService:88-108` | ✅ |
| 2.2 | Kode kejadian | `$p['kode_kejadian']` | `operasi_insiden.kode_kejadian` | `StatusOperasionalSection:62` | ✅ |
| 2.3 | Nama bencana | `$p['nama_bencana']` | `bencana_master_jenis.nama_bencana` | `StatusOperasionalSection:63` | ✅ |
| 2.4 | Peran + icon mapping | `getPeranMapping($p['peran_otoritas'])` | `operasi_penugasan.peran_otoritas` | `StatusOperasionalSection:108-119` | ✅ |
| 2.5 | Status insiden badge | `getStatusInsidenMapping($p['status_insiden'])` | `operasi_insiden.status_insiden` | `StatusOperasionalSection:121-132` | ✅ |
| 2.6 | Waktu mulai | `date('d M Y, H:i', strtotime($p['waktu_mulai']))` | `operasi_penugasan.waktu_mulai` | `StatusOperasionalSection:67` | ✅ |

### Actions

| # | Action | Type | Target | Status |
|---|--------|------|--------|--------|
| A3 | Penugasan item tap | `navigate` | `/incident/detail/{id_insiden}` | ⚠️ Route exists? |

### Issues

- **A3**: Target `/incident/detail/{id_insiden}` needs to be verified against GoRouter routes. There's no static `/incident/detail/:id` route in `app_router.dart`. There IS `RoutePaths.incidentDetail` = `/incident/:id` which maps to `SduiRemoteScreen(endpoint: 'public/incident/$id/detail')`. But the target from backend is `/incident/detail/{id_insiden}` — `incident/detail/123` vs `incident/123`. These are different paths.

---

## 3. CommandCenterSection (`CommandCenterSection.php`)

### Data Sources

| # | Feature | PHP Source | DB Table/Column | Files | Status |
|---|---------|-----------|----------------|-------|--------|
| 3.1 | Alert insiden (unclustered) | `$alertInsiden` | `operasi_insiden` LEFT JOIN `operasi_klaster` WHERE `ok.id IS NULL` | `AccountHomeService:150-185` | ✅ |
| 3.2 | Command center list | `$commandCenter` | `operasi_insiden` + `operasi_klaster` + `operasi_master_klaster` | `AccountHomeService:110-148` | ✅ |
| 3.3 | Kode kejadian | `$c['kode_kejadian']` | `operasi_insiden.kode_kejadian` | `CommandCenterSection:110` | ✅ |
| 3.4 | Nama klaster | `COALESCE(mk.nama_klaster, 'Tunggu Aktivasi')` | `operasi_master_klaster.nama_klaster` | `CommandCenterSection:135` | ✅ |
| 3.5 | Lama hari | `TIMESTAMPDIFF(DAY, i.waktu_mulai, NOW())` | computed | `CommandCenterSection:134` | ✅ |
| 3.6 | Jumlah sitrep | `(SELECT COUNT(*) FROM operasi_sitrep ...)` | subquery | `CommandCenterSection:136` | ✅ |
| 3.7 | Prioritas badge | `getPrioritasMapping($c['prioritas'])` | `operasi_insiden.prioritas` | `CommandCenterSection:154-163` | ✅ |
| 3.8 | Status badge | `getStatusInsidenMapping($c['status_insiden'])` | `operasi_insiden.status_insiden` | `CommandCenterSection:165-176` | ✅ |
| 3.9 | Command state | `getCommandStateMapping($c['command_state'])` | `operasi_insiden.status_operasi` | `CommandCenterSection:178-188` | ✅ |

### Actions

| # | Action | Type | Target | Status |
|---|--------|------|--------|--------|
| A4 | CC item tap | `navigate` | `/incident/detail/{id_insiden}` | ⚠️ Same route mismatch as A3 |

### Issues

- **Role-gated**: Only `super_admin`, `pwnu`, `pcnu` can see command center. `relawan`, `trc`, `publik` get `null`.
- **SQLite**: Returns empty array on SQLite (dev/test envs only — production uses MySQL).
- **A4**: Same path mismatch as A3 — `/incident/detail/{id}` vs GoRouter's `/incident/:id`.

---

## 4. MenuSection (`MenuSection.php`)

### Data Sources

| # | Feature | PHP Source | DB Table/Column | Files | Status |
|---|---------|-----------|----------------|-------|--------|
| 4.1 | No HP | `$profil['no_hp']` | `auth_users.no_hp` | `MenuSection:32` | ✅ |
| 4.2 | Status Akun badge | `getStatusAkunMapping($profil['status_akun'])` | `auth_users.status_akun` | `MenuSection:116-125` | ✅ |
| 4.3 | Terakhir Masuk | `$profil['terakhir_masuk']` formatted | `auth_users.terakhir_masuk` | `MenuSection:20` | ✅ |
| 4.4 | Ganti Password | static navigation | N/A (navigates to Flutter route) | `MenuSection:63` | ⚠️ |
| 4.5 | Keluar (Logout) | static button | N/A (submits to backend) | `MenuSection:77-99` | ⚠️ |

### Actions

| # | Action | Type | Target | Status |
|---|--------|------|--------|--------|
| A5 | Ganti Password | `navigate` | `/profil/ganti-password` | ❌ **Route doesn't exist** — No GoRouter route for `/profil/ganti-password` |
| A6 | Keluar | `submit` | `POST /api/v1/auth/logout` | ❌ **Broken** — `SubmitHandler` sends POST with empty body `{}` but uses `dio.post(endpoint, data: fields)` where `fields` is `{}` from `(object)[]` JSON. The **backend logout** (`AuthenticationApiController::logout()`) expects `auth:sanctum` middleware. But `SubmitHandler` uses `context.httpClient` which returns a NEW `Dio` (no auth headers). Logout call will get 401. |

### Issues

- **A5**: `/profil/ganti-password` has no GoRouter route. Tap does nothing (GoRouter throws).
- **A6**: `submit` action uses `context.httpClient` which instantiates a **plain Dio** via `context.httpClient` — no Bearer token. The logout endpoint requires `auth:sanctum`, so this will return 401. The `on_success` navigation to `/login` with `clear_stack: true` will never fire.

---

## 5. Cross-Cutting Issues

### 5.1 Authentication Flow

| Issue | Impact | Status |
|-------|--------|--------|
| Login catch-all exception | User can't login — sees "Terjadi kesalahan sistem atau kredensial salah" | ❌ |
| Logout uses plain Dio (no Bearer) | Logout POST returns 401 — silent failure | ❌ |
| Guest card target `/login` (now fixed) | Guest tap on "Tamu → login" does nothing | ✅ Fixed |

### 5.2 Availability Action

| Issue | Impact | Status |
|-------|--------|--------|
| `profil.toggle_tersedia` has no endpoint | Toggle tap → `FormatException: endpoint required` | ❌ |

### 5.3 Navigation Routes

| Backend Target | GoRouter Route | Match? |
|---------------|---------------|--------|
| `/login` | `/auth/login` | ❌ (fixed) |
| `/auth/login` | `/auth/login` | ✅ |
| `/incident/detail/{id}` | `/incident/:id` | ❌ |
| `/profil/ganti-password` | — (doesn't exist) | ❌ |

---

## 6. Root Cause × Layer Matrix

| Feature | Action | Root Cause | Layer | Status |
|---------|--------|-----------|-------|--------|
| Toggle ketersediaan | A2 | `endpoint` field missing in action payload | Backend (IdentitySection:105-110) | ❌ |
| Logout | A6 | `context.httpClient` = plain Dio (no Bearer) | Runtime (SduiScreen:79, SubmitHandler:27) | ❌ |
| Incident detail | A3, A4 | Route path mismatch `/incident/detail/{id}` vs `/incident/:id` | Backend + Flutter | ❌ |
| Ganti password | A5 | Route `/profil/ganti-password` doesn't exist | Flutter (app_router.dart) | ❌ |
| Guest card tap | A1 | Route `/login` doesn't match `/auth/login` | Flutter (account_home_provider.dart:31) | ✅ Fixed |

## 7. Summary Matrix

```
Section              │ Features │ Data Source │ Actions │ Working │ Status
─────────────────────┼──────────┼─────────────┼─────────┼─────────┼───────
IdentitySection      │    7     │  3 DB tbls  │    2    │   0/2   │  ⚠️
StatusOperasionalSec │    6     │  3 DB tbls  │    1    │   0/1   │  ⚠️
CommandCenterSection │    9     │  3 DB tbls  │    1    │   0/1   │  ⚠️
MenuSection          │    5     │  1 DB tbl   │    2    │   0/2   │  ⚠️
─────────────────────┼──────────┼─────────────┼─────────┼─────────┼───────
Total                │   27     │  7 DB tbls  │    6    │   0/6   │  ⚠️
```

**0 dari 6 actions berfungsi penuh.** Semua data dari database sudah benar, tapi routing actions dan action handlers belum terhubung.
