<?php

namespace Tests\Feature\Media;

use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Policies\MediaPolicy;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MediaPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(string $role, string $scopeType = 'pcnu', int $scopeId = 1): AuthUser
    {
        $roleModel = AuthRole::create(['nama_peran' => $role, 'level_otoritas' => 1]);
        return AuthUser::forceCreate([
            'no_hp' => '08' . rand(100000000, 999999999),
            'kata_sandi' => 'hash',
            'id_peran' => $roleModel->id_peran,
            'default_scope_type' => $scopeType,
            'default_scope_id' => $scopeId,
            'status_akun' => 'aktif',
        ]);
    }

    private function createMedia(array $overrides = []): MediaModel
    {
        return MediaModel::create(array_merge([
            'entity_type' => 'test',
            'entity_id' => 1,
            'path' => 'test/file.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'access_level' => 'PUBLIC',
            'disk' => 'public',
            'original_name' => 'file.jpg',
            'uploaded_by' => 999,
            'version' => 1,
            'is_active' => true,
        ], $overrides));
    }

    public function test_super_admin_can_view_any_media(): void
    {
        $user = $this->createUser('super_admin');
        $media = $this->createMedia();

        $this->actingAs($user);
        $policy = app(MediaPolicy::class);

        $this->assertTrue($policy->view($user, $media));
    }

    public function test_super_admin_can_delete_any_media(): void
    {
        $user = $this->createUser('super_admin');
        $media = $this->createMedia();

        $this->actingAs($user);
        $policy = app(MediaPolicy::class);

        $this->assertTrue($policy->delete($user, $media));
    }

    public function test_owner_can_access_own_media(): void
    {
        $user = $this->createUser('relawan');
        $media = $this->createMedia(['uploaded_by' => $user->id_pengguna]);

        $this->actingAs($user);
        $policy = app(MediaPolicy::class);
        $authContext = app(AuthorizationContextService::class);
        $authContext->clearCache();

        $this->assertTrue($policy->view($user, $media));
    }

    public function test_other_user_cannot_access_media(): void
    {
        $owner = $this->createUser('relawan');
        $other = $this->createUser('relawan');
        $media = $this->createMedia(['uploaded_by' => $owner->id_pengguna]);

        $this->actingAs($other);
        $policy = app(MediaPolicy::class);
        $authContext = app(AuthorizationContextService::class);
        $authContext->clearCache();

        $this->assertFalse($policy->view($other, $media));
    }

    public function test_upload_always_allowed(): void
    {
        $user = $this->createUser('relawan');
        $policy = app(MediaPolicy::class);

        $this->assertTrue($policy->upload($user));
    }
}
