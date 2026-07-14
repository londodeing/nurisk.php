# PLUGIN COMPATIBILITY MATRIX — NURISK

---

## ENVIRONMENT

| Component | Version | Status |
|-----------|---------|--------|
| Flutter SDK | (from local.properties) | ✅ |
| Dart SDK | ^3.12.2 | ✅ |
| Android Gradle Plugin | 9.0.1 | ✅ Latest |
| Kotlin | 2.3.20 | ✅ Latest |
| Gradle | 9.1.0 | ✅ Latest |
| Java | 17 (sourceCompatibility) | ✅ |
| compileSdk | Flutter default | ⚠️ Should set explicitly |
| minSdk | Flutter default | ⚠️ Should set explicitly |
| targetSdk | Flutter default | ⚠️ Should set explicitly |

---

## PLUGIN MATRIX

| Plugin | Version | Min SDK | Notes | Status |
|--------|---------|---------|-------|--------|
| `flutter_riverpod` | ^3.3.2 | — | Latest stable | ✅ |
| `go_router` | ^17.3.0 | — | Latest stable | ✅ |
| `dio` | ^5.4.3 | — | Latest stable | ✅ |
| `cached_network_image` | ^3.3.1 | — | Latest stable | ✅ |
| `drift` | ^2.18.0 | — | SQLite ORM | ✅ |
| `sqlite3_flutter_libs` | ^0.6.0+eol | — | EOL version — consider upgrading | ⚠️ |
| `flutter_dotenv` | ^6.0.1 | — | Latest stable | ✅ |
| `maplibre_gl` | ^0.26.2 | API 21+ | Applies KGP (build warning) | ⚠️ |
| `http` | ^1.2.1 | — | Redundant with dio; remove? | ⚠️ |
| `shared_preferences` | ^2.3.4 | — | Latest stable | ✅ |
| `path_provider` | ^2.1.5 | — | Latest stable | ✅ |
| `geolocator` | ^14.0.3 | API 21+ | Latest stable | ✅ |
| `image_picker` | ^1.1.2 | API 21+ | Latest stable; needs FileProvider on Android | ✅ |
| `cupertino_icons` | ^1.0.8 | — | Latest stable | ✅ |
| `flutter_lints` | ^6.0.0 | — | Dev dependency | ✅ |
| `riverpod_generator` | ^4.0.4 | — | Dev dependency | ✅ |
| `build_runner` | ^2.4.9 | — | Dev dependency | ✅ |
| `drift_dev` | ^2.18.0 | — | Dev dependency | ✅ |

---

## MISSING DEPENDENCIES

| Plugin | Purpose | Priority | Reason |
|--------|---------|----------|--------|
| `permission_handler` | Unified runtime permission management | 🔴 CRITICAL | Current code has NO permission management for camera/gallery |
| `flutter_local_notifications` | Push notification display | 🟡 MEDIUM | Required for notification channels on Android 13+ |
| `connectivity_plus` | Network status monitoring | 🟡 MEDIUM | For offline mode detection |

---

## ANDROID SDK VERSION ANALYSIS

| Setting | Current | Recommended | Reason |
|---------|---------|-------------|--------|
| `compileSdk` | `flutter.compileSdkVersion` | `35` (explicit) | Flutter default may lag; Android 15 is API 35 |
| `minSdk` | `flutter.minSdkVersion` | `23` (explicit) | maplibre_gl requires API 21+; API 23 ensures runtime permissions work |
| `targetSdk` | `flutter.targetSdkVersion` | `34` (explicit) | Match latest production Android version |

**Current settings rely on `flutter.compileSdkVersion` / `flutter.minSdkVersion` / `flutter.targetSdkVersion` which are dynamically determined by the Flutter Gradle plugin. This is fragile. Set explicit values.**

---

## KOTLIN / GRADLE ISSUES

### Issue 1: Kotlin Plugin Missing in `app/build.gradle.kts`

**Current**:
```kotlin
plugins {
    id("com.android.application")
    id("dev.flutter.flutter-gradle-plugin")
    // MISSING: id("org.jetbrains.kotlin.android")
}
```

**Impact**: Kotlin version defined in `settings.gradle.kts` (2.3.20) is not applied to the app module. Some Kotlin-based plugins may fail compilation.

**Fix**: Add Kotlin Android plugin:
```kotlin
plugins {
    id("com.android.application")
    id("dev.flutter.flutter-gradle-plugin")
    kotlin("android") version "2.3.20"
}
```

### Issue 2: maplibre_gl KGP Warning

```
WARNING: Your app uses plugins that apply Kotlin Gradle Plugin (KGP): maplibre_gl
Future versions of Flutter will fail to build.
```

**Impact**: Build warning only for now. Future Flutter versions may reject.

**Fix**: Monitor `maplibre_gl` updates for Built-in Kotlin migration.

### Issue 3: Gradle 9.1 + AGP 9.0.1

These are very new versions. Some plugins may not be compatible yet. If build issues arise, downgrade to AGP 8.7.x with Gradle 8.11.

---

## COMPATIBILITY RECOMMENDATIONS

| Priority | Action | Details |
|----------|--------|---------|
| 🔴 NOW | Add Kotlin plugin | Fix `app/build.gradle.kts` |
| 🔴 NOW | Set explicit SDK versions | `compileSdk=35`, `minSdk=23`, `targetSdk=34` |
| 🔴 NOW | Add `permission_handler` | Add to pubspec.yaml |
| 🟠 SOON | Remove redundant `http` | dio covers all HTTP needs |
| 🟠 SOON | Upgrade `sqlite3_flutter_libs` | EOL version; check for successor |
| 🟡 LATER | Monitor maplibre_gl KGP | Future Flutter versions |
