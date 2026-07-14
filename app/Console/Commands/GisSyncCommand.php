<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GisAdapterService;

class GisSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gis:sync';

    protected $description = 'Synchronize GIS layers from external sources (InaRISK, BMKG) to local Cache (Mirror Strategy)';

    public function handle(GisAdapterService $service)
    {
        $this->info('Starting GIS Sync...');
        
        $layers = $service->getActiveLayers();
        foreach ($layers as $layer) {
            if ($layer->render_type === 'raster_tile') {
                continue; // Tile proxy caching is handled on-the-fly or in a separate tile scraper
            }
            
            $this->info("Syncing layer: {$layer->layer_id}");
            // Delegate fetching and caching entirely to the service
            $service->syncLayerData($layer);
            $this->info("Layer {$layer->layer_id} synced successfully.");
        }
        
        $this->info('GIS Sync completed.');
    }
}
