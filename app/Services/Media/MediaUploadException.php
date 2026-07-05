<?php

namespace App\Services\Media;

use Exception;

class MediaUploadException extends Exception
{
    public function __construct(string $message = 'Media upload failed', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
