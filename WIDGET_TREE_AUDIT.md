# WIDGET TREE AUDIT REPORT

## 1. Top-Level Hierarchy
```mermaid
graph TD
    A[ProviderScope] --> B[NuriskApp]
    B -->|runtime.status == uninitialized| C[MaterialApp.router]
    B -->|runtime.status == failed| D[MaterialApp -> Scaffold -> Text]
    C -->|initialLocation| E[SplashScreen]
```

## 2. Navigation Target Paths (Route Tracing)
Upon splash duration expiry (~1500ms) and authentication status loaded:
- **Guest / Authenticated User**: Navigates to `/p/home`.
- **Route Matches**:
  - `StatefulShellRoute.indexedStack` builds `PublicBottomNav(navigationShell)`.
  - The default active branch is `home` (`/p/home`), which mounts `PublicDashboardScreen`.

## 3. Public Dashboard Screen Widget Subtree
```mermaid
graph TD
    A[PublicBottomNav] --> B[Scaffold]
    B -->|body| C[StatefulNavigationShell]
    C --> D[PublicDashboardScreen]
    D --> E[Scaffold]
    E -->|appBar| F[AppBar]
    E -->|body| G[RefreshIndicator]
    G --> H{configState.when}
    H -->|loading| I[Center -> CircularProgressIndicator]
    H -->|error| J[Center -> Column -> Icon + Text + TextButton]
    H -->|data| K[SduiScreen]
    K --> L[Scaffold]
    L -->|appBar| M[AppBar]
    L -->|body| N[SingleChildScrollView]
    N --> O[Column]
    O --> P[nodes.map - SduiRenderer]
```

## 4. Account Screen Widget Subtree (Profile Tab)
When user switches to the 'Akun' (Profile) tab `/p/profile`:
```mermaid
graph TD
    A[AccountHomeScreen] --> B{accountAsync.when}
    B -->|loading| C[Scaffold -> Center -> CircularProgressIndicator]
    B -->|error| D[Scaffold -> Center -> Column -> Icon + Text + ElevatedButton]
    B -->|data| E[SduiScreen]
    E --> F[Scaffold]
    F -->|appBar| G[AppBar]
    F -->|body| H[SingleChildScrollView -> Column -> SduiRenderer]
```

## 5. Terminal Widget Before WSOD / Blank Rendering
- On both the Dashboard (`/p/home`) and Account (`/p/profile`) screens, the final rendered widget in the subtree is `SduiScreen`'s `SingleChildScrollView -> Column`.
- Due to JSON contract mapping mismatches between the BFF responses and the client's models:
  - Dashboard: `nodes` is parsed as `[]` because the BFF returns `widgets` under `data`, but client expects `nodes` under top-level root.
  - Account: `nodes` is parsed as `[]` because the BFF returns `cards` containing `nodes`, but client expects `nodes` directly inside `data`.
- Since `nodes` is `[]`, the `Column` has 0 children. The screen below the app bar renders completely blank white (on light theme) or dark (on dark theme). No exception is thrown, meaning it silently displays a blank screen.
