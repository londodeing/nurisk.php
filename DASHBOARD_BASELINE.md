# Dashboard Baseline — Phase D0

## 1. Current API Endpoint

**URL:** `GET /api/public/dashboard/config`
**Controller:** `App\Http\Controllers\Api\PublicDashboardApiController@config`
**Backend Composer:** `App\Services\Dashboard\Composer\PublicDashboardComposer`

### Raw JSON Response (captured 2026-07-12)

```json
{
  "screen": "PublicDashboard",
  "layout": "vertical",
  "nodes": [
    {
      "schema_version": "1.0",
      "type": "ListView",
      "props": { "padding": 16, "spacing": 16 },
      "children": [
        {
          "type": "Row",
          "props": { "spacing": 12 },
          "children": [
            {
              "type": "Card",
              "props": { "expanded": true },
              "actions": { "on_tap": { "type": "navigate", "target": "/incident/list" } },
              "children": [
                { "type": "Row", "props": { "spacing": 8, "crossAxisAlignment": "center" }, "children": [
                  { "type": "Icon", "props": { "name": "warning", "foreground": "danger" } },
                  { "type": "Text", "props": { "text": "Insiden Aktif", "style": "caption" } }
                ]},
                { "type": "Text", "props": { "text": "5", "style": "headline", "foreground": "danger" } }
              ]
            },
            {
              "type": "Card",
              "props": { "expanded": true },
              "actions": { "on_tap": { "type": "navigate", "target": "/mission/list" } },
              "children": [
                { "type": "Row", "props": { "spacing": 8, "crossAxisAlignment": "center" }, "children": [
                  { "type": "Icon", "props": { "name": "engineering", "foreground": "info" } },
                  { "type": "Text", "props": { "text": "Misi Berjalan", "style": "caption" } }
                ]},
                { "type": "Text", "props": { "text": "0", "style": "headline", "foreground": "info" } }
              ]
            }
          ]
        },
        {
          "type": "Container",
          "props": { "padding": { "t": 16, "l": 0, "b": 0, "r": 0 } },
          "children": [
            { "type": "Text", "props": { "text": "Peringatan Dini", "style": "subtitle" } },
            { "type": "Card", "props": { "title": "[BMKG] Peringatan Banjir", "description": "Siaga banjir pesisir utara dan Solo Raya." } }
          ]
        },
        {
          "type": "Container",
          "props": { "padding": { "t": 16, "l": 0, "b": 0, "r": 0 } },
          "children": [
            { "type": "Text", "props": { "text": "Prakiraan Cuaca (12 Jam)", "style": "subtitle" } },
            {
              "type": "Row",
              "props": { "scrollable": true, "spacing": 8 },
              "children": [
                { "type": "Card", "children": [
                  { "type": "Text", "props": { "text": "", "style": "caption" } },
                  { "type": "Icon", "props": { "name": "cloud", "foreground": "info" } },
                  { "type": "Text", "props": { "text": "\u00b0C", "style": "body" } }
                ]},
                { "type": "Card", "children": [
                  { "type": "Text", "props": { "text": "", "style": "caption" } },
                  { "type": "Icon", "props": { "name": "cloud", "foreground": "info" } },
                  { "type": "Text", "props": { "text": "\u00b0C", "style": "body" } }
                ]}
              ]
            }
          ]
        },
        {
          "type": "Container",
          "props": { "padding": { "t": 16, "l": 0, "b": 0, "r": 0 } },
          "children": [
            { "type": "Text", "props": { "text": "Kejadian Terkini (Jawa Tengah)", "style": "subtitle" } },
            { "type": "Card", "actions": { "on_tap": { "type": "navigate", "target": "/incident/detail/36" } }, "props": { "title": "Banjir", "description": " - PCNU Kabupaten Kudus" } },
            { "type": "Card", "actions": { "on_tap": { "type": "navigate", "target": "/incident/detail/35" } }, "props": { "title": "Banjir", "description": " - PCNU Kabupaten Kudus" } },
            { "type": "Card", "actions": { "on_tap": { "type": "navigate", "target": "/incident/detail/34" } }, "props": { "title": "Banjir", "description": " - PCNU Kabupaten Kudus" } },
            { "type": "Card", "actions": { "on_tap": { "type": "navigate", "target": "/incident/detail/33" } }, "props": { "title": "Banjir", "description": " - PCNU Kabupaten Kudus" } },
            { "type": "Card", "actions": { "on_tap": { "type": "navigate", "target": "/incident/detail/32" } }, "props": { "title": "Banjir", "description": " - PCNU Kabupaten Kudus" } }
          ]
        },
        {
          "type": "Container",
          "props": { "padding": { "t": 16, "l": 0, "b": 0, "r": 0 } },
          "children": [
            { "type": "Text", "props": { "text": "Bantu Mereka", "style": "subtitle" } },
            {
              "type": "Card",
              "actions": { "on_tap": { "type": "navigate", "target": "/donation" } },
              "children": [
                { "type": "Text", "props": { "text": "Donasi Kemanusiaan", "style": "headline", "foreground": "success" } },
                { "type": "Text", "props": { "text": "Salurkan bantuan Anda untuk korban bencana melalui NU Care-LAZISNU.", "style": "body" } }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

**Response size:** ~3.2 KB (raw JSON)

---

## 2. Widget Tree (Current Flutter)

```
PublicDashboardScreen (ConsumerWidget)
├── AppBar (title: "Dashboard Publik", actions: [notification bell])
├── RefreshIndicator
│   └── configState.when()
│       ├── data: ConfigEntity
│       │   ├── layoutType == 'scene'
│       │   │   └── Scaffold → SduiRenderer(node: rootNode)
│       │   │       └── SduiRegistry resolves each component type
│       │   │           ├── 'ListView' → SduiListView
│       │   │           ├── 'Row' → SduiRow
│       │   │           ├── 'Container' → SduiContainer
│       │   │           ├── 'Text' → SduiText
│       │   │           ├── 'Icon' → SduiIcon
│       │   │           ├── 'Card' → SduiCard
│       │   │           └── (no WidgetRegistry component IDs used in current tree)
│       │   │
│       │   └── layoutType != 'scene'
│       │       └── SduiScreen(title, rootNode, appBar)
│       │           └── ActionDispatcherScope
│       │               └── SduiRenderer(node: rootNode)
│       │
│       ├── loading: Scaffold → CircularProgressIndicator
│       └── error: Scaffold → error message + "Coba Lagi" button
```

**Note:** The current tree from `PublicDashboardComposer` does NOT use `WidgetRegistry`-based component IDs (like `kpi_section`, `weather_card`, etc.). It uses pure SDUI primitives (`Text`, `Icon`, `Card`, `Row`, `Container`, `ListView`). The `WidgetRegistry` components (`KpiCardsSection`, `WeatherCard`, `IncidentFeedList`) are registered but **not currently emitted** by the current composer.

The separate `DashboardKpiRemoteDatasource` hitting `public/dashboard` likely returns 404 (no matching route), so the KPI section may already be showing error state.

---

## 3. Interaction Inventory

| # | Element | Interaction | Current Handler | Runtime? |
|---|---------|------------|----------------|----------|
| 1 | KPI Card: "Insiden Aktif" | onTap → navigate to /incident/list | `RuntimeAction (navigate)` | ✅ |
| 2 | KPI Card: "Misi Berjalan" | onTap → navigate to /mission/list | `RuntimeAction (navigate)` | ✅ |
| 3 | Incident Card | onTap → navigate to /incident/detail/{id} | `RuntimeAction (navigate)` | ✅ |
| 4 | Donation Card | onTap → navigate to /donation | `RuntimeAction (navigate)` | ✅ |
| 5 | Pull-to-refresh | refresh configProvider | `ref.read(configProvider.notifier).refresh()` | ❌ (Riverpod) |
| 6 | Notification bell | onPressed → (empty) | `() {}` | ❌ (no-op) |
| 7 | Error retry | onPressed → refresh | `ref.read(configProvider.notifier).refresh()` | ❌ (Riverpod) |
| 8 | Weather forecast | scroll (horizontal Row) | Native scroll | ✅ |
| 9 | Weather warning Card | (none — informational) | — | ✅ |

**Interactions that are NOT yet Runtime:**
- Pull-to-refresh (should dispatch `reload` action)
- Error retry (should dispatch `reload` action)
- Notification bell (currently no-op — placeholder)

---

## 4. Backend Data Sources (PublicDashboardComposer)

| Data | Source | Cached? |
|------|--------|---------|
| KPI analytics (total_incidents_active, personnel_mobilized) | `AnalyticsProjectionService` | ✅ |
| Weather warnings | `WeatherProjectionService` | ✅ |
| Weather forecast | `WeatherSnapshot` model (DB) | ✅ |
| Recent incidents | `OperasiInsiden` query (latest 5) | ❌ (direct query) |
| Donation banner | Static | ✅ |

---

## 5. Current Flutter Data Flow

```
PublicDashboardScreen
├── configProvider (AsyncNotifier)
│   └── ConfigRepositoryImpl
│       └── ConfigRemoteDatasourceImpl
│           └── GET api/public/dashboard/config
│               └── Response: { screen, layout, nodes: [...] }
│
├── dashboardKpiProvider (AsyncNotifier) — SEPARATE, hits public/dashboard
│   └── DashboardKpiRepositoryImpl
│       └── DashboardKpiRemoteDatasourceImpl
│           └── GET api/public/dashboard (likely 404 — not found in routes)
│
├── IncidentProvider — SEPARATE
│   └── GET api/public/dashboard (same endpoint, likely 404)
```

---

## 6. Performance Baseline (to be measured)

| Metric | Legacy | Runtime | Target |
|--------|--------|---------|--------|
| Payload size | ~3.2 KB | TBD | <10% increase |
| Build time (Flutter) | TBD | TBD | <10% regression |
| First frame | TBD | TBD | <10% regression |
| Average frame | TBD | TBD | <10% regression |

*Note: Accurate timing requires a running device/emulator.*

---

## 7. Screenshot / Golden Reference

Currently no golden file exists for the Public Dashboard. Existing golden tests (`sdui_goldens_test.dart`) use synthetic data, not the live API response.

---

## 8. Existing Tests

| Test File | Coverage |
|-----------|----------|
| `tests/Feature/PublicDashboardSnapshotTest.php` | ❌ Does not exist yet |
| `tests/Feature/SduiContractTest.php` | ✅ NSS 1.0 envelope contract (for Account) |
| `mobile/app/test/sdui_goldens_test.dart` | ⚠ Synthetic data only, no real dashboard payload |

---

## 9. Flutter Components Used

| SDUI Primitive | Flutter Widget | Status |
|---------------|---------------|--------|
| `ListView` | `SduiListView` | ✅ |
| `Row` | `SduiRow` | ✅ |
| `Container` | `SduiContainer` | ✅ |
| `Text` | `SduiText` | ✅ |
| `Icon` | `SduiIcon` | ✅ |
| `Card` | `SduiCard` | ✅ |

All primitives required by the dashboard are already implemented. No new primitives needed.

---

## 10. Blockers / Risks

1. **`public/dashboard` (without `/config`) endpoint does not exist** — Flutter's `DashboardKpiRemoteDatasource` and `IncidentRemoteDatasource` both call this non-existent endpoint. They likely always show errors.
2. **No WidgetRegistry components used** — The current composer uses pure SDUI primitives, not `kpi_section` / `weather_card` IDs. The `WidgetRegistry` is unused by the current flow.
3. **Refresh uses Riverpod, not RuntimeAction** — Pull-to-refresh calls `configProvider.notifier().refresh()` directly. Must be migrated to `RuntimeAction(reload)`.
4. **layoutType == 'scene' bypasses SduiScreen** — When `layoutType == 'scene'`, the screen renders via `SduiRenderer` directly without the action dispatcher. This means actions won't work in that mode.
