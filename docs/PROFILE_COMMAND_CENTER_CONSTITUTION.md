# PROFILE COMMAND CENTER CONSTITUTION

## 1. Philosophy
The Profile Screen is not a static identity card displaying only Name, Email, and a Logout button. It is a **Personal Command Center** representing the paradigm of **"Saya hari ini"** (My status today). It dynamically answers the following:
- Who am I active as today (Active Mandate, Position, Organization).
- What tasks are assigned to me.
- What approvals, meetings, or decisions are waiting for my action.
- What resources (vehicles, warehouses, assets) are allocated to me.

---

## 2. The Ten Profile Layers (Sections)

1. **Identity Card**: Large avatar, Call Sign, Online Status, Active Role, and Availability switch.
2. **Mandate Card**: Highlights the currently active mandate. Includes a "Ganti Mandat" button triggering the mandate picker dialog.
3. **Personal KPI**: Grid displaying dynamic counters (Missions, SPK approvals, pending drafts) fetched from the backend.
4. **Quick Action Grid**: Dynamic list of tiles tailored specifically to the active mandate's permission scope.
5. **My Tasks**: Lists assigned pending actions (e.g. pending assessments, signatures, reports).
6. **Organization Context**: Detailed hierarchy context (PWNU / PCNU / MWC / Posko).
7. **Allocated Resources**: Lists vehicles, shelters, and items assigned directly to the user.
8. **Recent Activity Timeline**: A chronological history of actions completed by the user.
9. **Settings**: Configurations (Notifications, Offline storage, PIN/Biometric authentication, themes).
10. **Logout Action**: Positioned securely at the bottom of the scroll view.

---

## 3. Zero Client-Side Role-Check Constitution
- **Strict Rule**: Flutter codebases MUST NOT contain logical routing blocks based on hardcoded roles:
  ```dart
  // STRICTLY PROHIBITED
  if (user.role == 'ketua') { ... }
  ```
- **Dynamic Render Architecture**: The entire list of widgets, buttons, icons, colors, and badge counts must be parsed from a single JSON configuration returned by the Backend-For-Frontend (BFF) API.
- If a permission is revoked or a menu added, only the backend JSON config updates.

---

## 4. Performance & Offline SLAs
- **Single request boundary**: The page must mount and fetch its entire data package using exactly one HTTP request: `GET /api/profile/home`.
- **Loading Target**: Startup Profile loading must complete in `<200 ms`.
- **Offline Resiliency**: Cache the configuration JSON in local SQLite/Drift databases. If the network is down, the screen immediately renders the cached state with an offline indicator.
- **Riverpod Lazy Loading**: Providers must lazily load heavy segments and support pull-to-refresh.
