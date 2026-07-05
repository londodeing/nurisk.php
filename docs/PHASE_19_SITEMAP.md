# NURISK Phase 19 — Unified Sitemap

Struktur hierarki menu ini dirancang berdasar urgensi di lapangan. Menu untuk setiap *role* disembunyikan jika mereka tidak memiliki otorisasi.

```text
Aplikasi NURISK (Web Base)
├── Dashboard
│   ├── PWNU Executive Summary
│   ├── PCNU Managerial View
│   ├── Posko Operational Board
│   └── Relawan Mobile Home
│
├── Peta & Komando (Command Center)
│   └── Live Map & Insiden Feed (Auto-refresh 30s)
│
├── Insiden & Posko
│   ├── Daftar Insiden Aktif
│   ├── Detail Insiden (Eskalasi, Assesment)
│   ├── Daftar Posko Aju
│   └── Kapasitas Posko (Overview)
│
├── Relawan & Penugasan
│   ├── Database Relawan (Profil & Skil)
│   ├── Mobilisasi BKO (Masuk/Keluar)
│   ├── Klaster Penugasan (Kesehatan, Logistik, Evakuasi)
│   └── Papan Penugasan (Kanban Board Task)
│
├── Laporan Lapangan
│   ├── Feed Sitrep (Realtime timeline)
│   └── Laporan Assesment Detail (TRC)
│
├── Logistik (Gudang)
│   ├── Etalase Stok
│   ├── Mutasi Masuk (Penerimaan)
│   ├── Mutasi Keluar (Distribusi)
│   └── Riwayat / Log Gudang
│
├── Tata Kelola (Governance)
│   ├── Rapat Pleno & Keputusan
│   ├── Kotak Masuk Surat
│   └── Arsip Surat Keluar
│
└── Pengaturan Sistem (Super Admin)
    ├── Master Data (Wilayah, Kategori Barang)
    └── Manajemen User & Akses
```
