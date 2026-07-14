# LIFECYCLE AUDIT — NURISK Flutter + Android

---

## CURRENT STATE

```
┌─────────────────────────────────────────────────────────────────┐
│                    CURRENT LIFECYCLE STATE                       │
│                                                                  │
│  Flutter App                                                     │
│  ├── WidgetsBindingObserver: ❌ NOT IMPLEMENTED                 │
│  ├── didChangeAppLifecycleState: ❌ NOT IMPLEMENTED             │
│  │                                                                │
│  ├── ReportWizard:                                              │
│  │   ├── initState: ✅ TextEditingControllers created           │
│  │   ├── dispose: ✅ TextEditingControllers disposed            │
│  │   ├── Camera opened: ❌ State NOT preserved on return        │
│  │   └── App backgrounded: ❌ No lifecycle handling             │
│  │                                                                │
│  ├── CopMapScreen:                                              │
│  │   ├── initState: ❌ No MapLibreMapController init            │
│  │   ├── dispose: ❌ MapLibreMapController NOT disposed         │
│  │   └── Tab switched: ❌ MapLibreMapController NO lifecycle    │
│  │                                                                │
│  ├── WarningNotifier:                                           │
│  │   ├── Timer.periodic: ✅ Created                            │
│  │   └── Timer cancel: ❌ NEVER called (no autoDispose)        │
│  │                                                                │
│  └── Riverpod Providers:                                        │
│      ├── autoDispose: ❌ NOT USED ANYWHERE                     │
│      └── ref.onDispose: ⚠️ Declared but never triggered        │
└─────────────────────────────────────────────────────────────────┘
```

---

## TARGET STATE

```
┌─────────────────────────────────────────────────────────────────┐
│                    TARGET LIFECYCLE STATE                        │
│                                                                  │
│  Flutter App (NuriskApp)                                         │
│  ├── WidgetsBindingObserver: ✅ MIXIN IMPLEMENTED               │
│  ├── didChangeAppLifecycleState:                                │
│  │   ├── AppLifecycleState.paused:                              │
│  │   │   ├── Save wizard draft to Riverpod                     │
│  │   │   ├── Cancel GPS polling                                 │
│  │   │   └── Mark map as inactive                               │
│  │   ├── AppLifecycleState.resumed:                             │
│  │   │   ├── Restore wizard draft                               │
│  │   │   ├── Resume GPS if needed                               │
│  │   │   └── Restore map state                                  │
│  │   └── AppLifecycleState.detached:                            │
│  │       └── Clean up all resources                             │
│  │                                                                │
│  ├── ReportWizard:                                              │
│  │   ├── initState: ✅ Create controllers + WidgetsBinding      │
│  │   ├── dispose: ✅ Dispose controllers + remove binding       │
│  │   └── Background restoration: ✅ via Riverpod                │
│  │                                                                │
│  ├── CopMapScreen:                                              │
│  │   ├── initState: ✅ Setup                                    │
│  │   ├── dispose: ✅ mapController.dispose()                    │
│  │   └── Background: ✅ Pause map rendering                     │
│  │                                                                │
│  ├── WarningNotifier:                                           │
│  │   ├── autoDispose: ✅ Added                                  │
│  │   └── Timer: ✅ Properly canceled on dispose                 │
│  │                                                                │
│  ├── MapLibreMap:                                               │
│  │   ├── onMapCreated: ✅ Store controller                      │
│  │   ├── Dispose: ✅ controller.dispose()                       │
│  │   └── Background: ✅ Pause rendering                          │
│  │                                                                │
│  └── Riverpod:                                                  │
│      ├── autoDispose: ✅ ON SHORT-LIVED PROVIDERS               │
│      └── ref.onDispose: ✅ ACTUALLY CALLED                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## ANDROID ACTIVITY LIFECYCLE vs FLUTTER

```
┌──────────────────────────────────────────────────────────────────┐
│  ANDROID ACTIVITY              FLUTTER ENGINE                    │
│                                                                    │
│  onCreate()                                                       │
│  │                                                                │
│  ├── FlutterMain.startInitialization()                            │
│  │                                                                │
│  onStart()                                                        │
│  │                                                                │
│  onResume()                                                        │
│  │               ───▶ AppLifecycleState.resumed                   │
│  │                    → WidgetsBindingObserver.didChangeAppLifecy │
│  │                      cleState(AppLifecycleState.resumed)       │
│  │                                                                │
│  onPause()                                                         │
│  │               ───▶ AppLifecycleState.hidden / .inactive        │
│  │                    → Save state                                │
│  │                                                                │
│  onStop()                                                          │
│  │               ───▶ AppLifecycleState.paused                    │
│  │                    → Cancel timers, pause GPS                  │
│  │                                                                │
│  onSaveInstanceState()                                            │
│  │  → Bundle: save navigation state, form data                   │
│  │                                                                │
│  onDestroy()                                                       │
│  │               ───▶ AppLifecycleState.detached                  │
│  │                    → Dispose all resources                     │
│  └────────────────────────────────────────────────────────────────│
│                                                                   │
│  CRITICAL GAP: Currently NURISK does NOT implement               │
│  WidgetsBindingObserver. The app does not know when it goes       │
│  to background or foreground. This causes:                        │
│  1. Camera/GPS state lost on return                               │
│  2. Map controller not paused in background                       │
│  3. Timers keep running while minimized                           │
│  4. Activity recreation causes full app restart                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## COMPONENT-BY-COMPONENT LIFECYCLE AUDIT

### ReportWizardScreen

| Lifecycle Event | Current | Required |
|----------------|---------|----------|
| `initState` | Creates 4 TextEditingControllers | ✅ OK |
| `dispose` | Disposes 4 TextEditingControllers | ✅ OK |
| Camera opens (push native) | State lost on return | ❌ Save to Riverpod before push |
| App backgrounded | No handler | ❌ Save draft to Riverpod |
| `mounted` check after async | ✅ Done at lines 282, 311 | ✅ OK |

**Fix**: Save form state to Riverpod provider before opening camera; restore on return.

### CopMapScreen

| Lifecycle Event | Current | Required |
|----------------|---------|----------|
| `initState` | Nothing | ❌ Should prepare state |
| `dispose` | Nothing | ❌ Must dispose `mapController` |
| `_onMapCreated` | Sets `mapController` | ✅ OK |
| App backgrounded | No handler | ❌ Pause map rendering |
| Tab switched | No handler | ❌ No lifecycle on tab switch |

**Fix**: 
```dart
@override
void dispose() {
  mapController?.dispose();
  super.dispose();
}
```

### WarningNotifier

| Lifecycle Event | Current | Required |
|----------------|---------|----------|
| Provider build | Creates 30s Timer.periodic | ✅ But needs autoDispose |
| Provider dispose | `_pollingTimer?.cancel()` declared but never called | ❌ onDispose never triggered |

**Fix**: Add `.autoDispose` to the provider:
```dart
final warningProvider = NotifierProvider.autoDispose<WarningNotifier, WarningState>(...);
```

### All Riverpod Providers

| Pattern | Usage | Assessment |
|---------|-------|------------|
| `NotifierProvider` | Used everywhere | ❌ Overuse without autoDispose |
| `autoDispose` | Not used anywhere | ❌ Should be used for short-lived UI state |
| `ref.onDispose` | Declared but never called in WarningNotifier | ❌ Misleading pattern |

**Fix**: Audit all providers. Add `.autoDispose` where appropriate (warning, map layers, wizard form state).

---

## LIFECYCLE ACTION PLAN

| # | Component | Action | Priority |
|---|-----------|--------|----------|
| 01 | `NuriskApp` | Add `WidgetsBindingObserver` mixin | HIGH |
| 02 | `NuriskApp` | Implement `didChangeAppLifecycleState` | HIGH |
| 03 | `CopMapScreen` | Add `dispose()` → `mapController?.dispose()` | HIGH |
| 04 | `WarningNotifier` | Change to `NotifierProvider.autoDispose` | MEDIUM |
| 05 | `ReportWizardScreen` | Save state to Riverpod before native calls | MEDIUM |
| 06 | All providers | Audit and add autoDispose where appropriate | MEDIUM |
| 07 | `CopMapScreen` | Add lifecycle handling (pause/resume) | MEDIUM |
