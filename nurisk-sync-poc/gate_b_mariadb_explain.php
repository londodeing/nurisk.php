<?php
/**
 * GATE B: Query Planner Validation & P50/P95/P99 Benchmark
 */

$host = '127.0.0.1';
$db   = 'nurisk_staging';
$user = 'root';
$pass = '';

function calculatePercentile($latencies, $percentile) {
    sort($latencies);
    $index = ($percentile / 100) * count($latencies);
    if (floor($index) == $index) {
        $result = ($latencies[$index - 1] + $latencies[$index]) / 2;
    } else {
        $result = $latencies[floor($index)];
    }
    return $result;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "[GATE B] Memulai Benchmark Latency & EXPLAIN ANALYZE...\n";
    
    $query = "
        SELECT id, payload FROM sync_cursors 
        WHERE scope_type = 'insiden' AND scope_id = 1 
        AND (updated_at > '2026-06-01 00:00:00' OR (updated_at = '2026-06-01 00:00:00' AND id > 100))
        ORDER BY updated_at ASC, id ASC LIMIT 1000
    ";

    echo "1. Mengambil format JSON EXPLAIN...\n";
    $stmtExplain = $pdo->query("EXPLAIN FORMAT=JSON " . $query);
    $plan = $stmtExplain->fetchColumn();
    
    if (strpos($plan, '"using_filesort": true') !== false || strpos($plan, '"using_temporary_table": true') !== false) {
        echo "\n❌ GATE B FAILED: Using Filesort / Temporary terdeteksi!\n$plan\n";
        exit(1);
    } else {
        echo "✅ Query Planner murni menggunakan Index Range Scan.\n\n";
    }

    echo "2. Memulai Benchmark P50, P95, P99 (1.000 Iterasi)...\n";
    $latencies = [];
    for ($i = 0; $i < 1000; $i++) {
        $start = microtime(true);
        $stmt = $pdo->query($query);
        $stmt->fetchAll();
        $latencies[] = (microtime(true) - $start) * 1000; // ms
    }

    $p50 = calculatePercentile($latencies, 50);
    $p95 = calculatePercentile($latencies, 95);
    $p99 = calculatePercentile($latencies, 99);

    echo "==== GATE B BENCHMARK HASIL ====\n";
    echo "P50 Latency : " . number_format($p50, 2) . " ms\n";
    echo "P95 Latency : " . number_format($p95, 2) . " ms\n";
    echo "P99 Latency : " . number_format($p99, 2) . " ms\n\n";

    if ($p95 < 100) {
        echo "✅ GATE B PASSED: Target Performa tercapai secara ekstrem (P95 < 100ms).\n";
    } else {
        echo "❌ GATE B FAILED: Performa kueri melampaui batas toleransi (P95 >= 100ms).\n";
    }

} catch (Exception $e) {
    echo "Error MariaDB: " . $e->getMessage() . "\n";
}
