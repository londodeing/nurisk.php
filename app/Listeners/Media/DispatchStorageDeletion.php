<?php

namespace App\Listeners\Media;

use App\Application\Media\Events\MediaDeletionRequested;
use App\Application\Media\Events\MediaReplacementRequested;
use App\Jobs\Media\DeleteObjectJob;

class DispatchStorageDeletion
{
    public function handle(MediaDeletionRequested|MediaReplacementRequested $event): void
    {
        $path = $event instanceof MediaReplacementRequested
            ? $event->oldPath
            : $event->path;

        if ($path !== '') {
            DeleteObjectJob::dispatch($path, $event->disk);
        }
    }
}
