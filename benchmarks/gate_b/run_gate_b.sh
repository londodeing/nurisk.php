#!/bin/bash
# Gate B: Query Performance Benchmark
# Target: P95 < 100ms, no filesort, no full scan

export PATH="/opt/lampp/bin:$PATH"

set -euo pipefail

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="nurisk"
LOG_FILE="benchmarks/gate_b/gate_b_$(date +%Y%m%d_%H%M%S).log"
ITERATIONS=50  # Jalankan setiap query 50x, ambil P95

mkdir -p benchmarks/gate_b

echo "===== GATE B: QUERY PERFORMANCE TEST =====" | tee "$LOG_FILE"
echo "Iterations per query: $ITERATIONS" | tee -a "$LOG_FILE"
echo "Start: $(date)" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

run_query_benchmark() {
    local query_file=$1
    local query_name=$2
    local times=()

    echo "--- Testing: $query_name ---" | tee -a "$LOG_FILE"

    # Warmup: jalankan sekali untuk mengisi query cache
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" < "$query_file" > /dev/null 2>&1

    # Benchmark: jalankan N iterasi
    for i in $(seq 1 "$ITERATIONS"); do
        start=$(date +%s%N)
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" < "$query_file" > /dev/null 2>&1
        end=$(date +%s%N)
        ms=$(( (end - start) / 1000000 ))
        times+=("$ms")
    done

    # Hitung P50, P95, P99, Max
    sorted_times=($(printf '%s\n' "${times[@]}" | sort -n))
    p50_idx=$(( ITERATIONS * 50 / 100 ))
    p95_idx=$(( ITERATIONS * 95 / 100 ))
    p99_idx=$(( ITERATIONS * 99 / 100 ))

    p50=${sorted_times[$p50_idx]}
    p95=${sorted_times[$p95_idx]}
    p99=${sorted_times[$p99_idx]}
    max=${sorted_times[-1]}

    echo "  P50: ${p50}ms | P95: ${p95}ms | P99: ${p99}ms | Max: ${max}ms" | tee -a "$LOG_FILE"

    if [ "$p95" -lt 100 ]; then
        echo "  STATUS: ✅ PASS (P95 = ${p95}ms < 100ms)" | tee -a "$LOG_FILE"
    else
        echo "  STATUS: ❌ FAIL (P95 = ${p95}ms ≥ 100ms)" | tee -a "$LOG_FILE"
    fi
    echo "" | tee -a "$LOG_FILE"
}

# Jalankan semua query
run_query_benchmark "benchmarks/gate_b/queries/q1_insiden_by_pcnu.sql"     "Q1: Insiden by PCNU (index scan)"
run_query_benchmark "benchmarks/gate_b/queries/q2_relawan_aktif.sql"        "Q2: Relawan aktif per insiden"
run_query_benchmark "benchmarks/gate_b/queries/q3_riwayat_status.sql"       "Q3: Riwayat status insiden"
run_query_benchmark "benchmarks/gate_b/queries/q4_jabatan_user.sql"         "Q4: Jabatan aktif user (scope load)"
run_query_benchmark "benchmarks/gate_b/queries/q5_dashboard_aggregate.sql"  "Q5: Dashboard aggregate PWNU"

echo "===== GATE B COMPLETED =====" | tee -a "$LOG_FILE"
echo "Log: $LOG_FILE"
