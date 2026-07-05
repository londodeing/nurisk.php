<?php

return [
    'snapshot_disk' => env('SYNC_SNAPSHOT_DISK', 'local'),

    'snapshot_path' => env('SYNC_SNAPSHOT_PATH', 'snapshots'),

    'snapshot_ttl_minutes' => (int) env('SYNC_SNAPSHOT_TTL_MINUTES', 60),
];
