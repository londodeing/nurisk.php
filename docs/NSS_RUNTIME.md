# NSS RUNTIME
## NURISK SDUI Specification — Runtime & Live Contract

**Versi**: 1.0.0-draft
**Status**: DRAFT (P0)

Dokumen ini mendefinisikan kontrak pengelolaan status (state management), penandaan versi (versioning), dan mesin pencari perbedaan (*Diff Engine*) untuk pembaruan *Live Runtime* secara instan.

---

## 1. Versioning Contract

Agar klien dapat mengidentifikasi pembaruan struktur SDUI, payload wajib menyertakan kontrak versi:

```json
{
  "schema_version": "1.0.0",
  "scene_id": "akun_workspace",
  "version": 1783678926701,
  "ttl_seconds": 120,
  "root": { ... }
}
```

*   **`schema_version`**: Mendefinisikan format JSON SDUI. Jika tidak cocok secara mayor dengan versi parser di Mobile, aplikasi **wajib melempar error (`throw`)** dan menolak *render* halaman. (Parser menolak *UnknownComponent fallback* untuk schema level).
*   **`version`**: Timestamp rilis UI/data terakhir.
*   **`ttl_seconds`**: Waktu hidup cache lokal sebelum klien wajib menarik data ulang (Time To Live).

---

## 2. Node Runtime Identity

Untuk memastikan efisiensi pencocokan *Diff Engine*, setiap node *HARUS* mengirimkan informasi identitas *runtime*:

```json
{
  "type": "Container",
  "id": "incident-card-5",
  "key": "assessment_5",
  "version": 17,
  "dirty": false
}
```

*   **`id`**: Kunci referensi unik absolut. Diff Engine Flutter akan menggunakan `id` ini untuk membedakan antara elemen yang perlu dirender ulang vs yang bisa dipakai ulang (reused).
*   **`key`**: (Opsional) Penanda data spesifik, sangat berguna untuk daftar bergulir (List/Grid) agar *state* scrolling tidak hilang.
*   **`version`** (Opsional pada tingkat node): Menandakan pembaruan mikro pada satu widget tanpa memuat ulang seluruh Scene.
*   **`dirty`**: Boolean (bawaan `false`). Jika `true`, menandakan data ini secara eksplisit telah berubah sejak ditarik terakhir kali, dan Flutter wajib melakukan sinkronisasi paksa pada *children* node tersebut.

---

## 3. Diff Engine & Patching Protocol

NURISK tidak akan me-render ulang seluruh halaman secara brutal. Ketika terjadi *polling* atau menerima data dari koneksi *real-time* (SSE/WebSocket), backend berhak mengirimkan **Patch Payload** alih-alih seluruh Scene JSON.

### Patch Format:
```json
{
  "patch": true,
  "target_scene": "akun_workspace",
  "mutations": [
    {
      "operation": "update",
      "node_id": "badge-status",
      "props": {
        "text": "Selesai",
        "background": "success"
      }
    },
    {
      "operation": "hide",
      "node_id": "incident-card-5"
    }
  ]
}
```
*(Flutter Engine bertanggung jawab untuk melakukan patching in-memory pada Widget Tree berdasarkan instruksi ini).*
