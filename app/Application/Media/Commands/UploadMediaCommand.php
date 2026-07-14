<?php

declare(strict_types=1);

namespace App\Application\Media\Commands;

use Illuminate\Http\UploadedFile;

/**
 * Command to upload a new media file and associate it with an entity.
 *
 * Idempotency is handled at the HTTP middleware layer — this command
 * represents a clean upload intent.
 */
final class UploadMediaCommand
{
    public function __construct(
        public readonly string $entityType,
        public readonly int $entityId,
        public readonly UploadedFile $file,
        public readonly string $visibility,
        public readonly ?int $uploadedBy = null,
        public readonly ?string $uploadedIp = null,
        public readonly ?string $uploadedUserAgent = null,
    ) {}
}
