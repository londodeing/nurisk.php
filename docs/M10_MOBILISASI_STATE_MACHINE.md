# M10 MOBILISASI STATE MACHINE

## Overview
Status mobilisasi mematuhi strictly transisi yang ditentukan, membatasi modifikasi data kecuali dalam state yang diizinkan (draft atau disetujui untuk pre-deployment).

## State Enum
- `draft`: Data diinisiasi tapi belum difinalisasi.
- `disetujui`: Permintaan mobilisasi divalidasi dan siap untuk berangkat.
- `berangkat`: Tim/individu sedang dalam perjalanan.
- `tiba`: Tim/individu telah tiba di lokasi tujuan.
- `selesai`: Misi mobilisasi telah diselesaikan dan tim kembali.
- `dibatalkan`: Misi mobilisasi dibatalkan sebelum operasi aktif.

## Valid Transitions
Setiap transisi direpresentasikan via REST endpoint action spesifik:

1. **`draft` → `disetujui`**
   - **Endpoint:** `POST /api/v1/mobilisasi/{uuid}/approve`
   - **Kondisi:** Hanya boleh dilakukan oleh pihak yang berwenang (misal, Komandan Insiden/PCNU).
   
2. **`disetujui` → `berangkat`**
   - **Endpoint:** `POST /api/v1/mobilisasi/{uuid}/depart`
   - **Kondisi:** Mobilisasi harus sudah memiliki jadwal/target keberangkatan. Status diubah saat pemberangkatan fisik dimulai.

3. **`berangkat` → `tiba`**
   - **Endpoint:** `POST /api/v1/mobilisasi/{uuid}/arrive`
   - **Kondisi:** Hanya dapat dipicu jika status saat ini adalah `berangkat`. Otomatis mencatat waktu `waktu_tiba` ke timestamp saat itu.

4. **`tiba` → `selesai`**
   - **Endpoint:** `POST /api/v1/mobilisasi/{uuid}/finish`
   - **Kondisi:** Menandakan siklus penuh telah ditutup.

5. **`draft` → `dibatalkan`** ATAU **`disetujui` → `dibatalkan`**
   - **Endpoint:** `POST /api/v1/mobilisasi/{uuid}/cancel`
   - **Kondisi:** Transisi ini tidak dapat dipicu jika mobilisasi sudah `berangkat` atau `tiba`.

## Guard Clauses (Aturan Ketat)
- Transisi tidak resmi (misal: `draft` langsung ke `berangkat`) akan dilempar HTTP `422 Unprocessable Entity` atau `400 Bad Request` dari Controller/Service layer (bukan hanya frontend).
- Jika sync offline mengirimkan record status melompat, sync engine akan menolak update dan meregisterkan `Conflict`.
