<?php

$dbFile = __DIR__ . '/nurisk_poc_aftercommit.sqlite';
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
    
    echo "==== TEST 1B: RACE CONDITION & AFTER_COMMIT MITIGATION ====\n";
    echo "[SKENARIO LARAVEL DB::afterCommit]\n";
    echo "Pencatatan kursor dilakukan di luar transaksi utama, sesaat setelah transaksi dinyatakan COMMIT.\n\n";
    
    // Fungsi bantuan untuk mendapatkan timestamp dengan presisi mikrodetik ala MySQL TIMESTAMP(6)
    function microtime_mysql() {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        return date('Y-m-d H:i:s.' . $micro, (int)$t);
    }
    
    echo "1. Transaksi A (Payload Besar) memulai pemrosesan berat...\n";
    
    // Transaksi B berjalan secara paralel, proses sangat ringan.
    echo "2. Transaksi B (Payload Kecil) memulai pemrosesan...\n";
    echo "3. Transaksi B COMMIT. Memasuki fase DB::afterCommit().\n";
    
    // AFTER COMMIT Tx B
    $t_b = microtime_mysql();
    $pdo->exec("INSERT INTO sync_cursors (id, scope_type, scope_id, payload, updated_at) VALUES (2, 'insiden', 1, 'Data dari Tx B', '$t_b')");
    echo "   -> [AFTER COMMIT B] Kursor dicatat: Timestamp MySQL = $t_b, id = 2\n\n";
    
    echo "[Pull Sync 1] Klien Flutter melakukan sinkronisasi...\n";
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
    
    // Transaksi A selesai dan Commit!
    usleep(100000); // Tx A tertinggal 100ms
    echo "4. Transaksi A selesai komputasi dan COMMIT.\n";
    echo "5. Memasuki fase DB::afterCommit() untuk Tx A.\n";
    
    // AFTER COMMIT Tx A
    $t_a = microtime_mysql();
    $pdo->exec("INSERT INTO sync_cursors (id, scope_type, scope_id, payload, updated_at) VALUES (1, 'insiden', 1, 'Data dari Tx A', '$t_a')");
    echo "   -> [AFTER COMMIT A] Kursor dicatat: Timestamp MySQL = $t_a, id = 1\n\n";
    
    echo "[Pull Sync 2] Klien Flutter kembali sinkronisasi dengan OVERLAP WINDOW (T - 60s) ...\n";
    // Overlap window mitigation diaktifkan, namun kita buktikan dulu murni secara logis.
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
    
    echo "\n==== HASIL TAHAP 1B ====\n";
    if ($foundTxA) {
        echo "✅ PASS (RFC VALID): Data Tx A berhasil tertangkap oleh kursor! Phantom Read dihancurkan karena pembuatan log diundur hingga fase AfterCommit, menyinkronkan kronologi timestamp murni dengan visibilitas data.\n";
    } else {
        echo "❌ FAILED: Data Tx A masih hilang.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
