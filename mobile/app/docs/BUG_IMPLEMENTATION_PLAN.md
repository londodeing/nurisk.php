# BUG IMPLEMENTATION PLAN — Mobile Runtime Fix

---

## ATOMIC TASKS

Each task is a complete, deployable unit of work with acceptance criteria.

---

### QAC-001: Add Missing Android Manifest Permissions

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 30 minutes |
| Files | `android/app/src/main/AndroidManifest.xml` |

**Root Cause**: Android 13+ requires `READ_MEDIA_IMAGES` for gallery access, `POST_NOTIFICATIONS` for notification permission. These are missing.

**Changes**:
1. Add `READ_MEDIA_IMAGES` permission (Android 13+)
2. Add `READ_MEDIA_VIDEO` permission (Android 13+)
3. Add `POST_NOTIFICATIONS` permission (Android 13+)
4. Add `FOREGROUND_SERVICE` permission
5. Add `READ_EXTERNAL_STORAGE` with `maxSdkVersion="32"` (legacy)
6. Add `WRITE_EXTERNAL_STORAGE` with `maxSdkVersion="32"` (legacy)

**Acceptance**: `flutter build apk` succeeds. Permissions appear in app settings.

---

### QAC-002: Add FileProvider for Camera

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 30 minutes |
| Files | `android/app/src/main/AndroidManifest.xml`, `android/app/src/main/res/xml/file_paths.xml` |

**Root Cause**: `image_picker` camera feature requires `FileProvider` for reliable file URI sharing.

**Changes**:
1. Create `res/xml/file_paths.xml` with cache-path, external-cache-path, files-path
2. Add `<provider>` element to AndroidManifest.xml

**Acceptance**: Camera capture returns valid file path.

---

### QAC-003: Add permission_handler Dependency

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 15 minutes |
| Files | `pubspec.yaml` |

**Root Cause**: No unified permission management library. Camera/gallery called without permission checks.

**Changes**:
1. Add `permission_handler: ^11.3.0` to pubspec.yaml
2. Run `flutter pub get`

**Acceptance**: `permission_handler` available for import.

---

### QAC-004: Add Camera Permission Check Before ImagePicker

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 2 hours |
| Files | `lib/features/public/report/presentation/screens/report_wizard_screen.dart` |

**Root Cause**: `ImagePicker().pickImage(source: ImageSource.camera)` called without checking `Permission.camera` first.

**Changes**:
1. Create reusable `PermissionService` or use `permission_handler` directly
2. Wrap `_pickFoto()` with:
   ```dart
   final status = await Permission.camera.request();
   if (status.isGranted) {
     // proceed with ImagePicker
   } else if (status.isPermanentlyDenied) {
     // show settings dialog
   } else {
     // show error message
   }
   ```

**Acceptance**: Camera opens only after permission granted. Denied → error message. Permanently denied → settings dialog.

---

### QAC-005: Add Gallery Permission Check Before ImagePicker

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 1 hour |
| Files | `lib/features/public/report/presentation/screens/report_wizard_screen.dart` |

**Root Cause**: `ImagePicker().pickImage(source: ImageSource.gallery)` called without checking storage/gallery permission.

**Changes**:
1. Determine correct permission based on Android version:
   - Android 13+: `Permission.photos`
   - Android <13: `Permission.storage`
2. Wrap `_pickFotoGallery()` with permission check

**Acceptance**: Gallery opens only after permission granted. Error message on denied.

---

### QAC-006: Fix Double Navigator.pop After Submit

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 15 minutes |
| Files | `lib/features/public/report/presentation/screens/report_wizard_screen.dart` |

**Root Cause**: `Navigator.pop(context)` called twice on dialog's context. Second pop uses invalid context.

**Changes**:
Replace:
```dart
onPressed: () {
  Navigator.pop(context);
  Navigator.pop(context);
},
```
With:
```dart
onPressed: () {
  Navigator.of(context, rootNavigator: true).pop(); // pop dialog
  // wizard will be popped by GoRouter back
},
```

**Acceptance**: Submit report → success dialog → tap "Selesai" → dialog dismisses → wizard returns to previous screen without crash.

---

### QAC-007: Add Kotlin Plugin to App Build

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 15 minutes |
| Files | `android/app/build.gradle.kts` |

**Root Cause**: Kotlin plugin not applied in app module. Some plugins may fail.

**Changes**:
```kotlin
plugins {
    id("com.android.application")
    id("dev.flutter.flutter-gradle-plugin")
    kotlin("android")
}
```

**Acceptance**: `flutter build apk` succeeds.

---

### QAC-008: Set Explicit Android SDK Versions

| Field | Value |
|-------|-------|
| Priority | 🟠 HIGH |
| Effort | 15 minutes |
| Files | `android/app/build.gradle.kts` |

**Root Cause**: SDK versions use `flutter.compileSdkVersion` etc., which are dynamic and fragile.

**Changes**:
```kotlin
defaultConfig {
    applicationId = "com.example.nurisk_mobile"
    compileSdk = 35
    minSdk = 23
    targetSdk = 34
    ...
}
```

Wait — `compileSdk` is outside `defaultConfig`. Let me correct:
```kotlin
android {
    compileSdk = 35
    ...
    defaultConfig {
        minSdk = 23
        targetSdk = 34
        ...
    }
}
```

**Acceptance**: `flutter build apk` succeeds.

---

### QAC-009: Remove Mock PIN Fallback in Production

| Field | Value |
|-------|-------|
| Priority | 🔴 CRITICAL |
| Effort | 15 minutes |
| Files | `lib/features/auth/presentation/widgets/pin_verification_dialog.dart` |

**Root Cause**: `catch(e) { ... if (_pinCtrl.text == '123456') { ... } }` — production code with hardcoded PIN bypass.

**Changes**: Remove the mock fallback. Show actual error to user. Log the error.

**Acceptance**: PIN verification does not accept `123456` on API failure. Shows error message instead.

---

### QAC-010: Add WidgetsBindingObserver to App

| Field | Value |
|-------|-------|
| Priority | 🟠 HIGH |
| Effort | 2 hours |
| Files | `lib/main.dart`, `lib/core/lifecycle/app_lifecycle.dart` |

**Root Cause**: No lifecycle management. App doesn't know when it goes to background.

**Changes**:
1. Create `AppLifecycleObserver` class
2. Implement `didChangeAppLifecycleState`
3. Notify Riverpod providers on state changes
4. Pause/resume timers, GPS, map rendering

**Acceptance**: App reacts to background/foreground transitions. No crash on return from camera.

---

### QAC-011: Add MapLibreMapController Dispose

| Field | Value |
|-------|-------|
| Priority | 🟠 HIGH |
| Effort | 15 minutes |
| Files | `lib/features/public/map/presentation/screens/cop_map_screen.dart` |

**Root Cause**: `mapController` is nullable but never disposed.

**Changes**:
```dart
@override
void dispose() {
  mapController?.dispose();
  super.dispose();
}
```

**Acceptance**: Map tab can be opened/closed multiple times without memory leak.

---

### QAC-012: Fix Null Assertions on DropdownButtonFormField

| Field | Value |
|-------|-------|
| Priority | 🟠 HIGH |
| Effort | 15 minutes |
| Files | `lib/features/map/presentation/widgets/spatial_filter_bottom_sheet.dart` |

**Root Cause**: `val!` at lines 62, 72 — `onChanged` callback passes nullable value.

**Changes**: Replace `val!` with proper null check:
```dart
if (val != null) setState(() => _severity = val);
```

**Acceptance**: Filter bottom sheet does not crash on null selection.

---

### QAC-013: Fix Governance Timeline Substring RangeError

| Field | Value |
|-------|-------|
| Priority | 🟠 HIGH |
| Effort | 15 minutes |
| Files | `lib/features/governance/presentation/widgets/governance_timeline_widget.dart` |

**Root Cause**: `event['time'].toString().substring(11, 16)` — RangeError if string < 16 chars.

**Changes**: Add length check before substring.

**Acceptance**: Timeline renders without crash even with null/incomplete time data.

---

### QAC-014: Fix WarningNotifier Timer Leak

| Field | Value |
|-------|-------|
| Priority | 🟡 MEDIUM |
| Effort | 15 minutes |
| Files | `lib/features/public/warning/presentation/notifiers/warning_provider.dart` |

**Root Cause**: Provider not `autoDispose`; `onDispose` never called; timer runs forever.

**Changes**:
```dart
final warningProvider = NotifierProvider.autoDispose<WarningNotifier, WarningState>(...);
```

**Acceptance**: Timer stops when warning widget is removed from tree.

---

### QAC-015: Fix Silent Catch Exceptions in Governance

| Field | Value |
|-------|-------|
| Priority | 🟡 MEDIUM |
| Effort | 15 minutes |
| Files | `lib/features/governance/presentation/notifiers/governance_provider.dart` |

**Root Cause**: `catch(e) { return false; }` swallows exception.

**Changes**: Log exception, show user feedback via state.

**Acceptance**: Governance decision errors are visible to user and logged.

---

### QAC-016: Replace Empty Catch Blocks with Proper Error Handling

| Field | Value |
|-------|-------|
| Priority | 🟡 MEDIUM |
| Effort | 30 minutes |
| Files | 5 datasource files (weather, warning, config, incident, dashboard_kpi) |

**Root Cause**: `throw Exception('Network error: $e')` — loses original stack trace.

**Changes**: Use `ErrorHandler.throwWrapped(error, stack)` or similar pattern to preserve original error.

**Acceptance**: Stack traces preserved in logs.

---

### QAC-017: Add Runtime Logging for Native Plugins

| Field | Value |
|-------|-------|
| Priority | 🟡 MEDIUM |
| Effort | 1 hour |
| Files | Multiple — all plugin call sites |

**Root Cause**: No logging around camera, GPS, gallery calls. Crashes invisible in logs.

**Changes**: Add `appLogger.i/e('Camera: ...')` at all plugin interaction points.

**Acceptance**: All plugin calls logged with start/end/error states.

---

### QAC-018: Add State Restoration for Report Wizard

| Field | Value |
|-------|-------|
| Priority | 🟡 MEDIUM |
| Effort | 2 hours |
| Files | `lib/features/public/report/presentation/screens/report_wizard_screen.dart`, `lib/features/public/report/presentation/notifiers/laporan_provider.dart` |

**Root Cause**: Wizard state lost when app is backgrounded or camera opens.

**Changes**: Save wizard state to Riverpod provider before opening camera. Restore on return.

**Acceptance**: Camera → take photo → back → wizard at same step with all data intact.

---

## IMPLEMENTATION ORDER

```
Sprint QAC-01 (Week 1): CRASH-STOPPING (3 days)
├── Day 1: QAC-001, QAC-002, QAC-003 (Manifest + FileProvider + permission_handler)
├── Day 2: QAC-004, QAC-005, QAC-006 (Camera permission, Gallery permission, Double pop)
└── Day 3: QAC-007, QAC-008, QAC-009 (Kotlin plugin, SDK versions, Mock PIN removal)

Sprint QAC-01 (Week 2): STABILITY (3 days)
├── Day 1: QAC-010, QAC-011 (WidgetsBindingObserver, Map dispose)
├── Day 2: QAC-012, QAC-013, QAC-014 (Null assertions, Timeline, Timer leak)
└── Day 3: QAC-015, QAC-016, QAC-017, QAC-018 (Error handling, Logging, State restoration)
```

---

## VERIFICATION CHECKLIST

After each QAC task, verify:

| Task | Verification |
|------|-------------|
| QAC-001 | Check AndroidManifest.xml has all required permissions |
| QAC-002 | Camera returns valid image file path |
| QAC-003 | `flutter pub get` succeeds; `Permission.camera` importable |
| QAC-004 | Camera denied → error; Camera granted → opens |
| QAC-005 | Gallery denied → error; Gallery granted → opens |
| QAC-006 | Submit report → success dialog → dismiss → no crash |
| QAC-007 | `flutter build apk` succeeds |
| QAC-008 | Build uses correct SDK versions |
| QAC-009 | PIN `123456` rejected on API failure |
| QAC-010 | App lifecycle events logged; timers pause on background |
| QAC-011 | Map tab switchable multiple times; no memory growth |
| QAC-012 | Filter sheet null selection → no crash |
| QAC-013 | Incomplete time data → no crash |
| QAC-014 | Warning widget removed → timer stops |
| QAC-015 | Governance error visible and logged |
| QAC-016 | Stack traces preserved in error logs |
| QAC-017 | Plugin calls appear in logs |
| QAC-018 | Camera → back → wizard state intact |
