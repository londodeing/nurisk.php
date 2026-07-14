# SDUI REGISTRY AUDIT REPORT

## 1. Registered Components Mapping
The [SduiRegistryInitializer](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_registry_initializer.dart) registers a total of 14 component builders:

| SDUI Key | Flutter Component Class | Category | Status |
|----------|--------------------------|----------|--------|
| `HeaderBanner` | `SduiHeaderBanner` | Domain Widget | Registered |
| `SummaryCard` | `SduiSummaryCard` | Domain Widget | Registered |
| `ActionList` | `SduiActionList` | Domain Widget | Registered |
| `ProfileCard` | `SduiProfileCard` | Domain Widget | Registered |
| `DocumentQueue` | `SduiDocumentQueue` | Domain Widget | Registered |
| `Grid` | `SduiGrid` | Domain Widget | Registered |
| `Timeline` | `SduiTimeline` | Domain Widget | Registered |
| `Container` | `SduiContainer` | Primitive | Registered |
| `Row` | `SduiRow` | Primitive | Registered |
| `Column` | `SduiColumn` | Primitive | Registered |
| `Text` | `SduiText` | Primitive | Registered |
| `Icon` | `SduiIcon` | Primitive | Registered |
| `Card` | `SduiCard` | Primitive | Registered |
| `RemoteNode` | `SduiRemoteNode` | Primitive | Registered |

## 2. Duplicate and Key Integrity Checks
- **Duplicate Registration Keys**: None. All `.register()` calls specify unique keys.
- **Empty Keys**: None. No key is empty or null.
- **Null Builder Registration**: None. All builders map correctly to valid closure initializers returning `SduiComponent` subclasses.

## 3. Unregistered Existing Component Files
The following files exist in `core/sdui/components` but are **not** registered in `SduiRegistryInitializer`:
- `sdui_bottom_sheet.dart` (not registered, key `BottomSheet` is missing)
- `sdui_chart.dart` (not registered, key `Chart` is missing)
- `sdui_checkbox.dart` (not registered, key `Checkbox` is missing)
- `sdui_dialog.dart` (not registered, key `Dialog` is missing)
- `sdui_dropdown.dart` (not registered, key `Dropdown` is missing)
- `sdui_form_field.dart` (not registered, key `FormField` is missing)
- `sdui_map.dart` (not registered, key `Map` is missing)
- `sdui_switch.dart` (not registered, key `Switch` is missing)
- `sdui_tabs.dart` (not registered, key `Tabs` is missing)

## 4. Impact Analysis
If the BFF returns any of the unregistered components (e.g. `Chart` or `Map`), `SduiRegistry.instance.getBuilder(type)` will return `null`. `SduiRenderer` will then construct an `SduiUnknownComponent`, which logs the missing renderer to `RuntimeLogger` and displays a fallback warning box. It does not cause a crash/WSOD but degrades the UI usability.
