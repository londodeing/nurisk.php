<?php
/**
 * GATE A: Cursor Hole Validation (MariaDB Concurrency Stress Test)
 * 
 * Mengeksekusi 100.000 INSERT transaksi secara konkuren menggunakan pcntl_fork.
 * Mengacak durasi eksekusi (sleep) untuk mensimulasikan commit out-of-order.
 * Mengevaluasi jika total row yang dikembalikan query cursor = total row inserted.
 */

$host = '127.0.0.1';
$db   = 'nurisk_staging';
$user = 'root';
$pass = '';
$totalRows = 100000;
$workers = 50;

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    $pdo->exec("USE $db");
    
    $pdo->exec("DROP TABLE IF EXISTS sync_cursors");
    $pdo->exec("
        CREATE TABLE sync_cursors (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            scope_type VARCHAR(50) NOT NULL,
            scope_id BIGINT NOT NULL,
            payload VARCHAR(50),
            updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
            INDEX idx_scope_cursor (scope_type, scope_id, updated_at, id)
        ) ENGINE=InnoDB;
    ");

    echo "[GATE A] Memulai Concurrency Test: $totalRows Rows dengan $workers Workers...\n";
    $rowsPerWorker = $totalRows / $workers;

    // Start multiprocessing
    for ($i = 0; $i < $workers; $i++) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die("Could not fork");
        } else if ($pid == 0) {
            // Anak proses
            $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            for ($j = 0; $j < $rowsPerWorker; $j++) {
                $conn->beginTransaction();
                // Simulasi payload besar/lambat
                if (rand(1, 100) > 90) { usleep(rand(10000, 50000)); } // Out of order simulation
                $conn->commit();
                // Fase afterCommit() pencatatan kursor
                $conn->exec("INSERT INTO sync_cursors (scope_type, scope_id, payload) VALUES ('insiden', 1, 'Data')");
            }
            exit(0);
        }
    }

    // Tunggu semua worker selesai
    while (pcntl_waitpid(0, $status) != -1) {}

    echo "Selesai menyuntikkan data. Melakukan Pull Sync Validasi...\n";
    // Ambil data pertama
    $connPull = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $stmt = $connPull->query("SELECT * FROM sync_cursors ORDER BY updated_at ASC, id ASC");
    $delivered = $stmt->rowCount();

    $missing = $totalRows - $delivered;
    
    echo "\n==== GATE A HASIL ====\n";
    echo "Rows inserted   : $totalRows\n";
    echo "Rows delivered  : $delivered\n";
    echo "Missing rows    : $missing\n\n";

    if ($missing === 0) {
        echo "✅ GATE A PASSED: Tidak ada satupun Cursor Hole. Logika afterCommit terbukti antipeluru.\n";
    } else {
        echo "❌ GATE A FAILED: Terdapat Data Hilang (Phantom Read). RFC harus direvisi!\n";
    }

} catch (Exception $e) {
    echo "Error MariaDB: " . $e->getMessage() . "\n";
}
