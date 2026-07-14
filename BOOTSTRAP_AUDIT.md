# BOOTSTRAP AUDIT REPORT

## 1. Entry Point Analysis (`main.dart` & `main_dev()` / `main_prod()`)
The app has a single entry point `main()` in [main.dart](file:///home/londo/nurisk/mobile/app/lib/main.dart). There are no separate `main_dev()` or `main_prod()` files.
- `WidgetsFlutterBinding.ensureInitialized()` is executed first to bind framework services.
- `dotenv.load(fileName: '.env')` loads environment variables from the `.env` file in assets.
- `SduiRegistryInitializer.initialize()` registers the server-driven UI elements.
- `runApp()` starts the Riverpod `ProviderScope` and mounts `NuriskApp`.

## 2. Runtime Initializer & Dependency Injection
- During `initState` of `_NuriskAppState`, the `appRouterProvider` is read and the `GoRouter` instance is assigned.
- In `addPostFrameCallback`, `RuntimeInitializer.initialize(_router)` is triggered asynchronously.
- `RuntimeInitializer` handles phase-based startup:
  1. `AppErrorBoundary.init()` to trap exceptions.
  2. `AppLifecycleService` initialization.
  3. Platform/Infrastructure Services: `PermissionService`, `NavigationService`, `MediaService`, `GeoService`.
  4. Services are stored in a static locator `RuntimeServicesScope.instance`.
  5. The `runtimeStateProvider` is set to `RuntimeStatus.ok`.

## 3. Storage & Session Bootsrap
- **Hive**: Not used (not declared in `pubspec.yaml`).
- **SQLite**: Managed by Drift (`PublicDatabase` using `NativeDatabase` or `WebDatabase`). The `publicDatabaseProvider` is declared in [database_provider.dart](file:///home/londo/nurisk/mobile/app/lib/core/storage/public/database_provider.dart) but returns `null` by default:
  ```dart
  final publicDatabaseProvider = Provider<PublicDatabase?>((ref) => null);
  ```
- **SharedPreference**: Used via `SharedPreferences.getInstance()` in local datasources.
- **Session / SecureStorage**: Managed via `FlutterSecureStorage` in `AuthStateNotifier._loadState()`, which reads keys asynchronously during Riverpod provider build.

## 4. Bootstrapping Race Condition Risks
- `RuntimeInitializer.initialize` runs asynchronously in `addPostFrameCallback` (after the first frame).
- In the first frame, `runtimeStateProvider` is `RuntimeStatus.uninitialized`, but the app still renders `MaterialApp.router` which directly routes to `RoutePaths.splash` (`SplashScreen`).
- `SplashScreen` waits for `authStateProvider` and `1500ms` delay. If any operation reads `runtimeServicesProvider` before `RuntimeInitializer.initialize` completes, a `TypeError` (in release mode) or `AssertionError` (in debug mode) will be thrown because `RuntimeServicesScope.instance` is still `null`.
- Drift `publicDatabaseProvider` returns `null` statically, meaning no local database storage operations will execute for tables using `PublicDatabase` (e.g. `governance` caching, incident caching) and instead will degrade to online-only or fail quietly.
