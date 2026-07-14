# Widget Inventory Audit

Audit terhadap seluruh komponen antarmuka (*Widgets*) aplikasi NURISK.

| Nama Widget | File Flutter | Status | Sumber Tata Letak / Konfigurasi |
| :--- | :--- | :--- | :--- |
| **HeaderBanner** | `widget_factory.dart` | **SDUI** | Dibuat dinamis dari JSON BFF. |
| **SummaryCard** | `widget_factory.dart` | **SDUI** | Dibuat dinamis dari JSON BFF. |
| **ActionList** | `widget_factory.dart` | **SDUI** | Dibuat dinamis dari JSON BFF. |
| **DocumentQueue** | `widget_factory.dart` | **SDUI** | Dibuat dinamis dari JSON BFF. |
| **WarningBanner** | `warning_banner.dart` | **Legacy UI** | Hardcoded di Flutter (masih dipakai di halaman publik lama). |
| **KpiCardsSection** | `kpi_cards_section.dart` | **Legacy UI** | Hardcoded warna, label, dan ikon di Flutter. |
| **WeatherCard** | `weather_card.dart` | **Legacy UI** | Hardcoded di Flutter. |
| **IncidentFeed** | `incident_feed.dart` | **Legacy UI** | Hardcoded logika penguraian di Flutter. |
| **CtaLogin** | `cta_login.dart` | **Legacy UI** | Hardcoded di Flutter. |
| **CtaVolunteer** | `cta_volunteer.dart` | **Legacy UI** | Hardcoded di Flutter. |
| **SettingsCard** | `settings_card.dart` | **Partial SDUI** | Parameter `action` di-render dari JSON, tetapi switch-case ikon masih hardcoded di Flutter. |
| **QuickCommandWidget** | `quick_command_widget.dart`| **Legacy UI** | Tombol SPK, Aktivasi Posko dll di-hardcode secara statis di Flutter. |
| **GovernanceTimeline**| `governance_timeline_widget.dart`|**Legacy UI** | Timeline persetujuan dokumen di-hardcode di Flutter. |
| **SpatialFilter** | `spatial_filter_bottom_sheet.dart`|**Legacy UI** | Filter spasial kebencanaan statis di Flutter. |

---

### Kode Bukti Kunci (Hardcoded Widget)
Contoh dari berkas [kpi_cards_section.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/dashboard/presentation/widgets/kpi_cards_section.dart#L24-L27):
```dart
_buildKpiCard(context, 'Insiden Aktif', kpi.activeIncidents.toString(), Icons.local_fire_department, Colors.red),
_buildKpiCard(context, 'Personel Aktif', kpi.verifiedIncidents.toString(), Icons.group, Colors.green),
_buildKpiCard(context, 'Korban Terdampak', kpi.impactedRegions.toString(), Icons.personal_injury, Colors.blue),
_buildKpiCard(context, 'Kebutuhan Mendesak', kpi.deployedVolunteers.toString(), Icons.warning, Colors.orange),
```
Semua parameter (ikon, warna, teks label) dipasang langsung di Flutter.
Jika *backend* ingin menambahkan satu kartu KPI baru (misalnya "Armada Ambulans"), pengembang **wajib menulis ulang kode Flutter dan merilis build baru**. Ini melanggar arsitektur SDUI.
