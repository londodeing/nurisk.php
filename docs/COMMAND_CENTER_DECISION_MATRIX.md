# COMMAND CENTER DECISION MATRIX

> Decision-first redesign: setiap widget harus menjawab pertanyaan operasional.
> Jika widget tidak membantu pengambilan keputusan, widget dihapus.

---

## ROLE: PWNU

### Q1: Apa yang terjadi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Daftar insiden aktif per PCNU | Insiden Table | ✅ OperasiInsiden + OrganisasiPcnu |
| Status operasi masing-masing | Status badge di tabel | ✅ status_insiden |
| Kapan terakhir update sitrep | Kolom "Sitrep Terakhir" di tabel | ✅ OperasiSitrep.waktu_sitrep |
| Jumlah total insiden aktif | Hero card | ✅ COUNT WHERE status NOT IN (selesai,dibatalkan) |
| Jumlah total personel | Hero card | ✅ COUNT OperasiPenugasan WHERE status=aktif |

**Kesenjangan:** Tidak ada — data insiden per PCNU lengkap.

### Q2: Apa yang paling berbahaya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| PCNU dengan sitrep >24 jam tidak update | Alert Bar — merah | ✅ MAX(waktu_sitrep) GROUP BY id_pcnu |
| Insiden prioritas tinggi tanpa respon | Alert Bar — merah | ✅ prioritas + status_insiden |
| Kebutuhan relawan kritis (dibuka >72 jam) | Alert Bar — kuning | ✅ RelawanKebutuhan.dibuat_pada |
| PCNU dengan beban berlebih (insiden >> personel) | Tidak ada widget | ❌ Butuh rasio insiden:personel per PCNU |

**Kesenjangan:** Rasio insiden:personel per PCNU. Data ada (count insiden, count personel per PCNU) tapi aggregation belum didesain.

### Q3: Apa yang harus saya lakukan sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Surat menunggu tanda tangan | Decision Queue | ✅ OperasiSuratKeluar.status=siap_tanda_tangan |
| Pleno menunggu persetujuan | Decision Queue | ✅ OperasiPleno.status=ditinjau |
| Eskalasi dari PCNU belum direspon | Decision Queue | ✅ OperasiEskalasi (table exists) |
| PCNU perlu dihubungi (sitrep overdue) | Decision Queue | ✅ dari Q2 — synthesized alert |

**Kesenjangan:** Tidak ada — data decision queue tersedia dari governance tables. Hanya perlu aggregasi.

### Q4: Siapa yang harus saya hubungi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Kontak PIC setiap PCNU | Contact Directory | ✅ AuthUser. Butuh relasi ke OrganisasiPcnu via jabatan/default_scope |
| Kontak Kadarji Operasi PCNU | Contact Directory | ❌ Tidak ada field "Kadarji Operasi" di model. Butuh interpretasi dari PenggunaJabatan. |
| Nomor emergency personel | Contact Directory | ✅ AuthUser.no_hp |

**Kesenjangan:** Mapping PIC/Kadarji per PCNU. Data ada di PenggunaJabatan + AuthUser.default_scope_id tapi perlu service layer untuk resolve.

### Q5: Keputusan apa yang menunggu saya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Surat siap ditandatangani | Decision Queue | ✅ |
| Pleno siap difinalisasi | Decision Queue | ✅ |
| Eskalasi perlu keputusan | Decision Queue | ✅ |
| Permintaan bantuan lintas PCNU | Decision Queue | ❌ Tidak ada mekanisme request bantuan antar PCNU |

**Kesenjangan:** Permintaan bantuan antar PCNU — belum ada fitur ini di sistem. Out of scope untuk MVP Phase 1.

---

## ROLE: PCNU

### Q1: Apa yang terjadi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Insiden aktif di wilayah | Insiden Table | ✅ OperasiInsiden.byPcnu() |
| Personel terdeploy | Personel summary | ✅ OperasiPenugasan.byPcnu() |
| Posko aktif dan statusnya | Posko Table | ✅ OperasiPosaju via id_insiden |
| Tugas berjalan | Tugas Table | ✅ OperasiTugas via klaster/insiden |

**Kesenjangan:** Tidak ada.

### Q2: Apa yang paling berbahaya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Insiden tanpa PIC | Alert Bar | ✅ OperasiPenugasan WHERE peran_otoritas=PIC AND status=aktif per id_insiden |
| Posko aktif tanpa personel | Alert Bar | ✅ OperasiPosaju LEFT JOIN OperasiPenugasan |
| Sitrep overdue | Alert Bar | ✅ MAX(waktu_sitrep) < now()-12h |
| Tugas overdue (berjalan > threshold) | Alert Bar | ✅ OperasiTugas WHERE status=berjalan AND dibuat_pada > threshold |
| Kebutuhan relawan tidak terpenuhi | Alert Bar | ✅ RelawanKebutuhan WHERE status_rekrutmen=dibuka AND jumlah_dibutuhkan > jumlah_terdaftar |

**Kesenjangan:** Threshold "overdue" perlu konfigurasi (default 24 jam untuk tugas, 12 jam untuk sitrep).

### Q3: Apa yang harus saya lakukan sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Buat sitrep untuk insiden tanpa update | Decision Queue | ✅ from Q2 |
| Assign PIC ke insiden | Decision Queue | ✅ OperasiPenugasan — butuh service |
| Approve pleno yang sudah ditinjau | Decision Queue | ✅ OperasiPleno.status=ditinjau |
| Finalisasi surat | Decision Queue | ✅ OperasiSuratKeluar.status=siap_tanda_tangan |
| Aktivasi posko baru | Decision Queue | ✅ OperasiPosaju WHERE status_alur=draft |

**Kesenjangan:** Tidak ada — semua data decision queue tersedia.

### Q4: Siapa yang harus saya hubungi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Kontak PJ setiap posko | Contact Directory | ✅ OperasiPosaju.pj_posaju → AuthUser |
| Kontak koordinator relawan | Contact Directory | ❌ Tidak ada field "koordinator relawan" |
| Kontak personel kunci | Contact Directory | ✅ AuthUser + PenggunaJabatan |

**Kesenjangan:** Koordinator relawan tidak terdefinisi di model saat ini.

### Q5: Keputusan apa yang menunggu saya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Pleno perlu ditinjau/difinalisasi | Decision Queue | ✅ |
| Surat perlu difinalisasi | Decision Queue | ✅ |
| Eskalasi perlu dikirim ke PWNU | Decision Queue | ✅ OperasiEskalasi |

**Kesenjangan:** Tidak ada.

---

## ROLE: POSKO

### Q1: Apa yang terjadi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Detail posko (nama, status, PJ) | Hero Card | ✅ OperasiPosaju |
| Tugas posko dan progres | Tugas Table | ✅ OperasiTugas.byPosaju() |
| Personel (yang sudah check-in) | Personel Table | ❌ Assigned ≠ Hadir. Butuh check-in. |
| Kebutuhan logistik | Kebutuhan Table | ⚠️ Data dari sitrep kebutuhan — ini yang HARMFUL |

**Kesenjangan:** Check-in mekanisme. Lihat Phase 7 untuk solusi.

### Q2: Apa yang paling berbahaya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Personel minimum tidak terpenuhi | Alert Bar | ⚠️ Perlu definisi "minimum" per posko. Belum ada. |
| Tugas overdue | Alert Bar | ✅ OperasiTugas WHERE status IN (rencana,tertunda) |
| Kebutuhan kritis tidak terpenuhi | Alert Bar | ✅ RelawanKebutuhan WHERE status=dibuka |
| Shift kosong (jadwal tidak terisi) | Alert Bar | ❌ Perlu aggregasi RelawanShift vs jadwal |
| Bantuan diminta belum direspon | Alert Bar | ❌ Tidak ada mekanisme request bantuan |

**Kesenjangan:** Minimum personel threshold (perlu konfigurasi). Shift gap analysis. Mekanisme request bantuan.

### Q3: Apa yang harus saya lakukan sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Update progres tugas | Quick Action | ✅ |
| Minta tambahan personel | Quick Action | ❌ Tidak ada form/endpoint |
| Update situasi posko | Quick Action | ✅ Buat sitrep (endpoint ada via SitrepService) |
| Lapor kebutuhan kritis | Quick Action | ❌ Tidak ada form cepat |

**Kesenjangan:** Quick action endpoints. Beberapa perlu dibuat baru.

### Q4: Siapa yang harus saya hubungi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Kontak PCNU coordinator | Contact Directory | ❌ Tidak ada mapping posko→koordinator PCNU |
| Kontak logistik | Contact Directory | ❌ Tidak ada role "logistik" |
| Kontak darurat medis | Contact Directory | ❌ Tidak ada |

**Kesenjangan:** Semua kontak perlu dimapping secara manual untuk MVP atau menggunakan data jabatan.

### Q5: Keputusan apa yang menunggu saya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Tugas yang belum dimulai | Decision Queue | ✅ OperasiTugas.status=rencana |
| Bantuan yang diminta belum ditindaklanjuti | Decision Queue | ❌ Tidak ada tracking |
| Shift yang perlu diisi | Decision Queue | ❌ Tidak ada jadwal future |

**Kesenjangan:** Tidak ada modul request/ticket bantuan. Tidak ada manajemen shift future.

---

## ROLE: RELAWAN

### Q1: Apa yang terjadi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Tugas saya hari ini | Tugas Table | ✅ OperasiTugas.ditugaskan_ke=auth()->id() |
| Status penugasan saya | Status Badge | ✅ AuthUser.is_tersedia + OperasiPenugasan |
| Informasi insiden lokasi tugas | Insiden Card | ✅ via penugasan→insiden |

**Kesenjangan:** Tidak ada.

### Q2: Apa yang paling berbahaya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Tugas overdue | Alert Bar | ✅ |
| Perubahan jadwal/lokasi | Alert Bar | ❌ Tidak ada notifikasi perubahan |
| Informasi bahaya di lokasi | Alert Bar | ❌ Tidak ada sistem broadcast ke relawan |

**Kesenjangan:** Tidak ada sistem notifikasi/broadcast. Untuk MVP, cukup tampilkan perubahan sebagai alert di dashboard.

### Q3: Apa yang harus saya lakukan sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Tugas prioritas selanjutnya | Decision Queue | ✅ Urutkan tugas by status |
| Check-in ke posko | Quick Action | ❌ Perlu dibuat |
| Lapor progres tugas | Quick Action | ✅ Update via OperasiTugasService |
| Ikuti shift | Tidak ada widget | ❌ Tidak ada info shift |

**Kesenjangan:** Check-in, info shift.

### Q4: Siapa yang harus saya hubungi sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Kontak supervisor (PJ posko) | Contact Directory | ✅ OperasiPosaju.pj_posaju → AuthUser.no_hp |
| Kontak koordinator lapangan | Contact Directory | ❌ Tidak ada mapping |
| Nomor darurat | Contact Directory | ❌ Tidak ada |

**Kesenjangan:** Kontak darurat perlu disediakan sebagai data seed/konfigurasi.

### Q5: Keputusan apa yang menunggu saya sekarang?

| Data Diperlukan | Widget | Tersedia? |
|---|---|---|
| Tugas baru ditugaskan | Decision Queue | ✅ OperasiTugas.status=rencana, ditugaskan_ke=me |
| Perubahan shift | Decision Queue | ❌ Tidak ada jadwal future |
| Lokasi tugas berubah | Decision Queue | ❌ Tidak ada tracking perubahan lokasi |

**Kesenjangan:** Perubahan shift dan lokasi tidak terdeteksi dalam sistem saat ini.

---

## SUMMARY: DATA KESENJANGAN

| Kesenjangan | Dampak | Prioritas | Solusi MVP |
|---|---|---|---|
| Check-in/check-out personel | Data personel di posko tidak akurat | HIGH | Tambah field `waktu_checkin`, `waktu_checkout` di OperasiPenugasan |
| Threshold overdue konfigurasi | Alert Bar tidak bisa hitung "terlambat" | MEDIUM | Hardcode dulu: tugas=24jam, sitrep=12jam |
| Request bantuan antar unit | Tidak bisa track permintaan | MEDIUM | Tidak masuk MVP Phase 1 — deferred |
| Mapping kontak per unit | Contact Directory kosong | HIGH | Seed data manual via database seed atau buat tabel `kontak_darurat` |
| Jadwal shift future | Tidak bisa deteksi shift kosong | LOW | Tidak masuk MVP Phase 1 |
| Notifikasi perubahan | Relawan tidak tahu perubahan | MEDIUM | Cukup tampilkan sebagai alert di dashboard (pasif) |
| PIC/Kadarji per PCNU mapping | PWNU tidak tahu siapa dihubungi | HIGH | Gunakan PenggunaJabatan + scope_id untuk resolve |
| Threshold personel minimum posko | Tidak bisa deteksi understaffed | HIGH | Default 3 orang per posko (hardcode) |
| Rasio insiden:personel | PWNU tidak tahu beban PCNU | MEDIUM | Tambah perhitungan di Alert Bar PWNU |
