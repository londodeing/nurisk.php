#!/bin/bash
# Gate C: HTTP Load Test
# Target: 5000 req/s, Error Rate < 0.1%, P95 < 500ms

export PATH="/opt/lampp/bin:$PATH"

set -euo pipefail

APP_URL="${APP_URL:-http://localhost:8000}"
LOG_FILE="benchmarks/gate_c/gate_c_$(date +%Y%m%d_%H%M%S).log"

mkdir -p benchmarks/gate_c

echo "===== GATE C: HTTP LOAD TEST =====" | tee "$LOG_FILE"
echo "Target: 5000 req/s, Error < 0.1%, P95 < 500ms" | tee -a "$LOG_FILE"
echo "App URL: $APP_URL" | tee -a "$LOG_FILE"
echo "Start: $(date)" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Test 1: Endpoint publik (tanpa auth) — Wilayah Kabupaten
echo "--- Test 1: GET /api/wilayah/kabupaten ---" | tee -a "$LOG_FILE"
ab -n 1000 -c 50 -k "$APP_URL/api/wilayah/kabupaten" 2>&1 | tee -a "$LOG_FILE"

echo "" | tee -a "$LOG_FILE"

# Test 2: Endpoint publik — Kecamatan (dengan query param)
echo "--- Test 2: GET /api/wilayah/kecamatan?id_kab=3318 ---" | tee -a "$LOG_FILE"
ab -n 1000 -c 50 -k "$APP_URL/api/wilayah/kecamatan?id_kab=3318" 2>&1 | tee -a "$LOG_FILE"

echo "" | tee -a "$LOG_FILE"
echo "===== GATE C COMPLETED =====" | tee -a "$LOG_FILE"
echo "Log: $LOG_FILE"

# Interpretasi manual:
echo "" | tee -a "$LOG_FILE"
echo "===== INTERPRETASI MANUAL =====" | tee -a "$LOG_FILE"
echo "Cari di output ab di atas:" | tee -a "$LOG_FILE"
echo "  'Requests per second' harus > 5000" | tee -a "$LOG_FILE"
echo "  'Failed requests' harus < 0.1% dari total requests" | tee -a "$LOG_FILE"
echo "  '95%' latency harus < 500ms" | tee -a "$LOG_FILE"
