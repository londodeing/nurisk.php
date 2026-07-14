# PUBLIC DOMAIN FIX PLAN — NURISK Mobile

> Phase 2.1 — Atomic task list with priorities
> Date: 2026-07-08

---

## EXECUTION RULES

1. **Critical** → fix immediately (security/production blocker)
2. **High** → fix this sprint
3. **Medium** → document, fix after High items
4. **Low** → document only, no code change this sprint
5. Each fix = 1 atomic commit
6. After each fix: `flutter analyze` must remain 0 errors

---

## CRITICAL (Fix Immediately)

| ID | Module | File | Problem | Fix |
|----|--------|------|---------|-----|
| C01 | Auth | `login_screen.dart:15` | Hardcoded phone _controller = TextEditingController(text: '08123456789') and password _controller with 'password' | Remove initial text from controllers — start empty |
| C02 | Auth | `pin_verification_dialog.dart:52` | Magic PIN bypass: `if (_pinCtrl.text == '123456') { Navigator.pop(context, true); }` in catch block | Remove magic PIN fallback — server response is the only authority |

---

## HIGH (Fix This Sprint)

| ID | Module | File | Problem | Fix |
|----|--------|------|---------|-----|
| H01 | Dashboard | `public_dashboard_screen.dart` | No per-widget ErrorBoundary — one widget crash crashes entire dashboard | Wrap each widget section (WarningBanner, WeatherCard, KPICards, IncidentFeed) with `ErrorBoundary.wrap()` |
| H02 | Dashboard | `public_dashboard_screen.dart` | No offline cache fallback | Read from cache if network fails; show "Offline — Terakhir diperbarui: HH:MM" |
| H03 | Report | `report_wizard_screen.dart` | No GPS mock detection — uses `GeoService` but ignores `GeoResult.isMocked` | Add `if (result.isMocked) { showWarning('GPS terdeteksi palsu'); }` — trust the GeoService result |
| H04 | Report | `report_wizard_screen.dart` | No upload failure retry | Add retry button on submission failure; expose `_retrySubmit()` |
| H05 | Auth | `auth_state_provider.dart` | Token stored in SharedPreferences instead of secure storage | Evaluate: PRD mandates secure storage. If flutter_secure_storage not added, document as known limitation. |
| H06 | Executive | `executive_dashboard_screen.dart:166-178` | Hardcoded demo analytics values | Remove hardcoded values — use governance_provider data or show empty state |
| H07 | Navigation | `public_bottom_nav.dart:63` | `SystemNavigator.pop()` bypasses NavigationService | Use `NavigationService.exit()` or keep as-is with documentation |
| H08 | Navigation | `spatial_filter_bottom_sheet.dart:83` | `Navigator.pop(context)` | Replace with `NavigationService.pop()` |
| H09 | Navigation | `report_wizard_screen.dart:269` | `Navigator.pop(context)` | Replace with `NavigationService.pop()` |

---

## MEDIUM (Document, Fix After High)

| ID | Module | Problem | Notes |
|----|--------|---------|-------|
| M01 | Dashboard | Add pull-to-refresh | DashboardOrchestrator already exists, just wire it |
| M02 | Dashboard | Add phased loading (above-fold first) | Warning + Weather + KPI first, then Incident, then News/Donation |
| M03 | Dashboard | Add NewsCard widget + route | Sprint F3 item — News module not started |
| M04 | Dashboard | Add DonationCard widget | Sprint F3 item — Lazisnu integration |
| M05 | Report | Add GPS timeout feedback (>30s) | Show user "GPS masih mencari sinyal..." after 10s |
| M06 | Report | Add GPS mati guidance | If GPS disabled, show dialog "Aktifkan GPS untuk akurasi lokasi" |
| M07 | Report | Add resume draft persistence | Save partial report to local storage, resume on reopen |
| M08 | COP Map | Add marker clustering | Use supercluster or grid-based clustering for >500 markers |
| M09 | COP Map | Add auto-pan on new P0 event | Requires SSE — Phase 2 |
| M10 | Auth | Add return-to parameter for login | After login, navigate back to triggering screen |
| M11 | Auth | Migrate tokens to Secure Storage | Add dependency if not present |
| M12 | Auth | Add Forgot Password flow | Backend endpoint must exist |
| M13 | Tracking | Add real-time SSE updates | Requires backend SSE endpoint |
| M14 | All | Audit exception messages: unify to Indonesian | All throw Exception messages should be in Indonesian |
| M15 | All | Add Skeleton loading for all async screens | Most screens have it, audit for gaps |

---

## LOW (Document Only)

| ID | Module | Problem | Notes |
|----|--------|---------|-------|
| L01 | Map | Hardcoded default coordinates (-6.2, 106.82) | Acceptable — Jakarta default center |
| L02 | Executive | SLA string categorization | Hardcoded 'Merah'/'Kuning'/'Hijau' — low risk |
| L03 | Auth | Biometric unlock | Phase 2 feature |
| L04 | Auth | Idle timeout 30 min | Phase 2 feature |
| L05 | Auth | Device UUID binding | Phase 2 feature |
| L06 | Tracking | SSE real-time updates | Phase 2 feature |

---

## ORDERED EXECUTION PLAN

```
Step 1: C01 — Remove hardcoded login credentials
Step 2: C02 — Remove magic PIN 123456
Step 3: H06 — Remove hardcoded executive demo data
Step 4: H01 — Add per-widget ErrorBoundary to Dashboard
Step 5: H03 — Add GPS mock detection to Report Wizard
Step 6: H04 — Add upload retry to Report Wizard
Step 7: H08 — Fix Navigator.pop in spatial_filter_bottom_sheet
Step 8: H09 — Fix Navigator.pop in report_wizard_screen
Step 9: H05 — Secure storage evaluation
Step 10: H07 — NavigationService exit documentation
```

---

## ACCEPTANCE CRITERIA

After all fixes:
- [ ] `flutter analyze` = 0 error, 0 new warning
- [ ] Zero hardcoded credentials in production code
- [ ] Zero magic bypass logic in production code
- [ ] Each dashboard widget has ErrorBoundary wrapper
- [ ] Report Wizard handles GPS mock, upload failure
- [ ] No `Navigator.pop()` without justification (dialogs only)
- [ ] Executive dashboard shows real data or empty state, not demo values
- [ ] All HTTP via Dio
- [ ] All native plugins via Runtime Services
