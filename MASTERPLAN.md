# MASTERPLAN: End-to-End Lifecycle Fix

**Versi:** 1.0  
**Audit Date:** 2026-06-30  
**Assessment Tests:** 5/5 PASS (23 assertions)

---

## Daftar Isi
1. [Phase 1: Penugasan TRC Web UI (GAP KRITIS)](#phase-1-penugasan-trc-web-ui)
2. [Phase 2: SPK → Auto-Penugasan (GAP SEDANG)](#phase-2-spk--auto-penugasan)
3. [Phase 3: Assessment Laporan Cetak (GAP SEDANG)](#phase-3-assessment-laporan-cetak)
4. [Phase 4: Assessment Review Workflow (GAP BARU)](#phase-4-assessment-review-workflow)
5. [Phase 5: Technical Debt (GAP RENDAH)](#phase-5-technical-debt)
6. [Dependencies & Ordering](#dependencies--ordering)
7. [Testing Strategy](#testing-strategy)

---

## Phase 1: Penugasan TRC Web UI

### Problem
`insiden/show.blade.php:230` — tombol "Tugaskan Personel" menggunakan `href="#"`.  
Tidak ada Blade views untuk CRUD penugasan. Flow hanya bisa via API manual.

### Task 1.1: Buat PenugasanController (Web)

**File:** `app/Http/Controllers/Operasi/PenugasanController.php`

**Method specifications:**

| Method | Route | View | Logic |
|--------|-------|------|-------|
| `index(OperasiInsiden $insiden)` | `GET /insiden/{insiden}/penugasan` | `operasi.penugasan.index` | Auth: `viewAny, [Penugasan::class, $insiden]`. Load `penugasan.pengguna.profil` where `id_insiden = $insiden->id_insiden`. |
| `create(OperasiInsiden $insiden)` | `GET /insiden/{insiden}/penugasan/create` | `operasi.penugasan.create` | Auth: `create, [Penugasan::class, $insiden]`. Load `AuthUser::where('status_akun','aktif')` untuk dropdown. |
| `store(Request, OperasiInsiden $insiden)` | `POST /insiden/{insiden}/penugasan` | redirect → index | Auth: `create, [Penugasan::class, $insiden]`. Validasi: `id_pengguna` required exists:auth_users, `peran_otoritas` required in:komandan_insiden,trc,relawan,medis,logistik,operator, `catatan` nullable. Panggil `PenugasanService::createPenugasan()`. |
| `show(OperasiInsiden $insiden, OperasiPenugasan $penugasan)` | `GET /insiden/{insiden}/penugasan/{penugasan}` | `operasi.penugasan.show` | Auth: `view, $penugasan`. Load `pengguna.profil`, `pemberiTugas.profil`, `history`. |
| `edit(OperasiInsiden $insiden, OperasiPenugasan $penugasan)` | `GET /insiden/{insiden}/penugasan/{penugasan}/edit` | `operasi.penugasan.edit` | Auth: `update, $penugasan`. Hanya jika `status_penugasan === 'draft'`. |
| `update(Request, OperasiInsiden $insiden, OperasiPenugasan $penugasan)` | `PUT /insiden/{insiden}/penugasan/{penugasan}` | redirect → show | Auth: `update, $penugasan`. Validasi peran_otoritas, catatan. |
| `destroy(OperasiInsiden $insiden, OperasiPenugasan $penugasan)` | `DELETE /insiden/{insiden}/penugasan/{penugasan}` | redirect → index | Auth: `delete, $penugasan`. Hanya jika status draft. |

### Task 1.2: Buat Blade Views Penugasan

**1. `resources/views/operasi/penugasan/index.blade.php`**
- Layout: `x-app-layout`
- Tabel daftar penugasan untuk satu insiden:
  - Kolom: Nama Personel, Peran, Status (badge warna), Waktu Mulai, Waktu Selesai
  - Tombol: "Tugaskan Baru" (jika `@can('create')`), "Detail" (link ke show)
  - Filter: peran_otoritas, status_penugasan
- `@empty` state jika belum ada penugasan

**2. `resources/views/operasi/penugasan/create.blade.php`**
- Layout: `x-app-layout`
- Form:
  - `id_pengguna` — dropdown select2 dari `$authUsers` (display: nama_lengkap + nomor_hp)
  - `peran_otoritas` — radio/dropdown: komandan_insiden, trc, relawan, medis, logistik, operator
  - `catatan` — textarea optional
- Submit POST ke `route('insiden.penugasan.store', $insiden)`

**3. `resources/views/operasi/penugasan/show.blade.php`**
- Layout: `x-app-layout`
- Detail card: Nama, Peran, Status (badge), Waktu Mulai/Selesai, Catatan
- Riwayat status (tabel dari `$penugasan->history`)
- Tombol aksi (conditional on status):
  - Jika draft: Edit, Hapus
  - Jika assigned: "Tandai Notified"
  - Jika notified: "Tandai Accepted" / "Tandai Rejected"
  - Jika accepted: "Tandai On Route"
  - Jika on_route: "Tandai On Site"
  - Jika on_site: "Tandai Completed"
  - Jika completed/cancelled/rejected: no actions

**4. `resources/views/operasi/penugasan/edit.blade.php`**
- Layout: `x-app-layout`
- Sama seperti create, tapi pre-filled dengan data existing
- Hanya bisa edit `peran_otoritas` dan `catatan` jika status === 'draft'

### Task 1.3: Tambah Routes

**File:** `routes/web.php`  
**Lokasi:** Di dalam group middleware `['auth', 'role:super_admin,pwnu,pcnu,trc']`, setelah route `insiden.spk.store`:

```php
Route::prefix('insiden/{insiden}/penugasan')->name('insiden.penugasan.')->group(function () {
    Route::get('/', [PenugasanController::class, 'index'])->name('index');
    Route::get('/create', [PenugasanController::class, 'create'])->name('create');
    Route::post('/', [PenugasanController::class, 'store'])->name('store');
    Route::get('/{penugasan}', [PenugasanController::class, 'show'])->name('show');
    Route::get('/{penugasan}/edit', [PenugasanController::class, 'edit'])->name('edit');
    Route::put('/{penugasan}', [PenugasanController::class, 'update'])->name('update');
    Route::delete('/{penugasan}', [PenugasanController::class, 'destroy'])->name('destroy');
});
```

### Task 1.4: Update insiden/show.blade.php — Tab Personel

**File:** `resources/views/operasi/insiden/show.blade.php`  
**Change:** Line 230 — ganti `href="#"` menjadi:

```blade
<a href="{{ route('insiden.penugasan.create', $insiden) }}" class="...">Tugaskan Personel</a>
```

Dan di loop penugasan, tambah link ke `route('insiden.penugasan.show', [$insiden, $pen])`.

---

## Phase 2: SPK → Auto-Penugasan

### Problem
`InsidenSpkController::store()` membuat Surat Tugas + update `operasi_insiden.no_spk_assesment`, tapi **tidak** membuat `OperasiPenugasan`. Admin harus 2 langkah: terbitkan SPK → buka API/dashboard → tugaskan personel.

### Task 2.1: Auto-Create Penugasan di InsidenSpkController

**File:** `app/Http/Controllers/Operasi/InsidenSpkController.php`

**Change:** Setelah `$surat` dibuat, di dalam transaction yang sama, create `OperasiPenugasan` untuk `id_penerima_spk`:

```php
// After $surat = $this->suratService->buatSurat(...)
// After $suratFinal = $this->suratService->finalisasi(...)
// Inside the same DB::transaction() block:

// Auto-create penugasan untuk penerima SPK
if (!empty($validated['id_penerima_spk'])) {
    $existing = OperasiPenugasan::where('id_insiden', $insiden->id_insiden)
        ->where('id_pengguna', $validated['id_penerima_spk'])
        ->whereNotIn('status_penugasan', ['completed', 'cancelled', 'rejected'])
        ->exists();

    if (!$existing) {
        OperasiPenugasan::create([
            'uuid_penugasan'   => (string) Str::uuid(),
            'id_insiden'       => $insiden->id_insiden,
            'id_pengguna'      => $validated['id_penerima_spk'],
            'peran_otoritas'   => 'trc',
            'status_penugasan' => 'draft',
            'waktu_mulai'      => now(),
            'ditugaskan_oleh'  => Auth::id(),
            'catatan'          => 'Auto-created dari penerbitan SPK: ' . ($validated['catatan_penugasan'] ?? ''),
        ]);

        OperasiPenugasanHistory::create([
            'id_penugasan'     => $penugasan->id_penugasan,
            'status_sebelumnya' => null,
            'status_baru'       => 'draft',
            'waktu_perubahan'   => now(),
            'diubah_oleh'       => Auth::id(),
        ]);
    }
}
```

**Add imports:** `use App\Models\OperasiPenugasan;`, `use App\Models\OperasiPenugasanHistory;`, `use Illuminate\Support\Str;`

### Task 2.2: Add "TRC" Label to SPK Penerima Dropdown

**File:** `resources/views/operasi/insiden/show.blade.php`  
**Change:** In the SPK form section where `$trcList` is looped for the `id_penerima_spk` dropdown, add role label:

```blade
<option value="{{ $user->id_pengguna }}">
    {{ $user->profil->nama_lengkap }} 
    @if($user->hasRole('trc')) (TRC) @endif
    @if($user->hasRole('relawan')) (Relawan) @endif
</option>
```

---

## Phase 3: Assessment Laporan Cetak

### Problem
`resources/views/operasi/assessment/laporan.blade.php` entirely hardcoded dummy data. Not production-ready.

### Task 3.1: Wire Up Laporan View to Real Data

**File:** `resources/views/operasi/assessment/laporan.blade.php`

**Change:** Replace ALL hardcoded values with dynamic `$assessment` and `$insiden` data.

**Expected data structure (passed from controller):**

```
$assessment → AssessmentUtama (load all relations)
$insiden    → OperasiInsiden
```

**Relations needed:**
- `petugas.profil` — Nama assessor
- `ringkasanSkor` — Skor & tingkat keparahan
- `dampakManusiaV2` — Korban jiwa
- `dampakInfrastruktur` — Infrastruktur rusak
- `dampakLingkungan` — Lingkungan
- `dampakEkonomi` — Ekonomi
- `dampakRumah` — Rumah rusak
- `dampakFasum` — Fasum rusak
- `dampakVital` — Vital rusak
- `biodataKejadian` — Kronologi
- `kebutuhanLanjutan` — Kebutuhan
- `kebutuhanMendesak` — Kebutuhan mendesak
- `lokasiDetail` — Lokasi detail
- `narasiDetail` — Narasi detail

**Fallback logic** (copy from `show.blade.php`):
```blade
{{ $dm->terdampak_jiwa ?? ($dm->menderita_mengungsi ?? 0) }}
```

### Task 3.2: Add PDF Preview Route

**File:** `routes/web.php`

```php
Route::get('/insiden/{insiden}/assessment/{assessment}/cetak', [AssessmentController::class, 'cetak'])
    ->name('insiden.assessment.cetak');
```

**File:** `app/Http/Controllers/Operasi/AssessmentController.php`

Add method `cetak(OperasiInsiden $insiden, AssessmentUtama $assessment)`:
```php
public function cetak(OperasiInsiden $insiden, AssessmentUtama $assessment)
{
    $this->authorize('view', $assessment);

    $assessment->loadMissing([
        'petugas.profil', 'ringkasanSkor', 'dampakManusiaV2', 'dampakManusia',
        'dampakInfrastruktur', 'dampakLingkungan', 'dampakEkonomi',
        'dampakRumah', 'dampakFasum', 'dampakVital',
        'biodataKejadian', 'kebutuhanLanjutan', 'kebutuhanMendesak',
        'kebutuhanNumerik.itemMaster', 'lokasiDetail', 'narasiDetail',
        'narasiKejadian'
    ]);

    return view('operasi.assessment.laporan', compact('insiden', 'assessment'));
}
```

### Task 3.3: Add Cetak Button to Assessment Show

**File:** `resources/views/operasi/assessment/show.blade.php`

Add after the edit button:
```blade
<a href="{{ route('insiden.assessment.cetak', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}" 
   class="inline-flex items-center px-4 py-2 bg-gray-600 border rounded-md font-semibold text-xs text-white hover:bg-gray-700">
    Cetak Laporan
</a>
```

---

## Phase 4: Assessment Review Workflow

### Problem
Tidak ada alur "submit for review" / "approve" / "reject" untuk assessment.  
Assessment langsung `is_latest = true` tanpa review dari pimpinan.

### Task 4.1: Migration — Add status_review to assessment_utama

**File:** `database/migrations/2026_07_01_000001_add_status_review_to_assessment_utama.php`

```php
Schema::table('assessment_utama', function (Blueprint $table) {
    $table->enum('status_review', ['draft', 'submitted', 'in_review', 'approved', 'rejected'])
        ->default('draft')
        ->after('is_latest');
    $table->text('catatan_review')->nullable()->after('status_review');
    $table->unsignedBigInteger('id_reviewer')->nullable()->after('catatan_review');
    $table->timestamp('waktu_review')->nullable()->after('id_reviewer');

    $table->foreign('id_reviewer')->references('id_pengguna')->on('auth_users')
        ->onDelete('set null');
});
```

### Task 4.2: Update AssessmentUtama Model

**File:** `app/Models/AssessmentUtama.php`

Add to `$fillable`:
```php
'status_review', 'catatan_review', 'id_reviewer', 'waktu_review'
```

Add relations:
```php
public function reviewer(): BelongsTo
{
    return $this->belongsTo(AuthUser::class, 'id_reviewer', 'id_pengguna');
}
```

### Task 4.3: Add Review Policy Method

**File:** `app/Policies/AssessmentPolicy.php`

Add method:
```php
public function review(AuthUser $user, AssessmentUtama $assessment): bool
{
    // Only super_admin, pwnu, or pcnu can review assessments
    $authCtx = app(AuthorizationContextService::class);
    if ($authCtx->hasAnyRole(['super_admin', 'pwnu'])) {
        return true;
    }
    if ($authCtx->hasRole('pcnu')) {
        $insiden = $assessment->insiden;
        return $insiden && $user->default_scope_id === $insiden->id_pcnu;
    }
    return false;
}

public function approve(AuthUser $user, AssessmentUtama $assessment): bool
{
    return $this->review($user, $assessment);
}

public function reject(AuthUser $user, AssessmentUtama $assessment): bool
{
    return $this->review($user, $assessment);
}
```

### Task 4.4: Add Review API Endpoints

**File:** `routes/api.php`

```php
Route::post('/insiden/{insiden}/assessment/{assessment}/submit', 
    [AssessmentApiController::class, 'submit'])->name('assessment.submit');
Route::post('/insiden/{insiden}/assessment/{assessment}/review', 
    [AssessmentApiController::class, 'review'])->name('assessment.review');
```

### Task 4.5: Add submit/review Methods to AssessmentApiController

**File:** `app/Http/Controllers/Api/AssessmentApiController.php`

Add methods:

```php
public function submit(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
{
    $this->authorize('update', $assessment);

    if ($assessment->status_review !== 'draft') {
        return response()->json(['message' => 'Assessment sudah di-submit.'], 422);
    }

    $assessment->update(['status_review' => 'submitted']);

    return response()->json(['message' => 'Assessment diajukan untuk review.']);
}

public function review(Request $request, OperasiInsiden $insiden, AssessmentUtama $assessment): JsonResponse
{
    $validated = $request->validate([
        'action' => 'required|in:approved,rejected',
        'catatan_review' => 'required_if:action,rejected|nullable|string|max:1000',
    ]);

    if ($assessment->status_review !== 'submitted') {
        return response()->json(['message' => 'Assessment belum di-submit.'], 422);
    }

    $this->authorize($validated['action'] === 'approved' ? 'approve' : 'reject', $assessment);

    $assessment->update([
        'status_review' => $validated['action'] === 'approved' ? 'in_review' : 'rejected',
        'catatan_review' => $validated['catatan_review'] ?? null,
        'id_reviewer' => $request->user()->id_pengguna,
        'waktu_review' => now(),
    ]);

    $message = $validated['action'] === 'approved' 
        ? 'Assessment disetujui dan masuk tahap review.' 
        : 'Assessment ditolak.';

    return response()->json(['message' => $message]);
}
```

### Task 4.6: Add Review UI to Assessment Show Blade

**File:** `resources/views/operasi/assessment/show.blade.php`

Add a review section at the top (after header, before content):

```blade
@can('review', $assessment)
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Review Assessment</h3>
        
        @if($assessment->status_review === 'submitted')
            <p class="text-yellow-600 mb-4">Assessment ini menunggu review Anda.</p>
            <form id="reviewForm" method="POST" 
                  action="{{ route('assessment.review', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Catatan Review</label>
                    <textarea name="catatan_review" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" name="action" value="approved"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Setujui
                    </button>
                    <button type="submit" name="action" value="rejected"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Tolak
                    </button>
                </div>
            </form>
        @elseif($assessment->status_review === 'in_review')
            <div class="flex items-center gap-2 text-green-600">
                <span>✅ Disetujui — sedang dalam review lanjutan</span>
            </div>
        @elseif($assessment->status_review === 'approved')
            <div class="flex items-center gap-2 text-green-600">
                <span>✅ Assessment telah disetujui</span>
            </div>
        @elseif($assessment->status_review === 'rejected')
            <div class="flex items-center gap-2 text-red-600">
                <span>❌ Ditolak: {{ $assessment->catatan_review }}</span>
            </div>
        @else
            <p class="text-gray-500">Assessment ini masih draft.</p>
        @endif
    </div>
@endcan
```

Also, add submit button for TRC (the assessment creator):
```blade
@if($assessment->status_review === 'draft')
    <form method="POST" action="{{ route('assessment.submit', [$insiden->id_insiden, $assessment->id_assessment_utama]) }}" class="inline">
        @csrf
        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
            Ajukan Review
        </button>
    </form>
@endif
```

---

## Phase 5: Technical Debt

### Task 5.1: Move Inline Queries from Views to Controllers

**File:** `app/Http/Controllers/Operasi/InsidenController.php`  
**Change:** In `show()` method, add these to the `loadMissing()` or pass them as additional variables:

```php
// Add these queries:
$plenoList = OperasiPleno::where('id_insiden', $insiden->id_insiden)
    ->with(['pimpinan.profil'])
    ->orderBy('waktu_pleno', 'desc')
    ->get();

$jurnalList = OperasiJurnal::where('id_insiden', $insiden->id_insiden)
    ->with(['pengguna.profil'])
    ->orderBy('dibuat_pada', 'desc')
    ->get();

return view('operasi.insiden.show', compact('insiden', 'plenoList', 'jurnalList', ...));
```

**File:** `resources/views/operasi/insiden/show.blade.php`  
**Change:** Lines 270 and 309 — replace inline queries with `$plenoList` and `$jurnalList`.

### Task 5.2: Move Inline Query from Laporan Index

**File:** `app/Http/Controllers/Operasi/LaporanKejadianController.php`  
**Change:** In `index()` method, add:
```php
$jenisBencanaList = BencanaMasterJenis::orderBy('nama_bencana')->get();
return view('operasi.laporan.index', compact('laporanList', 'jenisBencanaList'));
```

**File:** `resources/views/operasi/laporan/index.blade.php`  
**Change:** Line 27 — replace `\App\Models\BencanaMasterJenis::orderBy(...)` with `$jenisBencanaList`.

### Task 5.3: Fix Layout Inconsistency (Bootstrap → Tailwind)

**Affected views (7 files using `layouts.app` Bootstrap):**

| File | Priority |
|------|----------|
| `resources/views/operasi/sitrep/index.blade.php` | Medium |
| `resources/views/operasi/sitrep/create.blade.php` | Low |
| `resources/views/operasi/sitrep/show.blade.php` | Low |
| `resources/views/operasi/posaju/index.blade.php` | Low |
| `resources/views/operasi/posaju/create.blade.php` | Low |
| `resources/views/operasi/posaju/show.blade.php` | Low |
| `resources/views/operasi/klaster/index.blade.php` | Low |
| `resources/views/operasi/klaster/create.blade.php` | Low |
| `resources/views/operasi/klaster/show.blade.php` | Low |
| `resources/views/operasi/jurnal/index.blade.php` | Low |
| `resources/views/operasi/command_center/index.blade.php` | Low |
| `resources/views/governance/role-approval/index.blade.php` | Low |

**Migration pattern for each view:**
1. Change `@extends('layouts.app')` → `<x-app-layout>`
2. Change `<div class="container">` → `<div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">`
3. Change Bootstrap classes to Tailwind equivalents:
   - `row` → `grid grid-cols-1 md:grid-cols-... gap-...`
   - `col-*` → Tailwind grid
   - `table table-*` → Tailwind table classes
   - `btn btn-*` → Tailwind button classes
   - `alert alert-*` → Tailwind alert classes
   - `form-control` → Tailwind form classes
   - `mb-*`, `mt-*` → already similar
   - `text-*` (Bootstrap) → `text-*` (Tailwind, different values)

### Task 5.4: Wire Up Command Center to Live Data

**File:** `resources/views/operasi/command_center/index.blade.php`

**Change:** Replace hardcoded `0` stats with data from controller. Create a service method or use existing SQL views:

```blade
<div class="text-3xl font-bold">{{ $statRelawanAktif ?? 0 }}</div>
<div class="text-3xl font-bold">{{ $statPoskoAktif ?? 0 }}</div>
<div class="text-3xl font-bold">{{ $statInsidenAktif ?? 0 }}</div>
```

**File:** `app/Http/Controllers/Operasi/CommandCenterController.php` (if exists, otherwise create)

Add data loading:
```php
$statRelawanAktif = OperasiPenugasan::whereIn('status_penugasan', ['aktif', 'on_site'])->count();
$statPoskoAktif = OperasiPosaju::where('status_alur', 'aktif')->count();
$statInsidenAktif = OperasiInsiden::whereIn('status_insiden', ['terverifikasi', 'respon', 'pemulihan'])->count();
```

---

## Dependencies & Ordering

```
Phase 1 (Penugasan UI)
  └── No dependencies — can start immediately

Phase 2 (SPK Auto-Penugasan)
  └── Depends on: Phase 1 (conceptual only — no code dependency)
  └── Can be done in parallel with Phase 1

Phase 3 (Laporan Cetak)
  └── No dependencies — can start immediately

Phase 4 (Review Workflow)
  ├── Depends on: Migration (Task 4.1)
  ├── Depends on: Model update (Task 4.2)
  └── Depends on: Policy update (Task 4.3)

Phase 5 (Technical Debt)
  ├── Task 5.1: No dependencies
  ├── Task 5.2: No dependencies
  ├── Task 5.3: Low priority, do last
  └── Task 5.4: No dependencies
```

**Recommended execution order:**
```
Week 1: Phase 1 (Penugasan UI) + Phase 2 (SPK auto-penugasan) in parallel
Week 2: Phase 3 (Laporan Cetak) + Task 5.1 + 5.2 in parallel
Week 3: Phase 4 (Review Workflow)
Week 4: Task 5.3 (Layout migration) + Task 5.4 (Command Center)
```

---

## Testing Strategy

### For Each Phase

| Phase | Test Type | What to Assert |
|-------|-----------|----------------|
| Phase 1 | Feature test | PenugasanController CRUD via HTTP. Create penugasan → assert DB has record. View penugasan → assert 200. Delete draft → assert soft deleted. |
| Phase 2 | Unit + Feature | `InsidenSpkController::store()` with id_penerima_spk → assert `OperasiPenugasan` created. Without penerima → assert no penugasan. |
| Phase 3 | Feature | `GET /insiden/{id}/assessment/{id}/cetak` → assert 200. Assert view receives `$assessment` with all relations. |
| Phase 4 | Feature | Submit assessment → assert `status_review = submitted`. Review → approve → assert `status_review = in_review`. Review → reject → assert `status_review = rejected`. Unauthorized user → assert 403. |
| Phase 5.1-2 | Visual | Tidak perlu test — pastikan view tidak error. |
| Phase 5.3 | Visual | Tidak perlu test — pastikan view tidak error. |
| Phase 5.4 | Feature | CommandCenterController returns correct counts. |

### Existing Test Suite

```bash
php artisan test --filter=AssessmentTest
# Expected: 5 passed (23 assertions) — must remain 0 failures after each phase
```

### Test Files to Create

| File | Tests |
|------|-------|
| `tests/Feature/Operasi/PenugasanWebTest.php` | CRUD test for PenugasanController |
| `tests/Feature/Operasi/SpkAutoPenugasanTest.php` | SPK → auto penugasan integration |
| `tests/Feature/Operasi/AssessmentReviewTest.php` | Submit + review + approve + reject |
