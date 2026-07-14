<?php

namespace Tests\Feature\Media;

use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Models\AuthRole;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use DatabaseTransactions;

    private AuthUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $roleModel = AuthRole::create(['nama_peran' => 'relawan', 'level_otoritas' => 1]);
        $this->user = AuthUser::forceCreate([
            'no_hp' => '081234567890',
            'kata_sandi' => 'hash',
            'id_peran' => $roleModel->id_peran,
            'default_scope_type' => 'pcnu',
            'default_scope_id' => 1,
            'status_akun' => 'aktif',
        ]);

        Storage::fake('public');
        Queue::fake();
        Event::fake([ThumbnailGenerationRequested::class]);
    }

    public function test_upload_returns_201_and_persists_media(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg', 200, 200);

        $response = $this->actingAs($this->user)->post(route('api.media.upload'), [
            'entity_type' => 'test_entity',
            'entity_id' => 1,
            'file' => $file,
            'visibility' => 'PUBLIC',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'path', 'url', 'mime_type', 'size_bytes']);

        $mediaId = $response->json('id');

        $this->assertDatabaseHas('media', [
            'id' => $mediaId,
            'entity_type' => 'test_entity',
            'entity_id' => 1,
            'mime_type' => 'image/jpeg',
            'uploaded_by' => $this->user->id_pengguna,
        ]);

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_dispatches_thumbnail_generation_event(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg');

        $this->actingAs($this->user)->post(route('api.media.upload'), [
            'entity_type' => 'test_entity',
            'entity_id' => 1,
            'file' => $file,
            'visibility' => 'PUBLIC',
        ]);

        Event::assertDispatched(ThumbnailGenerationRequested::class);
    }

    public function test_upload_rejects_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.exe', 100);

        $response = $this->actingAs($this->user)->post(route('api.media.upload'), [
            'entity_type' => 'test_entity',
            'entity_id' => 1,
            'file' => $file,
            'visibility' => 'PUBLIC',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_upload_rejects_unauthenticated_request(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg');

        $response = $this->post(route('api.media.upload'), [
            'entity_type' => 'test_entity',
            'entity_id' => 1,
            'file' => $file,
            'visibility' => 'PUBLIC',
        ]);

        $response->assertStatus(401);
    }
}
