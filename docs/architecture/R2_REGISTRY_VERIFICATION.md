# R2 — Registry Verification

## Primitive Matrix

| # | Primitive | Backend Usage | Flutter Renderer | Registry Test | Action Support |
|---|-----------|---------------|------------------|---------------|----------------|
| 1 | `Container` | ✅ 22 uses | `SduiContainer` | ✅ | ✅ |
| 2 | `Column` | ✅ 13+ uses | `SduiColumn` | ✅ | ✅ |
| 3 | `Row` | ✅ 24 uses | `SduiRow` | ✅ | ✅ |
| 4 | `Text` | ✅ 41 uses | `SduiText` | ✅ | ✅ |
| 5 | `Icon` | ✅ 14 uses | `SduiIcon` | ✅ | ✅ |
| 6 | `SizedBox` | ✅ 10 uses | `SduiSizedBox` | ❌ missing test | ✅ |
| 7 | `Divider` | ✅ 7 uses | `SduiDivider` | ❌ missing test | ✅ |
| 8 | `Badge` | ✅ 5 uses | `SduiBadge` | ❌ missing test | ✅ |
| 9 | `Expanded` | ✅ 1 use | `SduiExpanded` | ❌ missing test | ✅ |
| 10 | `ListView` | ✅ 1 use (ScreenNode root) | `SduiListView` | ❌ missing test | ✅ |

## Dormant Primitives (registered, not produced by current Runtime)

| # | Primitive | Flutter Renderer | Registry Test | 
|---|-----------|------------------|---------------|
| 11 | `Card` | `SduiCard` | ✅ |
| 12 | `Flexible` | `SduiFlexible` | ❌ |
| 13 | `AspectRatio` | `SduiAspectRatio` | ❌ |
| 14 | `Grid` | `SduiGrid` | ✅ |
| 15 | `Timeline` | `SduiTimeline` | ✅ |
| 16 | `BottomSheet` | `SduiBottomSheet` | ✅ |
| 17 | `Chart` | `SduiChart` | ✅ |
| 18 | `Checkbox` | `SduiCheckbox` | ✅ |
| 19 | `Dialog` | `SduiDialog` | ✅ |
| 20 | `Dropdown` | `SduiDropdown` | ✅ |
| 21 | `FormField` | `SduiFormField` | ✅ |
| 22 | `Map` | `SduiMap` | ✅ |
| 23 | `Scene` | `SduiScene` | ❌ |
| 24 | `Switch` | `SduiSwitch` | ✅ |
| 25 | `Tabs` | `SduiTabs` | ✅ |

## Findings

1. **All 10 active primitives have Flutter renderers.** No missing renderers.
2. **5 active primitives lack registry tests**: `SizedBox`, `Divider`, `Badge`, `Expanded`, `ListView`.
3. **All active primitives support actions** (they extend `SduiComponent` which receives `node.actions`).
4. **No duplicate registrations** exist — each type is registered exactly once.

## Action Items for R3

1. Add registry tests for `SizedBox`, `Divider`, `Badge`, `Expanded`, `ListView`.
2. Create a renderability test that validates the full serializer output maps to registered primitives.
