<?php

namespace App\Listeners\Media;

use App\Application\Media\Events\ThumbnailGenerationRequested;
use App\Jobs\Media\GenerateThumbnailJob;

class DispatchThumbnailGeneration
{
    public function handle(ThumbnailGenerationRequested $event): void
    {
        GenerateThumbnailJob::dispatch($event);
    }
}
