# QUICK ACTION FRAMEWORK

> Standarisasi tombol aksi universal di semua dashboard.
> Setiap action = satu tombol yang langsung menuju fungsi operasional.
> Tidak perlu navigasi ke menu — action tersedia di dashboard.

---

## PRINSIP

1. **Satu klik, satu tujuan** — setiap tombol melakukan satu tindakan spesifik
2. **Konfirmasi untuk destruktif** — action berbahaya butuh modal konfirmasi
3. **Role-gated** — action hanya muncul untuk role yang berwenang
4. **Context-aware** — action menyesuaikan dengan data yang sedang dilihat
5. **Feedback segera** — setelah action, tampilkan toast notifikasi

---

## MASTER ACTION LIST

### Action Group: SITREP

| Action | Label | Route Name | Method | Permission | Icon | Color | Dashboard |
|---|---|---|---|---|---|---|---|
| Buat Sitrep | Buat Sitrep | `insiden.sitrep.create` | GET | pcnu | `bi-file-earmark-text` | `primary` | PCNU, POSKO |
| Lihat Sitrep | Sitrep Terbaru | `insiden.sitrep.show` | GET | all | `bi-file-earmark` | `outline-primary` | All |

### Action Group: POSKO

| Action | Label | Route Name | Method | Permission | Icon | Color | Dashboard |
|---|---|---|---|---|---|---|---|
| Aktivasi Posko | Aktivasi Posko | `insiden.posaju.create` | GET | pcnu | `bi-geo-alt` | `success` | PCNU |
| Detail Posko | Detail Posko | `insiden.posaju.show` | GET | all | `bi-geo` | `outline-success` | PCNU, POSKO |
| Minta Bantuan | Minta Bantuan | — (deferred) | — | posko | `bi-exclamation-triangle` | `warning` | POSKO |

### Action Group: PERSONEL

| Action | Label | Route Name | Method | Permission | Icon | Color | Dashboard |
|---|---|---|---|---|---|---|---|
| Assign Personel | Assign Personel | `insiden.penugasan.create` | GET | pcnu, pwnu | `bi-person-plus` | `info` | PCNU, PWNU |
| Check-in | Check-in | — (new) | POST | relawan | `bi-box-arrow-in-right` | `success` | RELAWAN |
| Check-out | Check-out | — (new) | POST | relawan | `bi-box-arrow-right` | `danger` | RELAWAN |
| Update Progres | Update Progres | `tugas.update` | PATCH | relawan, posko | `bi-arrow-up-circle` | `primary` | RELAWAN, POSKO |

### Action Group: GOVERNANCE

| Action | Label | Route Name | Method | Permission | Icon | Color | Dashboard |
|---|---|---|---|---|---|---|---|
| Approve Surat | Approve Surat | — (new api) | POST | pcnu, pwnu | `bi-check-circle` | `success` | PCNU, PWNU |
| Tolak Surat | Tolak Surat | — (new api) | POST | pcnu, pwnu | `bi-x-circle` | `danger` | PCNU, PWNU |
| Finalisasi Pleno | Finalisasi Pleno | `insiden.pleno.finalisasi` | PATCH | pcnu, pwnu | `bi-check2-square` | `success` | PCNU, PWNU |
| Tinjau Pleno | Tinjau Pleno | `insiden.pleno.tinjau` | PATCH | pcnu | `bi-eye` | `info` | PCNU |

### Action Group: KOMUNIKASI

| Action | Label | Route Name | Method | Permission | Icon | Color | Dashboard |
|---|---|---|---|---|---|---|---|
| Hubungi via WA | Hubungi | — | external | all | `bi-whatsapp` | `success` | All |
| Hubungi via Telp | Telepon | — | external | all | `bi-telephone` | `secondary` | All |

---

## TATA LETAK

Quick Actions muncul di dua tempat:

### 1. Action Bar (Header dashboard, sticky)

```
┌────────────────────────────────────────────────────────────┐
│ PWNU Dashboard                                    [+ Baru] │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌───────────────┐ │
│ │ Approve  │ │ Finalisasi│ │ Hubungi  │ │ Lihat Semua   │ │
│ │ Surat    │ │ Pleno    │ │ PCNU     │ │ Keputusan →   │ │
│ └──────────┘ └──────────┘ └──────────┘ └───────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### 2. Inline pada Decision Queue item

```
│ ● Sitrep overdue       │
│   INV-003              │
│   [Buat Sitrep]        │ ← Inline action
```

### 3. Inline pada tabel data

```
│ INV-003 │ Banjir │ Aktif │ 2 jam │ [Buat Sitrep] [Assign PIC] │
```

---

## IMPLEMENTASI

### File yang Dibuat

| File | Type |
|---|---|
| `resources/views/components/quick-action-bar.blade.php` | Blade component — action bar header |
| `resources/views/components/quick-action-button.blade.php` | Blade component — single button |
| `app/Services/CommandCenter/QuickActionService.php` | Service — resolve available actions per role + context |

### QuickActionService

```php
class QuickActionService
{
    /**
     * Get available quick actions for a user in current context.
     * Actions are filtered by role + scope.
     */
    public function getActions(AuthUser $user, ?string $context = null): array
    {
        // Returns array of action definitions
        // Each action: {label, route, method, icon, color, permission}
    }

    /**
     * Get inline actions for a specific entity (insiden, tugas, etc.)
     */
    public function getEntityActions(AuthUser $user, string $entityType, int $entityId): array
    {
        // Context-aware actions for a specific data row
    }
}
```

### API Endpoint

```
GET /api/cc/quick-actions?context=dashboard
GET /api/cc/quick-actions?context=insiden&id=3

Response: { actions: [...], context: "dashboard" }
```
