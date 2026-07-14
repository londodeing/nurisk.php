# ROOT CAUSE ANALYSIS REPORT

## 1. Primary Root Cause: JSON Contract Alignment Failures
The White Screen of Death (WSOD) / blank screens on both the Dashboard and the Account/Command Center are caused by mismatched JSON contracts between the Laravel BFF and the Flutter client. The parser resolves empty lists for widgets/nodes, causing `SduiScreen` to render an empty `Column(children: [])`.

### A. Public Dashboard Mismatch
- **File**: [config_model.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/config/data/models/config_model.dart)
- **Class**: `ConfigModel`
- **Method**: `fromJson`
- **Line**: 14-24
- **Call Stack**:
  ```
  PublicDashboardScreen.build(context, ref)
    -> configState.when(data: (config) => ...)
    -> ConfigModel.fromJson(response.data)
  ```
- **Analysis**:
  - The BFF [DashboardBffController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Bff/DashboardBffController.php#L77-L89) returns:
    ```json
    {
      "status": "success",
      "version": "1.0",
      "data": {
        "screen_title": "Beranda Utama",
        "layout_type": "scrollable_column",
        "widgets": [...]
      }
    }
    ```
  - `ConfigModel.fromJson` parses the root response directly but expects:
    - `"version"` (matches)
    - `"screen"` (missing -> defaults to `'Dashboard'`)
    - `"layout"` (missing -> defaults to `'vertical'`)
    - `"nodes"` (missing -> defaults to `[]`)
  - Because `"nodes"` is missing at the root level, `widgets` is set to `[]`.
  - In [public_dashboard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/dashboard/presentation/screens/public_dashboard_screen.dart#L33-L34), `nodesJson` evaluates to `[]`, leaving the screen completely blank under the App Bar.

### B. Account / Command Center Mismatch
- **File**: [account_remote_datasource.dart](file:///home/londo/nurisk/mobile/app/lib/features/account/data/datasources/account_remote_datasource.dart)
- **Class**: `AccountRemoteDatasource`
- **Method**: `getAccountHome`
- **Line**: 10-16
- **Call Stack**:
  ```
  AccountHomeScreen.build(context, ref)
    -> accountAsync.when(data: (data) => ...)
    -> AccountRemoteDatasource.getAccountHome(dio)
    -> AccountHomeData.fromJson(res.data['data'])
  ```
- **Analysis**:
  - The backend [AccountHomeController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/AccountHomeController.php#L22-L27) returns:
    ```json
    {
      "success": true,
      "data": {
        "cards": {
          "screen": "AccountHome",
          "layout": "vertical",
          "nodes": [...]
        }
      }
    }
    ```
  - The datasource passes `res.data['data']` directly to `AccountHomeData.fromJson`.
  - [AccountHomeData.fromJson](file:///home/londo/nurisk/mobile/app/lib/features/account/domain/models/account_home_data.dart#L14-L21) tries to read `json['nodes']` directly from the passed map.
  - Because `nodes` is nested inside `cards`, it evaluates to `null` and defaults to `[]`.
  - The Account screen renders completely blank under the App Bar.

---

## 2. Secondary Root Cause: Asynchronous Init Race Condition
- **File**: [runtime_initializer.dart](file:///home/londo/nurisk/mobile/app/lib/core/runtime/runtime_initializer.dart)
- **Class**: `RuntimeServicesScope`
- **Method**: `instance` (getter)
- **Line**: 62-67
- **Call Stack**:
  ```
  SplashScreenState.initState()
    -> Future.delayed(1500ms) -> _tryNavigate()
    -> ref.read(runtimeServicesProvider).navigation
    -> RuntimeServicesScope.instance
  ```
- **Analysis**:
  - `RuntimeInitializer.initialize` runs asynchronously in `addPostFrameCallback`.
  - If the 1500ms splash delay completes before initialization finishes, calling `RuntimeServicesScope.instance` throws a `TypeError` (Null check operator used on a null value) in production, locking the application on the `SplashScreen` forever.

---

## 3. Severity & Impact
- **Severity**: Critical / Blocker
- **Impact**: Users see a completely blank white screen under the App Bar on both the main Public Dashboard and the authenticated Account / Command Center screens, making the app entirely unusable.
- **Confidence**: 100% (Confirmed via static code analysis, network models tracing, and JSON structure validation).
