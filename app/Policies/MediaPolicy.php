<?php

namespace App\Policies;

use App\Infrastructure\Media\Persistence\Models\MediaModel;
use App\Models\AuthUser;
use App\Models\LaporanKejadian;
use App\Models\OperasiInsiden;
use App\Services\Auth\AuthorizationContextService;

class MediaPolicy
{
    public function __construct(
        protected AuthorizationContextService $authContext,
    ) {}

    public function upload(AuthUser $user): bool
    {
        return true;
    }

    public function view(AuthUser $user, MediaModel $media): bool
    {
        return $this->canAccess($user, $media);
    }

    public function delete(AuthUser $user, MediaModel $media): bool
    {
        return $this->canAccess($user, $media);
    }

    public function replace(AuthUser $user, MediaModel $media): bool
    {
        return $this->canAccess($user, $media);
    }

    private function canAccess(AuthUser $user, MediaModel $media): bool
    {
        if ($this->authContext->hasAnyRole(['super_admin', 'pwnu'])) {
            return true;
        }

        if ($this->authContext->hasRole('pcnu')) {
            $idPcnu = $this->resolveEntityPcnu($media);

            if ($idPcnu !== null) {
                return $this->authContext->canAccessInsiden($idPcnu);
            }
        }

        return $media->uploaded_by === $user->id_pengguna;
    }

    private function resolveEntityPcnu(MediaModel $media): ?int
    {
        if ($media->entity_type === null || $media->entity_id === null) {
            return null;
        }

        return match ($media->entity_type) {
            'laporan' => LaporanKejadian::find($media->entity_id)?->id_pcnu,
            'incident' => OperasiInsiden::find($media->entity_id)?->id_pcnu,
            default => null,
        };
    }
}
