#!/bin/bash
# Gate A: Concurrent Insert Stress Test
# Target: 100.000 rows, 0 missing, 0 deadlock

export PATH="/opt/lampp/bin:$PATH"

set -euo pipefail

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="nurisk_bench"
TOTAL_ROWS=100000
THREADS=20
ROWS_PER_THREAD=$((TOTAL_ROWS / THREADS))
LOG_FILE="benchmarks/gate_a/gate_a_$(date +%Y%m%d_%H%M%S).log"

mkdir -p benchmarks/gate_a

echo "===== GATE A: CONCURRENT INSERT TEST =====" | tee "$LOG_FILE"
echo "Target: $TOTAL_ROWS rows | Threads: $THREADS | Rows/thread: $ROWS_PER_THREAD" | tee -a "$LOG_FILE"
echo "Start: $(date)" | tee -a "$LOG_FILE"

# 1. Setup tabel bersih
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" \
    < benchmarks/gate_a/setup_concurrent_test.sql 2>&1 | tee -a "$LOG_FILE"

# 2. Fungsi insert per thread
run_thread() {
    local thread_id=$1
    local rows=$2
    local mysql_cmd="mysql -h $DB_HOST -P $DB_PORT -u $DB_USER $DB_NAME"

    # Buat SQL INSERT bulk untuk efisiensi
    local sql="INSERT INTO bench_insert_test (batch_id, seq_in_batch, payload) VALUES "
    local values=()
    for i in $(seq 1 "$rows"); do
        values+=("($thread_id, $i, CONCAT('thread_${thread_id}_row_', $i))")
    done
    # Gabungkan dengan koma
    local values_str=$(IFS=,; echo "${values[*]}")
    echo "${sql}${values_str};" | $mysql_cmd
}

export -f run_thread
export DB_HOST DB_PORT DB_USER DB_PASS DB_NAME

# 3. Jalankan semua thread secara paralel
START_TIME=$(date +%s%N)

for thread in $(seq 1 "$THREADS"); do
    run_thread "$thread" "$ROWS_PER_THREAD" &
done

# Tunggu semua thread selesai
wait

END_TIME=$(date +%s%N)
DURATION_MS=$(( (END_TIME - START_TIME) / 1000000 ))

# 4. Verifikasi hasil
ACTUAL_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" \
    -se "SELECT COUNT(*) FROM bench_insert_test;")

echo "" | tee -a "$LOG_FILE"
echo "===== GATE A RESULTS =====" | tee -a "$LOG_FILE"
echo "Duration      : ${DURATION_MS}ms" | tee -a "$LOG_FILE"
echo "Expected rows : $TOTAL_ROWS" | tee -a "$LOG_FILE"
echo "Actual rows   : $ACTUAL_COUNT" | tee -a "$LOG_FILE"

if [ "$ACTUAL_COUNT" -eq "$TOTAL_ROWS" ]; then
    echo "STATUS        : ✅ PASS — Semua $TOTAL_ROWS row berhasil diinsert tanpa data hilang" | tee -a "$LOG_FILE"
else
    MISSING=$((TOTAL_ROWS - ACTUAL_COUNT))
    echo "STATUS        : ❌ FAIL — $MISSING row hilang!" | tee -a "$LOG_FILE"
    exit 1
fi

echo "End: $(date)" | tee -a "$LOG_FILE"
echo "Log: $LOG_FILE"
