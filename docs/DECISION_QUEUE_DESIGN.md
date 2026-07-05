# DECISION QUEUE DESIGN

> Widget paling penting di Command Center.
> Decision Queue = daftar item yang membutuhkan tindakan manusia.
> Setiap role memiliki Decision Queue yang berbeda.

---

## ARSITEKTUR

Decision Queue adalah komponen Blade reusable yang dirender di sidebar kanan setiap dashboard.

```
┌─────────────────────┐
│ DECISION QUEUE      │ ← Header
├─────────────────────┤
│ ● Sitrep overdue    │ ← Item with severity dot
│   Insiden #INV-003  │
│   2 jam tanpa update│
│   [Buat Sitrep]     │ ← Inline quick action
├─────────────────────┤
│ ● Surat menunggu TT │
│   SK-024/PWNU       │
│   [Approve] [Tolak] │
├─────────────────────┤
│ ○ Pleno ditinjau    │ ← Lower severity (empty dot)
│   Pleno #PL-005     │
│   [Finalisasi]      │
└─────────────────────┘
```

### Data Structure (JSON dari API)

```json
{
  "queue": [
    {
      "id": "dq-001",
      "severity": "critical",
      "kategori": "sitrep_overdue",
      "judul": "Sitrep overdue: INV-003",
      "deskripsi": "Insiden Banjir Jakarta Pusat — 2 jam tanpa update sitrep",
      "waktu": "2026-06-20T14:30:00Z",
      "tautan": "/insiden/3",
      "aksi_tersedia": [
        {"label": "Buat Sitrep", "route": "/insiden/3/sitrep/create", "method": "GET", "icon": "bi-file-earmark-text", "color": "primary"}
      ]
    }
  ]
}
```

### Mekanisme

- Polling: 30s (red) — karena item decision perlu muncul cepat
- Sumber: synthesized dari multiple tabel (tidak ada satu tabel "decision queue")
- Service: `DecisionQueueService::getQueueForRole(AuthUser $user): array`
- Filter: otomatis berdasarkan role + scope user
- Maksimal: 10 item (jika lebih dari 10, link ke halaman "Semua Keputusan")

---

## PWNU — DECISION QUEUE

### Sumber Data

| Item | Sumber | Query Filter | Severity |
|---|---|---|---|
| Surat menunggu tanda tangan | `operasi_surat_keluar` | `status_surat = 'siap_tanda_tangan'` AND `id_pcnu IN (accessiblePcnuIds)` | **critical** |
| Pleno menunggu finalisasi | `operasi_pleno` | `status_pleno = 'ditinjau'` AND `id_insiden.pcnu IN (accessiblePcnuIds)` | **critical** |
| Eskalasi belum direspon | `operasi_eskalasi` | `status_eskalasi = 'dikirim'` (perlu cek kolom aktual) | **critical** |
| PCNU sitrep overdue | `operasi_sitrep` | `MAX(waktu_sitrep) < now()-24h` GROUP BY `id_pcnu` | high |
| Kebutuhan relawan kritis | `relawan_kebutuhan` | `status_rekrutmen = 'dibuka'` AND `dibuat_pada < now()-72h` | high |

### Empty State

```
┌─────────────────────┐
│ DECISION QUEUE      │
├─────────────────────┤
│ ✓ Tidak ada          │
│   keputusan yang    │
│   menunggu          │
│                     │
│ Last checked: 30s   │
└─────────────────────┘
```

---

## PCNU — DECISION QUEUE

### Sumber Data

| Item | Sumber | Query Filter | Severity |
|---|---|---|---|
| Sitrep perlu dibuat | `operasi_insiden` LEFT JOIN `operasi_sitrep` | Insiden aktif tanpa sitrep >12h | **critical** |
| Insiden tanpa PIC | `operasi_insiden` LEFT JOIN `operasi_penugasan` | Insiden tanpa penugasan dengan peran_otoritas = 'pic' | **critical** |
| Posko tanpa PJ | `operasi_posaju` | `pj_posaju IS NULL` AND `waktu_ditutup IS NULL` | high |
| Tugas belum dimulai | `operasi_tugas` | `status_tugas = 'rencana'` AND `dibuat_pada < now()-24h` | high |
| Pleno perlu ditinjau | `operasi_pleno` | `status_pleno = 'draft'` AND `dibuat_pada < now()-24h` | normal |
| Surat perlu review | `operasi_surat_keluar` | `status_surat = 'review_paraf'` | normal |

### Contoh Render

```
┌─────────────────────┐
│ DECISION QUEUE      │
├─────────────────────┤
│ ● Sitrep overdue    │
│   INV-003: 14 jam   │
│   [Buat Sitrep]     │
├─────────────────────┤
│ ● PIC belum diassign│
│   INV-007: 2 hari   │
│   [Assign PIC]      │
├─────────────────────┤
│ ○ Tugas rencana     │
│   TGS-012: 2 hari   │
│   [Assign Personel] │
└─────────────────────┘
```

---

## POSKO — DECISION QUEUE

### Sumber Data

| Item | Sumber | Query Filter | Severity |
|---|---|---|---|
| Tugas overdue | `operasi_tugas` | `status_tugas IN ('rencana','tertunda')` AND `dibuat_pada < now()-24h` AND `id_posaju = {posko_id}` | **critical** |
| Personel minimum | `operasi_penugasan` WHERE check-in hari ini | COUNT < 3 (hardcode threshold) | **critical** |
| Kebutuhan kritis | `relawan_kebutuhan` | `status_rekrutmen = 'dibuka'` AND `id_posaju = {posko_id}` | high |
| Tugas baru | `operasi_tugas` | `status_tugas = 'rencana'` AND `dibuat_pada > now()-24h` AND `id_posaju = {posko_id}` | normal |
| Bantuan belum direspon | — | DEFERRED ke Phase 2 | — |

---

## RELAWAN — DECISION QUEUE

### Sumber Data

| Item | Sumber | Query Filter | Severity |
|---|---|---|---|
| Tugas baru ditugaskan | `operasi_tugas` | `status_tugas = 'rencana'` AND `ditugaskan_ke = {user_id}` | **critical** |
| Tugas mendekati deadline | `operasi_tugas` | `status_tugas = 'berjalan'` AND `dibuat_pada < now()-48h` AND `ditugaskan_ke = {user_id}` | high |
| Perubahan shift | — | DEFERRED ke Phase 2 | — |
| Pergantian lokasi | — | DEFERRED ke Phase 2 | — |

---

## IMPLEMENTASI

### File yang Dibuat

| File | Type |
|---|---|
| `app/Services/CommandCenter/DecisionQueueService.php` | Service — query logic per role |
| `app/Http/Resources/CommandCenter/DecisionQueueResource.php` | Resource |
| `resources/views/components/decision-queue.blade.php` | Blade component (reusable) |
| `resources/views/components/decision-queue-item.blade.php` | Blade component (single item) |

### Service Method Signature

```php
class DecisionQueueService
{
    public function __construct(
        private AuthorizationContextService $authCtx,
        private SitrepService $sitrepService,
        // ... other services
    ) {}

    /**
     * @return array<int, array{id:string, severity:string, kategori:string, judul:string, deskripsi:string, waktu:string, tautan:string, aksi_tersedia:array}>
     */
    public function getQueue(AuthUser $user): array
    {
        return match ($user->peran->nama_peran) {
            'super_admin', 'pwnu' => $this->getPwnuQueue($user),
            'pcnu' => $this->getPcnuQueue($user),
            'relawan' => $this->getRelawanQueue($user),
            default => [],
        };
    }
}
```

### API Endpoint

```
GET /api/cc/decision-queue
Response: { queue: [...], last_checked: "2026-06-20T14:30:00Z" }
Middleware: auth
Polling: 30s (red)
```
