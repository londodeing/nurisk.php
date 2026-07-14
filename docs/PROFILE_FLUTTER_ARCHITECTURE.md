# FLUTTER PROFILE ATOMIC ARCHITECTURE

The profile feature follows clean architecture guidelines under `/lib/features/profile`.

---

## 1. Directory Structure

```
lib/features/profile/
├── data/
│   ├── datasources/
│   │   ├── profile_local_datasource.dart       # Drift/SQLite offline store
│   │   └── profile_remote_datasource.dart      # Dio API caller
│   ├── models/
│   │   └── profile_data_model.dart             # Parses GET /api/profile
│   └── repositories/
│       └── profile_repository_impl.dart
├── domain/
│   ├── entities/
│   │   └── profile_entity.dart
│   ├── repositories/
│   │   └── profile_repository.dart
│   └── usecases/
│       └── get_profile_usecase.dart
└── presentation/
    ├── notifiers/
    │   └── profile_notifier.dart               # Riverpod state management (Single Provider)
    ├── screens/
    │   └── profile_screen.dart                 # Main adaptive UI entrypoint
    └── widgets/
        ├── sections/
        │   ├── identity_section.dart
        │   ├── mandate_section.dart
        │   ├── kpi_grid_section.dart
        │   ├── quick_actions_section.dart
        │   ├── task_list_section.dart
        │   ├── organization_section.dart
        │   ├── resources_section.dart
        │   └── timeline_section.dart
        └── guest_profile_widget.dart           # UI fallback for unauthenticated
```

---

## 2. Riverpod State Management Flow (Single Aggregated Provider)

- **ProfileNotifier** (`Notifier<AsyncValue<ProfileData>>`):
  - Watches `authStateProvider` in `build()`.
  - If authenticated:
    - First emits the cached profile configuration from `profileLocalDatasource` immediately.
    - Triggers an asynchronous remote fetch from `profileRemoteDatasource` via `GET /api/profile`.
    - On success: saves response to Drift database and updates the state.
  - If unauthenticated:
    - Emits a custom state indicating `guest` mode (renders `GuestProfileWidget`).
- **Decoupled Actions Mapper**:
  - The API payload provides `action_type` strings (e.g., `ACTION_APPROVAL`).
  - `profile_screen.dart` uses a local mapper `_mapActionType()` to convert these types into Flutter-specific `IconData`, `Color`, and navigation routes via GoRouter.
- **Offline Caching Pattern**:
  - The local database caches the exact configuration JSON schema.
  - During boot, `profile_screen.dart` reads the cache (renders immediately).
  - The HTTP request happens in the background. Once the response lands, the UI updates seamlessly.
