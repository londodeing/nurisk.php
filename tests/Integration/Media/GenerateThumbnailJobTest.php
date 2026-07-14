<?php

namespace Tests\Integration\Media;

use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Domain\Media\Factories\MediaFactory;
use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Infrastructure\Media\Persistence\Repositories\EloquentMediaRepository;
use App\Infrastructure\Media\Storage\Contracts\StorageProvider;
use App\Jobs\Media\GenerateThumbnailJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateThumbnailJobTest extends TestCase
{
    use DatabaseTransactions;

    private MediaModel $media;
    private string $sourcePath;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $imgPath = tempnam(sys_get_temp_dir(), 'test_') . '.jpg';
        $img = imagecreatetruecolor(200, 150);
        $bg = imagecolorallocate($img, 100, 150, 200);
        imagefill($img, 0, 0, $bg);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 50, 65, 'TEST', $textColor);
        imagejpeg($img, $imgPath, 85);
        imagedestroy($img);

        $this->sourcePath = 'test/source.jpg';
        Storage::disk('public')->put($this->sourcePath, file_get_contents($imgPath));
        unlink($imgPath);

        $this->media = MediaModel::create([
            'entity_type' => 'test',
            'entity_id' => 1,
            'path' => $this->sourcePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => Storage::disk('public')->size($this->sourcePath),
            'access_level' => 'PUBLIC',
            'disk' => 'public',
            'original_name' => 'source.jpg',
            'width' => 200,
            'height' => 150,
            'version' => 1,
            'is_active' => true,
        ]);
    }

    public function test_thumbnail_is_created_and_persisted(): void
    {
        $event = new ThumbnailGenerationRequested(
            mediaId: $this->media->id,
            mimeType: 'image/jpeg',
        );

        $job = new GenerateThumbnailJob($event);
        $job->handle(
            app(EloquentMediaRepository::class),
            app(MediaFactory::class),
            app(StorageProvider::class),
        );

        $this->assertDatabaseHas('media_conversions', [
            'media_id' => $this->media->id,
            'conversion_type' => 'thumbnail',
        ]);

        $conversion = $this->media->conversions()->where('conversion_type', 'thumbnail')->first();
        $this->assertNotNull($conversion);
        Storage::disk('public')->assertExists($conversion->path);
    }

    public function test_thumbnail_job_is_idempotent(): void
    {
        $event = new ThumbnailGenerationRequested(
            mediaId: $this->media->id,
            mimeType: 'image/jpeg',
        );

        $job = new GenerateThumbnailJob($event);
        $job->handle(
            app(EloquentMediaRepository::class),
            app(MediaFactory::class),
            app(StorageProvider::class),
        );

        $job2 = new GenerateThumbnailJob($event);
        $job2->handle(
            app(EloquentMediaRepository::class),
            app(MediaFactory::class),
            app(StorageProvider::class),
        );

        $this->assertDatabaseCount('media_conversions', 1);
    }
}
