# Architecture Recovery Roadmap: Phase 3.0

Dokumen ini mendefinisikan strategi transisi bertahap (*Architecture Recovery*) untuk mengembalikan NURISK Mobile & Backend kembali ke prinsip Server-Driven UI (SDUI) yang sejati tanpa memicu regresi atau bug baru.

---

## 1. Dashboard (Publik & Utama)
*   **Status Saat Ini:** **Mature** (Pasca Phase 2.2C)
*   **Sasaran Akhir:** Mempertahankan kestabilan dynamic rendering.
*   **Rencana Migrasi:**
    - *Sprint A:* Pemantauan error rate setelah integrasi `WidgetFactory`.
*   **Risiko & Mitigasi:** 
    - *Risiko:* Perubahan data darurat di server memicu crash render di Flutter.
    - *Mitigasi:* Menggunakan *fallback widget* (EmptyStateWidget) di Flutter jika tipe widget dari BFF tidak dikenali.

---

## 2. Profile & Account
*   **Status Saat Ini:** **Hybrid** (55%)
*   **Sasaran Akhir:** **Mature** (100% didikte dari BFF)
*   **Kesenjangan (Gap):**
    - Identitas profil (`IdentityCard`) masih native statis.
    - Ikon pengaturan di-hardcode berdasarkan nama ID di Flutter.
*   **Rencana Migrasi:**
    - *Sprint B:* Mengubah response `settings` BFF agar menyertakan string ikon standar (material design glyph).
    - *Sprint C:* Mengubah `IdentityCard` di Flutter agar dirender sebagai generic widget `SummaryCard`.
*   **Risiko & Mitigasi:**
    - *Risiko:* Tombol logout tidak berfungsi jika action mapping salah di server.
    - *Mitigasi:* Melakukan unit test pada `ActionResolver` khusus untuk tipe aksi internal.

---

## 3. Map & COP (Common Operational Picture)
*   **Status Saat Ini:** **Traditional** (15%)
*   **Sasaran Akhir:** **Mature** (100% SDUI untuk Layer & Legend)
*   **Kesenjangan (Gap):**
    - Layer kontrol keras di sisi Flutter.
    - Warna penanda (*marker*) dan legenda peta statis.
*   **Rencana Migrasi:**
    - *Sprint D:* Pembuatan `MapLayerBffController` di Laravel yang mengembalikan daftar layer aktif dan legenda.
    - *Sprint E:* Refactor `MapScreen` di Flutter agar membaca konfigurasi layer dinamis dan menerapkan penataan gaya dinamis.
*   **Risiko & Mitigasi:**
    - *Risiko:* Performa rendering peta menurun saat mengurai konfigurasi dinamis.
    - *Mitigasi:* Menggunakan caching layer di Flutter (`drift` SQLite local cache) untuk metadata layer.

---

## 4. Governance & Executive Dashboard
*   **Status Saat Ini:** **Traditional** (0%)
*   **Sasaran Akhir:** **Mature** (100% SDUI)
*   **Kesenjangan (Gap):**
    - Belum ada BFF khusus untuk dasbor Keputusan/Pimpinan.
    - Widget timeline dan antrean tanda tangan dokumen statis.
*   **Rencana Migrasi:**
    - *Sprint F:* Pembuatan `GovernanceBffController` di Laravel.
    - *Sprint G:* Penggabungan antrean persetujuan dokumen ke dalam `DocumentQueueWidget` generik di Flutter.
*   **Risiko & Mitigasi:**
    - *Risiko:* Pimpinan salah menyetujui dokumen penting akibat inkonsistensi status.
    - *Mitigasi:* Penerapan tanda tangan elektronik (e-signature) berbasis OTP yang diisolasi di luar parser SDUI.

---

## 5. Report Wizard (Lapor Kejadian)
*   **Status Saat Ini:** **Traditional** (10%)
*   **Sasaran Akhir:** **Hybrid / Mature** (Formulir dinamis berbasis JSON Schema)
*   **Kesenjangan (Gap):**
    - Seluruh form field (input teks, dropdown kategori bencana) statis di Flutter.
*   **Rencana Migrasi:**
    - *Sprint H:* Rancang skema JSON Form NURISK di Laravel.
    - *Sprint I:* Bangun `SduiFormRenderer` di Flutter untuk membangun form field dinamis dari skema tersebut.
*   **Risiko & Mitigasi:**
    - *Risiko:* Validasi input (misal: panjang karakter) terlewat di klien.
    - *Mitigasi:* Validasi form dijalankan di dua sisi, dengan server sebagai penentu akhir (*single source of truth*).
