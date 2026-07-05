# API_CONTRACT.md — NURISK API Specifications
# Kontrak API Resmi — Software Architect

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Status: FREEZE (Kontrak Resmi untuk Laravel Backend, Web Frontend, dan Flutter Mobile)

---

## 1. STANDAR GLOBAL API RESPONSES

Semua endpoint API di NURISK wajib mengembalikan format JSON yang konsisten.

### A. Respon Sukses (Success Response)
Digunakan untuk operasi GET single/list, POST, PUT, PATCH, dan DELETE.
```json
{
  "success": true,
  "message": "Data berhasil diambil/disimpan/diperbarui/dihapus",
  "data": {}
}
```
*Catatan: `data` bisa berupa Objek `{}` atau Array `[]`.*

### B. Respon Gagal Validasi (Validation Error - HTTP 422)
Wajib dikembalikan jika Form Request validation gagal.
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "kode_kejadian": [
      "Kolom kode kejadian wajib diisi.",
      "Kolom kode kejadian harus unik."
    ],
    "id_jenis_bencana": [
      "Jenis bencana yang dipilih tidak valid."
    ]
  }
}
```

### C. Respon Error Server / Otorisasi / General (HTTP 400, 401, 403, 404, 500)
```json
{
  "success": false,
  "message": "Pesan error spesifik (misal: Anda tidak memiliki akses ke scope wilayah ini / Data tidak ditemukan)"
}
```

### D. Standar Pagination Response
Setiap endpoint list data yang memiliki parameter pagination wajib mengembalikan format:
```json
{
  "success": true,
  "data": [
    {
      "id_insiden": 1,
      "kode_kejadian": "INC-20260616-001"
    }
  ],
  "links": {
    "first": "http://nurisk.test/api/insiden?page=1",
    "last": "http://nurisk.test/api/insiden?page=5",
    "prev": null,
    "next": "http://nurisk.test/api/insiden?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "http://nurisk.test/api/insiden",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

## 2. API SECURITY & RATE LIMITING

1. **Authentication**: Menggunakan **Laravel Sanctum** (Stateful Session untuk Web, Bearer Token untuk Flutter Mobile).
2. **Header Wajib**: 
   * `Accept: application/json`
   * `Authorization: Bearer {token}` (untuk Flutter Mobile)
3. **Rate Limiting**:
   * Endpoint Public / Auth (Login): Maksimal 10 request per menit per IP.
   * Endpoint Operasional API: Maksimal 100 request per menit per User ID.
4. **Scope & Authorization**: Setiap request akan divalidasi silang berdasarkan Role, Scope Wilayah, dan Assignment Kontekstual di Laravel Policy sebelum data diproses.

---

## 3. ENDPOINT AUTHENTICATION (`/api/auth`)

### A. Login Pengguna
* **Endpoint**: `POST /api/auth/login`
* **Request Payload**:
  ```json
  {
    "username": "operator_pcnu_demak",
    "password": "PasswordS1apS1aga!"
  }
  ```
* **Success Response (HTTP 200)**:
  ```json
  {
    "success": true,
    "message": "Login berhasil",
    "data": {
      "token": "1|abcdef1234567890...",
      "user": {
        "id_pengguna": 10,
        "username": "operator_pcnu_demak",
        "email": "demak@nurisk.nu.or.id",
        "default_scope_type": "pcnu",
        "default_scope_id": 3321,
        "role": {
          "id_peran": 3,
          "nama_peran": "pcnu",
          "level_otoritas": 3
        }
      }
    }
  }
  ```

### B. Logout Pengguna
* **Endpoint**: `POST /api/auth/logout`
* **Success Response (HTTP 200)**:
  ```json
  {
    "success": true,
    "message": "Logout berhasil"
  }
  ```

### C. Profil Pengguna Terautentikasi
* **Endpoint**: `GET /api/auth/profile`
* **Success Response (HTTP 200)**:
  ```json
  {
    "success": true,
    "data": {
      "id_pengguna": 10,
      "username": "operator_pcnu_demak",
      "email": "demak@nurisk.nu.or.id",
      "profil": {
        "nama_lengkap": "H. Ahmad Dahlan",
        "nomor_hp": "08123456789",
        "alamat": "Jl. Sultan Fatah No. 10, Demak"
      },
      "jabatan_aktif": [
        {
          "id_jabatan_posisi": 4,
          "nama_jabatan": "Komandan TRC",
          "tipe_lingkup": "pcnu",
          "id_lingkup": 3321
        }
      ]
    }
  }
  ```

---

## 4. ENDPOINT DOMAIN UTAMA

### A. Domain: INSIDEN (`/api/insiden`)
* **`GET /api/insiden`**: Ambil list insiden (filter: `status_insiden`, `id_pcnu`, `prioritas`).
* **`GET /api/insiden/{id}`**: Detail insiden lengkap dengan riwayat status.
* **`POST /api/insiden`**: Buat insiden baru (Hanya PCNU/PWNU/Super Admin).
  * Payload:
    ```json
    {
      "id_laporan_asal": 5,
      "id_jenis_bencana": 1,
      "id_pcnu": 3321,
      "kode_kejadian": "INC-20260616-012",
      "prioritas": "tinggi",
      "lokasi_deskripsi": "Kecamatan Karanganyar, Demak",
      "latitude": -6.8943,
      "longitude": 110.6385,
      "waktu_mulai": "2026-06-16 08:00:00"
    }
    ```
* **`PUT /api/insiden/{id}/status`**: Perbarui status alur kerja/transisi (Trigger riwayat status).
  * Payload:
    ```json
    {
      "status_insiden": "respon",
      "alasan": "Pleno memutuskan aktivasi tanggap darurat."
    }
    ```

### B. Domain: ASSESSMENT (`/api/assessment`)
* **`GET /api/assessment`**: List assessment.
* **`POST /api/assessment`**: Input hasil kaji cepat/lanjutan lapangan.
  * Payload:
    ```json
    {
      "id_insiden": 12,
      "jenis_laporan": "kaji_cepat",
      "cakupan_wilayah_deskripsi": "Desa Karanganyar RT 01-05",
      "dampak_manusia": {
        "meninggal": 0,
        "hilang": 2,
        "menderita_mengungsi": 350
      },
      "kebutuhan_mendesak": [
        {
          "nama_kebutuhan": "Tenda darurat",
          "jumlah": 5,
          "satuan": "Unit"
        },
        {
          "nama_kebutuhan": "Air bersih",
          "jumlah": 1000,
          "satuan": "Liter"
        }
      ]
    }
    ```

### C. Domain: SITREP (`/api/sitrep`)
* **`GET /api/sitrep?id_insiden={id}`**: Ambil list sitrep dari insiden tertentu.
* **`POST /api/sitrep`**: Buat draft sitrep baru (Auto snapshot via DB trigger).
  * Payload:
    ```json
    {
      "id_insiden": 12,
      "nomor_sitrep": 1,
      "id_assessment_basis": 4,
      "keadaan_umum": "Banjir mulai surut 10cm, cuaca mendung.",
      "upaya_pwnu_pcnu": "Penyaluran logistik tahap pertama di posko 1."
    }
    ```
* **`POST /api/sitrep/{id}/finalize`**: Finalisasi sitrep (mengunci snapshot & meng-generate hash SHA-256).

### D. Domain: LOGISTIK (`/api/logistik`)
* **`GET /api/logistik/gudang`**: List gudang di bawah scope wilayah user.
* **`GET /api/logistik/stok?id_gudang={id}`**: Cek stok barang per gudang.
* **`POST /api/logistik/permintaan`**: Ajukan permintaan barang logistik (oleh koordinator posko/klaster).
  * Payload:
    ```json
    {
      "id_insiden": 12,
      "id_gudang_tujuan": 2,
      "prioritas": "mendesak",
      "catatan_permintaan": "Kebutuhan mendesak untuk posko pengungsian utama",
      "items": [
        {
          "id_barang": 45,
          "jumlah_diminta": 50,
          "catatan_item": "Mie Instan Dus"
        }
      ]
    }
    ```
* **`PUT /api/logistik/permintaan/{id}/approve`**: Approve permintaan logistik (Oleh PWNU/PCNU logistik).

### E. Domain: RELAWAN (`/api/relawan`)
* **`POST /api/relawan/pendaftaran`**: Pendaftaran mandiri relawan (oleh user publik/relawan).
  * Payload:
    ```json
    {
      "id_relawan_kebutuhan": 3,
      "nik": "3321010101010001",
      "keahlian": [1, 3]
    }
    ```
* **`PUT /api/relawan/pendaftaran/{id}/verify`**: Verifikasi dan aktivasi relawan oleh PCNU.

### F. Domain: PLENO (`/api/pleno`)
* **`POST /api/pleno`**: Rencana rapat pleno pengambilan keputusan.
* **`POST /api/pleno/{id}/keputusan`**: Tambah butir keputusan pleno.
* **`POST /api/pleno/{id}/finalize`**: Finalisasi rapat pleno (kunci keputusan dan buat relasi surat resmi).

### G. Domain: SURAT KELUAR (`/api/surat`)
* **`POST /api/surat`**: Buat draf surat resmi.
  * Payload:
    ```json
    {
      "id_surat_jenis": 2,
      "perihal": "Surat Tugas Relawan Banjir Karanganyar",
      "isi_surat_snapshot": "Dengan ini menugaskan...",
      "id_jabatan_ttd": 4
    }
    ```
* **`POST /api/surat/{id}/paraf`**: Lakukan persetujuan paraf berurutan.
* **`POST /api/surat/{id}/sign`**: Tanda tangani surat jika seluruh paraf telah disetujui.

---

## 5. KETENTUAN UPLOAD FILE & MEDIA

Semua upload berkas wajib melewati endpoint `/api/media/upload` dan mematuhi batas berikut:

* **Ukuran Maksimal**: 
  * Foto / Gambar (PNG, JPG, JPEG): Maksimal 2MB.
  * Dokumen (PDF): Maksimal 5MB.
* **Mime Types**: `image/png`, `image/jpeg`, `image/jpg`, `application/pdf`.
* **Struktur folder internal di S3/Local storage**:
  * `/storage/app/public/insiden/{id_insiden}/` (Foto dokumentasi bencana)
  * `/storage/app/public/surat/{id_surat}/` (PDF dokumen surat resmi)
  * `/storage/app/public/sitrep/{id_sitrep}/` (PDF sitrep resmi)
* **Response Upload**:
  ```json
  {
    "success": true,
    "message": "Berkas berhasil diunggah",
    "data": {
      "id_file": 105,
      "file_path": "/storage/insiden/12/foto_banjir_01.jpg",
      "file_url": "http://nurisk.test/storage/insiden/12/foto_banjir_01.jpg"
    }
  }
  ```
