<?php
$arti = "/home/londo/.gemini/antigravity-ide/brain/16109252-72d6-4fd1-b9b9-1eeade9004b4/artifacts/account";
$l = json_decode(file_get_contents('legacy_home.json'), true);
$r = json_decode(file_get_contents('runtime_home.json'), true);

file_put_contents("$arti/identity/legacy.json", json_encode($l['root']['children'][0], JSON_PRETTY_PRINT));
file_put_contents("$arti/identity/runtime.json", json_encode($r['nodes'][0]['children'][0]['children'][0], JSON_PRETTY_PRINT));

// Assignment is index 1 in legacy root, index 1 in runtime screen sections
file_put_contents("$arti/assignment/legacy.json", json_encode($l['root']['children'][1], JSON_PRETTY_PRINT));
file_put_contents("$arti/assignment/runtime.json", json_encode($r['nodes'][0]['children'][1]['children'][0], JSON_PRETTY_PRINT));

// Command Center is index 2 in legacy root, index 2 in runtime screen sections
file_put_contents("$arti/command_center/legacy.json", json_encode($l['root']['children'][2], JSON_PRETTY_PRINT));
file_put_contents("$arti/command_center/runtime.json", json_encode($r['nodes'][0]['children'][2]['children'][0], JSON_PRETTY_PRINT));

// Menu is index 3 in legacy root, index 3 in runtime screen sections
file_put_contents("$arti/menu/legacy.json", json_encode($l['root']['children'][3], JSON_PRETTY_PRINT));
file_put_contents("$arti/menu/runtime.json", json_encode($r['nodes'][0]['children'][3]['children'][0], JSON_PRETTY_PRINT));

echo "Extracted.\n";
