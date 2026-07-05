# DATA FRESHNESS STRATEGY

> Setiap angka di dashboard harus memiliki indikator kesegaran data.
> Tanpa freshness indicator, operator tidak bisa membedakan data real-time dan data basi.

---

## FRESHNESS KATEGORI

| Warna | Rentang | Makna | Tindakan |
|---|---|---|---|
| 🟢 Hijau | <15 menit | Data segar | Dapat digunakan untuk keputusan |
| 🟡 Kuning | 15–60 menit | Data cukup segar | Waspada — cek timestamp sebelum keputusan besar |
| 🔴 Merah | >60 menit | Data basi | JANGAN gunakan untuk keputusan tanpa konfirmasi lapangan |

---

## FRESHNESS PER WIDGET

### PWNU Dashboard

| Widget | Sumber Data | Source Timestamp | Kalkulasi Usia | Kategori Default |
|---|---|---|---|---|
| Total insiden aktif | `operasi_insiden.dibuat_pada` (max) | `MAX(dibuat_pada)` dari insiden aktif | `now() - MAX(dibuat_pada)` | 🟢 Hijau (real-time) |
| Total personel aktif | `operasi_penugasan.diperbarui_pada` (max) | `MAX(diperbarui_pada)` dari penugasan aktif | `now() - MAX(diperbarui_pada)` | 🟢 Hijau (real-time) |
| Daftar insiden | `operasi_insiden.diperbarui_pada` | Per baris: `diperbarui_pada` | `now() - diperbarui_pada` | 🟢 Hijau |
| Decision Queue | `MAX(waktu)` dari sumber item | Per item: timestamp sumber | Per item | 🟢 Hijau (synthesized) |
| Alert Bar | Dari decision queue + query | Real-time | — | 🟢 Hijau |

### PCNU Dashboard

| Widget | Sumber Data | Source Timestamp | Kalkulasi Usia | Kategori Default |
|---|---|---|---|---|
| Hero row (insiden, personel, posko, tugas) | MAX timestamps from each table | Per card | `now() - MAX(updated_at)` | 🟢 Hijau |
| Daftar insiden + sitrep | `operasi_sitrep.waktu_sitrep` | Per insiden: `waktu_sitrep` dari sitrep terbaru | `now() - waktu_sitrep` | **⚠️ KRITIS — bisa merah** |
| Daftar tugas | `operasi_tugas.dibuat_pada` | `MAX(dibuat_pada, diperbarui_pada)` | `now() - updated_at` | 🟢 Hijau |
| Kebutuhan relawan | `relawan_kebutuhan.dibuat_pada` | `MAX(dibuat_pada)` | `now() - dibuat_pada` | 🟢 Hijau |

### POSKO Dashboard

| Widget | Sumber Data | Source Timestamp | Kalkulasi Usia | Kategori Default |
|---|---|---|---|---|
| Info posko | `operasi_posaju.dibuat_pada` | `dibuat_pada` | Real-time | 🟢 Hijau |
| Personel (check-in) | `operasi_penugasan.diperbarui_pada` (dengan check-in field) | `MAX(diperbarui_pada)` per personel | `now() - diperbarui_pada` | 🟢 Hijau |
| Tugas | `operasi_tugas.dibuat_pada` | Per baris | `now() - dibuat_pada` | 🟢 Hijau |
| Kebutuhan | `relawan_kebutuhan.dibuat_pada` | Per baris | `now() - dibuat_pada` | 🟡 Kuning |

### RELAWAN Dashboard

| Widget | Sumber Data | Source Timestamp | Kalkulasi Usia | Kategori Default |
|---|---|---|---|---|
| Status saya | `auth_users.diperbarui_pada` | `diperbarui_pada` | `now() - diperbarui_pada` | 🟢 Hijau |
| Tugas saya | `operasi_tugas.dibuat_pada` | Per baris | `now() - dibuat_pada` | 🟢 Hijau |
| Info insiden | `operasi_insiden.diperbarui_pada` | `diperbarui_pada` | `now() - diperbarui_pada` | 🟢 Hijau |

---

## IMPLEMENTASI

### 1. Freshness Badge Component

```blade
{{-- resources/views/components/freshness-badge.blade.php --}}
@props(['timestamp'])

@php
    $age = now()->diffInMinutes($timestamp);
    $color = match(true) {
        $age < 15 => 'success',   // green
        $age < 60 => 'warning',   // yellow
        default   => 'danger',    // red
    };
    $label = match(true) {
        $age < 1 => 'baru saja',
        $age < 60 => $age . ' menit lalu',
        $age < 1440 => floor($age / 60) . ' jam lalu',
        default => floor($age / 1440) . ' hari lalu',
    };
@endphp

<span class="badge bg-{{ $color }}" title="Data: {{ $timestamp->format('d M Y H:i') }}">
    <i class="bi bi-clock"></i> {{ $label }}
</span>
```

### 2. Hero Card with Freshness

```
┌────────────────────┐
│  15                │ ← Large number
│  Insiden Aktif     │ ← Label
│ 🟢 2 menit lalu    │ ← Freshness badge
└────────────────────┘
```

### 3. Table Row with Freshness

```
│ INV-003 │ Banjir │ Aktif │ 🟡 Sitrep: 45 menit lalu │ [Buat Sitrep] │
│ INV-007 │ Gempa  │ Respon │ 🔴 Sitrep: 4 jam lalu   │ [Buat Sitrep] │
```

### 4. FreshnessService

```php
class FreshnessService
{
    public function getFreshness(\DateTimeInterface $timestamp): array
    {
        $minutes = now()->diffInMinutes($timestamp);

        return [
            'color' => match(true) {
                $minutes < 15 => 'success',
                $minutes < 60 => 'warning',
                default => 'danger',
            },
            'label' => $this->humanReadable($minutes),
            'minutes' => $minutes,
            'is_stale' => $minutes >= 60,
        ];
    }

    public function humanReadable(int $minutes): string
    {
        return match(true) {
            $minutes < 1 => 'baru saja',
            $minutes < 60 => "{$minutes} menit lalu",
            $minutes < 1440 => floor($minutes / 60) . ' jam lalu',
            default => floor($minutes / 1440) . ' hari lalu',
        };
    }
}
```

---

## SPECIAL CASE: SITREP FRESHNESS

Sitrep adalah sumber data paling kritis untuk kesegaran.

Setiap insiden di tabel harus menampilkan:

```
Status Sitrep: 🟢 Sitrep 10 menit lalu  → OK
Status Sitrep: 🟡 Sitrep 45 menit lalu  → Waspada, butuh update segera
Status Sitrep: 🔴 Sitrep 4 jam lalu     → KRITIS, segera buat sitrep
Status Sitrep: ⚫ Belum ada sitrep      → Belum pernah dibuat
```

### Render di Daftar Insiden (PCNU)

```
│ Kode    │ Bencana        │ Status   │ Sitrep Terakhir          │ Aksi            │
│─────────┼────────────────┼──────────┼─────────────────────────┼─────────────────│
│ INV-003 │ Banjir Jakpus  │ Respon   │ 🟢 10 menit lalu        │ [Detail]        │
│ INV-007 │ Gempa Cianjur  │ Respon   │ 🔴 4 jam lalu           │ [Buat Sitrep]   │
│ INV-012 │ Tanah Longsor  │ Verifikasi│ ⚫ Belum ada            │ [Buat Sitrep]   │
```
