# COMMAND CENTER WIDGET AUDIT — FINAL

> Audit final seluruh widget. Setiap widget dikategorikan KEEP, MERGE, REMOVE, atau HARMFUL.
> Kriteria: widget harus membantu pengambilan keputusan operasional.
> Widget yang tidak lulus audit, dihapus.

---

## DASHBOARD PWNU — SEBELUM: 10 widget → SESUDAH: 6 widget

| # | Widget Lama | Kategori | Alasan |
|---|---|---|---|
| 1 | Total insiden aktif | **KEEP** | Core metric. PWNU butuh tahu jumlah operasi berjalan. |
| 2 | Total personel aktif | **MERGE** → ke Hero Row | Gabung dengan #1, #3 sebagai ringkasan 3 angka. |
| 3 | Posko aktif | **MERGE** → ke Hero Row | Gabung dengan #1, #2. Tidak perlu widget terpisah. |
| 4 | Total korban terkini | **HARMFUL — REMOVE** | Data dari snapshot sitrep yang bisa basi 6-12 jam. PWNU bisa ambil keputusan alokasi sumber daya berdasarkan data usang. Hanya tampil dalam konteks sitrep. |
| 5 | Daftar insiden aktif | **KEEP** | Widget inti PWNU. Harus ada kolom: PCNU, kode, status, prioritas, sitrep terakhir, tindakan. |
| 6 | Activity timeline | **REMOVE** | Noise di level provinsi. 10 event acak dari seluruh PCNU tanpa filter prioritas tidak membantu keputusan. |
| 7 | Posko per PCNU chart | **REMOVE** | VANITY. Bar chart "5 posko vs 3 posko" tanpa konteks insiden tidak drive keputusan. |
| 8 | Kebutuhan relawan | **MERGE** → Panel Sumber Daya | Gabung angka kebutuhan + mobilisasi jadi satu panel ringkasan. |
| 9 | Mobilisasi aktif | **MERGE** → Panel Sumber Daya | Angka saja di panel ringkasan. Tidak perlu widget detail. |
| 10 | Sitrep terlambat | **KEEP** → pindah ke Decision Queue | Komponen critical. Tapi tampil sebagai item Decision Queue, bukan widget terpisah. |

### PWNU — Widget Baru yang Ditambahkan

| Widget Baru | Kategori | Sumber Data |
|---|---|---|
| Decision Queue | **KEEP (NEW)** | Surat ± Pleno ± Eskalasi ± Sitrep Overdue |
| Contact Directory | **KEEP (NEW)** | AuthUser + PenggunaJabatan + OrganisasiPcnu |
| Alert Bar | **KEEP (NEW)** | Sitrep overdue, insiden prioritas tinggi, kebutuhan kritis |
| Quick Actions | **KEEP (NEW)** | Approve Surat, Approve Pleno, Hubungi PCNU |

### PWNU Final Widget Count: 10 (6 existing + 4 new) — Replacements untuk yang dihapus

---

## DASHBOARD PCNU — SEBELUM: 12 widget → SESUDAH: 8 widget

| # | Widget Lama | Kategori | Alasan |
|---|---|---|---|
| 1 | Insiden aktif (hero) | **KEEP** | Core metric. |
| 2 | Personel aktif (hero) | **MERGE** → Hero Row | Gabung dengan #1, #3. |
| 3 | Posko aktif (hero) | **MERGE** → Hero Row | Gabung dengan #1, #2. |
| 4 | Total korban (hero) | **HARMFUL — REMOVE** | Data snapshot sitrep basi. Menyesatkan untuk alokasi sumber daya. |
| 5 | Tugas aktif (hero) | **MERGE** → Hero Row | Gabung dengan #1, #2, #3. Hero row jadi 4 angka. |
| 6 | Daftar insiden + sitrep | **KEEP** | Widget inti PCNU. Harus tampilkan status sitrep terbaru. |
| 7 | Activity timeline | **REMOVE** | Ganti dengan focused activity feed inline di tabel insiden. |
| 8 | Daftar tugas & progres | **KEEP** | Widget inti. Merge daftar + hero tugas jadi satu panel. |
| 9 | Mobilisasi | **REMOVE** | Count tanpa konteks = vanity. Tampilkan di panel Sumber Daya PCNU saja. |
| 10 | Kebutuhan relawan | **KEEP** | Merge dengan panel kebutuhan. |
| 11 | Logistik (via sitrep) | **HARMFUL — REMOVE** | PALING BERBAHAYA. Menampilkan "daftar kebutuhan" sebagai "data stok logistik". Hapus. Tidak ada logistik module di sistem. |
| 12 | Sitrep terlambat | **KEEP** → pindah ke Decision Queue | Komponen critical sebagai item decision queue. |

### PCNU — Widget Baru

| Widget Baru | Kategori | Sumber Data |
|---|---|---|
| Decision Queue | **KEEP (NEW)** | Sitrep overdue, insiden tanpa PIC, posko tanpa PJ, pleno, surat |
| Contact Directory | **KEEP (NEW)** | AuthUser (PJ posko, personel kunci) |
| Alert Bar | **KEEP (NEW)** | Sitrep overdue, posko tanpa personel, kebutuhan kritis |
| Quick Actions | **KEEP (NEW)** | Buat Sitrep, Assign PIC, Aktivasi Posko, Approve Surat/Pleno |

### PCNU Final Widget Count: 12 (8 existing + 4 new)

---

## DASHBOARD POSKO — SEBELUM: 8 widget → SESUDAH: 7 widget

| # | Widget Lama | Kategori | Alasan |
|---|---|---|---|
| 1 | Info posko (hero) | **KEEP** | Critical — operator butuh tahu identitas posko. |
| 2 | Personel di posko (hero) | **KEEP** | TAPI: harus ganti data source dari "assigned" ke "check-in". Lihat Phase 7. |
| 3 | Tugas posko (hero) | **MERGE** → ke Panel Tugas | Gabung hero tugas + tabel jadi satu panel. |
| 4 | Kebutuhan (hero) | **MERGE** → ke Panel Kebutuhan | Angka kebutuhan di panel ringkasan. |
| 5 | Daftar tugas + progres | **MERGE** dengan #3 | Satu panel Tugas dengan hero count + tabel. |
| 6 | Daftar personel | **MERGE** dengan #2 | Satu panel Personel dengan count + tabel. |
| 7 | Kebutuhan relawan | **MERGE** dengan #4 | Satu panel Kebutuhan dengan count + daftar. |
| 8 | Timeline posko | **REMOVE** | Noise. Operator posko update data di sistem, tidak baca timeline. |

### POSKO — Widget Baru

| Widget Baru | Kategori | Sumber Data |
|---|---|---|
| Decision Queue | **KEEP (NEW)** | Tugas overdue, personel minimum breached, kebutuhan kritis |
| Contact Directory | **KEEP (NEW)** | PCNU coordinator, logistik, darurat (seed data) |
| Alert Bar | **KEEP (NEW)** | Personel minimum, tugas overdue, kebutuhan kritis, shift kosong |
| Quick Actions | **KEEP (NEW)** | Update progres, minta bantuan, update situasi, check-in personel |

### POSKO Final Widget Count: 10 (6 existing merged + 4 new)

---

## DASHBOARD RELAWAN — SEBELUM: 5 widget → SESUDAH: 5 widget

| # | Widget Lama | Kategori | Alasan |
|---|---|---|---|
| 1 | Status saya (hero) | **KEEP** | TAPI: expand dengan info shift berikutnya + lokasi. |
| 2 | Tugas aktif saya (hero) | **MERGE** → ke Panel Tugas | Gabung dengan #3 jadi satu panel. |
| 3 | Daftar tugas saya | **MERGE** dengan #2 | Satu panel Tugas dengan count + tabel + progress bar. |
| 4 | Info insiden terkait | **KEEP** | TAPI: pindah ke sidebar/panel kecil. Bukan widget utama. |
| 5 | Timeline saya | **REMOVE** | Zero operational value. Relawan tahu apa yang mereka lakukan. |

### RELAWAN — Widget Baru

| Widget Baru | Kategori | Sumber Data |
|---|---|---|
| Decision Queue | **KEEP (NEW)** | Tugas baru, tugas mendekati deadline |
| Contact Directory | **KEEP (NEW)** | Supervisor (PJ posko), coordinator, emergency |
| Alert Bar | **KEEP (NEW)** | Tugas overdue, perubahan status penugasan |
| Quick Actions | **KEEP (NEW)** | Check-in, check-out, update progres, hubungi supervisor |

### RELAWAN Final Widget Count: 7 (3 existing merged + 4 new)

---

## WIDGET INVENTORY FINAL

| Dashboard | Sebelum | Dihapus | Digabung | Baru | Sesudah | Reduksi |
|---|---|---|---|---|---|---|
| PWNU | 10 | 3 | 3→1 | 4 | 10 | 0% (restruktur) |
| PCNU | 12 | 4 | 3→1 | 4 | 12 | 0% (restruktur) |
| POSKO | 8 | 1 | 4→2 | 4 | 10 | +25% (tambah decision support) |
| RELAWAN | 5 | 1 | 2→1 | 4 | 7 | +40% (tambah decision support) |

> **Catatan:** Jumlah widget tidak berkurang drastis karena widget BARU (Decision Queue, Contact Directory, Alert Bar, Quick Actions) MENGGANTIKAN widget yang dihapus. Tujuan bukan mengurangi jumlah widget, tapi MENGGANTI widget informasional dengan widget DECISION SUPPORT.

### Widget HARMFUL yang Dihapus

| Widget | Dashboard | Bahaya | Mitigasi |
|---|---|---|---|
| Total korban terkini | PWNU, PCNU | PWNU/PCNU alokasi sumber daya berdasarkan data basi | Hanya tampil dalam konteks sitrep dengan timestamp jelas |
| Logistik (via sitrep) | PCNU | Menampilkan "daftar kebutuhan" sebagai "data stok" — sangat menyesatkan | Hapus. Tidak ada data inventory di sistem. |
| Personel assigned = hadir | POSKO | Menampilkan personel yang di-assign sebagai personel yang hadir | Fix dengan check-in mechanism (Phase 7) |

### Widget Vanity yang Dihapus

| Widget | Dashboard | Alasan |
|---|---|---|
| Activity timeline | PWNU | 10 event acak dari provinsi — noise |
| Posko per PCNU chart | PWNU | Bar chart tanpa konteks — zero decision value |
| Mobilisasi list | PCNU | Count per jenis tanpa ETA/lokasi — vanity |
| Timeline posko | POSKO | Operator update data, tidak baca timeline |
| Timeline saya | RELAWAN | Self-referential — zero value |
