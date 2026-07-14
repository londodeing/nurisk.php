<?php

namespace App\Listeners;

use App\Services\Projection\IncidentProjectionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateOperationalProjection implements ShouldQueue
{
    use InteractsWithQueue;

    protected IncidentProjectionService $projectionService;

    public function __construct(IncidentProjectionService $projectionService)
    {
        $this->projectionService = $projectionService;
    }

    /**
     * Handle the event.
     * We will use a generic event payload or listen to specific Eloquent events.
     */
    public function handle($event): void
    {
        try {
            // For now, assuming event has $incident property
            if (isset($event->incident)) {
                $this->projectionService->projectIncident($event->incident);
            }
        } catch (\Exception $e) {
            Log::error("Failed to process UpdateOperationalProjection: " . $e->getMessage());
        }
    }
}
