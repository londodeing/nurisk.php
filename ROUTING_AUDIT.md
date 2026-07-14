# ROUTING AUDIT REPORT

This audit covers [app_router.dart](file:///home/londo/nurisk/mobile/app/lib/core/router/app_router.dart).

## 1. Route Path Declarations
The routing paths are defined as static constants under the `RoutePaths` class:
- `splash` = `/splash`
- `login` = `/auth/login`
- `register` = `/auth/register`
- `mandate` = `/auth/mandate`
- `home` = `/p/home`
- `map` = `/p/map`
- `report` = `/p/report`
- `resource` = `/p/resource`
- `profile` = `/p/profile`

## 2. Guard Redirect Analysis
The `GoRouter` redirect logic ensures correct role-based or session-based navigation:
- **Splash Exception**: `/splash` is bypassed during loading to prevent boot traps.
- **Loading Phase**: If `auth.isLoading` is true, redirect returns `null` (keeps the current view, which is the `SplashScreen`).
- **Authenticated Users on Auth Pages**: If `auth.isAuthenticated` is true and the user tries to access `/auth/login` or `/auth/register`, they are redirected to `/p/home`.
- **Guest Users on Protected Pages**: If `auth.isAuthenticated` is false and the user tries to access routes starting with `/g/` or `/governance/` (protected prefix), they are redirected to `/p/home`.
- **Verdict on Loop Detection**: The redirects are one-way towards the safe fallback `/p/home`, which is public and requires no redirection. No infinite loop loops are present.

## 3. Builder and Page Configuration
- Every declared route has a valid `builder` closure returning a concrete `Widget` subclass.
- The `StatefulShellRoute.indexedStack` has five `StatefulShellBranch` branches representing the main tabs:
  1. `/p/home` -> `PublicDashboardScreen`
  2. `/p/map` -> `CopMapScreen`
  3. `/p/report` -> `ReportWizardScreen`
  4. `/p/resource` -> `ResourceScreen`
  5. `/p/profile` -> `AccountHomeScreen`
- No route definition has missing builders, ensuring GoRouter can compile and render all screens correctly.
