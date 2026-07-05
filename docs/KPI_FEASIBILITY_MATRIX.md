# KPI FEASIBILITY MATRIX

> Klasifikasi biaya query per KPI untuk menentukan kelayakan masuk MVP.
>
> **Kategori:**
> - **A (Murah)** — 1 tabel, indexed, <50ms
> - **B (Moderat)** — 2-3 JOIN, indexed, <200ms
> - **C (Mahal)** — >3 JOIN atau subquery berat, >200ms — TIDAK MASUK MVP

---

## KATEGORI A (MURAH) — Langsung masuk MVP

| # | KPI | Query Source | Index yang Tersedia | Est. Waktu |
|---|---|---|---|---|
| 1 | Insiden aktif | `operasi_insiden` WHERE status NOT IN | PK + status_insiden | <10ms |
| 2 | Insiden per status | `operasi_insiden` GROUP BY status | status_insiden | <20ms |
| 3 | Insiden per PCNU | `operasi_insiden` GROUP BY id_pcnu | id_pcnu + status | <20ms |
| 4 | Insiden prioritas tinggi | `operasi_insiden` WHERE prioritas + aktif | id_pcnu, status, prioritas | <10ms |
| 5 | Insiden baru hari ini | `operasi_insiden` WHERE dibuat_pada >= today | dibuat_pada | <10ms |
| 9 | Personel aktif | `operasi_penugasan` WHERE status='aktif' | status_penugasan | <10ms |
| 10 | Personel per insiden | `operasi_penugasan` GROUP BY id_insiden | id_insiden + status | <20ms |
| 12 | Penugasan selesai hari ini | `operasi_penugasan` WHERE diperbarui_pada >= today | diperbarui_pada | <10ms |
| 14 | Posko aktif | `operasi_posaju` WHERE waktu_ditutup IS NULL | waktu_ditutup | <10ms |
| 15 | Posko per insiden | `operasi_posaju` GROUP BY id_insiden | id_insiden | <20ms |
| 17 | Sitrep hari ini | `operasi_sitrep` WHERE waktu_sitrep >= today | waktu_sitrep | <10ms |
| 21 | Mobilisasi aktif | `operasi_mobilisasi` WHERE status IN | status_mobilisasi | <10ms |
| 22 | Mobilisasi hari ini | `operasi_mobilisasi` WHERE dibuat_pada >= today | dibuat_pada | <10ms |
| 24 | Tugas aktif | `operasi_tugas` WHERE status='berjalan' | status_tugas | <10ms |
| 25 | Tugas selesai hari ini | `operasi_tugas` WHERE status='selesai' + dibuat_pada | dibuat_pada | <10ms |
| 29 | Kebutuhan dibuka | `relawan_kebutuhan` WHERE status='dibuka' | status_rekrutmen | <10ms |
| 30 | Total relawan dibutuhkan | `relawan_kebutuhan` SUM(jumlah_dibutuhkan) WHERE dibuka | status_rekrutmen | <10ms |
| 34 | Pendaftar baru hari ini | `relawan_pendaftaran` WHERE waktu_daftar >= today | waktu_daftar | <10ms |
| 35 | Pendaftar dalam seleksi | `relawan_pendaftaran` WHERE status='seleksi' | status_pendaftaran | <10ms |
| 38 | Relawan terdaftar | `auth_users` WHERE id_peran + status_akun | id_peran, status_akun | <10ms |
| 41 | User login hari ini | `auth_users` WHERE terakhir_masuk >= today | terakhir_masuk | <10ms |
| 42 | Aktivitas hari ini | `operasi_jurnal` WHERE dibuat_pada >= today | dibuat_pada | <10ms |
| 45 | Pleno aktif | `operasi_pleno` WHERE status IN | status_pleno | <10ms |
| 47 | Surat hari ini | `operasi_surat_keluar` WHERE dibuat_pada >= today | dibuat_pada | <10ms |

---

## KATEGORI B (MODERAT) — Masuk MVP dengan optimasi

| # | KPI | Query Source | JOIN | Indeks yang Dibutuhkan | Est. Waktu |
|---|---|---|---|---|---|
| 6 | Rata-rata waktu respon | `operasi_insiden` | AVG(timestamp diff) | id_pcnu + status_insiden | <100ms |
| 7 | Insiden tanpa sitrep >24jam | `operasi_insiden` LEFT JOIN `operasi_sitrep` | 2 tabel, subquery | id_insiden (kedua tabel) | <150ms |
| 8 | Total dampak terkini | `operasi_sitrep` → `operasi_sitrep_dampak` | 3 tabel (via insiden) | id_sitrep (dampak) | <100ms |
| 11 | Personel per klaster | `operasi_penugasan` → `operasi_klaster` | 2 tabel | id_klaster_operasi | <50ms |
| 13 | Total personel unik | `operasi_penugasan` | COUNT DISTINCT | id_insiden + id_pengguna | <80ms |
| 16 | Posko tanpa PJ | `operasi_posaju` + AuthUser | LEFT JOIN | pj_posaju | <30ms |
| 18 | Insiden tanpa sitrep >24jam | `operasi_insiden` + MAX sitrep | LEFT JOIN + GROUP BY | id_insiden | <100ms |
| 19 | Total korban terkini | `operasi_sitrep_dampak` via sitrep terbaru | 3 tabel + subquery | id_sitrep | <150ms |
| 20 | Total kebutuhan terbuka | `operasi_sitrep_kebutuhan` via sitrep terbaru | 3 tabel + subquery | id_sitrep | <150ms |
| 23 | Mobilisasi per jenis | `operasi_mobilisasi` | GROUP BY jenis | jenis_mobilisasi | <30ms |
| 26 | Tugas tertunda | `operasi_tugas` | 1 tabel filter | status_tugas | <10ms |
| 27 | Progres rata-rata | `operasi_tugas` | AVG | status_tugas | <20ms |
| 28 | Tugas per user | `operasi_tugas` | GROUP BY ditugaskan_ke | ditugaskan_ke | <30ms |
| 31 | Kebutuhan terpenuhi | `relawan_kebutuhan` | 1 tabel filter | status_rekrutmen | <10ms |
| 32 | Kebutuhan per posko | `relawan_kebutuhan` | GROUP BY id_posaju | id_posaju | <30ms |
| 33 | Pendaftar per kebutuhan | `relawan_kebutuhan` + COUNT subquery | 2 tabel LEFT JOIN | id_relawan_kebutuhan | <80ms |
| 36 | Pendaftar diterima | `relawan_pendaftaran` | 1 tabel filter | status_pendaftaran | <10ms |
| 37 | Rasio diterima/ditolak | `relawan_pendaftaran` | COUNT + GROUP BY | status_pendaftaran | <20ms |
| 39 | Relawan tersedia (standby) | `auth_users` + NOT EXISTS penugasan | 2 tabel + subquery | id_pengguna (penugasan) | <150ms |
| 40 | Relawan bertugas | `auth_users` + EXISTS penugasan aktif | 2 tabel JOIN | id_pengguna (penugasan) | <100ms |
| 43 | Aktivitas per kategori | `operasi_jurnal` | GROUP BY kategori | kategori_event | <20ms |
| 44 | Aktivitas 24 jam | `operasi_jurnal` | WHERE + GROUP BY time | dibuat_pada | <50ms |
| 46 | Pleno hari ini | `operasi_pleno` | 1 tabel filter | dibuat_pada | <10ms |
| 48 | Surat perlu review | `operasi_surat_keluar` | 1 tabel filter | status_surat | <10ms |

---

## KATEGORI C (MAHAL) — TIDAK MASUK MVP

| # | KPI | Alasan |
|---|---|---|
| — | Waktu respon agregat lintas PCNU | Perlu full table scan + perhitungan timestamp diff di banyak baris |
| — | Forecasting/trend analysis | Perlu window functions atau aggregation berat |
| — | Heatmap geografis real-time | Perlu koordinat + clustering di JS, di luar scope pilot |

---

## KESIMPULAN FEASIBILITY

| Kategori | Jumlah | Masuk MVP |
|---|---|---|
| A (murah) | 24 ✅ | Ya — polling 5-30 detik |
| B (moderat) | 24 ✅ | Ya — polling 30-60 detik |
| C (mahal) | 0 ✅ | Tidak ada di MVP |

**Semua 48 KPI masuk kategori A atau B.**
**Tidak ada KPI yang membutuhkan tabel baru atau schema change.**
**Semua KPI dapat dijalankan <200ms dengan index yang tepat.**
