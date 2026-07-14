# NETWORK AUDIT REPORT

This audit inspects the networking setup utilizing Dio in [auth_api_client.dart](file:///home/londo/nurisk/mobile/app/lib/core/api/auth_api_client.dart).

## 1. Connection Configurations & Timeouts
- **Base URL**: Loaded via `dotenv` from `.env` under key `API_BASE_URL`. Defaults to `http://10.0.2.2:8000/api/` (Android emulator loopback).
- **Timeouts**:
  - `connectTimeout`: 10 seconds.
  - `receiveTimeout`: 15 seconds.
- **Content Headers**: Default headers are set:
  - `Accept`: `application/json`
  - `Content-Type`: `application/json`

## 2. Request Interceptors
The `InterceptorsWrapper` adds authentication state dynamically on every request:
- If `authState.isAuthenticated` is true, the following headers are injected:
  - `Authorization`: `Bearer <token>`
  - `X-Scope-Id`: `<activeScopeId>`
  - `X-Scope-Type`: `<activeScopeType>`
  - `X-Role`: `<activeRole>`
- Requests, responses, and errors are logged using `appLogger`.

## 3. Response Status Code & Error Handling
- **401/403 Session Invalidation**:
  If the API responds with `401 Unauthorized` or `403 Forbidden` on the session check, the `AuthStateNotifier` triggers `logout()`, clearing all secure storage keys and sending the user back to the public homepage in guest mode.
- **Connection Failures**:
  Handled by repository try-catch blocks and formatted for the UI using the central [DioExceptionMapper](file:///home/londo/nurisk/mobile/app/lib/core/error/dio_exception_mapper.dart), preventing raw network stacktraces from appearing on screens.

## 4. SSL & CORS Compliance
- **Cleartext Traffic**:
  The default development URL uses HTTP (`http://10.0.2.2:8000`). If cleartext traffic is disabled in Android's `AndroidManifest.xml` (`android:usesCleartextTraffic="false"`) or iOS's `Info.plist` App Transport Security, connections will fail with network request blocked errors.
- **CORS Configuration**:
  Since this is a mobile client app, CORS is generally ignored by native mobile OS network layers, but the Laravel BFF has CORS middleware enabled on the `/api/*` routes to allow web-based debugging/testing.
