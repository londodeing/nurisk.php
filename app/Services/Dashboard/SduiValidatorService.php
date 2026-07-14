<?php

namespace App\Services\Dashboard;

class SduiValidatorService
{
    private const ALLOWED_PRIMITIVES = [
        'Container', 'Row', 'Column', 'Grid', 'Stack', 'Spacer', 'Divider',
        'Text', 'Image', 'Icon', 'Avatar', 'Badge',
        'Card', 'Statistic', 'Metric', 'Timeline', 'Progress', 'Button', 'Action',
        'List'
    ];

    public function validate(array $json): bool
    {
        if (!isset($json['schema_version']) || !isset($json['screen']) || !isset($json['nodes'])) {
            throw new \Exception("SDUI JSON missing required root fields.");
        }

        foreach ($json['nodes'] as $node) {
            $this->validateNode($node);
        }

        return true;
    }

    private function validateNode(array $node): void
    {
        if (!isset($node['type'])) {
            throw new \Exception("SDUI Node missing 'type'.");
        }

        if (!in_array($node['type'], self::ALLOWED_PRIMITIVES)) {
            throw new \Exception("SDUI Invalid Primitive Type: {$node['type']}");
        }

        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->validateNode($child);
            }
        }
    }
}
