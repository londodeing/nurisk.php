# NURISK Navigation Flow Diagrams

---

## 1. Navigation Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     GoRouter (root)                         │
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │  /splash │  │ /auth/*  │  │ /g/exec  │  │ /incident/ │  │
│  │          │  │          │  │          │  │ /report/   │  │
│  │ Splash   │  │ Login    │  │ Exec.    │  │ Deep Link  │  │
│  │ Screen   │  │ Register │  │ Dsb.     │  │ Routes     │  │
│  │          │  │ Mandate  │  │          │  │            │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │           StatefulShellRoute.indexedStack               ││
│  │                                                         ││
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  ││
│  │  │  Branch  │ │  Branch  │ │  Branch  │ │  Branch  │  ││
│  │  │    0     │ │    1     │ │    2     │ │   3,4    │  ││
│  │  │  /p/home │ │  /p/map  │ │/p/report │ │/p/resrc  │  ││
│  │  │  Dsbh.   │ │  MapLib  │ │  Wizard  │ │ Profile  │  ││
│  │  │  Indexed │ │  Indexed │ │  Indexed │ │ Indexed  │  ││
│  │  │  Stack   │ │  Stack   │ │  Stack   │ │  Stack   │  ││
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘  ││
│  │                                                         ││
│  │         PublicBottomNav (PopScope wrapper)              ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Back Stack Flow

```
                         Back Pressed
                              │
                              ▼
              ┌──────────────────────────────┐
              │  PopScope (canPop: false)     │
              │  onPopInvoked triggered        │
              └──────────┬───────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────────┐
        │  Current loc != tab root path?      │
        └──────────┬──────────────┬───────────┘
                   │ YES          │ NO
                   ▼              ▼
            context.pop()   Current tab = Home?
            (pop sub-route)      │           │
                                YES          NO
                                 │            │
                                 ▼            ▼
              ┌────────────────────┐    goBranch(0)
              │ lastBackPress      │    (go to Home)
              │ < 2 seconds ago?   │
              └──────┬──────┬──────┘
                   YES      NO
                     │        │
                     ▼        ▼
              SystemNav     Show snackbar
              .pop()        Record timestamp
```

---

## 3. Authentication Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                        PUBLIC ZONE                               │
│                                                                   │
│  ┌────────────┐    ┌────────────────┐    ┌────────────────────┐  │
│  │  Splash    │───▶│  Public Dsbh.  │───▶│  Bottom Navigation │  │
│  │  /splash   │    │  /p/home       │    │  Home / Map /      │  │
│  └────────────┘    └────────────────┘    │  Lapor / Info /    │  │
│                                           │  Profil            │  │
│                                           └────────┬───────────┘  │
│                                                    │              │
│                                         ┌──────────▼──────────┐  │
│                                         │  Profile Tab        │  │
│                                         │  (Not Authenticated) │  │
│                                         │  "Masuk (Login)"     │  │
│                                         └──────────┬──────────┘  │
│                                                    │ push        │
│                                         ┌──────────▼──────────┐  │
│                                         │  Login Screen       │  │
│                                         │  /auth/login        │  │
│                                         │  (no bottom nav)    │  │
│                                         └──────────┬──────────┘  │
│                                                    │ success     │
└────────────────────────────────────────────────────┼──────────────┘
                                                     │
                                                     ▼
                              ┌──────────────────────────────────────┐
                              │         AUTHENTICATED ZONE           │
                              │                                       │
                              │  ┌─────────────────────────────────┐  │
                              │  │  User has mandates?             │  │
                              │  └──────┬──────────────┬───────────┘  │
                              │      YES               NO             │
                              │         │                │            │
                              │         ▼                ▼            │
                              │  ┌────────────┐  ┌───────────────┐    │
                              │  │  Mandate   │  │  Redirect to  │    │
                              │  │  Picker    │  │  /p/home      │    │
                              │  │  /auth/    │  │               │    │
                              │  │  mandate   │  │               │    │
                              │  └──────┬─────┘  └───────────────┘    │
                              │         │ go                          │
                              │         ▼                             │
                              │  ┌────────────────────────┐          │
                              │  │  Executive Dashboard   │          │
                              │  │  /g/executive          │          │
                              │  │  (no bottom nav)       │          │
                              │  └────────────────────────┘          │
                              │                                       │
                              │              │                        │
                              │              ▼ Logout                 │
                              │  ┌────────────────────────┐          │
                              │  │  Redirect to /p/home   │          │
                              │  │  (Back to Public Zone) │          │
                              │  └────────────────────────┘          │
                              └───────────────────────────────────────┘
```

---

## 4. Mandate Flow

```
┌──────────────┐
│  Login       │
│  Success     │
└──────┬───────┘
       │
       ▼
┌────────────────────────────────────────┐
│  AuthState updated:                     │
│  - isAuthenticated = true              │
│  - token, userId, userName set         │
└────────────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│  Does user have multiple mandates?     │
│  (gov/exec roles)                      │
└──────────┬─────────────────┬───────────┘
           │ YES              │ NO
           ▼                  ▼
┌────────────────────┐  ┌────────────────┐
│  Navigate to        │  │  Navigate to   │
│  /auth/mandate      │  │  /p/home       │
│  (with userId,      │  │  (Public Dsbh) │
│   userName,         │  └────────────────┘
│   mandates list)    │
└──────────┬─────────┘
           │
           ▼
┌────────────────────────────────────────┐
│  MandatePickerScreen                   │
│  - Shows list of available mandates    │
│  - User taps one                       │
│  - POST /auth/mandate                  │
│  - AuthState updated with role/scope   │
└──────────┬─────────────────────────────┘
           │ success
           ▼
┌────────────────────────────────────────┐
│  Navigate to /g/executive             │
│  (Executive Dashboard with bottom nav  │
│   not shown — full screen)             │
└────────────────────────────────────────┘
           │
           ▼ Logout
┌────────────────────────────────────────┐
│  AuthState cleared                     │
│  Navigate to /p/home                  │
│  (Back to Public Dashboard)            │
└────────────────────────────────────────┘
```

---

## 5. Deep Link Flow

```
                         Deep Link Received
                                │
                                ▼
                    ┌───────────────────────┐
                    │  GoRouter matches      │
                    │  route path            │
                    └───────────┬───────────┘
                                │
              ┌─────────────────┼─────────────────┐
              ▼                 ▼                  ▼
    ┌────────────────┐ ┌──────────────┐ ┌──────────────────┐
    │ /incident/:id  │ │ /map         │ │ /report/         │
    │                │ │              │ │ :trackingCode    │
    │ → Show full    │ │ → Redirect   │ │                  │
    │   screen       │ │   to /p/map  │ │ → Show tracking  │
    │   incident     │ │   (Map tab)  │ │   screen         │
    │   detail       │ │              │ │                  │
    └────────────────┘ └──────────────┘ └──────────────────┘
                                │
                    ┌───────────────────┐
                    │ /governance/      │
                    │ approval/:id      │
                    │                   │
                    │ ┌───────────────┐ │
                    │ │ Authenticated?│ │
                    │ └──┬──────┬────┘ │
                    │  YES      NO     │
                    │   │        │     │
                    │   ▼        ▼     │
                    │ Show     Redirect │
                    │ Detail   to       │
                    │          /auth/   │
                    │          login    │
                    └───────────────────┘

  URL Scheme: nurisk://path
  Example:    nurisk://incident/123
              nurisk://map
              nurisk://report/TRX-2024-001
              nurisk://governance/approval/55
```

---

## 6. Bottom Navigation Tab Preservation

```
┌─────────────────────────────────────────────────────────────────┐
│  StatefulShellRoute.indexedStack                               │
│                                                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌────────┐ │
│  │  Tab 0      │  │  Tab 1      │  │  Tab 2      │  │  ...   │ │
│  │  Home       │  │  Map        │  │  Lapor      │  │        │ │
│  │             │  │             │  │             │  │        │ │
│  │  ┌───────┐  │  │  ┌───────┐  │  │  ┌───────┐  │  │        │ │
│  │  │Indexed│  │  │  │Indexed│  │  │  │Indexed│  │  │        │ │
│  │  │Stack  │  │  │  │Stack  │  │  │  │Stack  │  │  │        │ │
│  │  │ per   │  │  │  │ per   │  │  │  │ per   │  │  │        │ │
│  │  │Branch │  │  │  │Branch │  │  │  │Branch │  │  │        │ │
│  │  └───────┘  │  │  └───────┘  │  │  └───────┘  │  │        │ │
│  │             │  │             │  │             │  │        │ │
│  │  - Scoll    │  │  - Camera  │  │  - Step     │  │        │ │
│  │  Position   │  │  Position  │  │  - Form     │  │        │ │
│  │  - Filters  │  │  - Zoom    │  │  - Data     │  │        │ │
│  │  - Data     │  │  - Layers  │  │  - Photo    │  │        │ │
│  │             │  │  - Markers │  │             │  │        │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └────────┘ │
│                                                                 │
│  ═══════════════════════════════════════════════════════════════ │
│                                                                 │
│  Switching from Tab 0 → Tab 1 → Tab 0:                          │
│  - Tab 0 state is preserved (IndexedStack keeps it alive)       │
│  - No rebuild, no refetch, no recreation                        │
│  - Scroll position, filters, data all intact                    │
│                                                                 │
│  Switching from Tab 1 → Tab 0 → Tab 1:                          │
│  - Map camera position preserved                                │
│  - Active layers preserved                                      │
│  - Markers preserved                                            │
│  - No reload of map data                                        │
└─────────────────────────────────────────────────────────────────┘
```
