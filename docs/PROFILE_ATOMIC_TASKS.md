# PROFILE COMMAND CENTER ATOMIC TASKS

This document details the backlog tasks for implementing the dynamic profile system.

---

## Task Backlog

- **PROFILE-001**: Audit Authentication, Mandates, Roles, and Permissions in both Flutter front-end and Laravel back-end models.
- **PROFILE-002**: Draft the permanent page guidelines in `PROFILE_COMMAND_CENTER_CONSTITUTION.md`.
- **PROFILE-003**: Create BFF API endpoint `GET /api/profile/home` consolidating Identity, Mandates, KPI stats, Quick Actions, Tasks, Resources, and Activities.
- **PROFILE-004**: Implement Dynamic Menu Engine in Flutter (parses JSON config lists to render customized action grids/sections dynamically without client role checks).
- **PROFILE-005**: Build the **Identity Card Section** (Avatar, Call Sign, Status indicator, Online availability switch).
- **PROFILE-006**: Build the **Mandate Card Section** (Displays active mandate, includes "Ganti Mandat" button triggering Mandate Picker Dialog).
- **PROFILE-007**: Build the **Personal KPI Section** (Stat card grid showing Missions, SPK approvals, and Drafts from BFF API).
- **PROFILE-008**: Build the **My Tasks & Action Items Section** (Render checklist representing active missions, signatures, and pending reports).
- **PROFILE-009**: Build the **Resources & Organization Context Section** (List assigned tactical assets e.g. vehicles, shelters, and PWNU/PCNU location info).
- **PROFILE-010**: Integrate settings controls, recent activity timeline widget, logout fallback action, and test end-to-end integration.
