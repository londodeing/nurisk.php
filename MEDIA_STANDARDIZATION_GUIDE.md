# MEDIA STANDARDIZATION GUIDE

## Current State: 4 Different Upload Patterns

| Pattern | Used By | Issues |
|---------|---------|--------|
| **Direct `store()` in controller** | `LaporController` | Inline, no validation request, no transaction safety |
| **Storage facade in service** | `SuratPdfService` | Works but no standard filename convention |
| **fgetcsv from temp** | `OrgAssetController::import` | File never stored, no audit trail |
| **Form with no handler** | `assets/create.blade.php:foto` | Uploaded to nowhere |

## Proposed Standard: `MediaUploadService`

### Single Entry Point

```php
class MediaUploadService
{
    public function upload(
        UploadedFile $file,
        string $category,     // 'laporan', 'aset', 'profil', 'dokumen'
        ?string $subfolder,   // optional subfolder like incident ID
        array $options = []   // resize, thumbnail, visibility
    ): MediaUploadResult;
    
    public function delete(string $path): bool;
    public function cleanupOrphans(): int;
}
```

### Standard Path Convention

```
{category}/{year}/{month}/{uuid}.{ext}
Examples:
  laporan/2026/06/550e8400-e29b-41d4-a716-446655440000.jpg
  aset/2026/06/550e8400-e29b-41d4-a716-446655440001.png
  surat/2026/06/550e8400-e29b-41d4-a716-446655440002.pdf
```

### Database Convention

All file-path fields MUST:
- Store **relative path** only (no full URL, no absolute path)
- Be `varchar(255) NULL`
- Be included in model `$fillable`
- Be accessed via `Storage::url($record->field)` for display

### Controller Pattern

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    
    $mediaResult = $this->mediaUploadService->upload(
        $request->file('foto'),
        'laporan'
    );
    
    DB::transaction(function () use ($validated, $mediaResult) {
        $record = LaporanKejadian::create([
            ...$validated,
            'photo_path' => $mediaResult->path,
        ]);
        
        $mediaResult->associate($record, 'photo_path');
    });
}
```

### Required Across All Modules

| Module | Current Pattern | Target |
|--------|----------------|--------|
| Public Laporan | Inline in controller | MediaUploadService |
| Asset Registration | **Missing entirely** | MediaUploadService |
| Asset CSV Import | fgetcsv without store | Store original + process |
| Surat PDF | Storage facade | MediaUploadService |
| Organization SK | Unknown | MediaUploadService |
| Assessment Photos | Not implemented | MediaUploadService |

### File Processing Pipeline

```
Upload
  → MIME validation
  → Size validation
  → Virus scan (future)
  → UUID filename generation
  → Store to disk (public or private disk)
  → Generate thumbnail (if image)
  → Return path
  → Associate with DB record (within transaction)
  → Cleanup on rollback
```
