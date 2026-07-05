# COMMAND CENTER VISION

**Date:** 2026-06-20  
**New Document** — Realtime Disaster Operations Command Center

---

## 1. Visi

Command Center adalah pusat kendali operasional NURISK yang menampilkan data realtime dari seluruh domain operasi. Bukan sekadar dashboard statis — ini adalah *live operations room* digital untuk PWNU, PCNU, Posko, dan Relawan.

### Prinsip Desain

| Prinsip | Deskripsi |
|---|---|
| **Live by default** | Setiap widget auto-refresh via SSE, bukan polling manual |
| **Role-based view** | PWNU melihat semua PCNU; PCNU melihat scope sendiri; Relawan melihat penugasan sendiri |
| **Drill-down** | Klik card → detail → action |
| **Mobile-first** | Responsif untuk smartphone relawan di lapangan |
| **Offline-resilient** | Menampilkan last-known-state saat koneksi terputus |

---

## 2. Dashboard Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                    COMMAND CENTER                           │
├──────────────┬──────────────┬──────────────┬────────────────┤
│ PWNU DASH    │ PCNU DASH    │ POSKO DASH   │ RELAWAN DASH  │
│ (Super Admin │ (PCNU Admin) │ (Komandan    │ (Relawan      │
│  & PWNU)     │              │  Posko)      │  Lapangan)    │
├──────────────┴──────────────┴──────────────┴────────────────┤
│                   SHARED WIDGETS                            │
│  ┌─────────┐ ┌──────────┐ ┌─────────┐ ┌────────────────┐  │
│  │ Live    │ │ Volunteer│ │ Logistik│ │ Escalation     │  │
│  │ Map     │ │ Status   │ │ Overview│ │ Monitor        │  │
│  └─────────┘ └──────────┘ └─────────┘ └────────────────┘  │
│  ┌─────────┐ ┌──────────┐ ┌─────────┐ ┌────────────────┐  │
│  │ Live    │ │ Surat    │ │ Pleno   │ │ Jurnal Terbaru │  │
│  │ Event   │ │ Pending  │ │ Status  │ │ Feed           │  │
│  │ Feed    │ │ Paraf    │ │         │ │                │  │
│  └─────────┘ └──────────┘ └─────────┘ └────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Dashboard PWNU

**Target Audience:** PWNU Super Admin, PWNU Leadership  
**Scope:** Semua insiden di seluruh PCNU Jawa Tengah

### Widgets

| Widget | Type | Data Source | Refresh |
|---|---|---|---|
| **Insiden Aktif** | Stat card | `v_command_center_summary` | SSE event |
| **Total Personel di Lapangan** | Stat card | `operasi_penugasan` count | SSE event |
| **Total Pos Aju Aktif** | Stat card | `operasi_posaju` count | SSE event |
| **PCNU Peringatan** | Alert list | Alerts per PCNU (stok kritis, gap) | SSE event |
| **Live Map** | Leaflet map | All active insiden | SSE event (new/update marker) |
| **Live Event Feed** | Scrolling feed | All P0 events across all PCNU | SSE event |
| **Kapasitas Operasi per PCNU** | Bar chart | Aggregated by PCNU | Every 5 min |
| **Eskalasi Pending** | Alert card | `eskalasi` with status not processed | SSE event |
| **Stok Kritis Lintas PCNU** | Table | `logistik_stok` below threshold | SSE event |
| **Surat Pending Tanda Tangan** | List | Surat with `siap_tanda_tangan` | SSE event |
| **Perbandingan Insiden per Bulan** | Time series | `operasi_insiden` by month | Every 15 min |

### Layout

```
┌──────────────────────────────────────────────────────────┐
│  PWNU COMMAND CENTER                          [PCNU: ALL]│
├─────────────┬──────────────┬──────────────┬──────────────┤
│  Insiden    │  Personel    │  Pos Aju     │  PCNU Alert  │
│  Aktif: 12  │  Di Lap: 342│  Aktif: 28   │  3 PCNU 🔴   │
├─────────────┴──────────────┴──────────────┴──────────────┤
│                                                          │
│              LIVE MAP (Leaflet.js)                        │
│  [Marker: ● Insiden Aktif ● Pos Aju ● Stok Kritis]      │
│                                                          │
├─────────────┬────────────────────────────────────────────┤
│  LIVE FEED  │  CAPACITY PER PCNU                          │
│  [08:30]    │  ████████ PCNU A (5 insiden)               │
│   Pos Aju   │  ██████   PCNU B (3 insiden)               │
│   dibuka    │  ████     PCNU C (2 insiden)               │
│   di PCNU B │  ██       PCNU D (1 insiden)               │
│             │                                            │
│  [08:25]    │  STOK KRITIS                                │
│   Eskalasi  │  🟡 PCNU A: Beras (20 kg remaining)        │
│   PCNU C    │  🔴 PCNU B: Obat (5 box remaining)         │
│  ...        │  🟡 PCNU D: Air (50 galon remaining)       │
└─────────────┴────────────────────────────────────────────┘
```

---

## 4. Dashboard PCNU

**Target Audience:** PCNU Admin, PCNU Leadership  
**Scope:** Insiden di PCNU sendiri

### Widgets

| Widget | Type | Data Source | Refresh |
|---|---|---|---|
| **Insiden Aktif** | Stat card | Per-PCNU filter | SSE event |
| **Personel di Lapangan** | Stat card | Per-insiden aggregation | SSE event |
| **Pos Aju Aktif** | Stat card | Per-PCNU filter | SSE event |
| **Live Map** | Leaflet map | Insiden in this PCNU only | SSE event |
| **Insiden List** | Sortable table | All insiden in this PCNU | SSE event |
| **Live Event Feed** | Scrolling feed | All P0 events in this PCNU | SSE event |
| **Stok Kritis per Gudang** | Alert table | Stok per gudang | SSE event |
| **Gap Kebutuhan Terbuka** | Kanban | `operasi_gap_kebutuhan` | SSE event |
| **Surat Pending Paraf** | Workflow list | Surat with active paraf | SSE event |
| **Klaster Progress** | Progress bars | Per-insiden klaster progress | SSE event |

### Layout

```
┌──────────────────────────────────────────────────────────┐
│  PCNU COMMAND CENTER — PCNU Jakarta Selatan              │
├─────────────┬──────────────┬──────────────┬──────────────┤
│  Insiden    │  Personel    │  Pos Aju     │  Stok Kritis │
│  Aktif: 4   │  Di Lap: 87 │  Aktif: 6    │  2 item 🔴   │
├─────────────┴──────────────┴──────────────┴──────────────┤
│                                                          │
│              LIVE MAP (PCNU Scope Only)                   │
│                                                          │
├──────────────────────────┬───────────────────────────────┤
│  INSIDEN LIST            │  KLASTER PROGRESS              │
│  ⬤ Banjir Jaksel [RESP] │  [Insiden: Banjir Jaksel]     │
│    └ Klaster: 6/6 aktif │  SAR     ████████░░ 80%       │
│    └ Personel: 23       │  Medis   ██████░░░░ 60%       │
│    └ Pos Aju: 2         │  Logistik████░░░░░░ 40%       │
│  ⬤ Angin Puyuh [RESP]  │  Dapur   ██░░░░░░░░ 20%       │
│    └ Klaster: 4/6 aktif │                                 │
│    └ Personel: 15       │  LIVE FEED                     │
│                         │  [09:15] Assessment baru       │
│  GAP KEBUTUHAN          │  [09:10] Relawan tiba di Posko │
│  [terbuka] 3 gap        │  [09:05] Stok beras diperbarui │
│  [diproses] 2 gap       │                                 │
└──────────────────────────┴───────────────────────────────┘
```

---

## 5. Dashboard Posko (Pos Aju)

**Target Audience:** Komandan Posko, Koordinator Klaster  
**Scope:** Satu insiden spesifik

### Widgets

| Widget | Type | Data Source | Refresh |
|---|---|---|---|
| **Insiden Info** | Header card | `operasi_insiden` detail | SSE event |
| **Pos Aju Info** | Card | `operasi_posaju` detail | SSE event |
| **Personel per Klaster** | Table | `operasi_mobilisasi_personil` grouped | SSE event |
| **Tugas per Klaster** | Kanban | `operasi_tugas` by status | SSE event |
| **Stok Pos Aju** | Table | `logistik_stok` filtered by posaju | SSE event |
| **Permintaan Logistik** | Kanban | `logistik_permintaan` by status | SSE event |
| **Sensus Pengungsian** | Chart | `pengungsian_sensus_harian` | Every 15 min |
| **Timeline Jurnal** | Timeline | `operasi_jurnal` for this insiden | SSE event |
| **Anggota Tim** | Team list | `relawan_penugasan` + `operasi_penugasan` | SSE event |
| **Shift Aktif** | Timeline | `relawan_shift` for today | Every 5 min |

---

## 6. Dashboard Relawan

**Target Audience:** Relawan di Lapangan  
**Scope:** Diri sendiri + insiden yang ditugaskan

### Widgets

| Widget | Type | Data Source | Refresh |
|---|---|---|---|
| **My Assignments** | Card list | `operasi_penugasan` for self | SSE event |
| **Current Shift** | Card | `relawan_shift` active | SSE event |
| **My Tasks** | Checklist | `operasi_tugas` assigned | SSE event |
| **Notifications** | Bell icon | P0 events mentioning self | SSE event |
| **Quick Actions** | Button list | Assessment, Sitrep, Laporan | N/A |
| **Insiden Map** | Mini Leaflet | Current incident location | SSE event |

### Layout (Mobile-First)

```
┌────────────────────────────────┐
│  Halo, Andi!      🔔 (2 baru) │
│  ⟵ My Dashboard               │
├────────────────────────────────┤
│  📋 PENUGASAN AKTIF            │
│  ┌──────────────────────────┐  │
│  │ Banjir Jakarta Selatan   │  │
│  │ Peran: TRC               │  │
│  │ Shift: 08:00-16:00 WIB   │  │
│  │ Status: ✅ Di Lokasi      │  │
│  └──────────────────────────┘  │
├────────────────────────────────┤
│  📋 TUGAS SAYA                 │
│  ☐ Assessment dampak RW 01    │
│  ☑ Sitrep harian              │
│  ☐ Cek kebutuhan medis Posko  │
├────────────────────────────────┤
│  ⚡ AKSI CEPAT                  │
│  [Buat Assessment] [Lapor]    │
├────────────────────────────────┤
│  🗺️ MAP INSIDEN                │
│  [Mini Map with Current Pos]  │
└────────────────────────────────┘
```

---

## 7. Live Map Specification

### Layer Control

| Layer | Visible To | Data Source | Marker Style |
|---|---|---|---|
| Insiden Aktif | All | `operasi_insiden` (status != selesai) | Circle with icon per jenis bencana |
| Pos Aju Aktif | All | `operasi_posaju` (status = aktif) | Square with flag icon |
| Lokasi Assessment | PCNU, PWNU | `assessment_utama` with coordinates | Star icon |
| Titik Pengungsian | PCNU, PWNU | `operasi_pos_pengungsian` | House icon |
| Stok Kritis | PCNU, PWNU | `logistik_stok` below threshold | Warning triangle |
| Personel Location | Komandan | `operasi_mobilisasi_personil` with GPS | User dot (real-time) |

### Interaction

| Action | Result |
|---|---|
| Click marker | Popup with summary → "Lihat Detail" link |
| Cluster click | Zoom in to show individual markers |
| Filter by status | Dropdown: Semua / Respon / Pemulihan |
| Filter by PCNU | Dropdown (PWNU only) |
| Layer toggle | Checkbox per layer |
| Auto-refresh | SSE event adds/updates/removes markers |

### Technical Specs

| Parameter | Value |
|---|---|
| Library | Leaflet.js 1.9 + Leaflet.markercluster |
| Max markers | 500 visible |
| Cluster radius | 50 pixels |
| Auto-pan | On new P0 event in viewport |
| Tile provider | OpenStreetMap / PMTiles (offline-capable) |

---

## 8. Live Event Feed

### Feed Design

- Scrolling list, newest at top
- Max 50 items visible; older items archived
- Color-coded by domain:
  - 🔴 **Insiden** — P0
  - 🟡 **Assessment/Sitrep** — P1
  - 🟢 **Mobilisasi** — P1
  - 🔵 **Logistik** — P1
  - 🟣 **Governance** — P0
- Click event → navigate to detail page
- Sound alert for P0 events (toggleable)

### Event Display

```
[09:15:23] 🟣 Pleno difinalisasi — Insiden Banjir Jakarta Selatan
           Keputusan: Perpanjangan operasi 7 hari
           
[09:12:01] 🔴 Eskalasi — Insiden Angin Puyuh Cilacap
           Level: PCNU → PWNU

[09:10:45] 🟢 Relawan tiba di Posko — Posko Utama Cilacap
           Andi Pratama (TRC)

[09:08:12] 🟡 Assessment baru — Insiden Banjir Jakarta Selatan
           Korban: 15 meninggal, 200 mengungsi
```

---

## 9. Implementation Plan

| # | Component | Effort | Phase | Depends On |
|---|---|---|---|---|
| 1 | PWNU Dashboard v1 (summary + map) | 3 days | Phase 0 | SSE events |
| 2 | PCNU Dashboard v1 | 2 days | Phase 0 | SSE events |
| 3 | Live Event Feed | 1 day | Phase 0 | SSE events |
| 4 | Live Map with clustering | 2 days | Phase 0 | — |
| 5 | Posko Dashboard | 3 days | Phase 1 | Domain APIs |
| 6 | Relawan Dashboard (mobile) | 2 days | Phase 1 | Flutter SDK |
| 7 | Widget drill-down | 2 days | Phase 1 | Phase 0 widgets |
| 8 | Layer control + filter | 1 day | Phase 1 | Phase 0 map |
| 9 | Personel live location | 3 days | Phase 2 | GPS integration |
| 10 | Notification system | 3 days | Phase 3 | WebSocket upgrade |
