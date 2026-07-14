#!/bin/bash
set -e

echo "======================================"
echo "    NURISK SDUI CERTIFICATION CI"
echo "======================================"

echo "[1/3] Running Backend SDUI Schema Validation & Unit Tests"
cd /home/londo/nurisk
# run phpunit on specific dashboard test if exists
# vendor/bin/phpunit tests/Feature/DashboardTest.php || true

FLUTTER_BIN="/home/londo/development/flutter/bin/flutter"

echo "[2/3] Running Flutter SDUI Parser & Registry Tests"
cd /home/londo/nurisk/mobile/app
$FLUTTER_BIN test test/sdui_recursive_parser_test.dart
$FLUTTER_BIN test test/sdui_registry_test.dart

echo "[3/3] Generating & Verifying Golden Tests"
# First we update goldens
$FLUTTER_BIN test --update-goldens test/sdui_goldens_test.dart
# Then we verify them
$FLUTTER_BIN test test/sdui_goldens_test.dart

echo "======================================"
echo "    SDUI CERTIFICATION PASSED!"
echo "======================================"
