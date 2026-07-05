# KPI INVENTORY — COMMAND CENTER

> KPI yang dapat dihitung dari data aktual yang ada di codebase.
> Hanya KPI yang berasal dari tabel/model yang benar-benar ada.

---

## OPERASI INSIDEN

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 1 | Jumlah insiden aktif | `status_insiden NOT IN (selesai, dibatalkan)` | COUNT | id_pcnu |
| 2 | Jumlah insiden per status | `status_insiden` | COUNT + GROUP BY | id_pcnu |
| 3 | Jumlah insiden per PCNU | `id_pcnu` | COUNT + GROUP BY | id_pcnu |
| 4 | Insiden prioritas tinggi | `prioritas = 'tinggi'` AND aktif | COUNT | id_pcnu |
| 5 | Insiden baru hari ini | `dibuat_pada >= today` | COUNT | id_pcnu |
| 6 | Rata-rata waktu respon | AVG(`waktu_respon_dimulai` - `waktu_verifikasi`) | AVG | id_pcnu |
| 7 | Insiden tanpa sitrep >24jam | WHERE no sitrep in last 24h | COUNT + SUBQUERY | id_pcnu |
| 8 | Total dampak terkini* | via sitrep dampak terbaru | SUM | id_pcnu |

\* Dampak terkini diambil dari sitrep terbaru per insiden, bukan dari assessment langsung.

---

## OPERASI PENUGASAN

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 9 | Personel aktif (sedang bertugas) | `status_penugasan = 'aktif'` | COUNT | id_insiden → id_pcnu |
| 10 | Personel per insiden | `id_insiden` + `status_penugasan = 'aktif'` | COUNT + GROUP BY | id_pcnu |
| 11 | Personel per klaster | `id_klaster_operasi` + `status_penugasan = 'aktif'` | COUNT + GROUP BY | id_pcnu |
| 12 | Penugasan selesai hari ini | `status_penugasan = 'selesai'` AND `diperbarui_pada >= today` | COUNT | id_pcnu |
| 13 | Total personel pernah ditugaskan | COUNT distinct `id_pengguna` | COUNT DISTINCT | id_insiden → id_pcnu |

---

## OPERASI POSAJU (POSKO)

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 14 | Posko aktif | `waktu_ditutup IS NULL` | COUNT | id_insiden → id_pcnu |
| 15 | Posko per insiden | `id_insiden` + aktif | COUNT + GROUP BY | id_pcnu |
| 16 | Posko tanpa PJ terisi | `pj_posaju IS NULL` AND aktif | COUNT | id_pcnu |

---

## OPERASI SITREP

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 17 | Sitrep hari ini | `waktu_sitrep >= today` | COUNT | id_pcnu (via insiden) |
| 18 | Insiden tanpa sitrep >24jam | MAX(`waktu_sitrep`) < now()-24h | EXISTS check | id_pcnu |
| 19 | Total korban terkini* | `operasi_sitrep_dampak` via sitrep terbaru per insiden | SUM(meninggal, luka, mengungsi) | id_pcnu |
| 20 | Total kebutuhan terbuka | `operasi_sitrep_kebutuhan` via sitrep terbaru per insiden | SUM(jumlah) | id_pcnu |

\* Korban terkini = snapshot dari sitrep terbaru per insiden (bukan real-time assessment).

---

## OPERASI MOBILISASI

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 21 | Mobilisasi aktif (draft/berangkat) | `status_mobilisasi IN ('draft','berangkat')` | COUNT | id_pcnu |
| 22 | Mobilisasi hari ini | `dibuat_pada >= today` | COUNT | id_pcnu |
| 23 | Mobilisasi per jenis | `jenis_mobilisasi` | COUNT + GROUP BY | id_pcnu |

---

## OPERASI TUGAS

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 24 | Tugas aktif (berjalan) | `status_tugas = 'berjalan'` | COUNT | id_operasi_klaster → id_insiden → id_pcnu |
| 25 | Tugas selesai hari ini | `status_tugas = 'selesai'` AND `dibuat_pada >= today` | COUNT | id_pcnu |
| 26 | Tugas tertunda | `status_tugas = 'tertunda'` | COUNT | id_pcnu |
| 27 | Progres rata-rata tugas aktif | AVG(`progres_persen`) WHERE `status_tugas = 'berjalan'` | AVG | id_pcnu |
| 28 | Tugas per role (ditugaskan_ke) | `ditugaskan_ke` | COUNT + GROUP BY | id_pcnu |

---

## RELAWAN KEBUTUHAN

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 29 | Kebutuhan relawan dibuka | `status_rekrutmen = 'dibuka'` | COUNT | id_pcnu |
| 30 | Total relawan dibutuhkan | SUM(`jumlah_dibutuhkan`) WHERE `status_rekrutmen = 'dibuka'` | SUM | id_pcnu |
| 31 | Kebutuhan terpenuhi | `status_rekrutmen = 'terpenuhi'` | COUNT | id_pcnu |
| 32 | Kebutuhan per posko | `id_posaju` + dibuka | COUNT + GROUP BY | id_pcnu |
| 33 | Jumlah pendaftar per kebutuhan | COUNT `relawan_pendaftaran` per `id_relawan_kebutuhan` | COUNT (subquery) | id_pcnu |

---

## RELAWAN PENDAFTARAN

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 34 | Pendaftar baru hari ini | `waktu_daftar >= today` | COUNT | Via kebutuhan → id_pcnu |
| 35 | Pendaftar dalam seleksi | `status_pendaftaran = 'seleksi'` | COUNT | id_pcnu |
| 36 | Pendaftar diterima | `status_pendaftaran = 'diterima'` | COUNT | id_pcnu |
| 37 | Rasio diterima/ditolak | COUNT diterima / COUNT ditolak | RATIO | id_pcnu |

---

## AUTH USER (PERSONEL)

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 38 | Relawan terdaftar | `id_peran = relawan` AND `status_akun = 'aktif'` | COUNT | id_unit scope |
| 39 | Relawan tersedia (standby) | `is_tersedia = true` AND tidak punya penugasan aktif | COUNT + SUBSELECT | id_unit scope |
| 40 | Relawan sedang bertugas | punya `operasi_penugasan` dengan `status_penugasan = 'aktif'` | COUNT DISTINCT | id_unit scope |
| 41 | User login hari ini | `terakhir_masuk >= today` | COUNT | Scope |

---

## OPERASI JURNAL (AKTIVITAS)

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 42 | Aktivitas hari ini | `dibuat_pada` | COUNT | id_insiden → id_pcnu |
| 43 | Aktivitas per kategori | `kategori_event` | COUNT + GROUP BY | id_pcnu |
| 44 | Aktivitas 24 jam terakhir | `dibuat_pada >= now()-24h` | COUNT + time-series | id_pcnu |

---

## OPERASI PLENO

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 45 | Pleno aktif (draft/ditinjau) | `status_pleno IN ('draft','ditinjau')` | COUNT | id_pcnu |
| 46 | Pleno hari ini | `dibuat_pada >= today` | COUNT | id_pcnu |

---

## OPERASI SURAT KELUAR

| # | KPI | Sumber | Agregasi | Filter Scope |
|---|---|---|---|---|
| 47 | Surat hari ini | `dibuat_pada >= today` | COUNT | id_pcnu |
| 48 | Surat perlu review | `status_surat IN ('draft','review_paraf')` | COUNT | id_pcnu |

---

## TOTAL: 48 KPI teridentifikasi

Semua KPI di atas bersumber dari data yang benar-benar ada di database.
Tidak ada KPI yang membutuhkan tabel atau kolom baru.
