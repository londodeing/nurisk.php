# Ultra-Atomic Tasks — Pos Aju Domain (M15)

**Berdasarkan:** ARD_POSAJU.md + MySQL Source of Truth
**Prioritas:** P0=🔴KRITIS, P1=🟠TINGGI, P2=🟡NORMAL, P3=🟢RENDAH
**Estimasi:** 1 task = 1 file change + 1 test (minimal)

---

## 🔴 PRIORITAS 0 — KEAMANAN & INTEGRITAS

### P0-T01: Policy create() Guard: Cek Locked Insiden
**Module:** J — Security
**Location:** `app/Policies/OperasiPosajuPolicy.php`
**Detail:**
- Pass `?OperasiInsiden $insiden = null` ke `create()`
- Jika insiden diberikan dan `is_locked = true`, return false
- Update `PosAjuWebController::store()` untuk pass insiden ke policy
- Update `PosAjuWebController::create()` untuk pass insiden ke policy
**Done When:** Attempt create pos aju untuk insiden terkunci → 403

### P0-T02: Migration: FK logistik_stok.id_posaju → operasi_posaju
**Module:** E — Logistik
**Location:** `database/migrations/2026_07_14_000005_add_fk_logistik_stok_posaju.php`
**Detail:**
- Migration: ALTER TABLE `logistik_stok` ADD CONSTRAINT FK `id_posaju` → `operasi_posaju(id_posaju)` ON DELETE SET NULL
- Rollback: drop FK
**Done When:** Constraint exists in INFORMATION_SCHEMA

### P0-T03: Migration: FK logistik_permintaan.id_posaju_tujuan → operasi_posaju
**Module:** E — Logistik
**Location:** `database/migrations/2026_07_14_000006_add_fk_logistik_permintaan_posaju.php`
**Detail:**
- Migration: ALTER TABLE `logistik_permintaan` ADD CONSTRAINT FK `id_posaju_tujuan` → `operasi_posaju(id_posaju)` ON DELETE SET NULL
**Done When:** Constraint exists

### P0-T04: Migration: Cleanup Dead Column id_periode_operasi
**Module:** A — Core
**Location:** `database/migrations/2026_07_14_000007_drop_id_periode_operasi_from_posaju.php`
**Detail:**
- Migration: DROP COLUMN `id_periode_operasi` from `operasi_posaju`
- Remove from model $fillable
**Done When:** Column dropped, model updated

### P0-T05: Migration: Add FK operasi_posaju.id_pleno_pendirian → operasi_pleno
**Module:** A — Core
**Location:** `database/migrations/2026_07_14_000008_add_fk_posaju_pleno_pendirian.php`
**Detail:**
- ALTER TABLE `operasi_posaju` ADD CONSTRAINT FK `id_pleno_pendirian` → `operasi_pleno(id_pleno)` ON DELETE SET NULL
**Done When:** Constraint exists

### P0-T06: Migration: Add FK operasi_posaju.id_surat_pendirian → operasi_surat_keluar
**Module:** A — Core
**Location:** `database/migrations/2026_07_14_000009_add_fk_posaju_surat_pendirian.php`
**Detail:**
- ALTER TABLE `operasi_posaju` ADD CONSTRAINT FK `id_surat_pendirian` → `operasi_surat_keluar(id_surat)` ON DELETE SET NULL
**Done When:** Constraint exists

---

## 🟠 PRIORITAS 1 — DISTRIBUSI BANTUAN (NEW)

### P1-T01: Migration: Create tabel operasi_distribusi
**Module:** D — Distribusi
**Location:** `database/migrations/2026_07_14_000010_create_operasi_distribusi_table.php`
**Detail:**
- Create table dengan kolom per ARD section D.1
- FKs: `id_posaju` → operasi_posaju, `id_klaster_operasi` → operasi_klaster, `id_penugasan` → operasi_penugasan, `id_barang_katalog` → logistik_barang_katalog (nullable), `dibuat_oleh` → auth_users
- ENUM `status_distribusi`: `direncanakan,didistribusikan,diterima,direview`
- Indexes: `id_posaju`, `id_klaster_operasi`, `status_distribusi`
- Soft delete via `dihapus_pada`
**Done When:** Table exists with all constraints

### P1-T02: Migration: Create tabel operasi_feedback_distribusi
**Module:** D — Distribusi
**Location:** `database/migrations/2026_07_14_000011_create_operasi_feedback_distribusi_table.php`
**Detail:**
- Create table dengan kolom per ARD section D.2
- FKs: `id_distribusi` → operasi_distribusi ON DELETE CASCADE, `id_pengguna` → auth_users
- ENUM `kecukupan`: `kurang,cukup,berlebih`
- ENUM `kualitas`: `baik,sedang,buruk`
- ENUM `status_feedback`: `draft,final`
- Unique: `id_distribusi` (1 feedback per distribusi)
**Done When:** Table exists with all constraints

### P1-T03: Model: OperasiDistribusi
**Module:** D — Distribusi
**Location:** `app/Models/OperasiDistribusi.php`
**Detail:**
- Table: `operasi_distribusi`, PK: `id_distribusi`
- $fillable: semua kolom kecuali PK, timestamps, softdelete
- $casts: `waktu_distribusi => datetime`, `jumlah => decimal:2`
- Relationships:
  - `posaju()`: BelongsTo → OperasiPosaju
  - `klasterOperasi()`: BelongsTo → OperasiKlaster
  - `penugasan()`: BelongsTo → OperasiPenugasan
  - `barangKatalog()`: BelongsTo → LogistikBarangKatalog
  - `pembuat()`: BelongsTo → AuthUser
  - `feedback()`: HasOne → OperasiFeedbackDistribusi
- Methods:
  - `scopeAktif($query)`: status in `direncanakan,didistribusikan`
  - `isFinal()`: status === 'direview'
  - `labelStatus()`: human readable
**Done When:** Model works in tinker

### P1-T04: Model: OperasiFeedbackDistribusi
**Module:** D — Distribusi
**Location:** `app/Models/OperasiFeedbackDistribusi.php`
**Detail:**
- Table: `operasi_feedback_distribusi`, PK: `id_feedback`
- $fillable: semua kecuali PK, timestamps
- $casts: `tepat_waktu => boolean`, `tepat_sasaran => boolean`, `dikunci_pada => datetime`
- Relationships:
  - `distribusi()`: BelongsTo → OperasiDistribusi
  - `pengguna()`: BelongsTo → AuthUser
- Methods:
  - `isFinal()`: status_feedback === 'final'
  - `finalize()`: set status_feedback='final', dikunci_pada=now()
**Done When:** Model works in tinker

### P1-T05: Controller: DistribusiWebController (Index + Create + Store)
**Module:** D — Distribusi
**Location:** `app/Http/Controllers/Operasi/DistribusiWebController.php`
**Detail:**
- `index(?OperasiInsiden $insiden = null)`: list distribusi by insiden/pos aju
- `create(?OperasiInsiden $insiden = null, ?OperasiPosaju $posaju = null)`: form with pos aju dropdown, klaster dropdown, barang katalog dropdown
- `store(Request)`: validasi + create
- Authorization via existing OperasiPosajuPolicy
**Done When:** Can create distribusi record via web form

### P1-T06: Controller: DistribusiWebController (Status transitions)
**Module:** D — Distribusi
**Location:** `app/Http/Controllers/Operasi/DistribusiWebController.php`
**Detail:**
- `distribusikan(Distribusi $distribusi)`: set status='didistribusikan', waktu_distribusi=now()
- `terima(Distribusi $distribusi)`: set status='diterima'
- `kirimFeedback(DistribusiFeedbackRequest, Distribusi $distribusi)`: create/update feedback, set status='direview'
- Each method: policy check, jurnal event
**Done When:** Full flow testable end-to-end

### P1-T07: View: Distribusi Index
**Module:** D — Distribusi
**Location:** `resources/views/operasi/distribusi/index.blade.php`
**Detail:**
- Table: nama barang, klaster, jumlah, status, waktu, aksi
- Filter by: pos aju, klaster, status
- Tab on pos aju show page
**Done When:** Displays all distribusi records

### P1-T08: View: Distribusi Form (Create + Feedback)
**Module:** D — Distribusi
**Location:** `resources/views/operasi/distribusi/create.blade.php`
**Detail:**
- Form: barang (katalog dropdown), nama_barang (text fallback), jumlah, satuan, lokasi_tujuan, penerima, klaster, waktu
- Feedback form: kecukupan (radio), kualitas, tepat_waktu (checkbox), tepat_sasaran, kendala (textarea), rekomendasi
**Done When:** Form submits successfully

### P1-T09: Routes: Distribusi Web Routes
**Module:** D — Distribusi
**Location:** `routes/web.php`
**Detail:**
- Scoped under `/insiden/{insiden}/posaju/{posaju}/distribusi` (name: `insiden.posaju.distribusi.*`)
- Flat: `/distribusi` (name: `distribusi.*`)
- Routes: index, create, store, distribusikan, terima, feedback
**Done When:** Route list shows all routes

### P1-T10: Request: StoreDistribusiRequest
**Module:** D — Distribusi
**Location:** `app/Http/Requests/Operasi/StoreDistribusiRequest.php`
**Detail:**
- Validation rules per ARD
- `id_posaju`: required, exists:operasi_posaju
- `id_klaster_operasi`: required, exists:operasi_klaster
- `nama_barang`: required, string, max:255
- `jumlah`: required, numeric, min:0.01
- `satuan`: required, string, max:50
- `waktu_distribusi`: required, date
**Done When:** Validation passes/fails correctly

### P1-T11: Request: StoreFeedbackDistribusiRequest
**Module:** D — Distribusi
**Location:** `app/Http/Requests/Operasi/StoreFeedbackDistribusiRequest.php`
**Detail:**
- `kecukupan`: required, in:kurang,cukup,berlebih
- `kualitas`: required, in:baik,sedang,buruk
- `tepat_waktu`: required, boolean
- `tepat_sasaran`: required, boolean
- `kendala`: nullable, string
- `rekomendasi`: nullable, string
**Done When:** Validation works

---

## 🟠 PRIORITAS 1 — INTEGRASI LOGISTIK

### P1-T12: Model: Update LogistikStok — Add posaju() Relation
**Module:** E — Logistik
**Location:** `app/Models/LogistikStok.php`
**Detail:**
- Add `posaju()`: BelongsTo → OperasiPosaju
- Add `id_posaju` to fillable
**Done When:** `$stok->posaju` returns model

### P1-T13: View: Stok Tab di Pos Aju Show (Enhance)
**Module:** E — Logistik
**Location:** `resources/views/operasi/posaju/show.blade.php`
**Detail:**
- Show stok with: nama barang, jumlah tersedia, satuan, gudang asal
- Link to logistik mutasi page
- Empty state: "Belum ada stok"
**Done When:** Stok tab shows data from DB

### P1-T14: View: Permintaan Logistik dari Pos Aju
**Module:** E — Logistik
**Location:** `resources/views/operasi/posaju/permintaan.blade.php` (partial)
**Detail:**
- Button "Ajukan Permintaan Logistik" on show page
- Form: barang, jumlah, prioritas, catatan
- List permintaan dengan status
**Done When:** Can create and view permintaan

---

## 🟠 PRIORITAS 1 — PENUGASAN & KLASTER

### P1-T15: View: Personel Tab di Pos Aju Show (Enhance)
**Module:** C — Klaster
**Location:** `resources/views/operasi/posaju/show.blade.php`
**Detail:**
- Show penugasan personel: nama, peran, klaster, status, masa tugas
- Group by klaster
- Show komandan aktif at top
**Done When:** Personel tab shows data

### P1-T16: Feature: Tampilkan Klaster di Pos Aju Show
**Module:** C — Klaster
**Location:** `resources/views/operasi/posaju/show.blade.php`
**Detail:**
- Tab "Klaster" menampilkan klaster yang terhubung via penugasan
- Nama klaster, koordinator, status, progres
- Kanban-style cards per klaster
**Done When:** Klaster tab renders

---

## 🟡 PRIORITAS 2 — JURNAL

### P2-T01: Service: PosajuJurnalService
**Module:** G — Jurnal
**Location:** `app/Services/Operasi/PosajuJurnalService.php`
**Detail:**
- `catat(string $kategori, OperasiPosaju $posaju, ?string $deskripsi = null)`: create operasi_jurnal entry
- `kategori_event`: p0_t01_test_map values: `posaju_dibuat`, `posaju_diaktifkan`, `posaju_diperpanjang`, `posaju_ditutup`, `komandan_ditunjuk`, `komandan_berakhir`, `distribusi_dibuat`, `distribusi_dikirim`, `distribusi_direview`
- Auto-set: `id_insiden` from posaju, `tabel_referensi='operasi_posaju'`, `id_referensi=posaju.id_posaju`, `id_pengguna` from auth
**Done When:** Jurnal entry created on each event

### P2-T02: Integrate: Jurnal di PosAjuWebController
**Module:** G — Jurnal
**Location:** `app/Http/Controllers/Operasi/PosAjuWebController.php`
**Detail:**
- Inject PosajuJurnalService
- Call `jurnal->catat(...)` di: store, activate, close, extend
**Done When:** Jurnal entries created on each action

### P2-T03: Integrate: Jurnal di PosajuKomandanController
**Module:** G — Jurnal
**Location:** `app/Http/Controllers/Operasi/PosajuKomandanController.php`
**Detail:**
- Call `jurnal->catat('komandan_ditunjuk', ...)` di store()
- Call `jurnal->catat('komandan_berakhir', ...)` di destroy()
**Done When:** Jurnal entries created

### P2-T04: Integrate: Jurnal di DistribusiWebController
**Module:** G — Jurnal
**Location:** `app/Http/Controllers/Operasi/DistribusiWebController.php`
**Detail:**
- Call jurnal di store, distribusikan, terima, kirimFeedback
**Done When:** Jurnal created for distribusi events

---

## 🟡 PRIORITAS 2 — MAP & API

### P2-T05: Feature: Command Center Map — Show all Active Pos Aju
**Module:** H — Map
**Location:** `resources/views/dashboard/command-center.blade.php`
**Detail:**
- Fetch from `GET /api/operasi/insiden/{insiden}/posaju/aktif`
- Show markers for all active pos aju
- Popup: nama, status, PJ, jumlah stok
- Color-code by klaster
**Done When:** Map shows pins for active pos aju

### P2-T06: API: Add Distribusi Resource
**Module:** I — API
**Location:** `app/Http/Resources/Operasi/OperasiDistribusiResource.php`
**Detail:**
- Resource: id, posaju, klaster, barang, jumlah, status, waktu, feedback
- Collection resource
**Done When:** API returns proper JSON

### P2-T07: API: CRUD Routes for Distribusi
**Module:** I — API
**Location:** `routes/api.php`
**Detail:**
- `apiResource('distribusi', DistribusiApiController::class)`
- Add route: `POST distribusi/{distribusi}/feedback`
**Done When:** API routes registered

### P2-T08: Controller: DistribusiApiController
**Module:** I — API
**Location:** `app/Http/Controllers/Api/Operasi/DistribusiApiController.php`
**Detail:**
- CRUD methods (index, store, show, update)
- `feedback()`: store feedback
- Authorization via policy
**Done When:** API CRUD works

---

## 🟡 PRIORITAS 2 — KOMPONEN UI

### P2-T09: View: Show Page — Tab Distribusi
**Module:** D — Distribusi
**Location:** `resources/views/operasi/posaju/show.blade.php`
**Detail:**
- Tab "Distribusi" menampilkan daftar distribusi bantuan dari pos aju ini
- Table atau card view: barang, jumlah, klaster, status, waktu
- Button "Buat Distribusi Baru"
- Link ke detail distribusi
**Done When:** Tab appears with data

### P2-T10: View: Show Page — Tab Feedback
**Module:** D — Distribusi
**Location:** `resources/views/operasi/posaju/show.blade.php`
**Detail:**
- Tab "Feedback" menampilkan feedback distribusi
- Per distribusi: kecukupan, kualitas, tepat_waktu, kendala
- Color-coded: hijau (cukup), kuning (kurang), merah (berlebih)
- Form untuk isi feedback jika status = diterima
**Done When:** Tab appears with feedback data

### P2-T11: Component: PosAjuStatusBadge
**Module:** A — Core
**Location:** `resources/views/components/operasi/posaju-status-badge.blade.php`
**Detail:**
- Reusable badge component
- Props: `status` (string)
- Colors: direncanakan=yellow, aktif=green, diperpanjang=blue, ditutup=gray
- Returns: `<span class="px-2 py-1 rounded-full text-xs font-semibold bg-{color}-100 text-{color}-700">{{ $label }}</span>`
**Done When:** Component renders correctly

### P2-T12: Component: PosAjuMap
**Module:** H — Map
**Location:** `resources/views/components/operasi/posaju-map.blade.php`
**Detail:**
- Reusable Leaflet component
- Props: `posaju` (model), `height` (default 250px)
- Render marker with popup
- If no coords: show "Koordinat belum diatur"
**Done When:** Component reusable across views

---

## 🟢 PRIORITAS 3 — TEKNIS & PERBAIKAN

### P3-T01: Test: Pos Aju Activation Requires Pleno Keputusan
**Module:** A — Core
**Location:** `tests/Feature/Operasi/PosajuActivationTest.php`
**Detail:**
- Test: activate tanpa id_pleno_keputusan → error/403
- Test: activate dengan id_pleno_keputusan valid → success
**Done When:** Tests pass

### P3-T02: Test: Pos Aju Create Requires Koordinat
**Module:** A — Core
**Location:** `tests/Feature/Operasi/PosajuCreateTest.php`
**Detail:**
- Test: create tanpa latitude → validation error
- Test: create tanpa longitude → validation error
- Test: create dengan koordinat valid → success
**Done When:** Tests pass

### P3-T03: Test: Pos Aju Ditutup Tidak Bisa Diaktifkan
**Module:** A — Core
**Location:** `tests/Feature/Operasi/PosajuCloseReactivateTest.php`
**Detail:**
- Test: close pos aju → status ditutup
- Test: activate pos aju ditutup → error
**Done When:** Tests pass

### P3-T04: Test: Distribusi Bantuan Flow
**Module:** D — Distribusi
**Location:** `tests/Feature/Operasi/DistribusiTest.php`
**Detail:**
- Test: create distribusi → status 'direncanakan'
- Test: distribusikan → status 'didistribusikan'
- Test: terima → status 'diterima'
- Test: feedback → status 'direview'
- Test: feedback final → terkunci
**Done When:** All tests pass

### P3-T05: Test: Logistik Stok FK Constraint
**Module:** E — Logistik
**Location:** `tests/Feature/Operasi/LogistikFkTest.php`
**Detail:**
- Test: create logistik_stok with valid id_posaju → ok
- Test: create logistik_stok with invalid id_posaju → error
**Done When:** Tests pass

### P3-T06: Seed: PosAjuSeeder
**Module:** A — Core
**Location:** `database/seeders/Operasi/PosAjuSeeder.php`
**Detail:**
- Seed 2-3 pos aju with realistic data
- One with komandan, one without
- One with distribusi + feedback
- One active, one closed
**Done When:** `db:seed` creates data

### P3-T07: Fix: OperasiAktivasi — Hubungkan ke OperasiPosaju (or drop)
**Module:** A — Core
**Location:** `database/migrations/2026_07_14_000012_fix_operasi_aktivasi.php`
**Detail:**
- Option A: Add `id_posaju` FK to operasi_aktivasi, drop `id_komandan` (replaced by posaju reference)
- Option B: Drop the table if unused
- Decision based on further analysis
**Done When:** Table resolved

### P3-T08: Cleanup: Hapus unused method tutup() from Policy
**Module:** A — Core
**Location:** `app/Policies/OperasiPosajuPolicy.php`
**Detail:**
- `tutup()` delegasi ke `close()` — remove duplicate
- Update controller to use `close()` instead of `tutup()`
**Done When:** Dead code removed

---

## 📋 LINTAS MODUL — DEPENDENCIES

### Dependency Graph
```
P0-T01 (Policy Create Locked)
  ├── P0-T02 (FK Logistik Stok)
  ├── P0-T03 (FK Logistik Permintaan)
  ├── P0-T04 (Drop Dead Column)
  ├── P0-T05 (FK Pleno Pendirian)
  └── P0-T06 (FK Surat Pendirian)

P1-T01 (Tabel Distribusi) ──► P1-T03 (Model Distribusi)
  │                            ├── P1-T05 (Controller)
  │                            ├── P1-T07 (View Index)
  │                            ├── P1-T08 (View Create)
  │                            ├── P1-T09 (Routes)
  │                            └── P1-T10 (Request)
  │
  └── P1-T02 (Tabel Feedback) ──► P1-T04 (Model Feedback)
                                   └── P1-T11 (Request Feedback)

P2-T01 (Jurnal Service) ──► P2-T02 (Integrate Controller)
  │                          ├── P2-T03 (Integrate Komandan)
  │                          └── P2-T04 (Integrate Distribusi)
  │
  └── P2-T06 (API Resource) ──► P2-T07 (API Routes)
                                 └── P2-T08 (API Controller)

P3-T01 (Test Activation) ──► P3-T02 (Test Create)
  │                          └── P3-T03 (Test Close)
  │
  P3-T04 (Test Distribusi) ──► P3-T06 (Seeder)
  │
  P3-T07 (Fix Aktivasi)
  └── P3-T08 (Cleanup Policy)
```

### Execution Order (Recommended)
```
Phase 1 — Foundation (P0):
  P0-T01 → P0-T02 → P0-T03 → P0-T04 → P0-T05 → P0-T06

Phase 2 — Distribusi (P1):
  P1-T01 → P1-T02 → P1-T03 → P1-T04 → P1-T10 → P1-T11
  → P1-T09 → P1-T05 → P1-T06 → P1-T07 → P1-T08

Phase 3 — Logistik & Klaster (P1):
  P1-T12 → P1-T13 → P1-T14 → P1-T15 → P1-T16

Phase 4 — Jurnal (P2):
  P2-T01 → P2-T02 → P2-T03 → P2-T04

Phase 5 — API & Map (P2):
  P2-T05 → P2-T06 → P2-T07 → P2-T08

Phase 6 — UI (P2):
  P2-T09 → P2-T10 → P2-T11 → P2-T12

Phase 7 — Tests & Cleanup (P3):
  P3-T01 → P3-T02 → P3-T03 → P3-T04 → P3-T05 → P3-T06 → P3-T07 → P3-T08
```
