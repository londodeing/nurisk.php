# Decision-Centered Architecture

Arsitektur ini didesain terbalik: dari "Pertanyaan" menuju "Keputusan", baru kemudian direalisasikan dalam "Aksi" antarmuka (*UI Actions*).

---

## 1. Relawan Lapangan
- **Pertanyaan:** "Di mana saya dibutuhkan sekarang?"
- **Keputusan:** Menerima atau menolak penugasan.
- **Aksi (UI):** Klik tombol [Ambil Tugas] atau [Tandai Selesai].

## 2. Tim Reaksi Cepat (TRC)
- **Pertanyaan:** "Wilayah terisolir mana yang belum disentuh relawan?"
- **Keputusan:** Memprioritaskan jalur *assessment* hari ini.
- **Aksi (UI):** Klik *Nearby Incidents* lalu [Mulai Assessment].

## 3. Komandan Posko
- **Pertanyaan:** "Logistik apa yang kritis dan siapa relawan yang diam?"
- **Keputusan:** Menyesuaikan alokasi relawan atau meminta tambahan sembako.
- **Aksi (UI):** [Distribusi Ulang SDM] via *Decision Queue*, atau [Eskalasi ke PCNU].

## 4. Operator Posko
- **Pertanyaan:** "Apa lagi yang belum saya laporkan?"
- **Keputusan:** Memilih formulir logistik atau formulir Sitrep.
- **Aksi (UI):** [Input Sitrep] atau [Catat Mutasi].

## 5. Koordinator Klaster
- **Pertanyaan:** "Posko mana yang kekurangan pasokan air bersih (WASH) paling parah?"
- **Keputusan:** Menggeser *buffer stock* air bersih antar-posko.
- **Aksi (UI):** [Transfer Logistik Sektoral] di *Gap Analysis Card*.

## 6. Ketua PCNU
- **Pertanyaan:** "Apakah semua posko di bawah cabang saya terkendali?"
- **Keputusan:** Menggabungkan posko, menutup posko, atau meminta BKO PWNU.
- **Aksi (UI):** [Review Eskalasi Posko] dan [Kirim Permintaan Bantuan Provinsi].

## 7. Ketua PWNU / Eksekutif
- **Pertanyaan:** "Kabupaten mana yang akan runtuh jika tidak dibantu hari ini?"
- **Keputusan:** Memerintahkan pergerakan masif *buffer stock* provinsi.
- **Aksi (UI):** Membaca *Critical Area*, klik [Tindak Lanjuti via Telepon/Surat].

## 8. Penandatangan (Approver)
- **Pertanyaan:** "Dokumen eskalasi apa yang tertahan (bottleneck) di meja saya?"
- **Keputusan:** Mengevaluasi legalitas pengajuan dan menyetujuinya.
- **Aksi (UI):** [Approve Semua] atau [Revisi Dokumen].
