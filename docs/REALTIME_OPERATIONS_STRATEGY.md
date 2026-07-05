# REALTIME OPERATIONS STRATEGY

**Date:** 2026-06-20  
**New Document** — Created for Realtime Disaster Operations Platform

---

## 1. Arsitektur Event

### 1.1 Teknologi

| Layer | Teknologi | Rationale |
|---|---|---|
| Event System | Laravel Events + Broadcasting | Framework-native, no additional library |
| Broadcast Driver | Pusher / Laravel Reverb / Soketi | Pilih berdasarkan skala: Pusher (managed), Reverb (self-hosted Laravel), Soketi (self-hosted Node.js) |
| Cache + Pub/Sub | Redis | Required for cross-instance broadcast |
| Transport | SSE (Server-Sent Events) untuk Phase 0-1 | Lebih sederhana dari WebSocket, built-in HTTP/2 support |
| Transport | WebSocket untuk Phase 2+ | Full duplex untuk command center interaktif |
| Target | Phase 0: SSE via Redis pub/sub | Phase 1: Upgrade ke WebSocket via Reverb |

### 1.2 Event Flow

```
┌──────────┐    ┌──────────────┐    ┌───────────┐
│ Laravel  │───►│ Redis Pub/Sub│───►│ Reverb/   │
│ Event    │    │              │    │ Soketi    │
│ Dispatch │    │              │    │           │
└──────────┘    └──────────────┘    └─────┬─────┘
                                          │
                ┌─────────────────────────┼─────────────────────┐
                │                         │                     │
          ┌─────▼─────┐           ┌───────▼───────┐    ┌───────▼───────┐
          │ Flutter    │           │ Browser Admin │    │ Grafana /     │
          │ SSE Client │           │ SSE Client    │    │ Monitoring    │
          └───────────┘           └───────────────┘    └───────────────┘
```

### 1.3 Event Schema

```json
{
  "event": "insiden.created",
  "version": "1.0",
  "timestamp": "2026-06-20T10:30:00+07:00",
  "scope": {
    "type": "pcnu",
    "id": 3321
  },
  "data": {
    "id_insiden": 1,
    "uuid": "abc-123",
    "judul": "Banjir Jakarta Selatan",
    "status": "draft",
    "lokasi": { "lat": -6.2, "lng": 106.8 }
  },
  "actor": {
    "id_pengguna": 42,
    "nama": "Admin PCNU Jakarta"
  }
}
```

### 1.4 Broadcast Channels

| Channel | Scope | Audience | Event Types |
|---|---|---|---|
| `private-scope.pcnu.{id}` | PCNU | All users in PCNU | All events within PCNU scope |
| `private-scope.pwnu` | PWNU | PWNU super-admins | All events across all PCNU |
| `private-insiden.{id}` | Insiden | Users assigned to insiden | Insiden-specific events |
| `private-user.{id}` | User | Single user | Personal notifications |
| `public-map` | Public | Unauthenticated | Public incident updates |

---

## 2. Realtime Event Matrix

### 2.1 Insiden Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `insiden.created` | `InsidenService::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU admins, PWNU | P0 |
| `insiden.status.changed` | `InsidenService::transisiStatus()` | `private-scope.pcnu.{id_pcnu}` | All PCNU users, PWNU | P0 |
| `insiden.updated` | `InsidenController::update()` | `private-scope.pcnu.{id_pcnu}` | All PCNU users, PWNU | P1 |
| `insiden.locked` | `InsidenService::transisiStatus('selesai')` | `private-scope.pcnu.{id_pcnu}` | All PCNU users, PWNU | P1 |
| `laporan_kejadian.created` | `LaporanKejadianController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU operators | P1 |

### 2.2 Assessment Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `assessment.created` | `AssessmentController::store()` | `private-insiden.{id}` | TRC, Komandan Insiden, PCNU | P0 |
| `assessment.newest` | Assessment trigger `is_latest` | `private-insiden.{id}` | All assigned users | P2 |

### 2.3 Sitrep Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `sitrep.finalized` | `SitrepService::finalisasi()` | `private-insiden.{id}`, `private-scope.pcnu.{id_pcnu}` | PCNU, PWNU, Komandan | P0 |
| `sitrep.created` | `SitrepController::store()` | `private-insiden.{id}` | All assigned users | P2 |

### 2.4 Mobilisasi & Penugasan Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `penugasan.created` | `PenugasanController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU, assigned user | P0 |
| `penugasan.revoked` | `PenugasanController::destroy()` | `private-insiden.{id}` | Komandan, PCNU | P0 |
| `mobilisasi.status.changed` | Mobilisasi state machine (depart/arrive/finish) | `private-insiden.{id}` | Komandan, Koordinator Klaster | P0 |
| `mobilisasi.created` | `MobilisasiController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU, PWNU | P1 |
| `klaster.progress.updated` | `KlasterController::update()` | `private-insiden.{id}` | Koordinator Klaster, Komandan | P1 |

### 2.5 Pos Aju Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `posaju.created` | `PosajuController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU, PWNU | P1 |
| `posaju.status.changed` | `PosajuController::tutup()` | `private-insiden.{id}` | Komandan, PCNU | P1 |
| `posaju.komandan.changed` | `PosajuController::gantiKomandan()` | `private-insiden.{id}` | All assigned users | P2 |

### 2.6 Logistik Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `logistik.stok.critical` | Trigger when `jumlah_tersedia < threshold` | `private-insiden.{id}`, `private-scope.pcnu.{id_pcnu}` | Logistik, Komandan, PCNU | P0 |
| `logistik.permintaan.created` | `PermintaanController::store()` | `private-scope.pcnu.{id_pcnu}` | Logistik gudang | P1 |
| `logistik.permintaan.status.changed` | Permintaan workflow | `private-insiden.{id}` | Pemohon, Logistik | P1 |
| `logistik.mutasi.recorded` | `LogistikMutasiService::catat()` | `private-scope.pcnu.{id_pcnu}` | Logistik, auditor | P2 |

### 2.7 Surat & Governance Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `surat.finalized` | `SuratService::finalisasi()` | `private-scope.pcnu.{id_pcnu}`, `private-user.{penandatangan}` | Penandatangan, PCNU | P0 |
| `surat.paraf.activated` | `SuratService::prosesParaf()` — paraf berikutnya aktif | `private-user.{paraf_user}` | Paraf user (next in chain) | P0 |
| `surat.paraf.rejected` | `SuratService::prosesParaf()` — ditolak | `private-user.{pembuat_surat}` | Pembuat surat | P1 |
| `pleno.finalized` | `PlanoService::finalisasi()` | `private-scope.pcnu.{id_pcnu}` | PCNU, PWNU | P0 |
| `eskalasi.created` | `EskalasiService::buat()` | `private-scope.pwnu`, `private-scope.pcnu.{id_pcnu}` | PWNU, PCNU tereskalasi | P0 |
| `aktivasi.created` | `AktivasiController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU, PWNU | P1 |

### 2.8 Feedback & Evaluation Events

| Event | Source | Broadcast Channel | Target Audience | Criticality |
|---|---|---|---|---|
| `feedback.created` | `FeedbackController::store()` | `private-insiden.{id}` | Komandan, Koordinator Klaster | P2 |
| `gap.created` | `GapKebutuhanController::store()` | `private-scope.pcnu.{id_pcnu}` | PCNU, Logistik | P2 |
| `gap.status.changed` | Gap workflow | `private-scope.pcnu.{id_pcnu}` | PCNU, domain PIC | P2 |

---

## 3. Command Center Events

Command Center subscribes to all events for realtime dashboard updates:

| Dashboard Widget | Subscribes To | Update Mechanism |
|---|---|---|
| Insiden Aktif Counter | `insiden.created`, `insiden.status.changed` | Increment/decrement |
| Live Map Markers | `insiden.created`, `insiden.updated`, `insiden.status.changed` | Add/update/remove marker |
| Recent Events Feed | All P0 events | Prepend to feed (max 50 visible) |
| Volunteer Status | `mobilisasi.status.changed` | Update status badge |
| Logistik Critical Alerts | `logistik.stok.critical` | Flash alert + sound |
| Surat Pending Paraf | `surat.paraf.activated` | Update paraf queue |
| Pleno Final Status | `pleno.finalized` | Update status badge |
| Eskalasi Alert | `eskalasi.created` | High-priority flash alert |

---

## 4. Event Prioritization & Throttling

| Priority | Events | Delivery SLA | Throttle |
|---|---|---|---|
| P0 | insiden.created/changed, penugasan.created/revoed, mobilisasi.status, stok.critical, surat.finalized, pleno.finalized, eskalasi.created | < 1s | Unlimited |
| P1 | insiden.updated, posaju.created, permintaan, surat.paraf | < 3s | Max 100/min per channel |
| P2 | assessment.newest, sitrep.created, klaster.progress, feedback, gap | < 10s | Max 30/min per channel |
| P3 | system health, user online status | < 30s | Max 10/min per channel |

---

## 5. Security

| Threat | Mitigation |
|---|---|
| Unauthorized event subscription | All channels are private (need Sanctum auth + scope verification) |
| Event injection | Server-authoritative — client publishes via API, events are generated server-side |
| Replay attack | Each event has unique `id` — client deduplicates by `id` |
| Rate limit abuse | Per-channel throttle per user (10 msg/s max) |
| Data leak via channel ID | Use UUIDs not sequential IDs for scope references |

---

## 6. Implementation Roadmap

| Phase | Component | Effort | Depends On |
|---|---|---|---|
| 0 | Event classes defined | 1 day | — |
| 0 | SSE endpoint implementation | 1 day | — |
| 0 | Redis pub/sub config | 0.5 day | Redis installed |
| 0 | Basic event broadcasting (insiden, penugasan) | 2 days | Event classes |
| 1 | Full event integration (all domains) | 3 days | Phase 0 |
| 1 | SSE auth + scope filtering | 1 day | Phase 0 |
| 1 | Command Center event subscription | 2 days | Phase 0 |
| 2 | WebSocket upgrade (Reverb) | 3 days | Phase 1 |
| 2 | Event persistence + replay | 2 days | Phase 1 |
| 3 | Notification system (push) | 3 days | Phase 2 |
