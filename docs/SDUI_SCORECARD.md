# SDUI Maturity Matrix & Scorecard

Dokumen ini mendefinisikan metodologi penilaian tingkat kedewasaan (*maturity*) implementasi Server-Driven UI (SDUI) di platform NURISK, serta memetakan kondisi riil saat ini menggunakan **SDUI Maturity Matrix**.

---

## 1. Metodologi Penilaian (SDUI Maturity Levels)

Setiap domain dinilai berdasarkan 4 pilar arsitektur SDUI NURISK dengan klasifikasi tingkat kedewasaan sebagai berikut:

1.  **UI Layout Control (UI):** Apakah susunan tata letak komponen dikirim oleh peladen (`BFF`), bukan dideklarasikan secara statis di Flutter?
2.  **Data Aggregation (Data):** Apakah seluruh metrik, status lencana (*badge*), KPI, dan data presentasi dihitung/diformat di peladen (`BFF`) sebelum dikirim?
3.  **Dynamic Renderer (Renderer):** Apakah klien (Flutter) merender antarmuka menggunakan komponen generik (*Universal Renderer*) tanpa adanya logika *hardcode* percabangan peran atau status?
4.  **BFF Dynamic Integration (BFF):** Apakah rute penanganan aksi (*Action Resolver*) dan navigasi dikirim secara dinamis oleh peladen via BFF?

### Klasifikasi Status Domain:
*   **Traditional (0-25%):** Seluruh antarmuka, navigasi, dan pengolahan data dikunci mati di Flutter (*Fat Client*).
*   **Hybrid (26-75%):** Data dimuat secara dinamis, namun rendering visual dan alur navigasi masih bergantung pada logika statis Flutter.
*   **Mature (76-100%):** Klien bertindak murni sebagai Universal Renderer pasif. Seluruh layout, styling, navigasi, dan hak akses dikendalikan penuh oleh BFF.

---

## 2. SDUI Maturity Matrix

Berdasarkan investigasi forensik kode sumber, berikut adalah tabel matriks kematangan domain NURISK:

| Domain | UI Layout (UI) | Data Format (Data) | Renderer Client | BFF Actions (BFF) | Status Akhir | Deskripsi Kesenjangan Utama |
| :--- | :---: | :---: | :---: | :---: | :--- | :--- |
| **Public Dashboard** | ✅ | ✅ | ✅ | ✅ | **Mature** (95%) | Kepatuhan penuh setelah perbaikan Phase 2.2C. |
| **Profile & Account** | ✅ | ✅ | ❌ | ✅ | **Hybrid** (55%) | Widget identitas dan ikon settings masih di-*hardcode* di Flutter. |
| **Operations & Map (COP)** | ❌ | ✅ | ❌ | ❌ | **Traditional** (15%) | Kontrol layer, warna pin, legenda, dan popup keras di Flutter. |
| **Lapor (Report Wizard)** | ❌ | ❌ | ❌ | ❌ | **Traditional** (10%) | Input form fields didefinisikan manual di Flutter. |
| **TRC Assessment** | ❌ | ❌ | ❌ | ❌ | **Traditional** (10%) | Penugasan di-*hardcode* warna lencana dan aksi klik formulir. |
| **Governance Dashboard** | ❌ | ❌ | ❌ | ❌ | **Traditional** (0%) | BFF Controller khusus untuk Pimpinan belum dibuat. |

---

## 3. Rumus Perhitungan Nilai (Weighted Scoring Formula)

Skor persentase untuk setiap domain dihitung menggunakan pembobotan berikut:
$$\text{Skor Domain} = (\text{UI} \times 30\%) + (\text{Data} \times 30\%) + (\text{Renderer} \times 20\%) + (\text{BFF} \times 20\%)$$

*Di mana nilai untuk setiap pilar yang terpenuhi adalah 100%, setengah terpenuhi adalah 50%, dan tidak terpenuhi adalah 0%.*

### Contoh Perhitungan: Profile & Account
*   **UI Layout Control:** Terpenuhi (100% $\times$ 30% = 30%)
*   **Data Aggregation:** Terpenuhi (100% $\times$ 30% = 30%)
*   **Dynamic Renderer:** Tidak Terpenuhi (0% $\times$ 20% = 0%)
*   **BFF Actions:** Setengah Terpenuhi (50% $\times$ 20% = 10%)
*   **Total Skor:** **70%** (Mengalami penyesuaian karena menu aksi logout kini dinamis pasca Phase 2.2C).
