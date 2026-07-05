# NURISK Phase 19 — UI Architecture

Dokumen ini mendefinisikan arsitektur antarmuka (*UI Architecture*) berbasis Bootstrap 5 untuk NURISK.

## 1. Layout Application
Aplikasi NURISK akan menggunakan struktur tata letak administrasi klasik yang sudah diakrabi pengguna (misal: gaya AdminLTE/Tabler) karena terbukti memiliki *cognitive friction* paling rendah.

- **Top Navbar:** Menampilkan Nama Pengguna, Role yang sedang aktif, Notifikasi Lonceng, Jam Server, dan Tombol Sinkronisasi Manual (jika koneksi memburuk).
- **Sidebar (Kiri):** Menu Navigasi Utama yang dapat di-*collapse* (diperkecil) untuk menghemat ruang di layar tablet.
- **Main Content Area:** Ruang kerja utama. Menggunakan kontainer *fluid* (`container-fluid`).
- **Floating Action Button (FAB) / Quick Action Bar:** Posisi tetap di kanan bawah layar mobile atau baris atas di desktop untuk 5 aksi esensial.

## 2. Navigation Structure & Page Ownership
Navigasi akan merender tautan yang berbeda murni berdasarkan `Role` dari pengguna (*Role-Based Access Control*).

### PWNU (Pusat / Eksekutif)
- **Fokus:** Agregasi makro dan strategis.
- **Menu Utama:** Executive Dashboard, Peta Provinsi, Daftar Cabang (PCNU), Laporan Distribusi Bantuan.

### PCNU (Manajer Cabang Daerah)
- **Fokus:** Pemantauan taktis dan tata kelola yurisdiksi.
- **Menu Utama:** PCNU Dashboard, Daftar Posko, Daftar Relawan Cabang, Manajemen Pleno, Persetujuan Eskalasi.

### Posko (Operator Garis Depan)
- **Fokus:** Operasi harian, mutasi masuk/keluar, dan penugasan relawan seketika.
- **Menu Utama:** Posko Dashboard (Ops Center), Penugasan Harian, Mutasi Logistik, Entri Sitrep.

### Relawan (Mobile Web Worker)
- **Fokus:** Menerima tugas dan lapor. Tampilan dirancang *mobile-first*.
- **Menu Utama:** Tugas Saya, Lapor Titik Darurat (Assesment Cepat), Input Sitrep, Profil & Absen.

## 3. Komponen Inti UI
Sistem dilarang menggunakan SPA (React/Vue). Mengandalkan interaktivitas murni via:
- **Blade Components (`<x-card>`, `<x-alert>`, dll):** Reusability di *server-side*.
- **jQuery (untuk manipulasi DOM ringan):** Penanganan modal, tab, dan *form validation*.
- **Bootstrap 5 (CSS/JS):** Layout, grid, modal, offcanvas, dan utilitas responsif.
- **Fetch API / jQuery AJAX:** Untuk *polling* Command Center (interval 30 detik) dan memproses *Quick Actions* tanpa me-*reload* keseluruhan halaman jika memungkinkan.
