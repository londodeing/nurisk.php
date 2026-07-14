<?php
namespace App\Services\Dashboard;

use App\Services\Dashboard\Builders\BlockBuilderInterface;
use App\Services\Dashboard\Builders\WarningBlockBuilder;
use App\Services\Dashboard\Builders\WeatherBlockBuilder;
use App\Services\Dashboard\Builders\KpiBlockBuilder;
use App\Services\Dashboard\Builders\IncidentBlockBuilder;
use App\Services\Dashboard\Builders\NewsBlockBuilder;
use App\Services\Dashboard\Builders\TrcQueueBlockBuilder;

class ComponentRegistry {
    private static array $builders = [
        'WarningBlock' => WarningBlockBuilder::class,
        'WeatherBlock' => WeatherBlockBuilder::class,
        'KpiBlock' => KpiBlockBuilder::class,
        'IncidentBlock' => IncidentBlockBuilder::class,
        'NewsBlock' => NewsBlockBuilder::class,
        'TrcQueueBlock' => TrcQueueBlockBuilder::class,
    ];

    public static function build(array $block, DashboardProjectionService $projection): ?array {
        $type = $block['type'] ?? '';
        if (!isset(self::$builders[$type])) {
            return null;
        }

        $builderClass = self::$builders[$type];
        /** @var BlockBuilderInterface $builder */
        $builder = new $builderClass();
        return $builder->build($block, $projection);
    }
}
