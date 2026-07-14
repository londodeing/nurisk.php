<?php

namespace App\Services\Sdui\Runtime\Screens;

use App\Services\Dashboard\Composer\PublicDashboardComposer;

class DashboardHomeService
{
    public function __construct(
        private PublicDashboardComposer $composer
    ) {}

    public function compose(): array
    {
        $tree = $this->composer->compose();

        // The composer returns a tree with schema_version at the root.
        // Extract it for the envelope and remove from the root node.
        $schemaVersion = $tree['schema_version'] ?? '1.0';
        unset($tree['schema_version']);

        return [
            'schema_version' => $schemaVersion,
            'scene_id'       => 'public_dashboard',
            'version'        => time(),
            'ttl_seconds'    => 60,
            'root'           => $tree,
        ];
    }
}
