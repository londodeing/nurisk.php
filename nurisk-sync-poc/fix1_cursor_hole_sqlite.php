<?php

$dbFile = __DIR__ . '/nurisk_poc.sqlite';
if (file_exists($dbFile)) {
    unlink($dbFile);
}

try {
    $pdo = new PDO("sqlite:" . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Setup SQLite Table
    $pdo->exec("
        CREATE TABLE sync_cursors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scope_type TEXT NOT NULL,
            scope_id INTEGER NOT NULL,
            payload TEXT,
            updated_at TEXT
        );
    ");
    
    echo "==== TEST 1: RACE CONDITION & CURSOR HOLE (FIX-1) ====\n";
    echo "[SKENARIO MYSQL CONCURRENCY]\n";
    echo "Dalam MySQL, 'ON UPDATE CURRENT_TIMESTAMP' dibuat saat kueri dieksekusi, BUKAN saat COMMIT.\n\n";
    
    $t1 = "2026-06-17 10:00:00.000000";
    $t2 = "2026-06-17 10:00:00.050000"; // 50ms later
    
    echo "1. [Waktu: T1] Transaksi A (Payload Besar) memulai INSERT.\n";
    echo "   -> Nilai updated_at MySQL = $t1, id = 1\n";
    echo "   -> (Transaksi A belum COMMIT, tabel masih dilock untuk row ini)\n\n";
    
    echo "2. [Waktu: T2] Transaksi B (Payload Kecil) memulai INSERT.\n";
    echo "   -> Nilai updated_at MySQL = $t2, id = 2\n";
    
    echo "3. Transaksi B COMMIT lebih dulu (karena payload kecil).\n";
    // Insert B ke dalam DB
    $pdo->exec("INSERT INTO sync_cursors (id, scope_type, scope_id, payload, updated_at) VALUES (2, 'insiden', 1, 'Data dari Tx B', '$t2')");
    
    echo "\n[Pull Sync 1] Klien Flutter melakukan sinkronisasi...\n";
    $stmtPull = $pdo->query("SELECT * FROM sync_cursors WHERE scope_type='insiden' AND scope_id=1 ORDER BY updated_at ASC, id ASC");
    $pulled1 = $stmtPull->fetchAll(PDO::FETCH_ASSOC);
    $lastCursorTime = null;
    $lastCursorId = null;
    foreach ($pulled1 as $row) {
        echo "  -> Terbaca: ID {$row['id']} | Timestamp {$row['updated_at']} | Payload: {$row['payload']}\n";
        $lastCursorTime = $row['updated_at'];
        $lastCursorId = $row['id'];
    }
    echo "  [Device State] Kursor lokal disimpan: updated_at=$lastCursorTime, id=$lastCursorId\n\n";
    
    echo "4. Transaksi A akhirnya COMMIT (setelah proses lama).\n";
    // Insert A ke dalam DB dengan stempel waktu lama
    $pdo->exec("INSERT INTO sync_cursors (id, scope_type, scope_id, payload, updated_at) VALUES (1, 'insiden', 1, 'Data dari Tx A', '$t1')");
    
    echo "\n[Pull Sync 2] Klien Flutter kembali sinkronisasi dengan kursor lokalnya...\n";
    echo "   Query: SELECT * WHERE updated_at > '$lastCursorTime' OR (updated_at = '$lastCursorTime' AND id > $lastCursorId)\n";
    
    $stmtPull2 = $pdo->prepare("
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
        echo "  -> (KOSONG) Tidak ada data yang ditarik!\n";
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
        echo "✅ PASS: Data Tx A berhasil tertangkap.\n";
    } else {
        echo "❌ FAILED: Data Tx A hilang secara permanen (Phantom Read / Cursor Hole terjadi!).\n";
        echo "   Penyebab: Timestamp Tx A ($t1) JAUH LEBIH KECIL daripada kursor lokal Flutter ($t2).\n";
        echo "   Kueri 'updated_at > T2' melewatkan T1 selamanya.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
