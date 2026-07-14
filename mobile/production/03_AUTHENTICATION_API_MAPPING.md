# NURISK MOBILE — AUTHENTICATION API MAPPING
## Document 03: Authentication API Contract
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Authentication  
**Base URL (dev)**: `http://127.0.0.1:8000/api`  
**Base URL (production)**: `https://app.nurisk.id/api`  
**Auth Header**: `Authorization: Bearer {sanctum_token}`

---

## KONVENSI DOKUMEN

| Field | Keterangan |
|-------|-----------|
| 🔓 | Endpoint publik, tidak perlu token |
| 🔐 | Endpoint protected, wajib token |
| ⚠️ | Endpoint ada di backend, belum final contract |
| ❌ | Endpoint belum ada, perlu dibuat |

---

## ENDPOINT 1: LOGIN

### `POST /api/auth/login` 🔓

**Fungsi**: Autentikasi user dengan nomor HP dan password. Mengembalikan Bearer Token.

**Request**:
```json
{
  "no_hp": "628123456789",
  "kata_sandi": "password123",
  "device_name": "flutter_samsung_a54_android"
}
```

**Field Validation**:
| Field | Rule |
|-------|------|
| `no_hp` | required, string |
| `kata_sandi` | required, string |
| `device_name` | optional, string, max:255, default: "api-client" |

**Response 200 (Success)**:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|AbcDefGhiJklMno...",
    "user": {
      "id_pengguna": 42,
      "no_hp": "628123456789",
      "status_akun": "aktif",
      "id_peran": 3,
      "terakhir_masuk": "2026-07-06T13:00:00.000000Z",
      "profil": {
        "nama_lengkap": "Ahmad Fauzi",
        "foto_profil": null,
        "jenis_kelamin": "L"
      },
      "peran": {
        "id": 3,
        "nama": "relawan"
      }
    }
  }
}
```

**Response 422 (Validation / Wrong Credentials)**:
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "no_hp": ["Nomor handphone atau kata sandi yang Anda masukkan salah."]
  }
}
```

**Response 403 (Account Inactive)**:
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "no_hp": ["Akun Anda belum aktif atau sedang dinonaktifkan/ditangguhkan."]
  }
}
```

**Possible Errors**:
| HTTP | Skenario | Flutter Action |
|------|---------|----------------|
| 422 | Credential salah | Tampilkan error di field |
| 422 | Akun tidak aktif | Tampilkan pesan dengan status akun |
| 500 | Server error | Tampilkan "Terjadi kesalahan sistem, coba lagi" |

**Caching**: ❌ Tidak dicache. Login selalu online.  
**Retry**: ❌ Tidak di-retry otomatis. Jika gagal, tampilkan error ke user.  
**Timeout**: 30 detik  
**Offline Behaviour**: Tampilkan dialog "Tidak ada koneksi internet. Login memerlukan koneksi."

---

## ENDPOINT 2: LOGOUT

### `POST /api/auth/logout` 🔐

**Fungsi**: Mencabut Bearer Token aktif dari database Sanctum.

**Request**: *(tidak perlu body)*
```
Authorization: Bearer {token}
```

**Response 200 (Success)**:
```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

**Possible Errors**:
| HTTP | Skenario | Flutter Action |
|------|---------|----------------|
| 401 | Token sudah invalid | Anggap logout berhasil, hapus lokal |
| 500 | Server error | Tetap lakukan local logout |

**Caching**: ❌ Tidak dicache.  
**Retry**: ❌ Tidak di-retry. Selalu lakukan local cleanup terlepas dari response.  
**Timeout**: 10 detik  
**Offline Behaviour**: Lakukan local logout (hapus token dari storage). API call diabaikan.

---

## ENDPOINT 3: FETCH PROFILE (ME)

### `GET /api/auth/me` 🔐

**Fungsi**: Mengambil profil lengkap user yang sedang login beserta relasi jabatan.

**Request**: *(tidak perlu body)*
```
Authorization: Bearer {token}
```

**Response 200 (Success)**:
```json
{
  "success": true,
  "data": {
    "id_pengguna": 42,
    "no_hp": "628123456789",
    "status_akun": "aktif",
    "id_peran": 3,
    "is_tersedia": true,
    "terakhir_masuk": "2026-07-06T13:00:00.000000Z",
    "profil": {
      "nama_lengkap": "Ahmad Fauzi",
      "foto_profil": null,
      "jenis_kelamin": "L",
      "tempat_lahir": "Sidoarjo",
      "tanggal_lahir": "1990-05-15"
    },
    "peran": {
      "id": 3,
      "nama": "relawan"
    },
    "jabatanPosisi": [
      {
        "jabatan": {
          "id": 7,
          "nama": "Koordinator Logistik"
        }
      }
    ]
  }
}
```

**Possible Errors**:
| HTTP | Skenario | Flutter Action |
|------|---------|----------------|
| 401 | Token invalid | Trigger refresh/logout flow |
| 500 | Server error | Gunakan data dari cache |

**Caching**: ✅ Cache di SQLite. TTL: 24 jam. Serve from cache jika offline.  
**Retry**: ✅ Retry 2x dengan backoff 2 detik jika network error.  
**Timeout**: 15 detik  
**Offline Behaviour**: Serve dari cache SQLite. Tampilkan banner "Data belum diperbarui."

---

## ENDPOINT 4: DEVICE TOKEN REFRESH

### `POST /api/v1/device/refresh-token` 🔓

**Fungsi**: Memperbaharui device_token (bukan Sanctum token). Digunakan untuk perpanjangan sesi device.

**Request**:
```json
{
  "device_uuid": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Response 200 (Success)**:
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "device_token": "randomstring60chars...",
    "expires_at": "2026-08-05T13:00:00+07:00"
  }
}
```

**Response 403 (Device Inactive)**:
```json
{
  "success": false,
  "message": "Device is not active."
}
```

**Possible Errors**:
| HTTP | Skenario | Flutter Action |
|------|---------|----------------|
| 403 | Device dinonaktifkan | Logout paksa + pesan "Device dinonaktifkan oleh admin" |
| 404 | Device tidak ditemukan | Device baru, lanjutkan dengan membuat device |
| 500 | Server error | Retry 3x, kemudian full re-login |

**Caching**: ❌ Tidak dicache.  
**Retry**: ✅ Retry 3x dengan backoff exponential.  
**Timeout**: 15 detik  
**Offline Behaviour**: Skip refresh. Gunakan device_token yang ada sampai online kembali.

---

## ENDPOINT 5: LIST DEVICES

### `GET /api/v1/devices` 🔐

**Fungsi**: Mendapatkan daftar semua device aktif milik user.

**Response 200**:
```json
{
  "data": [
    {
      "uuid_device": "550e8400-...",
      "platform": "Android",
      "app_version": "1.2.0",
      "status": "active",
      "trust_score": 100,
      "created_at": "2026-06-01T08:00:00Z"
    }
  ]
}
```

**Caching**: ✅ Cache 1 jam.  
**Retry**: ✅ Retry 2x.  
**Timeout**: 15 detik  
**Offline Behaviour**: Serve dari cache.

---

## ENDPOINT 6: LOGOUT SEMUA DEVICE

### `POST /api/v1/devices/logout-all` 🔐

**Fungsi**: Mencabut semua Sanctum token milik user (logout dari semua device).

**Response 200**:
```json
{
  "success": true,
  "message": "Semua device berhasil di-logout."
}
```

**Possible Errors**:
| HTTP | Skenario | Flutter Action |
|------|---------|----------------|
| 401 | Token sudah invalid | Anggap sudah logout |
| 500 | Server error | Tampilkan error, jangan lakukan local logout |

**Caching**: ❌  
**Retry**: ❌  
**Timeout**: 15 detik  
**Offline Behaviour**: Tampilkan error "Memerlukan koneksi internet."

---

## ENDPOINT 7: DELETE DEVICE SPECIFIC

### `DELETE /api/v1/devices/{uuid}` 🔐

**Fungsi**: Menonaktifkan satu device tertentu berdasarkan UUID.

**Response 200**:
```json
{
  "success": true,
  "message": "Device berhasil dihapus."
}
```

**Caching**: ❌  
**Timeout**: 15 detik  
**Offline Behaviour**: Tampilkan error "Memerlukan koneksi internet."

---

## ENDPOINT 8: GET MANDATES (untuk session context)

### `GET /api/governance/mandates?user_id={id}` 🔐

**Fungsi**: Mendapatkan semua mandate aktif milik user untuk ditampilkan di Mandate Picker.

**Query Parameters**:
| Parameter | Tipe | Keterangan |
|-----------|------|-----------|
| `user_id` | int | Filter berdasarkan user |
| `per_page` | int | Jumlah per halaman, default 15 |

**Response 200**:
```json
{
  "data": {
    "data": [
      {
        "id": 12,
        "sk_id": 5,
        "user_id": 42,
        "node_position_id": 8,
        "tanggal_mulai": "2026-01-01",
        "tanggal_berakhir": null,
        "status": "aktif",
        "sk": { "id": 5, "nomor": "SK/001/PWNU/2026" },
        "user": {
          "id_pengguna": 42,
          "profil": { "nama_lengkap": "Ahmad Fauzi" }
        }
      }
    ],
    "meta": { "total": 1 }
  }
}
```

**Caching**: ✅ Cache 12 jam di SQLite. Critical data — cache aggressively.  
**Retry**: ✅ Retry 3x.  
**Timeout**: 20 detik  
**Offline Behaviour**: Serve dari cache. Tampilkan timestamp "Terakhir diperbarui: {waktu}".

---

## ENDPOINT 9: GET MANDATE DETAIL

### `GET /api/governance/mandates/{id}` 🔐

**Fungsi**: Detail satu mandate beserta relasi posisi, node, dan SK.

**Response 200**:
```json
{
  "data": {
    "id": 12,
    "node_position_id": 8,
    "tanggal_mulai": "2026-01-01",
    "tanggal_berakhir": null,
    "status": "aktif",
    "sk": { "nomor": "SK/001/PWNU/2026", "tanggal": "2026-01-01" },
    "nodePosition": {
      "id": 8,
      "position": { "id": 3, "name": "Koordinator", "level": 2 },
      "node": {
        "id": 15,
        "name": "PCNU Sidoarjo",
        "territory_code": "3515",
        "structureLevel": { "name": "PCNU", "level": 3 }
      }
    }
  }
}
```

**Caching**: ✅ Cache 12 jam.  
**Timeout**: 15 detik  
**Offline Behaviour**: Serve dari cache.

---

## ENDPOINT YANG BELUM ADA — GAP LIST

| # | Endpoint | Kebutuhan | Priority |
|---|---------|-----------|---------|
| G-01 | `POST /api/auth/forgot-password` | Reset password via OTP | P0 untuk Sprint F2 |
| G-02 | `POST /api/auth/verify-otp` | Verifikasi OTP | P0 untuk Sprint F2 |
| G-03 | `POST /api/auth/reset-password` | Set password baru | P0 untuk Sprint F2 |
| G-04 | `PUT /api/auth/change-password` | Ganti password (authenticated) | P1 |
| G-05 | `GET /api/auth/me/permissions` | Structured permission list | **P0 untuk Sprint F1** |
| G-06 | `GET /api/auth/me/territories` | Territory aktif dari mandate | P1 |
| G-07 | `POST /api/auth/fcm-token` | Simpan FCM token ke server | P0 untuk Sprint F1 |
| G-08 | `DELETE /api/auth/fcm-token` | Hapus FCM token saat logout | P1 |

> **PENTING**: G-05 (permissions endpoint) adalah dependency kritikal untuk RBAC di Flutter. Tanpa ini, Flutter tidak dapat menampilkan menu dan tombol yang tepat berdasarkan permission.

---

## TIMEOUT MATRIX

| Endpoint | Timeout | Max Retry | Backoff |
|---------|---------|-----------|---------|
| Login | 30s | 0 | — |
| Logout | 10s | 0 | — |
| GET /me | 15s | 2 | 2s linear |
| Device Refresh | 15s | 3 | Exponential |
| GET Mandates | 20s | 3 | 2s linear |

---

*Document Status: DRAFT — Gap List G-01 hingga G-08 harus dikonfirmasi dengan tim backend sebelum Sprint F1*
