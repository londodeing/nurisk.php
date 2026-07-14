<?php

declare(strict_types=1);

namespace App\Domain\Media\Exceptions;

use App\Domain\Media\Entities\Media;

/**
 * Thrown when a Media aggregate is not found by the given identifier.
 */
final class MediaNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Media with ID %d not found.', $id));
    }
}
