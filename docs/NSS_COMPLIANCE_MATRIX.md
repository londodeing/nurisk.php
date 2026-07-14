# NSS Primitive Compliance Matrix (AT-003)

Matriks di bawah ini memantau tingkat kepatuhan implementasi SDUI terhadap Spesifikasi NSS-CORE. 
*Catatan: Dashboard Publik tidak akan dimigrasi sebelum seluruh komponen dalam daftar ini mencapai status 100% Coverage.*

| Primitive | NSS | Builder | Composer | Validator | Parser | Registry | Renderer | Golden |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Container** | `pri_container` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Row** | `pri_row` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Column** | `pri_column` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Card** | `pri_card` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Text** | `pri_text` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Icon** | `pri_icon` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Button** | `pri_button` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Badge** | `pri_badge` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Divider** | `pri_divider` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Grid** | `pri_grid` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **List / ListView**| `pri_list` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Stack** | `pri_stack` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Positioned** | `pri_positioned` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Map** | `pri_map` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Timeline** | `pri_timeline` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **BottomSheet** | `pri_bottom_sheet` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Accordion** | `pri_accordion` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Chart** | `pri_chart` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **FormField** | `pri_form_field` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Checkbox** | `pri_checkbox` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Switch** | `pri_switch` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Dropdown** | `pri_dropdown` | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Wrap** | `pri_wrap` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Spacer** | `pri_spacer` | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Expanded** | `pri_expanded` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Flexible** | `pri_flexible` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **SizedBox** | `pri_sized_box`| ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **AspectRatio**| `pri_aspect` | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ |

---

## 2. Tingkat Kepatuhan Saat Ini (Coverage Report)

*   **Total Primitive NSS Resmi**: 28
*   **Kesiapan Backend (Laravel)**: 0 / 28 mematuhi NSS (Sebagian besar menggunakan styling lama / tailwind).
*   **Kesiapan Registry (Flutter)**: 22 / 28 terdaftar (Terdapat primitive yang baru ditambahkan seperti Expanded, dll).
*   **Kesiapan Renderer (Flutter)**: 10 / 28 mematuhi NSS (Container, Row, Column, Text, Icon, Badge, dan 4 layout constraints telah direfactor).
*   **Total Coverage**: **35%** (Di sisi Frontend) 

---

### **Task Selanjutnya (AT-004)**
Seluruh 28 primitive pada tabel ini harus diubah statusnya menjadi **✅ (PASS)** sebelum *Dashboard Publik* diizinkan untuk direfactor. Langkah pemulihan akan difokuskan ke Parser dan Registry Flutter.
