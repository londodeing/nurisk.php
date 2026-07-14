<?php

declare(strict_types=1);

namespace App\Application\Media\Commands;

use Illuminate\Http\UploadedFile;

/**
 * Command to replace an existing media file while preserving the aggregate identity.
 */
final class ReplaceMediaCommand
{
    public function __construct(
        public readonly int $mediaId,
        public readonly UploadedFile $file,
    ) {}
}
