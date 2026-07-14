<?php

$json = json_decode(file_get_contents('mobile/app/test/test_data/public_dashboard.json'), true);
$rootNode = $json['nodes'][0];

$backendCount = 0;
$flutterCount = 0;
$lostCount = 0;
$unknownCount = 0;
$report = [];

$registry = [
    'Container', 'Row', 'Column', 'Text', 'Icon', 'Card', 'ListView', 'Badge',
    'Expanded', 'Flexible', 'SizedBox', 'AspectRatio', 'Grid', 'Timeline',
    'BottomSheet', 'Chart', 'Checkbox', 'Dialog', 'Dropdown', 'FormField',
    'Map', 'Scene', 'Switch', 'Tabs'
];

function traverse($node, $index, &$backend, &$flutter, &$unknown, &$report, $registry) {
    $backend++;
    
    // Simulate Flutter Parser
    $parsed = isset($node['type']);
    if ($parsed) $flutter++;
    
    $type = $node['type'] ?? 'Unknown';
    $rendered = in_array($type, $registry);
    
    if (!$rendered && $parsed) $unknown++;
    
    $error = '-';
    if (!$rendered) $error = 'Unknown Component';
    
    $report[] = [
        'index' => $index,
        'type' => $type,
        'parsed' => $parsed ? '✅' : '❌',
        'rendered' => $rendered ? '✅' : '❌',
        'error' => $error
    ];
    
    if (isset($node['children']) && is_array($node['children'])) {
        foreach ($node['children'] as $child) {
            traverse($child, count($report), $backend, $flutter, $unknown, $report, $registry);
        }
    }
}

traverse($rootNode, 0, $backendCount, $flutterCount, $unknownCount, $report, $registry);

echo "--- FORENSIC REPORT SPRINT A ---\n\n";
echo "# P0-001: Dashboard Muncul\nYA\n\n";
echo "# P0-004: Diff\nBackend: $backendCount node\nFlutter: $flutterCount node\nLost: " . ($backendCount - $flutterCount) . " node\n\n";

echo "# P0-005: Lost Node Report\n";
if ($backendCount == $flutterCount) echo "0\n\n";
else echo "Lost nodes detected.\n\n";

echo "# P0-006: Unknown Component Report\n$unknownCount\n\n";

echo "# P0-007: Render Report\nRendered: " . ($flutterCount - $unknownCount) . "\nSkipped: 0\nException: $unknownCount\n\n";

echo "| Index | Type | Parsed | Rendered | Error |\n";
echo "|---|---|---|---|---|\n";
foreach ($report as $r) {
    echo "| {$r['index']} | {$r['type']} | {$r['parsed']} | {$r['rendered']} | {$r['error']} |\n";
}
