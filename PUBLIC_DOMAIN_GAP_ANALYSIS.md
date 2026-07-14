# PUBLIC DOMAIN GAP ANALYSIS — PRD vs Implementation

---

## 1. Dashboard

| PRD Requirement | Implementation | Gap | Severity |
|----------------|----------------|-----|----------|
| KPI dari Read Model | KPI dari API langsung via repository | Tidak ada Read Model pattern | MEDIUM |
| Semua widget muncul | Widget registry by config, fallback hardcoded | Fallback berfungsi tapi tidak ter-cache | HIGH |
| Skeleton loading | ✅ Ada di KPI, Incident Feed | — | OK |
| Error + retry | ✅ Ada di semua widget | — | OK |
| Cache (offline) | ✅ Drift cache (DashboardKPI, Incident, Warning, Weather) | — | OK |
| Refresh (pull-to-refresh) | ✅ Orchestrator refresh semua provider | Satu gagal = semua dianggap gagal | HIGH |
| CTA berfungsi | Volunteer CTA, Login CTA | ✅ | OK |
| Navigation benar | ✅ Menuju tab yang tepat | — | OK |
| Weather aktif | ✅ Weather card dari API | — | OK |

**SUM**: 1 high gap, 1 medium gap

---

## 2. COP Map

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Flutter hanya renderer | ✅ GeoJSON dari backend via layer plugin | — | OK |
| Tidak boleh fetch tambahan setelah GeoJSON | ❌ Setiap live update (30s) toggle layer = fetch ulang | Harusnya hanya refresh layer data, bukan toggle | CRITICAL |
| Layer Control | ✅ Bottom sheet plugin | — | OK |
| Legend | ❌ FilterControlBottomSheet konten belum terverifikasi | Mungkin kosong | MEDIUM |
| Operational Objects | ✅ OperationalObject model dari GeoJSON properties | — | OK |
| Marker | ✅ MapLibre GeoJSON circles/polygons/lines | — | OK |
| Bottom Sheet | ✅ Tampil jika selectedObject != null | Berpotensi double sheet | MEDIUM |
| Timeline | ✅ TimelineRenderer widget | — | OK |
| Filter | ✅ OperationalFilter model + provider | — | OK |
| Live Update | ✅ Timer 30 detik | **Double toggle bug** | CRITICAL |
| Map Camera | ✅ Initial position Jakarta, zoom 12 | — | OK |
| Map Style | ❌ Hardcoded CartoDB | Tidak bisa ganti tema | MEDIUM |
| Offline placeholder | ❌ Tidak ada | Tidak ada fallback offline untuk peta | MEDIUM |
| Loading | ❌ Tidak ada loading state | Initial load via try/catch tanpa UI loading | HIGH |
| Retry | ❌ Tidak ada retry | Jika map config gagal, layer tidak pernah aktif | HIGH |
| Error | ❌ Tidak ada error state | Hanya log ke RuntimeLogger | HIGH |
| Marker clustering | ❌ Tidak ada | Semua marker ditampilkan langsung | MEDIUM |

**SUM**: 2 critical, 3 high, 4 medium gaps

---

## 3. Report Wizard

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Step 1 — Data Pelapor | ✅ Nama + HP validation | — | OK |
| GPS | ✅ GeoService dengan permission, mock detection | — | OK |
| Permission | ✅ PermissionService | — | OK |
| Reverse Geocode | ❌ Tidak ada reverse geocode | Hanya titik koordinat, tidak ada nama jalan | MEDIUM |
| Kategori | ✅ Dropdown jenisBencana | — | OK |
| Foto | ✅ Camera + Gallery | — | OK |
| Deskripsi | ✅ TextField max 2000 chars | — | OK |
| Review | ✅ Review screen sebelum submit | — | OK |
| Submit (via repository) | ❌ Bypass — panggil Dio langsung di screen | **Architecture violation** | CRITICAL |
| GPS mati | ✅ Detected, shown in red container | — | OK |
| GPS lambat | ✅ Timeout 15s | — | OK |
| GPS mock | ✅ Detected, error shown | — | OK |
| Upload gagal | ✅ Caught, shown in review step | — | OK |
| Validation | ✅ All required fields validated | — | OK |
| Error | ✅ Shown in review step with retry button | — | OK |
| Loading | ✅ CircularProgressIndicator on submit button | — | OK |
| Success | ✅ AlertDialog with tracking code | **Tidak navigasi ke tracking screen** | MEDIUM |
| Cancel | ✅ Back button/step | — | OK |
| Resume | ❌ Tidak ada | Jika wizard ditutup, data hilang | MEDIUM |

**SUM**: 1 critical, 2 medium gaps

---

## 4. Tracking

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Kode laporan → API | ❌ Panggil Dio langsung, bukan lewat repository | Architecture violation | CRITICAL |
| Timeline | ✅ Timeline dengan status color + time | — | OK |
| Status | ✅ DITERIMA, VERIFIED, ASSESSMENT, RESPONSE, RECOVERY, REJECTED | — | OK |
| Sinkron Backend | ✅ Polling every 15s | No lifecycle pause | HIGH |
| Loading | ✅ CircularProgressIndicator | — | OK |
| Error | ✅ Error with retry button | — | OK |
| Empty | ✅ "Belum ada informasi tracking" | — | OK |
| Live update | ✅ Timer 15s polling | **No pause on background** | HIGH |

**SUM**: 1 critical, 1 high gap

---

## 5. News

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Loading | ✅ Centered CircularProgressIndicator | — | OK |
| Pagination | ❌ Tidak ada | Hanya single page fetch | HIGH |
| Image | ✅ Image.network with errorBuilder | — | OK |
| Empty | ✅ "Belum ada berita" | — | OK |
| Retry | ✅ invalidate(provider) | — | OK |
| Refresh | ✅ Via invalidation | — | OK |
| Skeleton | ❌ CircularProgressIndicator saja | **Tidak ada skeleton loader** | MEDIUM |

**SUM**: 1 high, 1 medium gap

---

## 6. Resource

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Resource List | ✅ ListView dari provider | — | OK |
| Map Navigation | ❌ Tidak ada | Resource list only, no map view | MEDIUM |
| Contact | ❌ Tidak ada | Item tidak punya contact info display | LOW |
| Filter | ❌ Tidak ada filter UI | Provider punya kategori param tapi tidak di-screen | MEDIUM |
| Search | ❌ Tidak ada | Tidak ada search bar | MEDIUM |
| Distance | ❌ Tidak ada | Tidak ada perhitungan jarak dari user | MEDIUM |
| Pagination | ❌ Tidak ada | Semua resource di-fetch sekali | HIGH |

**SUM**: 1 high, 5 medium gaps

---

## 7. Guest Profile

| Requirement | Status | Gap | Severity |
|-------------|--------|-----|----------|
| Guest Mode | ✅ "Sesi Belum Aktif" view | — | OK |
| Login CTA | ✅ "Masuk Ke Akun Saya" button | — | OK |
| Register CTA | ✅ "Daftar Akun Baru" button | — | OK |
| Quick Access | ✅ 4 menu tiles | — | OK |
| About / Help | ❌ Tidak ada | No FAQ, no about page | LOW |
| Privacy / Terms | ❌ Tidak ada | No privacy policy link | LOW |
| Version | ❌ Tidak ada | No app version display | LOW |

**SUM**: 3 low gaps

---

## 8. Navigation Flow Audit

| Flow | Status | Issue |
|------|--------|-------|
| Splash → Home | ✅ | Working |
| Splash → Executive | ✅ | When authenticated with activeRole |
| Tab: Beranda → Peta → Lapor → Info → Akun | ❌ | **Info tab is placeholder Scaffold** |
| Login → Home/Executive | ✅ | Redirect correct |
| Register → Login/Home | ✅ | After registration |
| Logout → Home | ✅ | Auth listener redirects |
| Back from deep page | ❌ | Custom back handling in bottom nav may pop wrong |
| Deep Link → Tracking | ❌ | Route registered but placeholder |
| Deep Link → Incident Detail | ❌ | Route registered but placeholder |
| Assessment via route | ❌ | Hardcoded 'demo-uuid' in action mapping |

---

## Summary: Critical Integration Gaps

| # | Gap | Module | Impact |
|---|-----|--------|--------|
| 1 | Resource tab = placeholder Scaffold | Navigation | Users see blank screen on Info tab |
| 2 | Report submit bypasses repository | Report Wizard | Architecture violation |
| 3 | Tracking uses raw Dio | Tracking | Architecture violation |
| 4 | Map auto-load uses raw Dio | COP Map | Architecture violation |
| 5 | Map double-toggle bug | COP Map | Visual flicker every 30s |
| 6 | Report success doesn't navigate to Tracking | Report → Tracking | User must manually find tracking |
| 7 | 3 deep-link routes are placeholder | Router | Links lead to blank pages |