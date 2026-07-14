# JSON CONTRACT AUDIT REPORT

This audit inspects the backend JSON payloads returned by BFF controllers and maps them against what the Flutter client models expect.

## 1. Endpoint: `/api/account/home` (maps to `/api/account` target)
- **Controller**: `App\Http\Controllers\Api\AccountHomeController`
- **Actual JSON Response Structure**:
  ```json
  {
    "success": true,
    "data": {
      "cards": {
        "screen": "AccountHome",
        "layout": "vertical",
        "nodes": [ ... ]
      }
    }
  }
  ```
- **Flutter Expected Structure (`AccountHomeData.fromJson`)**:
  Expects the direct parameter to contain:
  ```json
  {
    "screen": "AccountHome",
    "layout": "vertical",
    "nodes": [ ... ]
  }
  ```
  But Flutter passes `res.data['data']` to `AccountHomeData.fromJson`. `res.data['data']` does **not** have the `nodes` key on its root level; it is nested inside the `cards` key!
- **Mismatch**: Critical. The client fails to parse `nodes` because it checks `json['nodes']`, yielding `[]`.

## 2. Endpoint: `/api/bff/dashboard` (maps to `/api/dashboard` target)
- **Controller**: `App\Http\Controllers\Api\Bff\DashboardBffController`
- **Actual JSON Response Structure**:
  ```json
  {
    "status": "success",
    "version": "1.0",
    "data": {
      "screen_title": "Beranda Utama",
      "layout_type": "scrollable_column",
      "widgets": [ ... ],
      "bottom_nav": [ ... ]
    }
  }
  ```
- **Flutter Expected Structure (`ConfigModel.fromJson`)**:
  Expects:
  ```json
  {
    "version": "1.0",
    "screen": "Dashboard",
    "layout": "vertical",
    "nodes": [ ... ]
  }
  ```
  But the actual payload wraps everything in `data` and uses keys `screen_title`, `layout_type`, and `widgets` instead of `screen`, `layout`, and `nodes`.
- **Mismatch**: Critical. The client fails to parse `widgets` (which it maps from key `nodes`), resulting in `widgets = []` in the model, and ultimately rendering an empty dashboard screen.

## 3. Endpoint: `/api/public/dashboard` (maps to `public/dashboard` or `public/dashboard/config`)
- **Controller**: `App\Http\Controllers\Api\PublicDashboardApiController`
- **Actual JSON Response Structure**: Returns the public config layout.
- **Flutter Expectation**: Matches standard `ConfigModel`.

## 4. Endpoint: `/api/cop` (maps to `/api/public/map/operational/{type}`)
- **Actual JSON Response Structure**: GeoJSON format.
- **Flutter Expectation**: Standard GeoJSON parsing features.

## 5. Endpoint: `/api/report` (maps to `/api/laporan` / `/api/lapor`)
- **Controller**: `App\Http\Controllers\Api\LaporanKejadianApiController`
- **Actual JSON Response Structure**: Payload with `success`, `message`, `data` (containing tracking code and details).
- **Flutter Expectation**: Standard JSON response with `tracking_code`.

## 6. Endpoint: `/api/profile`
- **Controller**: `App\Http\Controllers\Api\ProfileApiController`
- **Actual JSON Response Structure**: Profile data (`nama_lengkap`, `no_hp`, roles, mandates).
- **Flutter Expectation**: Profile data deserialization.
