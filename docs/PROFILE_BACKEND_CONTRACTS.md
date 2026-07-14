# PROFILE COMMAND CENTER API CONTRACTS

All API endpoints reside under the `/api/profile/` namespace.

---

## 1. Consolidated Profile Data
* **Endpoint**: `GET /api/profile`
* **Headers**: `Authorization: Bearer <token>`
* **Description**: Returns all configuration structure and stats needed to build the profile command center in one single request (BFF Pattern).
* **Success Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "identity": {
      "name": "Yudi Asmui",
      "call_sign": "Cakra-01",
      "avatar_url": "https://example.com/avatar.jpg",
      "online_status": "siaga",
      "is_available": true
    },
    "active_mandate": {
      "mandate_id": 101,
      "role": "pcnu",
      "position": "Ketua NU Peduli Kudus",
      "organization": "PCNU Kudus",
      "location": "Jawa Tengah"
    },
    "statistics": [
      { "label": "Misi Aktif", "value": 12, "key": "missions" },
      { "label": "Jam Relawan", "value": 86, "key": "volunteer_hours" },
      { "label": "Laporan Valid", "value": 4, "key": "incidents" }
    ],
    "quick_actions": [
      {
        "id": "qa_report",
        "title": "Lapor",
        "action_type": "ACTION_REPORT",
        "badge_count": 0
      },
      {
        "id": "qa_map",
        "title": "Misi",
        "action_type": "ACTION_MAP",
        "badge_count": 2
      }
    ],
    "tasks": [
      {
        "id": "t1",
        "title": "Kaji Cepat Banjir Welahan",
        "category": "Pending Assessment",
        "due_date": "2026-07-09T12:00:00Z"
      }
    ],
    "organization": {
      "level": "PCNU",
      "name": "PCNU Kudus",
      "office_address": "Jl. Raya Kudus No. 12"
    },
    "resources": [
      { "id": "r1", "name": "Ambulans PCNU Kudus", "status": "ready" }
    ],
    "activities": [
      { "time": "2026-07-08T18:00:00Z", "action": "Mengajukan draft pleno" }
    ],
    "settings_config": {
      "pin_configured": true,
      "biometric_enabled": false,
      "offline_mode_ready": true
    }
  }
}
```

*Note: The previous individual endpoints (`/api/profile/tasks`, `/api/profile/statistics`, `/api/profile/timeline`) have been removed and consolidated into this single BFF endpoint.*
