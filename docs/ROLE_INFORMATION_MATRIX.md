# ROLE INFORMATION MATRIX

> Kebutuhan informasi per role untuk Command Center MVP.
> Didasarkan pada data aktual yang tersedia di codebase.

---

## ROLE HIERARCHY & SCOPE

| Role | Level | Scope Data | Filter Method |
|---|---|---|---|
| PWNU | 80 | Semua PCNU di provinsi | `id_pcnu IN (child_of_pwnu_unit)` via OrganisasiUnit.parent_id |
| PCNU | 60 | PCNU sendiri | `id_pcnu = default_scope_id` |
| POSKO* | 60 | Posko tertentu di bawah PCNU | Via `pj_posaju` atau `id_posaju` di penugasan |
| RELAWAN | 40 | Insiden yang ditugaskan | Via `id_pengguna` di operasi_penugasan |

\* POSKO menggunakan role PCNU dengan scope tambahan ke posko spesifik.

---

## PWNU — KEPUTUSAN & INFORMASI

### Keputusan yang dibuat
1. Prioritas provinsi — insiden mana yang perlu eskalasi/dukungan
2. Alokasi sumber daya antar PCNU
3. Kebijakan tanggap darurat tingkat provinsi
4. Penerbitan surat keputusan provinsi

### Informasi yang dibutuhkan

| Informasi | Sumber | Frekuensi Akses | Polling | KPI # |
|---|---|---|---|---|
| Ringkasan seluruh operasi di provinsi | OperasiInsiden | Setiap buka | 30s | 1,2,3 |
| Insiden prioritas tinggi | OperasiInsiden | Real-time | 30s | 4 |
| Insiden baru hari ini | OperasiInsiden | Setiap buka | 30s | 5 |
| Peta insiden per PCNU | OperasiInsiden + OrganisasiPcnu | Setiap buka | 60s | 3 |
| Jumlah personel aktif total | OperasiPenugasan | Setiap buka | 30s | 9 |
| Total korban terkini | OperasiSitrepDampak | Setiap buka | 60s | 19 |
| Posko aktif per PCNU | OperasiPosaju | Setiap buka | 60s | 14,15 |
| Kebutuhan relawan terbuka | RelawanKebutuhan | Setiap buka | 60s | 29,30 |
| Aktivitas timeline provinsi | OperasiJurnal | Setiap buka | 30s | 42,44 |
| Sitrep terlambat (>24jam) | OperasiSitrep | Periodik | 5 menit | 18 |
| Mobilisasi aktif | OperasiMobilisasi | Setiap buka | 60s | 21 |
| Ringkasan pleno aktif | OperasiPleno | Setiap buka | 5 menit | 45 |

### Layout Prioritas
- **Atas (hero)**: total insiden, total personel, total korban, posko aktif
- **Kiri (utama)**: peta/list insiden per PCNU, daftar insiden prioritas
- **Kanan (sekunder)**: timeline aktivitas, ringkasan kebutuhan relawan, sitrep status
- **Bawah**: peringatan (sitrep terlambat, kebutuhan kritis)

---

## PCNU — KEPUTUSAN & INFORMASI

### Keputusan yang dibuat
1. Aktivasi/respon insiden di wilayah
2. Penugasan personel dan relawan
3. Pendirian posko
4. Penerbitan surat dan dokumen
5. Pelaksanaan pleno
6. Update sitrep

### Informasi yang dibutuhkan

| Informasi | Sumber | Frekuensi Akses | Polling | KPI # |
|---|---|---|---|---|
| Detail insiden aktif di wilayah | OperasiInsiden byPcnu | Setiap buka | 30s | 1,2 |
| Daftar personel dan status | OperasiPenugasan | Setiap buka | 30s | 9,10,11 |
| Daftar posko aktif | OperasiPosaju | Setiap buka | 30s | 14,15 |
| Sitrep terkini per insiden | OperasiSitrep | Setiap buka | 60s | 17 |
| Daftar tugas dan progres | OperasiTugas | Setiap buka | 60s | 24,25,26,27 |
| Mobilisasi dan pergerakan | OperasiMobilisasi | Setiap buka | 60s | 21,23 |
| Kebutuhan relawan & pendaftar | RelawanKebutuhan | Setiap buka | 60s | 29,30,31,33 |
| Timeline aktivitas | OperasiJurnal by id_pcnu | Setiap buka | 30s | 42,43,44 |
| Logistik (kebutuhan via sitrep) | OperasiSitrepKebutuhan | Setiap buka | 60s | 20 |
| Total korban | OperasiSitrepDampak | Setiap buka | 60s | 19 |
| Pleno dan keputusan | OperasiPleno | Setiap buka | 5 menit | 45,46 |
| Surat keluar | OperasiSuratKeluar | Setiap buka | 5 menit | 47,48 |

### Layout Prioritas
- **Atas (hero)**: insiden aktif, personel, posko, total korban
- **Kiri (utama)**: daftar insiden aktif + sitrep terbaru, daftar tugas
- **Kanan (sekunder)**: timeline aktivitas, kebutuhan relawan, mobilitas
- **Navigasi**: link ke detail insiden, posko, penugasan, pleno, surat

---

## POSKO — KEPUTUSAN & INFORMASI

### Keputusan yang dibuat
1. Update progres tugas di posko
2. Permintaan tambahan relawan
3. Update situasi lapangan (via sitrep/assessment)
4. Koordinasi jadwal shift

### Informasi yang dibutuhkan

| Informasi | Sumber | Frekuensi Akses | Polling | KPI # |
|---|---|---|---|---|
| Detail posko | OperasiPosaju | Setiap buka | 30s | 14 |
| Personel di posko | OperasiPenugasan via klaster/posaju | Setiap buka | 30s | 9 |
| Tugas posko & progres | OperasiTugas by id_posaju | Setiap buka | 30s | 24,25,26,27 |
| Kebutuhan relawan posko | RelawanKebutuhan by id_posaju | Setiap buka | 30s | 29,30,33 |
| Relawan shift | RelawanPenugasan by id_posaju | Setiap buka | 30s | 40 |
| Timeline aktivitas posko | OperasiJurnal by id_insiden | Setiap buka | 30s | 42 |
| Kebutuhan logistik posko | OperasiSitrepKebutuhan by insiden | Setiap buka | 60s | 20 |

### Layout Prioritas
- **Atas (hero)**: nama posko, status, personel di posko, kebutuhan mendesak
- **Kiri (utama)**: daftar tugas + progres, daftar relawan/jadwal
- **Kanan (sekunder)**: timeline aktivitas, kebutuhan logistik
- **Aksi**: update situasi, minta relawan, lapor progres

### Isu Scope
Tidak ada role 'posko' di auth_roles. Solusi untuk MVP:
- Posko dikelola oleh user PCNU dengan `id_posaju` spesifik
- Scope filter manual via `AuthorizationContextService` atau injeksi ke query
- Alternatif: scope_type = `pcnu`, scope_id = id_pcnu, PLUS id_posaju di session/claim

---

## RELAWAN — KEPUTUSAN & INFORMASI

### Keputusan yang dibuat
1. Update status tugas pribadi
2. Update progres tugas
3. Laporan aktivitas

### Informasi yang dibutuhkan

| Informasi | Sumber | Frekuensi Akses | Polling | KPI # |
|---|---|---|---|---|
| Tugas pribadi saya | OperasiTugas where ditugaskan_ke = me | Setiap buka | 30s | 24,28 |
| Penugasan insiden saya | OperasiPenugasan where id_pengguna = me | Setiap buka | 30s | 9 |
| Informasi insiden terkait | OperasiInsiden via penugasan | Setiap buka | 60s | 1 |
| Timeline aktivitas saya | OperasiJurnal by id_pengguna | Setiap buka | 30s | 42 |

### Layout Prioritas
- **Atas (hero)**: nama, status (tersedia/bertugas), tugas aktif
- **Utama**: daftar tugas pribadi + progres + tombol update
- **Sekunder**: informasi insiden terkait, timeline pribadi

---

## RINGKASAN KEBUTUHAN DATA PER ROLE

| Data | PWNU | PCNU | POSKO | RELAWAN |
|---|---|---|---|---|
| Insiden Aktif | ✅ Ringkasan | ✅ Detail | ✅ Per posko | ✅ Terkait saja |
| Personel | ✅ Total | ✅ Detail | ✅ Per posko | ❌ |
| Posko | ✅ Per PCNU | ✅ Detail | ✅ Detail | ❌ |
| Sitrep | ✅ Status | ✅ Isi | ✅ Isi | ❌ |
| Dampak | ✅ Total | ✅ Detail | ✅ Detail | ❌ |
| Tugas | ❌ | ✅ List | ✅ Detail | ✅ Pribadi |
| Kebutuhan Relawan | ✅ Ringkasan | ✅ Detail | ✅ Detail | ❌ |
| Mobilisasi | ✅ Ringkasan | ✅ Detail | ✅ Detail | ❌ |
| Timeline | ✅ Semua | ✅ Wilayah | ✅ Posko | ✅ Pribadi |
| Pleno | ✅ Ringkasan | ✅ Detail | ❌ | ❌ |
| Surat | ✅ Ringkasan | ✅ Detail | ❌ | ❌ |
| Tugas Pribadi | ❌ | ❌ | ❌ | ✅ |

## TOTAL ENDPOINT COUNT

| Role | Widget/Data Points | Endpoint Unik |
|---|---|---|
| PWNU | 12 | 9 (3 shared) |
| PCNU | 14 | 11 (3 shared) |
| POSKO | 10 | 7 (3 shared) |
| RELAWAN | 4 | 3 (1 shared) |
| **Total unik** | | **15 endpoints** ✅ (≤15) |
