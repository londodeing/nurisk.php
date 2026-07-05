# PHASE 19 — REVISED IMPLEMENTATION PLAN

Berdasarkan audit fungsional dari arsitektur NURISK awal, berikut adalah perombakan *roadmap* eksekusi (Sprint). Dashboard tidak lagi dikotakkan menjadi unit organisasi statis, melainkan dipisahkan menjadi kokpit sesuai *job-desk* pengambil keputusan.

## Completed (Legacy / Validated)
- **Sprint 19A:** UI Foundation & Design System (Selesai).
- **Sprint 19B:** Posko Dashboard (Di-rebranding menjadi *Komandan Posko Dashboard* - Selesai).
- **Sprint 19C:** PCNU Dashboard (Di-rebranding menjadi *PCNU Mission Coordination* - Selesai).

## The Revised Roadmap

### Sprint 19D — PWNU Executive Dashboard
- **Target:** Dashboard tren makro dan top-5 krisis. Tanpa tabel operasional.
- **Estimasi:** 3 Hari.

### Sprint 19E — TRC Dashboard
- **Target:** Dashboard *mobile-friendly* dengan fokus ke GPS, lokasi radius insiden, dan form *Quick Assessment*.
- **Estimasi:** 4 Hari.

### Sprint 19F — Komandan Posko Dashboard (Refinement)
- **Target:** Penyesuaian `posko.blade.php` agar hanya melayani keputusan eskalasi dan pengawasan *Bottleneck*. Membuang modul form *input* panjang.
- **Estimasi:** 2 Hari.

### Sprint 19G — Operator Posko Dashboard
- **Target:** Membangun *Data Entry Center*. Mengutamakan navigasi *keyboard* (TAB) untuk operator *Shift* administrasi yang mengetik mutasi logistik.
- **Estimasi:** 3 Hari.

### Sprint 19H — Koordinator Klaster Dashboard
- **Target:** Pembuatan *Gap Analysis* spesifik sektoral (kesehatan vs logistik).
- **Estimasi:** 3 Hari.

### Sprint 19I — Governance Approval Center
- **Target:** Penyatuan seluruh *Pending Surat* dan *Pending Pleno* ke dalam satu layar eksekusi 1-klik untuk Approver (Ketua/Sekretaris). Memuat *SLA Monitor*.
- **Estimasi:** 4 Hari.

### Sprint 19J — Command Center Final Integration
- **Target:** Menghubungkan titik *polling* ke layar proyektor raksasa, mengkalibrasi rasio UI agar terbaca dari jarak 5 meter.
- **Estimasi:** 2 Hari.

### Sprint 19K — UAT + Human Validation + UX Hardening
- **Target:** Pengujian keseluruhan (*end-to-end*). Menyisir sisa kelebihan klik (*click-depth*) dan memperbaiki kontras layar.
- **Estimasi:** 4 Hari.

---

> **Kesimpulan Final Rekonstruksi:**
> Arsitektur "Satu Organisasi = Satu Dashboard" tidak valid dalam operasi bertekanan tinggi. Transisi menuju "Satu Keputusan = Satu Dashboard" (*Role-Driven*) adalah jalur paling logis untuk mencapai target waktu kurang dari 10 detik per respon lapangan. Skema ini telah dideklarasikan sebagai spesifikasi final NURISK Phase 19.
