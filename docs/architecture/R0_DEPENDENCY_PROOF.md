# R0 — Dependency Proof

## Files Under Investigation

| File | Path | Status |
|------|------|--------|
| `widget_factory.dart` | `lib/core/registry/widget_factory.dart` | ❌ Dead code |
| `widget_registry.dart` | `lib/core/registry/widget_registry.dart` | ❌ Dead code |

## Import Analysis

### `widget_factory.dart`

```
grep -rn "widget_factory" lib/ → No matches
grep -rn "WidgetFactory" lib/ → Only self-reference (line 6: class WidgetFactory)
```

**Verdict: Zero imports. Zero references. Entirely dead.**

### `widget_registry.dart`

```
grep -rn "widget_registry" lib/ → No matches  
grep -rn "WidgetRegistry" lib/ → Only self-reference (line 12: class WidgetRegistry)
```

**Verdict: Zero imports. Zero references. Entirely dead.**

## Type String Analysis

### `ProfileCard`

| Location | File | Active? |
|----------|------|---------|
| `case 'ProfileCard':` | `widget_factory.dart:17` | ❌ Dead code file |
| `static _buildProfileCard()` | `widget_factory.dart:72` | ❌ Dead code file |
| `type: 'ProfileCard',` | `account_home_provider.dart:23` | ⚠️ Legacy guest data only |

### `ActionList`

| Location | File | Active? |
|----------|------|---------|
| `case 'ActionList':` | `widget_factory.dart:19` | ❌ Dead code file |
| `static _buildActionList()` | `widget_factory.dart:101` | ❌ Dead code file |
| `type: 'ActionList',` | `account_home_provider.dart:35` | ⚠️ Legacy guest data only |

## Active Dependency Graph

```
SduiRenderer
    ↓ uses
SduiRegistry  (lib/core/sdui/sdui_registry.dart — 4 imports)
    ↓ registers
SduiRegistryInitializer  (lib/core/sdui/sdui_registry_initializer.dart)
    ↓ called from
main.dart

No path connects to WidgetFactory or WidgetRegistry.
```

## Conclusion

Both files are safe to delete after:
1. R1 rewrites `_guestData()` to use primitives (eliminating `ProfileCard`/`ActionList` string references)
2. R3 renderability test passes
