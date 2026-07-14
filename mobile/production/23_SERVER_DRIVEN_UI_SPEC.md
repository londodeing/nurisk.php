# NURISK — SERVER DRIVEN UI & FEATURE FLAG SPECIFICATION
## Document 23: Dynamic Dashboard Configuration Layer
**Version**: 1.0.0 | **Status**: PENDING REVIEW | **Domain**: Public Layer  
**Author**: Enterprise Mobile Solution Architect

---

## 1. OBJECTIVE

Dokumen ini mendefinisikan transisi arsitektur NURISK Mobile dari *Hardcoded UI* menuju **Server-Driven UI (SDUI)**.
Tujuannya adalah mengeliminasi kebutuhan merilis ulang aplikasi (update APK/App Store) setiap kali ada perubahan prioritas tata letak, penambahan banner *event* (Ramadan, Idul Adha), atau penonaktifan modul yang sedang bermasalah/maintenance.

---

## 2. DASHBOARD CONFIGURATION LAYER

Backend NURISK akan menyediakan satu endpoint baru untuk mengendalikan tata letak *Dashboard* Publik:
- **Endpoint**: `GET /api/public/dashboard/config`
- **Tujuan**: Menentukan *Widget* apa saja yang aktif dan bagaimana urutan (*order*) tampilannya.

### 2.1 JSON Schema Contract
```json
{
  "version": "1.0",
  "layout": [
    "warning_banner",
    "weather_card",
    "kpi_section",
    "incident_feed",
    "cta_volunteer",
    "org_summary",
    "news_section",
    "cta_login"
  ],
  "feature_flags": {
    "show_donation": false,
    "enable_push_notification": true,
    "ramadan_theme": false
  }
}
```

---

## 3. WIDGET REGISTRY PATTERN

Dashboard tidak lagi mengimpor widget secara statis dan meletakkannya satu per satu di `CustomScrollView`.
Alih-alih, Dashboard mendelegasikan tugas *rendering* kepada sebuah `WidgetRegistry`.

### 3.1 Konsep Registry (Flutter)
```dart
class DashboardWidgetRegistry {
  static Widget build(String componentId) {
    switch (componentId) {
      case 'warning_banner':
        return const WarningBanner();
      case 'weather_card':
        return const WeatherCard();
      case 'kpi_section':
        return const KpiCardsSection();
      case 'incident_feed':
        return const IncidentFeedList();
      case 'cta_volunteer':
        return const CtaVolunteer();
      // ... future modules
      default:
        return const SizedBox.shrink(); // Unknown component handler
    }
  }
}
```

### 3.2 Dynamic Slivers
`PublicDashboardScreen` akan berubah menjadi perulangan (*mapping*) dinamis:
```dart
SliverList(
  delegate: SliverChildBuilderDelegate(
    (context, index) {
      final componentId = config.layout[index];
      return WidgetRegistry.build(componentId);
    },
    childCount: config.layout.length,
  ),
)
```

---

## 4. FEATURE FLAG ENGINE

Selain mengontrol urutan (*layout*), *Config Layer* juga bertindak sebagai **Feature Flag Engine**.
- **A/B Testing**: Backend dapat menyajikan konfigurasi *layout* yang berbeda kepada pengguna yang berbeda (atau berdasarkan probabilitas acak).
- **Maintenance Mode / Kill Switch**: Jika API Berita down, admin NURISK cukup mengubah `"news_section"` dari database layout, dan widget akan langsung lenyap dari HP seluruh pengguna dalam hitungan menit (bergantung pada TTL cache konfigurasi).
- **Conditional Triggering**: Tombol donasi atau spanduk relawan hanya aktif bila *feature_flag* bernilai `true`.

---

## 5. CACHING & OFFLINE STRATEGY

- Konfigurasi JSON ini **WAJIB DI-CACHE** ke dalam SQLite lokal (`config_table.dart`).
- Saat aplikasi dibuka tanpa internet, aplikasi merender formasi tata letak terakhir yang tersimpan di dalam memori.
- Konfigurasi JSON di sisi server idealnya di-cache secara permanen dengan memori memcached/Redis, dan hanya dibatalkan (*cache invalidation*) apabila Superadmin NURISK menekan tombol "Publish Layout Baru".
