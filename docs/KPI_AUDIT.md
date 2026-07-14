# KPI Compliance Audit

Audit terhadap tempat kalkulasi indikator performa utama (KPI) di dalam aplikasi.

---

## Temuan 1: Model KPI Dashboard Publik (Klien Memegang Kendali)
- **File:** [kpi_cards_section.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/dashboard/presentation/widgets/kpi_cards_section.dart)
- **Kode Bukti:**
  Komponen widget ini membaca status dari state provider lokal `ref.watch(dashboardOrchestratorProvider)` lalu memanggil `kpi.activeIncidents`, `kpi.verifiedIncidents`, dan memetakan datanya ke dalam 4 kotak KPI statis.
- **Pelanggaran:**
  Struktur KPI dikunci sebanyak 4 kartu di Flutter. Format data dan perhitungannya berasal dari kelas model lokal `KpiModel`. Hal ini melanggar `Dashboard View Model` yang meminta data KPI dikirimkan sebagai daftar generik dari backend (misal: array `metrics` yang fleksibel).

---

## Temuan 2: Aggregator KPI di Sisi Backend
- **File:** `app/Http/Controllers/Api/PublicDashboardApiController.php`
- **Analisis:**
  API lama mengirimkan objek `kpi` sebagai struktur JSON kaku:
  ```json
  "kpi": {
    "activeIncidents": 2,
    "verifiedIncidents": 5,
    "deployedVolunteers": 12,
    "impactedRegions": 4
  }
  ```
- **Koreksi Arsitektur:**
  Peladen harus mengirimkan KPI sebagai widget SDUI tipe `SummaryCardGrid` atau sejenisnya, dengan array parameter dinamis:
  ```json
  {
    "type": "SummaryCardGrid",
    "props": {
      "items": [
        { "label": "Insiden Aktif", "value": "2", "color": "#EF4444", "icon": "fire" },
        { "label": "Personel Aktif", "value": "5", "color": "#10B981", "icon": "users" }
      ]
    }
  }
  ```
  Dengan skema di atas, peladen bisa menambah, mengurangi, atau mengganti metrik yang ditampilkan kapan saja tanpa merusak Flutter.
