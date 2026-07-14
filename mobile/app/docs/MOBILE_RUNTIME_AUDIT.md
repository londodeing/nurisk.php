# MOBILE RUNTIME AUDIT — NURISK Flutter

Audit Date: 2026-07-08  
Audit Scope: End-to-End Mobile Runtime Integration  
Files Audited: 106 Dart files + 10 Android config files  

---

## 1. LANDSCAPE SUMMARY

| Layer | Status | Critical Issues |
|-------|--------|----------------|
| Android Manifest | ⚠️ INCOMPLETE | Missing camera/gallery permissions for Android 13+; missing FileProvider |
| Gradle / AGP | ✅ MODERN | AGP 9.0.1, Kotlin 2.3.20, Gradle 9.1, Java 17 |
| Kotlin Plugin | ❌ MISSING | Kotlin plugin not declared in `app/build.gradle.kts` |
| Plugin Versions | ⚠️ COMPATIBLE | All plugins on compatible versions; maplibre_gl applies KGP (warning) |
| Flutter Embedding | ✅ V2 | Correct `flutterEmbedding` meta-data |
| Runtime Permissions | ❌ BROKEN | Camera/gallery calls without permission request |
| Lifecycle | ❌ MISSING | No `WidgetsBindingObserver` anywhere |
| Riverpod | ⚠️ LEAK | WarningNotifier timer never disposed; no autoDispose usage |
| Navigation | ✅ FIXED | Previous audit anti-patterns resolved |
| Error Handling | ⚠️ WEAK | 10 empty/swallowing catch blocks; mock fallback in production |
| Null Safety | ⚠️ MODERATE | 10 unconditional `!` assertions, double Navigator.pop |
| MapLibre | ⚠️ UNTESTED | No lifecycle handling, no dispose for mapController |
| Offline | ❌ UNTESTED | No offline strategy for native features |
| State Restoration | ❌ ABSENT | No RestorationMixin, no PageStorageKey |

---

## 2. ROOT CAUSE ANALYSIS

### Root Cause #1: No Centralized Permission Service

**Symptoms**: Camera → Force Close, Gallery → Force Close, GPS → Force Close

**Root Cause**:
- `ImagePicker().pickImage(source: ImageSource.camera)` called at `report_wizard_screen.dart:107` **without** requesting `CAMERA` permission first
- `ImagePicker().pickImage(source: ImageSource.gallery)` called at line 123 **without** requesting `READ_MEDIA_IMAGES` (Android 13+) permission
- On Android 13+, `READ_EXTERNAL_STORAGE` is deprecated; `READ_MEDIA_IMAGES` is required but **not declared in AndroidManifest.xml**
- No `permission_handler` plugin in pubspec.yaml — no unified permission management

**Impact**: CRITICAL — 100% crash rate on Android 13+ devices when camera/gallery accessed

### Root Cause #2: No Lifecycle Management

**Symptoms**: App restart after returning from native plugin, camera/gps state lost

**Root Cause**:
- Zero `WidgetsBindingObserver` implementations in entire codebase
- When camera intent launches, Android may kill/recreate the Activity
- No state preservation across Activity recreation
- `MapLibreMapController` not disposed/recreated on lifecycle change

**Impact**: HIGH — data loss, app restart, map state corruption

### Root Cause #3: Riverpod Provider Disposal Mismatch

**Symptoms**: Background polling continues, memory leak

**Root Cause**:
- `WarningNotifier` registers `ref.onDispose(() { _pollingTimer?.cancel(); })` at line 17
- But the provider is NOT `autoDispose` — so `onDispose` is **never called**
- `_pollingTimer` (30-second periodic) runs for entire app lifetime
- No provider uses `.autoDispose` anywhere in the project

**Impact**: MEDIUM — battery drain, memory leak

### Root Cause #4: Double Navigator.pop After Dialog

**Symptoms**: Crash after submitting report

**Root Cause**: `report_wizard_screen.dart:296-297`
```dart
Navigator.pop(context); // pops AlertDialog
Navigator.pop(context); // CRASH — context is now invalid
```
After the dialog is popped, the same `context` (which belonged to the dialog) is used again. Should use `Navigator.of(context, rootNavigator: true)` pattern.

**Impact**: HIGH — crash after successful report submission

### Root Cause #5: Production Code with Mock Fallbacks

**Symptoms**: Silent auth bypass, unpredictable behavior

**Root Cause**: `pin_verification_dialog.dart:49` catches exception and falls back to hardcoded PIN `123456`. This mock code was left in production.

**Impact**: CRITICAL — security vulnerability + silent failures

### Root Cause #6: Missing Android Manifest Permissions for Android 13+

**Symptoms**: Permission denied/crash on modern Android

**Root Cause**: AndroidManifest.xml declares:
- `CAMERA` ✅
- `ACCESS_FINE_LOCATION` ✅  
- `ACCESS_COARSE_LOCATION` ✅
- `INTERNET` ✅

**MISSING** (Android 13+):
- `READ_MEDIA_IMAGES` — required for gallery access on Android 13+
- `READ_MEDIA_VIDEO` — required for video picker
- `POST_NOTIFICATIONS` — required for notification permission on Android 13+
- `ACCESS_BACKGROUND_LOCATION` — if background location is needed

**Impact**: HIGH — crashes on Android 13+ for gallery/media operations

### Root Cause #7: Missing FileProvider for Camera

**Symptoms**: Camera captures but returns null/crash

**Root Cause**: `image_picker` on Android requires a `FileProvider` declaration in AndroidManifest.xml for camera capture to work reliably. The project has NO FileProvider.

**Impact**: HIGH — camera captures may fail on some devices

---

## 3. AUDIT CHECKLIST COMPLETION

| Phase | Description | Files Audited | Findings |
|-------|-------------|---------------|----------|
| 1 | Project Config | 7 | Namespace mismatch, missing Kotlin plugin |
| 2 | Plugin Compatibility | 1 (pubspec) | maplibre_gl KGP warning, no permission_handler |
| 3 | Android Manifest | 1 | Missing 4+ permissions, no FileProvider |
| 4 | Runtime Permission | 2 Dart files | Camera/gallery without permission check |
| 5 | Plugin Lifecycle | 10 Dart files | No WidgetsBindingObserver anywhere |
| 6 | Riverpod | 12 provider files | Timer leak, no autoDispose |
| 7 | Navigation | 7 Dart files | Previous issues fixed, navigation OK |
| 8 | Report Wizard | 1 file (731 lines) | 6 issues found (3 HIGH, 2 MEDIUM, 1 LOW) |
| 9 | Camera | 2 files | No permission, no FileProvider |
| 10 | GPS | 2 files | Permission flow OK |
| 11 | Map | 1 file (199 lines) | No lifecycle, no dispose, mapController! risk |
| 12 | Emulator | N/A | See section 4 |
| 13-20 | Crash/Error/Logging | 106 files | 10+ swallowing catch blocks |

---

## 4. EMULATOR CONFIGURATION RECOMMENDATIONS

| Setting | Recommended Value | Notes |
|---------|-------------------|-------|
| RAM | 4GB+ | 2GB causes OOM with MapLibre |
| Storage | 2GB+ | Camera/Gallery operations need space |
| ABI | x86_64 | Fastest for development |
| Android Version | API 34 (Android 14) | Test on latest; also test API 33 |
| GPU | Hardware GLES 3.0 | Required for MapLibre rendering |
| Camera | VirtualSceneFront | Back camera often not available |
| GPS | GPS support on | For location testing |
| Snapshot | Enable | Faster subsequent launches |

---

## 5. COMPLETE FINDINGS MATRIX

### CRITICAL (5 findings)

| ID | File | Line | Issue | Root Cause |
|----|------|------|-------|------------|
| C-01 | `report_wizard_screen.dart` | 107 | Camera crash | No CAMERA permission check before ImagePicker |
| C-02 | `report_wizard_screen.dart` | 123 | Gallery crash | No READ_MEDIA_IMAGES permission check on Android 13+ |
| C-03 | `pin_verification_dialog.dart` | 49 | Security vulnerability | Mock fallback to PIN `123456` in production |
| C-04 | `AndroidManifest.xml` | 1-50 | Missing permissions | No READ_MEDIA_IMAGES, POST_NOTIFICATIONS, background location |
| C-05 | `AndroidManifest.xml` | 1-50 | Missing FileProvider | Camera FileProvider not declared |

### HIGH (6 findings)

| ID | File | Line | Issue | Root Cause |
|----|------|------|-------|------------|
| H-01 | `report_wizard_screen.dart` | 296-297 | Double Navigator.pop crash | Context invalid after dialog pop |
| H-02 | All files | N/A | No lifecycle handling | No WidgetsBindingObserver implemented |
| H-03 | `warning_provider.dart` | 17 | Timer never disposed | Provider not autoDispose; onDispose never called |
| H-04 | `app/build.gradle.kts` | 1-5 | Missing Kotlin plugin | `org.jetbrains.kotlin.android` not applied in app module |
| H-05 | `cop_map_screen.dart` | 63 | mapController! potential crash | Null assertion without re-check |
| H-06 | `spatial_filter_bottom_sheet.dart` | 62,72 | val! unconditional null assertion | onChanged can pass null |

### MEDIUM (5 findings)

| ID | File | Line | Issue | Root Cause |
|----|------|------|-------|------------|
| M-01 | `governance_provider.dart` | 105-107 | Silent catch | `catch(e) { return false; }` swallows exception |
| M-02 | `governance_timeline_widget.dart` | 68 | Substring RangeError | `.substring(11, 16)` on short string |
| M-03 | `report_wizard_screen.dart` | 267 | _fotoFile! indirect null | Guarded only by prior validation step |
| M-04 | `incident_provider.dart` | 69,73 | state.value! race | Mutable state could change between check and access |
| M-05 | `layer_control_bottom_sheet.dart` | 47-52 | Mock fallback in production | `// Mock Fallback` comment, no real error handling |

### LOW (4 findings)

| ID | File | Line | Issue | Root Cause |
|----|------|------|-------|------------|
| L-01 | `weather_remote_datasource.dart` | 32-34 | Lost stack trace | `throw Exception('Network error: $e')` |
| L-02 | `warning_remote_datasource.dart` | 26-28 | Lost stack trace | Same pattern |
| L-03 | `config_remote_datasource.dart` | 25-27 | Lost stack trace | Same pattern |
| L-04 | `incident_remote_datasource.dart` | 27-29 | Lost stack trace | Same pattern |

---

## 6. VERIFICATION STEPS

Each fix must be verified:

1. **Camera**: Open camera → take photo → return to wizard → data intact → submit
2. **Gallery**: Open gallery → select image → return to wizard → data intact → submit
3. **GPS**: Open wizard location step → get location → retry if denied → success
4. **Report**: Complete all steps → submit → success dialog → dismiss → wizard exits
5. **Map**: Open map tab → switch tabs → return → map state preserved
6. **Background**: App → background → camera → return → state preserved
7. **Notifications**: Request → grant → deny → verify no crash on all paths
8. **Logout**: All screens → logout → redirect to public dashboard
9. **Back**: All tabs → back → decision tree followed correctly
10. **Permission denied**: All permission requests → deny → app does not crash
