<?php

$dbFile = __DIR__ . '/nurisk_poc_explain.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
}

try {
    $pdo = new PDO("sqlite:" . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // SQLite Optimizations for massive inserts
    $pdo->exec("PRAGMA journal_mode = OFF;");
    $pdo->exec("PRAGMA synchronous = 0;");
    $pdo->exec("PRAGMA cache_size = 1000000;");
    
    $pdo->exec("
        CREATE TABLE sync_cursors (
            id INTEGER PRIMARY KEY,
            scope_type TEXT NOT NULL,
            scope_id INTEGER NOT NULL,
            updated_at TEXT NOT NULL
        );
    ");
    
    // The exact index from RFC
    $pdo->exec("CREATE INDEX idx_scope_cursor ON sync_cursors (scope_type, scope_id, updated_at, id);");
    
    echo "==== TEST 1C: EXPLAIN ANALYZE (QUERY PLAN) ====\n";
    
    // ============================================================
    // Test 1 Juta Row
    // ============================================================
    echo "\n[INFO] Melakukan injeksi 1.000.000 rows (menggunakan CTE SQLite yang sangat cepat)...\n";
    $pdo->exec("
        INSERT INTO sync_cursors (scope_type, scope_id, updated_at)
        WITH RECURSIVE cnt(x) AS (
            SELECT 1 
            UNION ALL 
            SELECT x+1 FROM cnt WHERE x < 1000000
        )
        SELECT 'insiden', 1, datetime('now', '-' || (x % 86400) || ' seconds') FROM cnt;
    ");
    $pdo->exec("ANALYZE;"); // Generate statistics
    
    echo "1 Juta Row selesai.\n";
    
    // Query yang akan dieksekusi berdasarkan RFC
    $query = "
        SELECT * FROM sync_cursors 
        WHERE scope_type = 'insiden' 
        AND scope_id = 1 
        AND (
            updated_at > '2026-06-01 10:00:00' 
            OR (updated_at = '2026-06-01 10:00:00' AND id > 500)
        )
        ORDER BY updated_at ASC, id ASC
        LIMIT 1000
    ";
    
    $stmt = $pdo->query("EXPLAIN QUERY PLAN " . $query);
    $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=> EXPLAIN QUERY PLAN (1 Juta Row):\n";
    $hasFilesort = false;
    foreach ($plan as $row) {
        $detail = $row['detail'];
        echo "   - $detail\n";
        if (strpos(strtoupper($detail), 'TEMP B-TREE') !== false || strpos(strtoupper($detail), 'SORT') !== false) {
            $hasFilesort = true;
        }
    }
    
    // ============================================================
    // Test 10 Juta Row
    // ============================================================
    echo "\n[INFO] Menambahkan hingga 10.000.000 rows...\n";
    $pdo->exec("
        INSERT INTO sync_cursors (scope_type, scope_id, updated_at)
        WITH RECURSIVE cnt(x) AS (
            SELECT 1000001 
            UNION ALL 
            SELECT x+1 FROM cnt WHERE x < 10000000
        )
        SELECT 'insiden', 1, datetime('now', '-' || (x % 86400) || ' seconds') FROM cnt;
    ");
    $pdo->exec("ANALYZE;"); 
    
    echo "10 Juta Row selesai.\n";
    
    $stmt = $pdo->query("EXPLAIN QUERY PLAN " . $query);
    $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=> EXPLAIN QUERY PLAN (10 Juta Row):\n";
    foreach ($plan as $row) {
        $detail = $row['detail'];
        echo "   - $detail\n";
        if (strpos(strtoupper($detail), 'TEMP B-TREE') !== false || strpos(strtoupper($detail), 'SORT') !== false) {
            $hasFilesort = true;
        }
    }
    
    echo "\n==== HASIL TAHAP 1C ====\n";
    if ($hasFilesort) {
        echo "❌ FAILED (Redesign Index Required!)\n";
        echo "Penyebab: Query Planner menggunakan 'TEMP B-TREE' (filesort) karena klausa OR (updated_at > X OR ...) menghalangi pemanfaatan composite index untuk proses ORDER BY.\n";
        echo "Saran: Kita harus menggunakan struktur cursor tuple (Row Value Constructor) yang disupport MySQL/PostgreSQL: `WHERE (updated_at, id) > (?, ?)`.\n";
    } else {
        echo "✅ PASS (RFC Valid!)\n";
        echo "Query Planner mengeksekusi Index Range Scan murni tanpa memerlukan filesort sementara.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
