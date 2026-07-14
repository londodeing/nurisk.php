# CRASH ANALYSIS — NURISK Mobile

---

## CRASH REGISTRY

| ID | Priority | Symptom | Frequency | Root Cause | File:Line |
|----|----------|---------|-----------|------------|-----------|
| CR-01 | 🔴 CRITICAL | Camera → Force Close | Always | `ImagePicker().pickImage(source: ImageSource.camera)` without CAMERA permission check | `report_wizard_screen.dart:107` |
| CR-02 | 🔴 CRITICAL | Gallery → Force Close (Android 13+) | Always | `ImagePicker().pickImage(source: ImageSource.gallery)` without `READ_MEDIA_IMAGES` permission | `report_wizard_screen.dart:123` |
| CR-03 | 🔴 CRITICAL | Report Submit → Crash | Always | `Navigator.pop(context)` twice — second pop uses invalid dialog context | `report_wizard_screen.dart:296-297` |
| CR-04 | 🔴 CRITICAL | Permission denied → no crash but silent failure | Always | No `READ_MEDIA_IMAGES` in AndroidManifest.xml for Android 13+ | `AndroidManifest.xml` |
| CR-05 | 🔴 CRITICAL | PIN verification → auth bypass | In catch block | `pin_verification_dialog.dart:49` falls back to hardcoded `123456` on API error | `pin_verification_dialog.dart:49` |
| CR-06 | 🟠 HIGH | App restart after camera/GPS | Intermittent | No `WidgetsBindingObserver`; Activity may recreate and lose state | All screens |
| CR-07 | 🟠 HIGH | Map → possible crash on controller access | Race | `mapController!` at `cop_map_screen.dart:63` — nullable without lifecycle guard | `cop_map_screen.dart:63` |
| CR-08 | 🟠 HIGH | Filter Bottom Sheet → crash on null | Conditional | `val!` at `spatial_filter_bottom_sheet.dart:62,72` — `onChanged` can pass null | `spatial_filter_bottom_sheet.dart:62,72` |
| CR-09 | 🟠 HIGH | Governance timeline → RangeError | When time string < 16 chars | `.substring(11, 16)` on potentially short/null string | `governance_timeline_widget.dart:68` |
| CR-10 | 🟡 MEDIUM | Warning polling → battery drain | Continuous | Timer.periodic never disposed — provider not autoDispose | `warning_provider.dart:17` |
| CR-11 | 🟡 MEDIUM | Governance decision → silent failure | On network error | `catch(e) { return false; }` — exception swallowed with no feedback | `governance_provider.dart:105-107` |
| CR-12 | 🟢 LOW | Network errors → lost stack traces | On network error | `throw Exception('Network error: $e')` in 5 datasources loses original stack | Multiple datasources |

---

## CRASH PRIORITIZATION

### 🔴 CRITICAL (Must Fix Before Any Feature Work)

```
CR-01 Camera Force Close
CR-02 Gallery Force Close  
CR-03 Submit Report Crash
CR-04 Missing Manifest Permissions
CR-05 PIN Auth Bypass (Security)
```

**Impact**: App crashes on core user flows. Blocks all testing.

### 🟠 HIGH (Must Fix Before Production)

```
CR-06 App Restart After Native Plugin
CR-07 Map Controller Crash
CR-08 Filter Sheet Crash
CR-09 Timeline RangeError
```

**Impact**: Unreliable UX, data loss, crashes on specific inputs.

### 🟡 MEDIUM (Fix After HIGH)

```
CR-10 Timer Battery Drain
CR-11 Silent Decision Failure
```

**Impact**: Resource leak, silent bugs.

### 🟢 LOW (Fix During Normal Maintenance)

```
CR-12 Lost Stack Traces
```

**Impact**: Makes debugging harder.

---

## CRASH CHAIN ANALYSIS

### Chain 1: Camera Flow

```
User taps "Ambil Foto"
  → _pickFoto() called [report_wizard_screen.dart:106]
    → ImagePicker().pickImage(source: ImageSource.camera) [line 107]
      ❌ NO CAMERA PERMISSION CHECK → PlatformException / SecurityException
      ❌ NO FileProvider in AndroidManifest → capture fails
      → Crash / Force Close
```

### Chain 2: Gallery Flow

```
User taps "Dari Galeri"
  → _pickFotoGallery() called [report_wizard_screen.dart:122]
    → ImagePicker().pickImage(source: ImageSource.gallery) [line 123]
      ❌ NO READ_MEDIA_IMAGES permission check (Android 13+)
      ❌ NO READ_EXTERNAL_STORAGE in manifest for older Android
      → SecurityException → Crash
```

### Chain 3: Report Submit Flow

```
User taps "Kirim Laporan" → success
  → showDialog (AlertDialog) [line 283]
    → User taps "Selesai"
      → Navigator.pop(context) [line 296] → pops dialog
      → Navigator.pop(context) [line 297] → CRASH
        → "Navigator operation requested with a context that does not include a Navigator."
```

### Chain 4: App Background / Foreground

```
App is running → User opens Camera/GPS
  → Android may destroy Activity (onStop → onDestroy)
    ❌ No onSaveInstanceState → state lost
    ❌ No WidgetsBindingObserver → no didChangeAppLifecycleState
    → Activity recreates → app restart
      → User sees splash screen instead of previous state
```

### Chain 5: Map Tab

```
User opens Map tab → MapLibreMap creates controller
  → User switches tabs → request to backend (public/map/config)
    ❌ mapController not disposed on tab hide
    → User returns to Map tab
      → mapController! used [line 63] → may be stale/null → CRASH
```

---

## CRASH FIX EFFORT ESTIMATE

| ID | Fix Complexity | Files to Change | Estimated Effort |
|----|---------------|-----------------|------------------|
| CR-01 | Medium | 2 | 2-3 hours |
| CR-02 | Medium | 2 | 1-2 hours |
| CR-03 | Low | 1 | 15 minutes |
| CR-04 | Low | 1 | 30 minutes |
| CR-05 | Low | 1 | 15 minutes |
| CR-06 | High | 5+ | 4-6 hours |
| CR-07 | Low | 1 | 30 minutes |
| CR-08 | Low | 1 | 15 minutes |
| CR-09 | Low | 1 | 15 minutes |
| CR-10 | Low | 1 | 15 minutes |
| CR-11 | Low | 1 | 15 minutes |
| CR-12 | Low | 5 | 30 minutes |

**Total estimated effort: ~12-16 hours**
