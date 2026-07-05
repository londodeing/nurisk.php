# Operational Role Matrix

Dokumen ini memetakan ulang seluruh aktor dalam operasi kebencanaan NURISK dari pendekatan struktural (PCNU/PWNU) menuju arsitektur *Role-Driven Operations*.

## 1. Level Lapangan

### Relawan Biasa
- **Tujuan Utama:** Menjalankan instruksi teknis (evakuasi, distribusi).
- **Keputusan:** Di mana posisi saya? Apa tugas saya berikutnya? Kapan saya selesai?
- **Data Minimum:** Titik GPS, Detail Penugasan (Lokasi, Tenggat, PIC).
- **Frekuensi:** Tinggi (Setiap jam selama *shift*).
- **Risiko Salah Desain:** *Information overload* menyebabkan kelambatan manuver lapangan.

### Tim Reaksi Cepat (TRC)
- **Tujuan Utama:** Asesmen awal dan pembukaan jalur.
- **Keputusan:** Titik krisis mana yang harus disurvei duluan? Apakah butuh eskalasi posko darurat?
- **Data Minimum:** Data aduan awal, ketersediaan sinyal/jalan.
- **Frekuensi:** Sedang (Pasca gempa/banjir awal).
- **Risiko Salah Desain:** Lambat merespons insiden baru.

### Komandan Posko
- **Tujuan Utama:** Manajer strategis di satu *node* lapangan.
- **Keputusan:** Apakah posko saya lumpuh? Relawan mana yang diam? Stok apa yang habis? Apakah saya harus melapor PCNU?
- **Data Minimum:** Antrean Keputusan (*Decision Queue*), Agregasi Stok, KPI Relawan.
- **Frekuensi:** Menengah (Tiap 1-2 Jam).
- **Risiko Salah Desain:** Terlalu banyak form input, kehilangan *helicopter view* lapangan.

### Operator Posko
- **Tujuan Utama:** Tenaga administrasi klerikal lapangan.
- **Keputusan:** (Hampir tidak ada, fokus pada *data entry*).
- **Data Minimum:** Form Mutasi Logistik, Form Absensi Relawan, Form Sitrep.
- **Frekuensi:** Sangat Tinggi (Konstan di depan PC).
- **Risiko Salah Desain:** Kelelahan (*fatigue*), kesalahan ketik (*typo*) karena terlalu banyak klik.

---

## 2. Level Klaster
### Koordinator Klaster (Misal: Klaster Kesehatan/Logistik)
- **Tujuan Utama:** Mengawal efektivitas spesifik sektoral.
- **Keputusan:** Posko mana yang krisis obat? Relawan medis mana yang menganggur?
- **Data Minimum:** Kesenjangan (*Gap*) Kebutuhan sektoral vs Stok Tersedia.
- **Frekuensi:** Menengah.
- **Risiko Salah Desain:** Tidak dapat melacak disparitas suplai.

---

## 3. Level Cabang (Kabupaten/Kota)
### Operator PCNU
- **Tujuan Utama:** Konsolidasi data lintas posko, administrasi persuratan.
- **Keputusan:** Posko mana yang telat kirim sitrep? Format surat apa yang harus didraf?
- **Data Minimum:** Status Sitrep posko, *template* surat.

### Ketua PCNU (Inc. Sekretaris)
- **Tujuan Utama:** Keputusan Bantuan Kendali Operasi (BKO) dan pembukaan/penutupan posko.
- **Keputusan:** Apakah posko A perlu dibantu posko B? Apakah kita butuh PWNU?
- **Data Minimum:** *Escalation Queue*, *Resource Distribution*.

---

## 4. Level Wilayah (Provinsi)
### Operator PWNU
- **Tujuan Utama:** Pengolah laporan agregat untuk BPBD/Pusat.
- **Keputusan:** Dokumen mana yang siap di-*export*?
- **Data Minimum:** Tabel rekapitulasi utuh, PDF *Generator*.

### Ketua PWNU (Eksekutif)
- **Tujuan Utama:** Monitor stabilitas keamanan provinsi.
- **Keputusan:** Cabang mana yang terburuk? Haruskah saya menandatangani penetapan status?
- **Data Minimum:** Top 5 Wilayah Kritis, Laju Tren, *Approval Center*.

---

## 5. Governance
### Penandatangan Surat / Pleno (Approver)
- **Tujuan Utama:** Memberikan legitimasi hukum secara instan.
- **Keputusan:** Setuju / Tolak / Revisi.
- **Data Minimum:** Daftar dokumen/pleno *pending*, SLA (*Service Level Agreement*).
- **Frekuensi:** Rendah - Menengah.
- **Risiko Salah Desain:** Telat merespons karena dokumen terkubur dalam menu kompleks.
