# RECOVERY PLAN REPORT

Below is the step-by-step recovery plan to stabilize the NURISK application and resolve the White Screen of Death (WSOD) and caching failures.

## 1. Sequence of Fixes

### Fix 1: Align Public Dashboard JSON Contract Parser
- **Target**: [config_model.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/config/data/models/config_model.dart)
- **Action**: Update `ConfigModel.fromJson` to correctly parse the wrapped `"data"` map returned by the BFF:
  - Map `json['data']['version']` to `version`.
  - Map `json['data']['screen_title']` to `screenTitle`.
  - Map `json['data']['layout_type']` to `layoutType`.
  - Map `json['data']['widgets']` to `widgets`.
- **Outcome**: The `widgets` list is correctly populated, letting `PublicDashboardScreen` parse and render the SDUI nodes.

### Fix 2: Align Account Screen JSON Contract Datasource
- **Target**: [account_remote_datasource.dart](file:///home/londo/nurisk/mobile/app/lib/features/account/data/datasources/account_remote_datasource.dart)
- **Action**: Update `getAccountHome` to pass the correct nested map to `AccountHomeData.fromJson`:
  - Pass `res.data['data']['cards']` instead of `res.data['data']`.
- **Outcome**: The parser finds the `nodes` list on the expected root level, resolving the blank Account screen.

### Fix 3: Resolve Boot Race Condition
- **Target**: [splash_screen.dart](file:///home/londo/nurisk/mobile/app/lib/core/splash/splash_screen.dart)
- **Action**: Guard the `_tryNavigate` method in `SplashScreenState` to ensure it does not attempt navigation before runtime initialization has finished:
  - Check `ref.read(runtimeStateProvider).status == RuntimeStatus.ok` prior to accessing `runtimeServicesProvider`.
- **Outcome**: Banish potential race conditions and `Null Check` / `Assertion` crashes during app launch.

### Fix 4: Enable Drift SQLite Local Cache Database
- **Target**: [database_provider.dart](file:///home/londo/nurisk/mobile/app/lib/core/storage/public/database_provider.dart)
- **Action**: Replace the hardcoded `null` return with an asynchronous initialization of the database:
  - Change `final publicDatabaseProvider = Provider<PublicDatabase?>((ref) => null);` to watch and load `createPublicDatabase()`.
- **Outcome**: Fully enables local caching of governance approvals, weather statistics, and incident feeds.

### Fix 5: Register Missing Components
- **Target**: [sdui_registry_initializer.dart](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_registry_initializer.dart)
- **Action**: Register the remaining component files (e.g. `Map`, `BottomSheet`, `Chart`) in `SduiRegistry.instance`.
- **Outcome**: Restores missing UI features and prevents fallback warning boxes.

### Fix 6: Correct Goldens Test Assertion
- **Target**: [sdui_goldens_test.dart](file:///home/londo/nurisk/mobile/app/test/sdui_goldens_test.dart)
- **Action**: Update the expected nodes count to match the JSON array length:
  - Change `expect(nodes.length, 3)` to `expect(nodes.length, 2)`.
- **Outcome**: Repairs the pipeline test suite.

---

## 2. Verification Plan

### Step A: Automated Regression Tests
Run the following local test suites to verify syntax and logic:
```bash
/home/londo/development/flutter/bin/flutter test test/core/master/sqlite_master_repository_test.dart
/home/londo/development/flutter/bin/flutter test test/core/master/json_master_repository_test.dart
/home/londo/development/flutter/bin/flutter test test/core/master/master_repository_impl_test.dart
/home/londo/development/flutter/bin/flutter test test/core/master/organization_repository_test.dart
```

### Step B: Golden Tests Verification
Execute the SDUI rendering pipelines test:
```bash
/home/londo/development/flutter/bin/flutter test test/sdui_goldens_test.dart
```

### Step C: Production Verification (Staging Build)
Build the staging APK and verify visually on an emulator/device:
```bash
/home/londo/development/flutter/bin/flutter build apk --debug
```
Install and run to verify:
1. Double-check that the splash screen completes and redirects without crash.
2. Confirm the Public Dashboard displays widgets loaded from the BFF (`bff/dashboard`).
3. Confirm the Account / Command Center screen correctly renders user profile cards and menu items.
4. Test offline capabilities by disabling connection and ensuring SQLite cache restoration is active.
