<?php

namespace App\Observers;

use App\Models\OperasiInsiden;
use App\Services\Projection\IncidentProjectionService;
use Illuminate\Support\Facades\Log;

class OperasiInsidenObserver
{
    protected IncidentProjectionService $projectionService;

    public function __construct(IncidentProjectionService $projectionService)
    {
        $this->projectionService = $projectionService;
    }

    /**
     * Handle the OperasiInsiden "created" event.
     */
    public function created(OperasiInsiden $insiden): void
    {
        $this->triggerProjection($insiden);
    }

    /**
     * Handle the OperasiInsiden "updated" event.
     */
    public function updated(OperasiInsiden $insiden): void
    {
        // Prevent projection update if only soft-deleting
        if ($insiden->isDirty('dihapus_pada') && $insiden->dihapus_pada !== null) {
            return;
        }

        $this->triggerProjection($insiden);
    }

    /**
     * Handle the OperasiInsiden "restored" event.
     */
    public function restored(OperasiInsiden $insiden): void
    {
        $this->triggerProjection($insiden);
    }

    /**
     * Handle the OperasiInsiden "deleted" event.
     */
    public function deleted(OperasiInsiden $insiden): void
    {
        $this->triggerRemoval($insiden);
    }

    /**
     * Handle the OperasiInsiden "force deleted" event.
     */
    public function forceDeleted(OperasiInsiden $insiden): void
    {
        $this->triggerRemoval($insiden);
    }

    /**
     * Execute projection service and handle exceptions
     */
    private function triggerProjection(OperasiInsiden $insiden): void
    {
        try {
            $this->projectionService->projectIncident($insiden);
        } catch (\Exception $e) {
            Log::error("Failed to project OperasiInsiden [{$insiden->id_insiden}]: " . $e->getMessage());
        }
    }

    /**
     * Execute removal projection service and handle exceptions
     */
    private function triggerRemoval(OperasiInsiden $insiden): void
    {
        try {
            $this->projectionService->removeProjection($insiden->id_insiden);
        } catch (\Exception $e) {
            Log::error("Failed to remove projection for OperasiInsiden [{$insiden->id_insiden}]: " . $e->getMessage());
        }
    }
}
