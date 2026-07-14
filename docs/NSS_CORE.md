# NSS CORE
## NURISK SDUI Specification — Core Primitives & Tokens

**Versi**: 1.0.0-draft
**Status**: DRAFT (P0)

Dokumen ini adalah **Single Source of Truth** untuk seluruh struktur dasar Primitive, Properties, Layout Constraint, dan Design Tokens di sistem NURISK SDUI.

---

## 1. Node Foundation (Base Attributes)
Setiap primitive wajib mendukung base attributes berikut di root level node (bukan di dalam `props`):

```json
{
  "type": "Container",
  "id": "unique-node-id",
  "key": "analytics-key",
  "visible": true,
  "enabled": true,
  "props": {},
  "children": []
}
```
*   **`id`**: Wajib unik. Digunakan oleh Diff Engine, Patching, dan Live Update.
*   **`key`**: Digunakan untuk state preservation dan tracking analytics (misal: `submit-button-assessment`).
*   **`visible`**: Kontrol rendering. Backend tidak boleh menghapus node dari JSON untuk menyembunyikan elemen; gunakan `visible: false` agar Live Update dapat mengembalikannya tanpa merusak tree struktur.
*   **`enabled`**: Status interaktif (disable tombol/form).

---

## 2. Design Tokens (Style Contract)
Backend dilarang mengirimkan nilai fisik (seperti `16`, `Color(0xFF...)`, `green-700`).

### A. Color Tokens
*   `primary`, `secondary`, `surface`, `background`, `danger`, `warning`, `info`, `success`
*   `text_main`, `text_muted`, `text_inverse`

### B. Radius Tokens
*   `none` (0px), `sm` (4px), `md` (8px), `lg` (16px), `xl` (24px), `full` (50%)

### C. Typography Tokens
*   `headline`, `title`, `subtitle`, `body`, `caption`, `metric`

---

## 3. Spacing & Constraint Schema
Padding dan Margin wajib menggunakan format objek yang konsisten, tidak boleh integer tunggal. 

### Format Spacing yang Diizinkan:
1.  **Semua sisi (All)**: `{"all": 16}`
2.  **Sumbu X/Y**: `{"x": 16, "y": 8}`
3.  **Spesifik (LTRB)**: `{"t": 8, "b": 16, "l": 16, "r": 16}`

---

## 4. Canonical Primitive List
Berikut adalah spesifikasi kanonikal dari 28+ Primitive resmi.

### 4.1. Layout & Constraint Primitives (Krusial)
Komponen ini mengontrol dimensi dan pembatas (constraints) pada layout modern.

**Expanded**
Memaksa child untuk mengisi ruang tersisa (hanya valid di dalam Row/Column).
```json
{
  "type": "Expanded",
  "id": "exp-1",
  "props": { "flex": 1 },
  "children": [...]
}
```

**Flexible**
Membiarkan child mengisi ruang tersisa secara proporsional.
```json
{
  "type": "Flexible",
  "id": "flex-1",
  "props": { "flex": 1, "fit": "loose" },
  "children": [...]
}
```

**SizedBox**
Memberikan dimensi absolut (pengganti Spacer atau blank area).
```json
{
  "type": "SizedBox",
  "id": "box-1",
  "props": { "width": 16, "height": 16 }
}
```

**AspectRatio**
Menjaga rasio panjang-lebar child.
```json
{
  "type": "AspectRatio",
  "id": "ar-1",
  "props": { "ratio": 1.5 },
  "children": [...]
}
```

### 4.2. Base Structural Primitives

**Container**
```json
{
  "type": "Container",
  "id": "container-main",
  "props": {
    "padding": { "all": 16 },
    "margin": { "b": 8 },
    "background": "surface",
    "border": "none",
    "radius": "lg"
  },
  "children": [...]
}
```
*(Catatan: Properti warna sekarang dipisah secara fungsional: `background`, `foreground`, `border`)*

**Row / Column**
```json
{
  "type": "Row",
  "id": "row-header",
  "props": {
    "spacing": 8,
    "mainAxisAlignment": "start",
    "crossAxisAlignment": "center",
    "scrollable": false
  },
  "children": [...]
}
```

**Text**
```json
{
  "type": "Text",
  "id": "txt-title",
  "props": {
    "text": "Konten",
    "style": "headline",
    "foreground": "text_main",
    "align": "left",
    "maxLines": 2,
    "overflow": "ellipsis"
  }
}
```

**Icon**
```json
{
  "type": "Icon",
  "id": "ic-warning",
  "props": {
    "name": "warning",
    "size": 24,
    "foreground": "danger"
  }
}
```

**Badge**
```json
{
  "type": "Badge",
  "id": "badge-status",
  "props": {
    "text": "Aktif",
    "background": "success",
    "foreground": "surface"
  }
}
```

*(Daftar lengkap 28 Primitive akan didokumentasikan di matriks komplain NSS).*
