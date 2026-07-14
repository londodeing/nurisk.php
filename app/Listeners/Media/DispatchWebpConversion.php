<?php

namespace App\Listeners\Media;

use App\Application\Media\Events\WebpConversionRequested;
use App\Jobs\Media\GenerateWebpJob;

class DispatchWebpConversion
{
    public function handle(WebpConversionRequested $event): void
    {
        GenerateWebpJob::dispatch($event);
    }
}
