# ARCH-003B — DOMAIN RULE MATRIX

## 1. Validasi Status Insiden (BR-ASSESSMENT-001)
- **Aturan**: Assessment hanya dapat dibuat jika status insiden adalah `terverifikasi` atau `respon`.
- **Implementasi yang Dibutuhkan**: Validasi di `AssessmentPolicy@create` dan `StoreAssessmentRequest` (melalui cross-check `status_insiden`).
- **Pesan Error**: "Assessment hanya dapat dibuat untuk insiden yang sudah terverifikasi atau dalam tahap respon."

## 2. Kondisi Terkunci (BR-ASSESSMENT-002)
- **Aturan**: Assessment tidak dapat dimodifikasi jika insiden sudah terkunci (`is_locked = 1`).
- **Implementasi yang Dibutuhkan**: Cek `$insiden->isTerkunci()` di seluruh aksi update/delete di `AssessmentPolicy`.
- **Status Saat Ini**: Sebagian terimplementasi (ada pengecekan `isTerkunci()` di create/update, tapi perlu dipastikan juga di Service/FormRequest).

## 3. Otoritas Pembuatan (BR-ASSESSMENT-003)
- **Aturan**: Hanya user yang memiliki role global `super_admin`, `pwnu`, `pcnu` dalam scope, atau `relawan` dengan assignment aktif sebagai `trc` pada insiden tersebut.
- **Implementasi yang Dibutuhkan**: Logika 4-Layer Authorization pada `AssessmentPolicy@create`.

## 4. Pelestarian Riwayat (BR-ASSESSMENT-004 & BR-ASSESSMENT-005)
- **Aturan**: Assessment bersifat append-only/versioning logis. Assessment baru akan menggantikan assessment lama sebagai the latest version (`is_latest = 1`), tetapi riwayat assessment lama tidak dihapus secara fisik.
- **Implementasi yang Dibutuhkan**: `tr_single_latest_assessment` sudah memastikan status the latest. Namun, perlu ada validasi bahwa update fisik (edit data) hanya bisa dilakukan pada assessment yang `is_latest = 1` dan belum lewat masa tenggang (opsional). Aturan versi ketat menyarankan edit = insert versi baru.

## 5. Larangan Hapus Basis Sitrep (BR-ASSESSMENT-008)
- **Aturan**: Assessment utama yang direferensikan oleh `operasi_sitrep.id_assessment_basis` tidak boleh dihapus.
- **Implementasi yang Dibutuhkan**: Validasi di `AssessmentPolicy@delete` dan DB Exception Handling.

## 6. Sinkronisasi API vs Domain (Conflict Resolution)
- **Aturan**: `DOMAIN_RULES.md` mensyaratkan teks bebas `kebutuhan_pangan` dan `kebutuhan_medis`. `API_CONTRACT.md` mensyaratkan list objek dinamis `nama_kebutuhan`, `jumlah`, `satuan`.
- **Implementasi**: Assessment Domain mengadopsi struktur `API_CONTRACT.md` untuk kompatibilitas Flutter, sehingga Domain Rules secara efektif diperbarui (de facto).
