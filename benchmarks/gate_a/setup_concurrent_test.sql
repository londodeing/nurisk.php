-- Tabel khusus benchmark Gate A (bukan tabel produksi)
-- Di-drop dan dibuat ulang setiap run agar hasil bersih

USE nurisk_bench;

DROP TABLE IF EXISTS bench_insert_test;

CREATE TABLE bench_insert_test (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    batch_id      INT NOT NULL COMMENT 'ID thread/batch yang melakukan insert',
    seq_in_batch  INT NOT NULL COMMENT 'Nomor urut dalam batch',
    payload       VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_batch (batch_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

-- Verifikasi tabel kosong sebelum test
SELECT COUNT(*) AS rows_before_test FROM bench_insert_test;
