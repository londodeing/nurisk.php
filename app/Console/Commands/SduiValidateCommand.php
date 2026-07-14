<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Dashboard\SduiValidatorService;
use App\Services\Dashboard\DashboardLayoutService;
use App\Services\Dashboard\DashboardJsonBuilder;
use App\Services\Dashboard\DashboardProjectionService;

class SduiValidateCommand extends Command
{
    protected $signature = 'sdui:validate';
    protected $description = 'Validate SDUI output schemas against strict rules.';

    public function handle(
        SduiValidatorService $validator, 
        DashboardLayoutService $layoutService, 
        DashboardJsonBuilder $builder,
        DashboardProjectionService $projection
    ) {
        $this->info("Validating SDUI layout configuration...");
        // In a real app we'd simulate various user states. We'll pass a mock user or null.
        $layout = $layoutService->getLayoutForUser(null);
        $json = $builder->build($layout, $projection);
        
        try {
            $validator->validate($json);
            $this->info("SDUI Layout OK.");
        } catch (\Exception $e) {
            $this->error("Validation failed: " . $e->getMessage());
            return 1;
        }

        $this->info('All SDUI outputs validated successfully.');
        return 0;
    }
}
