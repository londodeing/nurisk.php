<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuthUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Media\Persistence\Models\MediaModel as Media;
use App\Infrastructure\Media\Persistence\Models\MediaConversionModel as MediaConversion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

echo "============================================\n";
echo " FINAL PRODUCTION READINESS AUDIT RUNNER v3\n";
echo "============================================\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// 1. Get Authentication Token
$user = AuthUser::first();
if (!$user) {
    $user = AuthUser::factory()->create();
}
$token = $user->createToken('audit-token')->plainTextToken;
echo "[INFO] Authenticated as User ID: {$user->id}\n";
$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
];

// Phase 1 & 2: HTTP Upload & Validation
echo "\n--- Phase 1: Upload & Validation ---\n";
// Invalid upload
$response = Http::withHeaders($headers)->post("{$baseUrl}/media", [
    'entity_type' => 'laporan',
    'entity_id' => 999,
    // missing file
]);
if ($response->status() === 422) {
    echo "[PASS] Validation caught missing file.\n";
} else {
    echo "[FAIL] Validation failed. Status: " . $response->status() . "\n";
}

// Valid upload
// Create a fake image to upload
$tempImage = tempnam(sys_get_temp_dir(), 'test') . '.jpg';
$img = imagecreatetruecolor(100, 100);
imagejpeg($img, $tempImage);
imagedestroy($img);

$response = Http::withHeaders($headers)
    ->attach('file', file_get_contents($tempImage), 'test_image.jpg')
    ->post("{$baseUrl}/media", [
        'entity_type' => 'laporan',
        'entity_id' => 999,
        'collection_name' => 'default',
    ]);

if ($response->status() === 201) {
    echo "[PASS] File uploaded successfully.\n";
    $mediaData = $response->json();
    echo "       Raw Response: " . $response->body() . "\n";
    $mediaId = $mediaData['id'] ?? null;
    echo "       Media ID: {$mediaId}\n";
    if (!$mediaId) {
        echo "[FAIL] Missing media ID in response.\n";
        exit(1);
    }
} else {
    echo "[FAIL] File upload failed. Status: " . $response->status() . "\n";
    echo $response->body() . "\n";
    exit(1);
}

// Phase 2: Persistence (MySQL & MinIO)
echo "\n--- Phase 2: Persistence ---\n";
$media = Media::find($mediaId);
if ($media) {
    echo "[PASS] Record exists in MySQL.\n";
} else {
    echo "[FAIL] Record NOT found in MySQL.\n";
}

$path = $media->path;
if (Storage::disk('s3')->exists($path)) {
    echo "[PASS] File exists in MinIO (s3 disk).\n";
} else {
    echo "[FAIL] File NOT found in MinIO. Path: {$path}\n";
}

// Phase 3 & 4: Queue processing and Conversions
echo "\n--- Phase 3 & 4: Events, Queue, and Conversions ---\n";
echo "Executing queue worker for 1 job...\n";
// We can run artisan queue:work --once
exec("php artisan queue:work --once");
exec("php artisan queue:work --once"); // Try again in case there are multiple

$media->refresh();
$conversions = MediaConversion::where('media_id', $media->id)->get();
if ($conversions->count() > 0) {
    echo "[PASS] Conversion records found in database: " . $conversions->count() . "\n";
    foreach ($conversions as $conv) {
        if (Storage::disk('s3')->exists($conv->path)) {
            echo "       [PASS] Conversion '{$conv->conversion_type}' exists in MinIO.\n";
        } else {
            echo "       [FAIL] Conversion '{$conv->conversion_type}' missing in MinIO: {$conv->path}\n";
        }
    }
} else {
    echo "[FAIL] No conversion records found in database.\n";
}

// Phase 5: Presigned URL
echo "\n--- Phase 5: Presigned URL ---\n";
$response = Http::withHeaders($headers)->get("{$baseUrl}/media/{$mediaId}");
if ($response->status() === 200) {
    $url = $response->json('url');
    echo "[PASS] Fetch metadata successful. URL: {$url}\n";
    if (strpos($url, 'X-Amz-Signature') !== false || strpos($url, 'AWSAccessKeyId') !== false) {
        echo "[PASS] URL appears to be a presigned URL.\n";
    } else {
        echo "[WARN] URL might not be presigned: {$url}\n";
    }
} else {
    echo "[FAIL] Fetch metadata failed. Status: " . $response->status() . "\n";
}

// Phase 6: Replace
echo "\n--- Phase 6: Replace Operation ---\n";
$tempImage2 = tempnam(sys_get_temp_dir(), 'test2') . '.png';
$img2 = imagecreatetruecolor(100, 100);
imagepng($img2, $tempImage2);
imagedestroy($img2);

$response = Http::withHeaders($headers)
    ->attach('file', file_get_contents($tempImage2), 'replaced_image.png')
    ->post("{$baseUrl}/media/{$mediaId}/replace");

if ($response->status() === 200) {
    echo "[PASS] Replace successful.\n";
    if (!Storage::disk('s3')->exists($path)) {
        echo "[PASS] Old file deleted from MinIO.\n";
    } else {
        echo "[FAIL] Old file still exists in MinIO.\n";
    }
    $media->refresh();
    if (Storage::disk('s3')->exists($media->path)) {
        echo "[PASS] New file exists in MinIO.\n";
    } else {
        echo "[FAIL] New file NOT found in MinIO.\n";
    }
} else {
    echo "[FAIL] Replace failed. Status: " . $response->status() . "\n";
    echo $response->body() . "\n";
}

// Phase 7: Delete & Restore
echo "\n--- Phase 7: Delete & Restore ---\n";
$newPath = $media->path;
$response = Http::withHeaders($headers)->delete("{$baseUrl}/media/{$mediaId}");
if ($response->status() === 200 || $response->status() === 204) {
    echo "[PASS] Delete API successful.\n";
    
    // Check if soft deleted
    $deletedMedia = Media::withTrashed()->find($mediaId);
    if ($deletedMedia && $deletedMedia->trashed()) {
        echo "[PASS] Media record soft deleted in database.\n";
    } else {
        echo "[FAIL] Media record not soft deleted.\n";
    }

    if (Storage::disk('s3')->exists($newPath)) {
        echo "[PASS] Original file kept in MinIO (soft delete behavior).\n";
    } else {
        echo "[WARN] Original file deleted from MinIO.\n";
    }
} else {
    echo "[FAIL] Delete API failed. Status: " . $response->status() . "\n";
}

// Phase 8: Chaos Testing (Upload followed by DB rollback)
echo "\n--- Phase 8: Chaos Testing (DB failure) ---\n";
echo "Testing transaction wrapper during upload...\n";
// This requires modifying the code temporarily or simulating an error. We will just check if we can simulate it.
// We'll skip the automated DB failure injection for now and just note it for manual review or specific test.
echo "[SKIP] Complex to test via HTTP without test doubles. Will inspect UploadMediaHandler for DB::transaction.\n";

echo "\n--- Done ---\n";
@unlink($tempImage);
@unlink($tempImage2);

