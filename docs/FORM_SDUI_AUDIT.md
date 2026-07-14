# Form SDUI Compliance Audit

Audit terhadap formulir dinamis di dalam aplikasi (Form Laporan, Form Assessment TRC, dan Form Persetujuan Dokumen).

---

## 1. Wizard Laporan Kejadian (Report Wizard)
- **File:** [report_wizard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/report/presentation/screens/report_wizard_screen.dart)
- **Pelanggaran Arsitektur:**
  Seluruh tahapan form lapor (Step 1: Wilayah, Step 2: Klasifikasi Bencana, Step 3: Deskripsi & Foto) dibangun secara lokal. Elemen-elemen formulir (`TextField`, `Dropdown`, dan input foto) ditulis permanen.
- **Dampak Ketidakpatuhan:**
  Jika pengurus pusat (PBNU/PCNU) ingin meminta kolom input tambahan saat pelaporan (misalnya "Estimasi Jumlah Pengungsi"), perubahan ini mengharuskan rilis baru aplikasi seluler.
- **Rekomendasi SDUI:**
  Formulir harus dikirimkan sebagai susunan JSON Field dari BFF:
  ```json
  "form_fields": [
    { "id": "keterangan_situasi", "label": "Deskripsi Kejadian", "type": "text_area", "required": true },
    { "id": "estimasi_korban", "label": "Estimasi Korban", "type": "number", "required": false }
  ]
  ```

---

## 2. Wizard Penilaian Lapangan TRC (Assessment Wizard)
- **File:** [assessment_wizard_screen.dart](file:///home/londo/nurisk/mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart)
- **Pelanggaran Arsitektur:**
  Sama seperti Wizard Laporan, form penilaian TRC (mencakup data dampak manusia, kebutuhan logistik mendesak, infrastruktur rusak) dideklarasikan manual di sisi klien. Perubahan indikator penilaian lapangan akan merusak keselarasan klien jika skema tidak diubah menjadi SDUI.
