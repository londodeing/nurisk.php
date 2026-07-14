# NURISK Navigation Audit

## Executive Summary

Audit date: 2026-07-08  
Audited files: 110 Dart files, focus on 11 navigation-critical files  
Scanner: Automated + Manual code review  

---

## ANTI-PATTERN 1: `context.go()` for Bottom Navigation Tab Switching

**Severity**: CRITICAL  
**File**: `lib/features/public/dashboard/presentation/widgets/public_bottom_nav.dart:63-76`

```dart
void _onItemTapped(int index, BuildContext context) {
  switch (index) {
    case 0: context.go('/p/home'); break;
    case 1: context.go('/p/map'); break;
    // ...
  }
}
```

**Problem**:  
- `context.go()` replaces the entire route stack  
- Every tab switch destroys the previous tab's state  
- Widgets are rebuilt from scratch on every switch  
- Map camera position, scroll positions, form data are all lost  
- Triggers unnecessary network fetches  

**Fix**: Use `StatefulNavigationShell.goBranch()` with `StatefulShellRoute.indexedStack`.

---

## ANTI-PATTERN 2: No Back Button Handling

**Severity**: CRITICAL  
**Files**: Entire codebase — zero `PopScope` or `WillPopScope` found  

**Problem**:  
- Pressing Back from any bottom navigation tab immediately exits the app  
- No "Tekan sekali lagi untuk keluar" confirmation  
- No navigation stack preservation  
- Android 14+ Predictive Back gesture is not supported  

**Fix**: Implement `PopScope` in the shell wrapper with proper back stack management.

---

## ANTI-PATTERN 3: `ShellRoute` Without Stateful Branches

**Severity**: HIGH  
**File**: `lib/core/router/app_router.dart:52-78`

```dart
ShellRoute(
  builder: (context, state, child) => PublicBottomNav(child: child),
  routes: [ /* 5 GoRoutes */ ],
)
```

**Problem**:  
- `ShellRoute` wraps child content without `IndexedStack`  
- Each tab shares the same navigator — no isolation  
- No per-tab navigation stack  
- Tab routes are just path-based, not state-preserving  

**Fix**: Replace with `StatefulShellRoute.indexedStack` providing independent per-branch navigators.

---

## ANTI-PATTERN 4: `context.go()` for Post-Auth Navigation

**Severity**: HIGH  
**Files**:  
- `lib/features/auth/presentation/screens/login_screen.dart:71` — `context.go('/p/profile')`  
- `lib/features/auth/presentation/screens/mandate_picker_screen.dart:48` — `context.go('/g/executive')`  
- `lib/core/splash/splash_screen.dart:22` — `context.go('/p/home')`  

**Problem**:  
- Replaces entire navigation stack, losing any intermediate state  
- Login → go to Profile tab is semantically wrong (should go to Home/Dashboard)  
- No mandate flow integration  

**Fix**: Use `context.push()` or `context.go()` only at appropriate lifecycle points. Navigate to Home tab, not Profile tab.

---

## ANTI-PATTERN 5: No Navigator Keys

**Severity**: HIGH  
**File**: `lib/core/router/app_router.dart` (entire file)  

**Problem**:  
- No `GlobalKey<NavigatorState>` provided anywhere  
- Cannot programmatically control navigation state  
- No access to navigator for restoration purposes  

**Fix**: Provide navigator keys for root and shell navigators.

---

## ANTI-PATTERN 6: No State Restoration

**Severity**: HIGH  
**Files**: All screen files  

**Problem**:  
- No `PageStorageKey` used on any scrollable/list widgets  
- No `RestorationMixin` on any stateful widgets  
- App restart loses all navigation state  
- Scroll positions, map camera, form drafts are all lost  

**Fix**: Add `PageStorageKey` and `RestorationBucket` support.

---

## ANTI-PATTERN 7: No Deep Link Support

**Severity**: MEDIUM  
**File**: `lib/core/router/app_router.dart` (missing routes)  

**Problem**:  
- No routes defined for `nurisk://incident/123`, `nurisk://map`, etc.  
- Deep links would result in 404/not-found errors  
- Cannot share content URLs  

**Fix**: Add deep link route definitions.

---

## ANTI-PATTERN 8: `context.push()` Inside Bottom Navigation Tabs

**Severity**: MEDIUM  
**File**: `lib/features/profile/presentation/screens/profile_screen.dart:47,83,93,117,291,310`  

```dart
context.push('/g/executive');
context.push('/p/report');
context.push('/p/map');
context.push('/auth/login');
context.push('/auth/register');
```

**Problem**:  
- These push onto the root navigator stack  
- Profile tab pushes `/g/executive` → governance has no bottom nav → user is "trapped"  
- Pushing `/p/report` or `/p/map` creates duplicate instances of shared routes  
- Breaks the "single source of truth" for navigation  

**Fix**: 
- For governance: `context.push()` is acceptable (full-screen experience)  
- For report/map: Use tab switching via shell, or remove these entries  

---

## ANTI-PATTERN 9: Logout Without Navigation

**Severity**: MEDIUM  
**File**: `lib/features/profile/presentation/screens/profile_screen.dart:243-245`  

```dart
onTap: () async {
  await ref.read(authStateProvider.notifier).logout();
},
```

**Problem**:  
- Logout clears auth state but does NOT navigate anywhere  
- User is left on the same screen with no visual feedback  
- No redirect to Public Dashboard  

**Fix**: Listen to auth state changes at the app level and redirect to `/p/home`.

---

## ANTI-PATTERN 10: No Redirect Guard

**Severity**: MEDIUM  
**File**: `lib/core/router/app_router.dart` (missing `redirect` callback)  

**Problem**:  
- No auth guard to protect governance routes  
- No redirect from splash when already authenticated  
- Anyone can navigate to `/g/executive` directly  

**Fix**: Add GoRouter `redirect` callback for auth-based redirects.

---

## ANTI-PATTERN 11: Hardcoded Routes

**Severity**: LOW  
**Files**: Multiple  

```dart
'/p/home', '/p/map', '/p/report', '/auth/login', '/g/executive'
```

**Problem**:  
- Routes are string literals scattered across files  
- No centralized route constants  
- Refactoring requires changing many files  

**Fix**: Define route path constants in a single location.

---

## Audit Summary

| # | Anti-Pattern | Severity | File(s) |
|---|--------------|----------|---------|
| 1 | `context.go()` for tab switching | CRITICAL | `public_bottom_nav.dart` |
| 2 | No back button handling | CRITICAL | All screens |
| 3 | ShellRoute without stateful branches | HIGH | `app_router.dart` |
| 4 | `context.go()` for post-auth nav | HIGH | `login_screen.dart`, `mandate_picker_screen.dart`, `splash_screen.dart` |
| 5 | No Navigator keys | HIGH | `app_router.dart` |
| 6 | No state restoration | HIGH | All screens |
| 7 | No deep link support | MEDIUM | `app_router.dart` |
| 8 | `context.push()` inside tabs | MEDIUM | `profile_screen.dart` |
| 9 | Logout without navigation | MEDIUM | `profile_screen.dart` |
| 10 | No redirect guard | MEDIUM | `app_router.dart` |
| 11 | Hardcoded route strings | LOW | Multiple files |

Total: 11 anti-patterns identified. 3 CRITICAL, 3 HIGH, 4 MEDIUM, 1 LOW.
