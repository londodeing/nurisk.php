# CONTACT DIRECTORY DESIGN

> Direktori kontak operasional untuk setiap role.
> Dalam emergency, operator harus bisa menghubungi orang yang tepat dalam 1 klik.

---

## ARSITEKTUR

Contact Directory adalah panel slide-in (offcanvas Bootstrap 5) yang bisa dibuka dari tombol di header atau inline di Decision Queue.

```
┌─────────────────────────────────┐
│ ☰ Kontak Operasional       [✕] │ ← Offcanvas header
├─────────────────────────────────┤
│ 🔍 Cari kontak...              │ ← Search filter
├─────────────────────────────────┤
│ ┌─────────────────────────────┐│
│ │ 👤 Ahmad Fauzi             ││
│ │    PJ Posko — Posko Cawang ││
│ │    📞 0812-3456-7890       ││
│ │    [💬 WhatsApp] [📞 Telp] ││
│ └─────────────────────────────┘│
│ ┌─────────────────────────────┐│
│ │ 👤 Siti Nurhaliza          ││
│ │    Koordinator Lapangan     ││
│ │    📞 0812-3456-7891       ││
│ │    [💬 WhatsApp] [📞 Telp] ││
│ └─────────────────────────────┘│
└─────────────────────────────────┘
```

### Sumber Data

| Data | Sumber | Tersedia? |
|---|---|---|
| Nama | `auth_pengguna_profil.nama_lengkap` | ✅ |
| Nomor HP | `auth_users.no_hp` | ✅ |
| Role/Jabatan | `auth_roles.nama_peran` + `pengguna_jabatan.jabatan` | ✅ |
| Unit | `organisasi_unit.nama_unit` via `auth_users.id_unit` | ✅ |
| Foto | `auth_pengguna_profil.foto` (optional) | ❌ (maybe not, but optional) |
| WhatsApp link | `https://wa.me/62{no_hp}` | ✅ (generated) |

---

## PER ROLE

### PWNU — Contact Directory

**Isi:** Semua PIC PCNU di provinsi

```php
// Resolve logic:
// 1. Dapatkan semua PCNU di bawah provinsi user (AuthorizationContextService::getAccessiblePcnuIds)
// 2. Untuk setiap PCNU, cari user dengan jabatan "Kadarji" atau "Ketua" yang scope_id = id_pcnu
// 3. Prioritaskan yang punya jabatan operasional

$contacts = AuthUser::whereIn('default_scope_id', $accessiblePcnuIds)
    ->where('default_scope_type', 'pcnu')
    ->whereHas('jabatanAktif', fn($q) => $q->whereIn('jabatan', ['kadarji', 'ketua', 'sekretaris']))
    ->with('profil')
    ->get()
    ->map(fn($user) => [
        'nama' => $user->profil?->nama_lengkap ?? 'Unknown',
        'jabatan' => $user->jabatanAktif?->first()?->jabatan ?? 'Anggota',
        'unit' => "PCNU {$user->default_scope_id}",
        'no_hp' => $user->no_hp,
        'whatsapp_url' => $user->no_hp ? "https://wa.me/62{$user->no_hp}" : null,
        'last_seen' => $user->terakhir_masuk,
    ]);
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ Kontak PCNU                [✕] │
├─────────────────────────────────┤
│ 🔍 Cari PCNU...                │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐│
│ │ 👤 Ahmad Fauzi             ││
│ │    Kadarji — PCNU Jakarta  ││
│ │    📞 0812-3456-7890       ││
│ │    🟢 Online 5 menit lalu   ││
│ │    [💬 WA] [📞 Telp]       ││
│ └─────────────────────────────┘│
│ ┌─────────────────────────────┐│
│ │ 👤 Siti Nurhaliza          ││
│ │    Ketua — PCNU Bogor      ││
│ │    📞 0812-3456-7891       ││
│ │    🟡 Offline 2 jam lalu    ││
│ │    [💬 WA] [📞 Telp]       ││
│ └─────────────────────────────┘│
└─────────────────────────────────┘
```

### PCNU — Contact Directory

**Isi:** Kontak PJ Posko, koordinator relawan, personel kunci

```php
// Resolve logic:
// 1. PJ Posko: OperasiPosaju where id_insiden.pcnu = user's pcnu
// 2. Personel kunci: AuthUser dengan penugasan aktif di insiden user's pcnu
// 3. Prioritaskan PJ Posko

$pjPosko = OperasiPosaju::whereHas('insiden', fn($q) => $q->where('id_pcnu', $userPcnuId))
    ->whereNotNull('pj_posaju')
    ->with('pj.profil')
    ->get()
    ->map(fn($posko) => [
        'nama' => $posko->pj->profil?->nama_lengkap,
        'jabatan' => "PJ Posko {$posko->nama_posaju}",
        'no_hp' => $posko->pj->no_hp,
        'whatsapp_url' => "...",
    ]);
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ Kontak Operasional         [✕] │
├─────────────────────────────────┤
│ 🔍 Cari...                     │
├─────────────────────────────────┤
│ 📋 POSKO                       │ ← Section header
│ ┌─────────────────────────────┐│
│ │ 👤 Andi Wijaya             ││
│ │    PJ Posko Cawang         ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
│ ┌─────────────────────────────┐│
│ │ 👤 Budi Santoso            ││
│ │    PJ Posko Kebayoran      ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
│ 📋 PERSONEL                     │
│ ┌─────────────────────────────┐│
│ │ 👤 Citra Dewi              ││
│ │    Koordinator Lapangan    ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
└─────────────────────────────────┘
```

### POSKO — Contact Directory

**Isi:** PCNU coordinator, logistik, darurat

```php
// Resolve logic:
// 1. PCNU coordinator: Cari user dengan role pcnu yang default_scope_id = pcnu dari posko's insiden
// 2. Kontak darurat: dari tabel seed (data statis untuk MVP)
// 3. Kontak logistik: dari AuthUser dengan jabatan logistik (jika ada)

$pcnuCoordinator = AuthUser::where('default_scope_type', 'pcnu')
    ->where('default_scope_id', $pcnuId)
    ->whereHas('jabatanAktif', fn($q) => $q->where('jabatan', 'kadarji'))
    ->with('profil')
    ->first();
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ Kontak Darurat             [✕] │
├─────────────────────────────────┤
│ 🚨 DARURAT                     │
│ ┌─────────────────────────────┐│
│ │ 🏥 Ambulans/Medis          ││
│ │    📞 119                  ││
│ │    [📞 Telp]               ││
│ └─────────────────────────────┘│
│ ┌─────────────────────────────┐│
│ │ 🔥 Pemadam Kebakaran       ││
│ │    📞 113                  ││
│ │    [📞 Telp]               ││
│ └─────────────────────────────┘│
│ 📋 KOMANDO                     │
│ ┌─────────────────────────────┐│
│ │ 👤 Ahmad Fauzi             ││
│ │    Kadarji PCNU Jakarta    ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
│ 📋 LOGISTIK                    │
│ ┌─────────────────────────────┐│
│ │ 👤 Dedi Kurniawan          ││
│ │    Koordinator Logistik    ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
└─────────────────────────────────┘
```

### RELAWAN — Contact Directory

**Isi:** Supervisor (PJ Posko), koordinator, emergency

```php
// Resolve logic:
// 1. Supervisor: Cari OperasiPosaju where pj_posaju terkait dengan penugasan relawan
// 2. Emergency: dari seed data/konfigurasi

$supervisor = OperasiPenugasan::where('id_pengguna', auth()->id())
    ->where('status_penugasan', 'aktif')
    ->with('insiden.posaju.pj.profil')
    ->first()
    ?->insiden?->posaju?->first()?->pj;
```

**Tampilan:**
```
┌─────────────────────────────────┐
│ Kontak Saya               [✕] │
├─────────────────────────────────┤
│ 👤 SUPERVISOR                  │
│ ┌─────────────────────────────┐│
│ │ 👤 Andi Wijaya             ││
│ │    PJ Posko Cawang         ││
│ │    📞 0812... [💬] [📞]    ││
│ └─────────────────────────────┘│
│ 🚨 DARURAT                     │
│ ┌─────────────────────────────┐│
│ │ 📞 Call Center: 119        ││
│ │    [📞 Telp]               ││
│ └─────────────────────────────┘│
```

---

## IMPLEMENTASI

### File yang Dibuat

| File | Type |
|---|---|
| `app/Services/CommandCenter/ContactDirectoryService.php` | Service — resolve contacts per role |
| `app/Http/Resources/CommandCenter/ContactResource.php` | Resource |
| `resources/views/components/contact-directory.blade.php` | Blade offcanvas component |
| `resources/views/components/contact-card.blade.php` | Blade — single contact card |

### API Endpoint

```
GET /api/cc/contacts?role=pj-posko&search=andi
GET /api/cc/contacts/emergency

Response: { contacts: [...], type: "operational"|"emergency" }
Polling: 60s (blue) — kontak jarang berubah
```

### WhatsApp Integration

```blade
@if($contact['no_hp'])
    @php
        // Strip leading 0, prepend 62
        $phone = '62' . ltrim($contact['no_hp'], '0');
        $waUrl = "https://wa.me/{$phone}";
    @endphp
    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm">
        <i class="bi bi-whatsapp"></i> WhatsApp
    </a>
@endif
```
