<?php

$host = 'localhost';
$db   = 'nurisk_poc';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Setup DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    $pdo->exec("USE $db");
    $pdo->exec("DROP TABLE IF EXISTS sync_cursors");
    $pdo->exec("
        CREATE TABLE sync_cursors (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            scope_type VARCHAR(50) NOT NULL,
            scope_id BIGINT NOT NULL,
            payload VARCHAR(255),
            updated_at TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
            INDEX idx_scope_cursor (scope_type, scope_id, updated_at, id)
        ) ENGINE=InnoDB;
    ");
    
    echo "==== TEST 1: RACE CONDITION & CURSOR HOLE (FIX-1) ====\n";
    
    // We need 3 separate connections to simulate concurrency
    $connA = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $connB = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $connPull = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Transaksi A mulai dan insert data (Mendapat Timestamp T1)
    $connA->beginTransaction();
    $connA->exec("INSERT INTO sync_cursors (scope_type, scope_id, payload) VALUES ('insiden', 1, 'Data dari Tx A')");
    $stmtA = $connA->query("SELECT id, updated_at FROM sync_cursors WHERE payload = 'Data dari Tx A'");
    $rowA = $stmtA->fetch(PDO::FETCH_ASSOC);
    echo "Tx A: Inserted ID {$rowA['id']} at Timestamp {$rowA['updated_at']} (Belum Commit)\n";
    
    // Simulasi delay (Tx A masih memproses payload besar)
    usleep(50000); // 50ms delay
    
    // Transaksi B mulai, insert data (Mendapat Timestamp T2), dan langsung Commit
    $connB->beginTransaction();
    $connB->exec("INSERT INTO sync_cursors (scope_type, scope_id, payload) VALUES ('insiden', 1, 'Data dari Tx B')");
    $stmtB = $connB->query("SELECT id, updated_at FROM sync_cursors WHERE payload = 'Data dari Tx B'");
    $rowB = $stmtB->fetch(PDO::FETCH_ASSOC);
    echo "Tx B: Inserted ID {$rowB['id']} at Timestamp {$rowB['updated_at']} (Langsung Commit)\n";
    $connB->commit();
    
    // Pull Sync Pertama (Klien Flutter sync)
    echo "\n[Pull Sync 1] Menarik data baru...\n";
    $stmtPull = $connPull->query("SELECT * FROM sync_cursors WHERE scope_type='insiden' AND scope_id=1 ORDER BY updated_at ASC, id ASC");
    $pulled1 = $stmtPull->fetchAll(PDO::FETCH_ASSOC);
    $lastCursorTime = null;
    $lastCursorId = null;
    foreach ($pulled1 as $row) {
        echo "  -> Terbaca: ID {$row['id']} | Timestamp {$row['updated_at']} | Payload: {$row['payload']}\n";
        $lastCursorTime = $row['updated_at'];
        $lastCursorId = $row['id'];
    }
    echo "  [Device State] Kursor lokal disimpan: updated_at=$lastCursorTime, id=$lastCursorId\n\n";
    
    // Transaksi A akhirnya Commit
    echo "Tx A: Akhirnya Commit data (T1)\n";
    $connA->commit();
    
    // Pull Sync Kedua (Klien Flutter sync selanjutnya)
    echo "\n[Pull Sync 2] Menarik data dengan cursor > ($lastCursorTime, $lastCursorId)...\n";
    $stmtPull2 = $connPull->prepare("
        SELECT * FROM sync_cursors 
        WHERE scope_type='insiden' AND scope_id=1 
        AND (
            updated_at > ? 
            OR (updated_at = ? AND id > ?)
        )
        ORDER BY updated_at ASC, id ASC
    ");
    $stmtPull2->execute([$lastCursorTime, $lastCursorTime, $lastCursorId]);
    $pulled2 = $stmtPull2->fetchAll(PDO::FETCH_ASSOC);
    
    $foundTxA = false;
    if (count($pulled2) === 0) {
        echo "  -> (KOSONG) Tidak ada data baru yang ditarik.\n";
    } else {
        foreach ($pulled2 as $row) {
            echo "  -> Terbaca: ID {$row['id']} | Timestamp {$row['updated_at']} | Payload: {$row['payload']}\n";
            if ($row['payload'] === 'Data dari Tx A') {
                $foundTxA = true;
            }
        }
    }
    
    echo "\n==== HASIL VALIDASI FIX-1 ====\n";
    if ($foundTxA) {
        echo "✅ PASS: Data Tx A berhasil tertangkap oleh Sync 2.\n";
    } else {
        echo "❌ FAILED: Data Tx A hilang (Phantom Read / Cursor Hole terjadi!). Kursor gagal menangkap data karena timestamp Tx A < Tx B.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
