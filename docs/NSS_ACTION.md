# NSS ACTION
## NURISK SDUI Specification — Action & Navigation Contract

**Versi**: 1.0.0-draft
**Status**: DRAFT (P0)

Dokumen ini mendefinisikan kontrak baku (*canonical contract*) untuk seluruh aksi (interaksi pengguna) pada SDUI. Aksi ini disematkan pada properti `actions` di setiap node primitive (jika `enabled: true`).

---

## 1. Action Payload Structure
Aksi didefinisikan sebagai array of objects atau object tunggal.

```json
"actions": {
  "on_tap": {
    "type": "navigate",
    "target": "/incident/5"
  },
  "on_long_press": {
    "type": "bottom_sheet",
    "target": "menu_options"
  }
}
```

---

## 2. Supported Action Types

Hanya tipe aksi di bawah ini yang didukung oleh parser/handler Flutter. Tipe lain dilarang (`route`, `goto`, `push`).

### 2.1. Navigasi & Tampilan Layer

**`navigate`**
Berpindah halaman ke rute di dalam aplikasi.
```json
{
  "type": "navigate",
  "target": "/incident/5",
  "replace": false 
}
```
*Catatan: `target` WAJIB berupa path absolut fisik (`/incident/5`), bukan alias rute (`insiden.detail`).*

**`dialog`**
Memunculkan popup dialog modal (mengambil layout dari backend jika didefinisikan, atau fallback lokal).
```json
{
  "type": "dialog",
  "target": "confirm_delete"
}
```

**`bottom_sheet`**
Memunculkan lembar aksi dari bawah.
```json
{
  "type": "bottom_sheet",
  "target": "action_menu"
}
```

**`snackbar`**
Memunculkan pesan toast/snackbar singkat.
```json
{
  "type": "snackbar",
  "message": "Data berhasil disimpan",
  "status": "success"
}
```

### 2.2. Aksi Server / Form

**`submit`**
Mengirimkan form atau payload ke endpoint server.
```json
{
  "type": "submit",
  "endpoint": "/api/v1/action",
  "method": "POST",
  "payload": {
    "action_type": "profil.toggle_tersedia",
    "value": false
  },
  "on_success": {
    "type": "reload"
  }
}
```

**`toggle`**
Mengganti status boolean langsung dengan memanggil API (contoh: Switch on/off).
```json
{
  "type": "toggle",
  "endpoint": "/api/v1/setting/toggle",
  "state_key": "is_tersedia"
}
```

**`reload`**
Memuat ulang scene saat ini.
```json
{
  "type": "reload"
}
```

**`refresh`**
Sama dengan reload, tetapi memicu indikator *pull-to-refresh*.
```json
{
  "type": "refresh"
}
```

### 2.3. Eksternal & Utilitas

**`external_url`**
Membuka browser eksternal.
```json
{
  "type": "external_url",
  "url": "https://nurisk.id/privacy"
}
```

**`phone`**
Membuka aplikasi dialer.
```json
{
  "type": "phone",
  "number": "112"
}
```

**`email`**
Membuka email client.
```json
{
  "type": "email",
  "address": "support@nurisk.id"
}
```

**`download`**
Mengunduh file.
```json
{
  "type": "download",
  "url": "https://nurisk.id/file.pdf",
  "filename": "laporan.pdf"
}
```
