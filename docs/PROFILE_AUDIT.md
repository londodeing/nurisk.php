# NURISK Profile & Authentication Audit

## 1. Executive Summary

This document audits the current authentication, authorization, sitemaps, and profile layout implementation of the NURISK Flutter mobile application and Laravel backend. The goal is to migrate from a static identity-card model to a **Dynamic Profile Command Center (Adaptive Profile)** driven entirely by a single Backend-For-Frontend (BFF) endpoint.

---

## 2. Authentication & Authorization State Analysis

### Current Front-end Mapping
- **Provider**: `AuthStateNotifier` in `auth_state_provider.dart` (riverpod `Notifier` pattern).
- **Persistent Storage**: `SharedPreferences` keys:
  - `auth_token`
  - `auth_user_id`
  - `auth_user_name`
  - `auth_active_role` (Lapis 1)
  - `auth_active_scope_id` (Lapis 3)
  - `auth_active_scope_type` (e.g., pcnu, pwnu)
  - `auth_active_jabatan` (Lapis 2)
- **State Properties**: `AuthState` contains only primitives mapping the active mandate context.

### Current Backend Endpoints
- **Stateless API Auth**: `/api/auth/login` and `/api/auth/register/{jenis}` mapping to `AuthenticationApiController`.
- **User Load Scope**: Backend returns `$user->load(['profil', 'peran'])` but misses preloading active positions (`jabatanPosisi`) and permission checks on login response, forcing client-side estimation.

---

## 3. Discovered Anti-patterns (Architecture Audits)

### Anti-pattern 1: Client-Side Role Hardcoding
* **Problem**: The UI dynamically hides or displays menu tiles inside `ProfileScreen` based on client-side parsing:
  ```dart
  final isGovernance = role == 'pcnu' || role == 'pwnu' || ...
  ```
* **Impact**: Adding a new role or editing permissions requires updating, recompiling, and redeploying the mobile app.
* **Resolution**: Replace client-side routing logic with a unified JSON configuration structure fetched from a single BFF API endpoint.

### Anti-pattern 2: Static Profile Tab Placeholder
* **Problem**: The profile menu was treated merely as a settings/logout screen, presenting only user data cards.
* **Impact**: Underutilization of real estate. Internal disaster coordinators, TRC dispatchers, and volunteers had no visual dashboard representing "What I must do today."
* **Resolution**: Reorganize the Profile tab as a **Personal Command Center** segmented into 10 key adaptive layers.

### Anti-pattern 3: Lack of Guest Fallback States
* **Problem**: Unauthenticated screens were not routed or configured correctly in the profile tab, prompting blank spaces or empty headers.
* **Impact**: Disjointed user experience for public guests.
* **Resolution**: Build a dedicated **Guest Profile** displaying registration pathways, donation metrics, and public FAQ widgets.

---

## 4. Remediation Steps

1. Establish `PROFILE_COMMAND_CENTER_CONSTITUTION.md` as the permanent guideline.
2. Build the unified BFF endpoint `GET /api/profile/home`.
3. Design and implement the Front-end Dynamic Menu Engine to decode and render the sections, badges, and quick action grids dynamically.
