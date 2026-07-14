# R0.5 — Runtime Inventory

## Primitives Actually Produced by the Serializer

Source: exhaustive audit of all `Runtime::render()` calls in `app/Services/Sdui/Runtime/`.

| # | Kind (PHP) | Type (JSON, after `ucfirst()`) | Count | Flutter Renderer | Status |
|---|------------|-------------------------------|-------|-----------------|--------|
| 1 | `text` | `Text` | 41 | `SduiText` | ✅ |
| 2 | `row` | `Row` | 24 | `SduiRow` | ✅ |
| 3 | `container` | `Container` | 22 | `SduiContainer` | ✅ |
| 4 | `icon` | `Icon` | 14 | `SduiIcon` | ✅ |
| 5 | `column` | `Column` | 13 | `SduiColumn` | ✅ |
| 6 | `SizedBox` | `SizedBox` | 10 | `SduiSizedBox` | ✅ |
| 7 | `divider` | `Divider` | 7 | `SduiDivider` | ✅ |
| 8 | `badge` | `Badge` | 5 | `SduiBadge` | ✅ |
| 9 | `Expanded` | `Expanded` | 1 | `SduiExpanded` | ✅ |
| 10 | `avatar` | `Avatar` | 1 | ❌ not registered | ⛔ dead code only |

Additionally, the Serializer maps these domain types to wrapper primitives:

| Domain Type | Type (JSON) |
|-------------|-------------|
| `ScreenNode` | `ListView` (root) |
| `SectionNode` | `Column` (children container) |

All **9 active primitives** have registered Flutter renderers.

## Dormant Primitives (registered but never produced)

| Type | Registered? |
|------|-------------|
| `Card` | ✅ |
| `Flexible` | ✅ |
| `AspectRatio` | ✅ |
| `Grid` | ✅ |
| `Timeline` | ✅ |
| `BottomSheet` | ✅ |
| `Chart` | ✅ |
| `Checkbox` | ✅ |
| `Dialog` | ✅ |
| `Dropdown` | ✅ |
| `FormField` | ✅ |
| `Map` | ✅ |
| `Scene` | ✅ |
| `Switch` | ✅ |
| `Tabs` | ✅ |

These are safe to keep (forward-compatibility) but not needed by current Runtime.

## Dead Components (not in serializer output)

| Component | Located In | Status |
|-----------|-----------|--------|
| `ProfileCard` | `widget_factory.dart` + `_guestData()` | Dead code / legacy guest |
| `ActionList` | `widget_factory.dart` + `_guestData()` | Dead code / legacy guest |
| `ProfileComponent` (avatar kind) | `ProfileComponent.php` | Never called (zero imports) |
