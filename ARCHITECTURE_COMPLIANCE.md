# ARCHITECTURE COMPLIANCE REPORT

This report evaluates the current implementation against the original arsitektur specifications, PRD, and design requirements.

## 1. Compliance vs Spec (Document 23: Dynamic Dashboard Configuration Layer)
According to [23_SERVER_DRIVEN_UI_SPEC.md](file:///home/londo/nurisk/mobile/production/23_SERVER_DRIVEN_UI_SPEC.md):
- **Specified Contract**:
  - The endpoint `GET /api/public/dashboard/config` should return:
    ```json
    {
      "version": "1.0",
      "layout": ["warning_banner", "weather_card", ...],
      "feature_flags": { ... }
    }
    ```
  - Caching is required in a local SQLite table (`config_table`).
- **Actual Implementation**:
  - The actual Flutter implementation ([ConfigModel.fromJson](file:///home/londo/nurisk/mobile/app/lib/features/public/config/data/models/config_model.dart)) expects a custom payload containing key `nodes` (list of components) instead of `layout` strings.
  - The Laravel BFF ([DashboardBffController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Bff/DashboardBffController.php)) serves widgets nested under `data.widgets` using `screen_title`, `layout_type`, and `widgets` keys.
  - Caching is done in `SharedPreferences` instead of SQLite (`config_table`).
- **Compliance Status**: **NON-COMPLIANT** (Mismatched schema contracts and storage strategies).

## 2. Universal Component Catalog Integration
- **Specified Catalog**: The app should support a unified design system.
- **Actual Implementation**:
  - All basic primitives (`Container`, `Row`, `Column`, `Text`, `Icon`, `Card`, `RemoteNode`) are registered in [SduiRegistryInitializer](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_registry_initializer.dart).
  - However, complex components like `BottomSheet`, `Chart`, `Checkbox`, `Dialog`, `Dropdown`, `FormField`, `Map`, `Switch`, and `Tabs` have existing implementations in `core/sdui/components` but were never registered.
- **Compliance Status**: **PARTIALLY COMPLIANT** (Key UI components are implemented but excluded from registry).

## 3. SQLite Caching & Offline Strategy
- **Specified Caching**: Caching of configuration layout is required to be saved in a local SQLite table (`config_table`).
- **Actual Implementation**:
  - The client's `publicDatabaseProvider` is hardcoded to return `null`, rendering it useless for local caching.
  - The local database classes (e.g. `PublicDatabase`, `tables`) exist in code but are never instantiated or active, causing caching to revert to static inline fallbacks or SharedPreferences.
- **Compliance Status**: **NON-COMPLIANT** (Database provider disabled, caching strategies fail).
