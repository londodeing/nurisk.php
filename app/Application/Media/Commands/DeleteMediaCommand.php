<?php

declare(strict_types=1);

namespace App\Application\Media\Commands;

/**
 * Command to soft-delete a media record and schedule async object removal.
 */
final class DeleteMediaCommand
{
    public function __construct(
        public readonly int $mediaId,
    ) {}
}
