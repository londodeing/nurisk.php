# POSKO DATA FIDELITY REPORT

> Tiga masalah data fidelity yang harus diselesaikan sebelum POSKO dashboard berfungsi dengan benar.

---

## PROBLEM 1: POSKO OWNERSHIP

### Masalah
Sistem tidak mengetahui user mana yang mengelola posko mana. Role POSKO tidak ada di `auth_roles`. User POSKO menggunakan role PCNU dengan scope PCNU sendiri — tapi tidak ada binding ke posko spesifik.

### Dampak
- Operator POSKO login ke dashboard PCNU dan melihat semua insiden di wilayah — bukan hanya posko mereka
- Dashboard POSKO tidak bisa menampilkan data spesifik posko karena sistem tidak tahu milik siapa

### Solusi: 3 Opsi

#### Opsi A — pj_posaju sebagai ownership (DIREKOMENDASIKAN untuk MVP)

Gunakan field `pj_posaju` yang SUDAH ADA di `operasi_posaju` sebagai penentu kepemilikan.

```php
// Di PoskoDashboardService:
// Step 1: Cari semua posko di mana user adalah PJ
$poskoIds = OperasiPosaju::where('pj_posaju', auth()->id())
    ->whereNull('waktu_ditutup')
    ->pluck('id_posaju');

// Step 2: Jika user adalah PJ, scope data ke posko tersebut
if ($poskoIds->isNotEmpty()) {
    // Filter tugas, personel, kebutuhan by id_posaju
    $tugas = OperasiTugas::whereIn('id_posaju', $poskoIds);
    $kebutuhan = RelawanKebutuhan::whereIn('id_posaju', $poskoIds);
    $personel = OperasiPenugasan::whereHas('insiden.posaju', 
        fn($q) => $q->whereIn('id_posaju', $poskoIds)
    );
}
```

**Kelebihan:** Tidak perlu schema change. Field sudah ada. Data sudah diisi via proses aktivasi posko.
**Kekurangan:** Satu user hanya bisa jadi PJ satu posko (dalam praktik, PJ lead satu posko). Jika user perlu akses multiple posko, perlu Opsi B.

#### Opsi B — Tabel pivot user_posko (UNTUK PILOT LEBIH BESAR)

```sql
CREATE TABLE operasi_user_posko (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pengguna BIGINT UNSIGNED NOT NULL,
    id_posaju BIGINT UNSIGNED NOT NULL,
    peran VARCHAR(50) DEFAULT 'pj', -- 'pj', 'anggota', 'koordinator'
    FOREIGN KEY (id_pengguna) REFERENCES auth_users(id_pengguna) ON DELETE CASCADE,
    FOREIGN KEY (id_posaju) REFERENCES operasi_posaju(id_posaju) ON DELETE CASCADE,
    UNIQUE (id_pengguna, id_posaju)
);
```

**Kelebihan:** Fleksibel. Satu user bisa akses banyak posko.
**Kekurangan:** Butuh migrasi baru. Butuh data seed.

#### Opsi C — Via scope di session (UNTUK SPRINT INI)

Set login atau akses pertama, simpan `id_posaju` di session.

```php
// Di middleware atau LoginController:
if ($user->peran->nama_peran === 'pcnu' && $user->default_scope_type === 'pcnu') {
    $managedPosko = OperasiPosaju::where('pj_posaju', $user->id_pengguna)
        ->pluck('id_posaju');
    
    if ($managedPosko->isNotEmpty()) {
        session(['posko_ids' => $managedPosko->toArray()]);
    }
}
```

### Keputusan: **Opsi A untuk MVP** (pj_posaju). Upgrade ke Opsi B jika kebutuhan scaling terlihat.

---

## PROBLEM 2: ASSIGNED ≠ HADIR

### Masalah
Dashboard POSKO menampilkan personel yang di-ASSIGN ke posko sebagai personel yang HADIR. Dalam operasi lapangan, assigned personel bisa sedang di perjalanan, sakit, atau bertugas di tempat lain.

### Dampak
- "Personel di posko: 5" — padahal hanya 2 yang benar-benar ada
- Keputusan operasional berdasarkan data salah (mengirim tambahan personel ke posko yang sudah penuh, atau sebaliknya)
- Koordinator tidak bisa mendeteksi personel yang tidak hadir

### Solusi: Check-in/Check-out

#### Tambah kolom ke tabel operasi_penugasan

```php
// Migration baru
Schema::table('operasi_penugasan', function (Blueprint $table) {
    $table->timestamp('waktu_checkin')->nullable()->after('waktu_selesai');
    $table->timestamp('waktu_checkout')->nullable()->after('waktu_checkin');
    $table->string('lokasi_checkin')->nullable()->after('waktu_checkout');
    $table->string('lokasi_checkout')->nullable()->after('lokasi_checkin');
});
```

#### Mekanisme Check-in

```
Relawan tiba di posko → Buka dashboard → Klik "Check-in"
→ POST /api/relawan/checkin { id_penugasan: 123, latitude: -6.2, longitude: 106.8 }
→ Update operasi_penugasan SET waktu_checkin = NOW(), lokasi_checkin = '{json}'
→ Last seen = NOW()
```

#### Mekanisme Check-out

```
Relawan pulang/selesai shift → Klik "Check-out"
→ POST /api/relawan/checkout { id_penugasan: 123 }
→ Update operasi_penugasan SET waktu_checkout = NOW()
```

#### Query personel hadir

```php
// Personel yang SUDAH check-in dan BELUM check-out HARI INI
$personelHadir = OperasiPenugasan::where('id_insiden', $insidenId)
    ->where('status_penugasan', 'aktif')
    ->whereNotNull('waktu_checkin')
    ->whereNull('waktu_checkout')
    ->orWhere(function ($q) {
        $q->whereNotNull('waktu_checkin')
          ->whereDate('waktu_checkout', '>=', now()->toDateString());
    })
    ->with(['pengguna.profil'])
    ->get();
```

#### Status personel di POSKO dashboard

```
│ Nama           │ Check-in     │ Last Seen          │ Status │
│────────────────┼──────────────┼────────────────────┼────────│
│ Ahmad Fauzi   │ 07:30        │ 🟢 5 menit lalu    │ Hadir  │
│ Budi Santoso  │ —            │ 🟡 2 jam lalu      │ —      │
│ Citra Dewi    │ 08:00        │ 🔴 3 jam lalu      │ Hadir  │ ← Check-in tapi tidak terlihat 3 jam
│ Dedi Kurniawan│ —            │ —                  │ —      │ ← Belum pernah check-in
```

### Keputusan: **IMPLEMENT check-in/check-out sebelum POSKO dashboard digunakan.**

---

## PROBLEM 3: LOGISTIK MENYESATKAN

### Masalah
Widget "Logistik" di dashboard PCNU menggunakan data dari `operasi_sitrep_kebutuhan` yang sebenarnya adalah **daftar kebutuhan** (needs), bukan **data stok** (inventory).

### Dampak
- PCNU melihat daftar "Makanan: 500 porsi, Air: 1000 liter" dan mengira ini adalah stok yang tersedia
- Realitanya: ini adalah kebutuhan yang dicatat saat sitrep — barang mungkin belum ada
- Keputusan redistribusi logistik berdasarkan data yang salah

### Audit: Widget yang Terdampak

| Widget | Dashboard | Data Source | Masalah | Tindakan |
|---|---|---|---|---|
| Logistik | PCNU MVP | `operasi_sitrep_kebutuhan` | Menampilkan kebutuhan sebagai stok | **HAPUS dari dashboard** |
| Kebutuhan | POSKO MVP | `relawan_kebutuhan` + `operasi_sitrep_kebutuhan` | Campur aduk: relawan need vs supply need | Pisahkan. Hanya `relawan_kebutuhan` untuk MVP. |
| Panel Kebutuhan | PCNU redesain | `relawan_kebutuhan` | Sudah benar — hanya relawan need | ✅ OK |

### Solusi

#### 1. Hapus semua widget yang menggunakan `operasi_sitrep_kebutuhan` sebagai data stok.

Data `operasi_sitrep_kebutuhan` hanya boleh ditampilkan DALAM KONTEKS sitrep — bukan sebagai widget dashboard independen.

#### 2. Jika ada widget dengan judul "Logistik", ubah judul menjadi spesifik.

| Judul Salah | Judul Benar | Data Source |
|---|---|---|
| Logistik | Kebutuhan Relawan | `relawan_kebutuhan` |
| Logistik | Kebutuhan Tercatat | `operasi_sitrep_kebutuhan` (hanya dalam konteks sitrep) |
| Ketersediaan Logistik | — | Tidak ada data — HAPUS |

#### 3. Data freshness untuk kebutuhan

Jika kebutuhan ditampilkan, WAJIB menyertakan:
- Timestamp: "Berdasarkan sitrep 3 jam lalu"
- Sumber: "Data kebutuhan, bukan stok"
- Disclaimer visual: badge kuning/orange

### Keputusan: **HAPUS semua widget "Logistik" dari dashboard. Tidak ada data inventory di sistem saat ini.**

---

## SUMMARY: TINDAKAN YANG DIPERLUKAN

| Problem | Tindakan | Schema Change? | Sprint |
|---|---|---|---|
| POSKO Ownership | Gunakan `pj_posaju` sebagai ownership (Opsi A) | ❌ Tidak | 15B |
| Check-in/Check-out | Tambah kolom `waktu_checkin` + `waktu_checkout` di `operasi_penugasan` | ✅ Migrasi baru | 15B |
| Logistik Menyesatkan | Hapus widget Logistik dari semua dashboard | ❌ Tidak | 15B (sekarang) |

### Dependencies

```
Check-in migration → POSKO personel widget → POSKO dashboard
POSKO ownership resolve → POSKO data scope → POSKO dashboard
Logistik removal → PCNU widget restructure → PCNU dashboard
```
