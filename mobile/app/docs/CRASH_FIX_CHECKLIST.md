# CRASH FIX CHECKLIST — Production Readiness Gate

---

## PRE-FLIGHT CHECKS (Before Deploying Any Fix)

- [ ] `flutter analyze` passes with 0 errors
- [ ] `flutter build apk --debug` succeeds
- [ ] All QAC tasks assigned and scheduled
- [ ] Emulator configured per recommendations (RAM 4GB+, GPU HW, API 34)

---

## QAC-001: Android Manifest Permissions

- [ ] `READ_MEDIA_IMAGES` added for Android 13+
- [ ] `READ_MEDIA_VIDEO` added for Android 13+
- [ ] `POST_NOTIFICATIONS` added for Android 13+
- [ ] `FOREGROUND_SERVICE` added
- [ ] `READ_EXTERNAL_STORAGE` added with `maxSdkVersion="32"`
- [ ] `WRITE_EXTERNAL_STORAGE` added with `maxSdkVersion="32"`
- [ ] `flutter build apk` succeeds

## QAC-002: FileProvider

- [ ] `res/xml/file_paths.xml` created with valid paths
- [ ] `<provider>` added to AndroidManifest.xml
- [ ] `authorities` matches `${applicationId}.fileprovider`
- [ ] Camera capture returns valid file path

## QAC-003: permission_handler

- [ ] `permission_handler` added to pubspec.yaml
- [ ] `flutter pub get` succeeds
- [ ] `import 'package:permission_handler/permission_handler.dart'` compiles

## QAC-004: Camera Permission

- [ ] `Permission.camera.request()` called before `ImagePicker`
- [ ] Granted flow: camera opens
- [ ] Denied flow: shows error message
- [ ] Permanently denied: shows settings dialog
- [ ] No crash on any permission path

## QAC-005: Gallery Permission

- [ ] `Permission.photos.request()` for Android 13+
- [ ] `Permission.storage.request()` for Android <13
- [ ] Granted flow: gallery opens
- [ ] Denied flow: shows error message
- [ ] No crash on any permission path

## QAC-006: Double Navigator.pop

- [ ] Submit report → success dialog
- [ ] Tap "Selesai" → dialog dismisses without crash
- [ ] Wizard returns to previous screen

## QAC-007: Kotlin Plugin

- [ ] `kotlin("android")` added to app/build.gradle.kts
- [ ] `flutter build apk` succeeds

## QAC-008: SDK Versions

- [ ] `compileSdk = 35`
- [ ] `minSdk = 23`
- [ ] `targetSdk = 34`
- [ ] `flutter build apk` succeeds

## QAC-009: Mock PIN Removal

- [ ] Hardcoded `123456` fallback removed
- [ ] Actual error displayed on API failure
- [ ] Error logged to appLogger

## QAC-010: Lifecycle Management

- [ ] `WidgetsBindingObserver` mixin added to `NuriskApp`
- [ ] `didChangeAppLifecycleState` implemented
- [ ] Background: timers paused
- [ ] Foreground: state restored
- [ ] Camera/GPS return: state preserved

## QAC-011: Map Dispose

- [ ] `mapController?.dispose()` in CopMapScreen.dispose()
- [ ] No memory leak when switching tabs
- [ ] No crash when map controller accessed

## QAC-012: Null Assertions

- [ ] `val!` replaced with null check in spatial_filter_bottom_sheet.dart
- [ ] All `!` on nullable values removed from filter sheet

## QAC-013: Timeline RangeError

- [ ] `substring(11, 16)` guarded by length check
- [ ] Null time data handled gracefully

## QAC-014: Timer Leak

- [ ] `WarningNotifier` uses `.autoDispose`
- [ ] `_pollingTimer?.cancel()` actually called on dispose
- [ ] Timer stops when warning widget removed

## QAC-015: Silent Catch

- [ ] `catch(e) { return false; }` removed
- [ ] Error propagated to UI
- [ ] Error logged with full stack trace

## QAC-016: Error Handling

- [ ] All datasource catch blocks preserve original exception
- [ ] Stack traces logged

## QAC-017: Runtime Logging

- [ ] Camera calls logged (start/end/error)
- [ ] GPS calls logged (start/end/error)
- [ ] Gallery calls logged (start/end/error)
- [ ] All plugin errors logged with stack trace

## QAC-018: State Restoration

- [ ] Wizard state saved to Riverpod before camera/gallery
- [ ] Wizard state restored on return
- [ ] Current step preserved
- [ ] All form data preserved

---

## INTEGRATION TEST CHECKLIST

- [ ] Camera: Open → grant permission → take photo → return → verify
- [ ] Camera: Open → deny permission → verify error
- [ ] Camera: Open → permanently denied → verify settings dialog
- [ ] Gallery: Open → grant permission → select image → return → verify
- [ ] Gallery: Open → deny permission → verify error
- [ ] GPS: Open → grant → get location → verify
- [ ] GPS: Open → deny → verify error
- [ ] GPS: Open → disabled → verify "Aktifkan GPS" message
- [ ] Report: Fill all steps → submit → success dialog → dismiss → verify
- [ ] Map: Open tab → switch away → return → verify state
- [ ] Map: Open → background → foreground → verify state
- [ ] Back button: Home → back → snackbar → back again → exit
- [ ] Back button: Map tab → back → Home tab
- [ ] Back button: Wizard → camera → back → wizard at same step
- [ ] Logout: Any screen → logout → public dashboard
- [ ] Deep link: `nurisk://incident/123` → incident screen
- [ ] Background: App → background (home button) → resume → verify
- [ ] Background: App → camera → home button → resume → verify

---

## PRODUCTION READINESS GATE

All items below must be ✅ before releasing:

### Critical (Blocking)
- [ ] No force-close on camera/gallery/GPS
- [ ] No crash on report submit
- [ ] No security bypass via mock PIN
- [ ] All required manifest permissions declared
- [ ] FileProvider configured

### High (Required)
- [ ] App survives background/foreground cycle
- [ ] Map tab switchable without crash
- [ ] No null assertion crashes in filter sheet
- [ ] Timeline does not crash on incomplete data

### Medium (Should Fix)
- [ ] Timer does not leak
- [ ] Governance errors visible to user
- [ ] Stack traces preserved in logs
- [ ] Plugin calls logged

### Performance
- [ ] 60 FPS on main screens (dashboard, map)
- [ ] No jank on tab switch
- [ ] Memory stable over 30-minute usage
- [ ] No frame drops during map rendering

### Security
- [ ] No hardcoded secrets/credentials
- [ ] Token stored securely
- [ ] Permission model follows least-privilege
- [ ] Camera/Gallery files stored in app cache only

---

## SIGN-OFF

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | | | |
| QA Tester | | | |
| Product Owner | | | |

---

## NOTES

- Build verified: `flutter build apk --debug` ✅ (2026-07-08)
- Previous navigation audit resolved: 11 anti-patterns fixed
- This audit: 18 tasks identified (5 CRITICAL, 8 HIGH, 5 MEDIUM)
