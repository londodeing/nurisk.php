#!/bin/bash
export PATH="/opt/lampp/bin:$PATH"

mkdir -p benchmarks/gate_c

echo "=== WARMUP ==="
ab -n 100 -c 10 -k http://localhost:8080/api/wilayah/kabupaten > /dev/null 2>&1

echo "=== GATE C TEST 1: GET /api/wilayah/kabupaten ===" | tee benchmarks/gate_c/gate_c_retest.log
ab -n 5000 -c 50 -k \
   -H "Accept: application/json" \
   http://localhost:8080/api/wilayah/kabupaten \
   2>&1 | tee -a benchmarks/gate_c/gate_c_retest.log

echo "" | tee -a benchmarks/gate_c/gate_c_retest.log

echo "=== GATE C TEST 2: Concurrency 100 ===" | tee -a benchmarks/gate_c/gate_c_retest.log
ab -n 5000 -c 100 -k \
   -H "Accept: application/json" \
   http://localhost:8080/api/wilayah/kabupaten \
   2>&1 | tee -a benchmarks/gate_c/gate_c_retest.log

echo "" | tee -a benchmarks/gate_c/gate_c_retest.log

echo "=== GATE C TEST 3: GET /api/wilayah/kecamatan?id_kab=3318 ===" | tee -a benchmarks/gate_c/gate_c_retest.log
ab -n 3000 -c 50 -k \
   -H "Accept: application/json" \
   "http://localhost:8080/api/wilayah/kecamatan?id_kab=3318" \
   2>&1 | tee -a benchmarks/gate_c/gate_c_retest.log
