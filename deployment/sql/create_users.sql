-- =============================================
-- NURISK — Database Users Creation Script
-- Jalankan sebagai root di MySQL/MariaDB:
--   mysql -u root < deployment/sql/create_users.sql
-- =============================================

-- User aplikasi — hanya SELECT, INSERT, UPDATE, DELETE
-- BUKAN root, BUKAN GRANT ALL
CREATE USER IF NOT EXISTS 'nurisk_app'@'127.0.0.1'
    IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD_20CHAR_MIN';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE TEMPORARY TABLES
    ON nurisk.* TO 'nurisk_app'@'127.0.0.1';

FLUSH PRIVILEGES;

-- User testing — full akses ke database testing
CREATE USER IF NOT EXISTS 'nurisk_test'@'127.0.0.1'
    IDENTIFIED BY 'CHANGE_ME_DIFFERENT_TEST_PASSWORD';

GRANT ALL ON nurisk_testing.* TO 'nurisk_test'@'127.0.0.1';

FLUSH PRIVILEGES;

-- User backup — readonly (SELECT + LOCK TABLES + SHOW VIEW)
CREATE USER IF NOT EXISTS 'nurisk_backup'@'127.0.0.1'
    IDENTIFIED BY 'CHANGE_ME_BACKUP_PASSWORD';

GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER
    ON nurisk.* TO 'nurisk_backup'@'127.0.0.1';

FLUSH PRIVILEGES;

-- =============================================
-- Verifikasi
-- =============================================
SHOW GRANTS FOR 'nurisk_app'@'127.0.0.1';
SHOW GRANTS FOR 'nurisk_test'@'127.0.0.1';
SHOW GRANTS FOR 'nurisk_backup'@'127.0.0.1';
