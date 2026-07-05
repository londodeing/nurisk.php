# M10 MOBILISASI FLUTTER CONTRACT

## Schema (Read/Write)
Entitas `mobilisasi` tidak lagi menggunakan integer IDs secara public. Aplikasi Flutter wajib mematuhi skema JSON berikut untuk sinkronisasi via `POST /api/v1/sync` serta standard endpoint REST.

```json
{
  "id": "e3b0c442-989b-4643-9877-0a2a1c0d45a7",
  "uuid_insiden": "2c9d64a0-7f21-4f11-9a7c-54cf8e3c1a3b",
  "id_pengguna": 1029, // TETAP INTEGER khusus id_pengguna jika mengikuti convention Auth, atau sesuaikan jika Auth pakai UUID.
  "jenis_mobilisasi": "relawan",
  "status_mobilisasi": "draft",
  "lokasi_asal": "Markas PCNU",
  "lokasi_tujuan": "Posko Desa A",
  "waktu_berangkat": null,
  "waktu_tiba": null,
  "catatan": "Butuh angkutan darat",
  "sync_version": 1,
  "dibuat_pada": "2026-06-17T12:00:00Z",
  "diperbarui_pada": "2026-06-17T12:00:00Z"
}
```
*(Catatan: Referensi `id_pengguna` pada codebase saat ini dibiarkan Integer atau mengikuti konvensi Auth. Akan diselaraskan sesuai struktur relawan yang ada.)*

## REST APIs
Aplikasi Flutter dapat menggunakan endpoint REST untuk kasus non-offline:

- `GET /api/v1/mobilisasi`
- `GET /api/v1/mobilisasi/{uuid}`
- `POST /api/v1/mobilisasi`
- `PUT /api/v1/mobilisasi/{uuid}`
- `DELETE /api/v1/mobilisasi/{uuid}`

### State Machine Endpoints
Transisi wajib menggunakan RPC-style endpoints:
- `POST /api/v1/mobilisasi/{uuid}/approve`
- `POST /api/v1/mobilisasi/{uuid}/depart`
- `POST /api/v1/mobilisasi/{uuid}/arrive`
- `POST /api/v1/mobilisasi/{uuid}/finish`
- `POST /api/v1/mobilisasi/{uuid}/cancel`

Payload untuk transisi status tidak wajib menyertakan body (atau sekadar membawa field `catatan`). Response sukses akan langsung mengembalikan objek mobilisasi yang terupdate versi sync-nya.

## Bulk Sync Operations
Offline synchronization hanya boleh dilempar ke:
`POST /api/v1/sync`

Di dalam request payload, objek dimasukkan ke dalam `changes` array dengan key `"table": "mobilisasi"`.

```json
{
    "device_id": "device_flutter_001",
    "request_id": "req-9876",
    "cursors": {
        "mobilisasi": 120
    },
    "changes": [
        {
            "table": "mobilisasi",
            "cursor": 0,
            "data": {
                "uuid_mobilisasi": "NEW-UUID",
                "uuid_insiden": "INSIDEN-UUID",
                "status_mobilisasi": "draft",
                "sync_version": 1
                ...
            }
        }
    ]
}
```

Jika terjadi konflik (Flutter `sync_version` lebih kecil dari Server), server akan membalas dengan status HTTP **`409 Conflict`**. Flutter app harus membaca property `data.conflicts` untuk menampilkan dialog resolusi kepada pengguna atau menimpa secara paksa pada sync berikutnya.
