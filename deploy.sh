#!/bin/bash
# ============================================================
# NURISK — Deploy from local to VPS via SSH (Git-based)
# Usage:
#   ./deploy.sh                          # deploy branch saat ini
#   ./deploy.sh main                     # deploy branch tertentu
#   ./deploy.sh --setup                  # setup SSH + VPS pertama kali
#   ./deploy.sh --rollback               # rollback ke release sebelumnya
#   ./deploy.sh --rollback 2             # rollback 2 release sebelumnya
# ============================================================

set -euo pipefail

# === KONFIGURASI — sesuaikan sekali saja ===
VPS_SSH="${VPS_SSH:-ssh root@103.193.178.162}"
VPS_DEPLOY_SCRIPT="/var/www/nurisk/deployment/scripts/deploy.sh"
VPS_ROLLBACK_SCRIPT="/var/www/nurisk/deployment/scripts/rollback.sh"
GIT_REMOTE="origin"
# ============================================

BRANCH="${1:-}"
MODE="deploy"

if [ "$BRANCH" = "--setup" ]; then
    echo "=== SETUP VPS ==="
    echo "Menyalin deployment scripts ke VPS..."
    rsync -az --delete deployment/ "$VPS_SSH:/var/www/nurisk/deployment/"
    echo "Setup selesai."
    echo ""
    echo "Akses VPS: $VPS_SSH"
    exit 0
fi

if [ "$BRANCH" = "--rollback" ]; then
    MODE="rollback"
    STEPS="${2:-1}"
fi

# --- Cek working tree ---
if [ "$MODE" = "deploy" ]; then
    if [ -z "$BRANCH" ]; then
        BRANCH=$(git rev-parse --abbrev-ref HEAD)
    fi

    if ! git diff --quiet --exit-code; then
        echo "ERROR: Ada perubahan yang belum di-commit."
        echo "  Commit dulu atau stash: git stash"
        exit 1
    fi

    if [ -n "$(git stash list)" ]; then
        echo "WARNING: Ada stash entries. Pastikan tidak mengganggu."
    fi

    # --- Push ke GitHub ---
    echo "=== PUSH KE GITHUB ($BRANCH) ==="
    git push "$GIT_REMOTE" "$BRANCH"
    echo ""

    # --- SSH ke VPS dan jalankan deploy ---
    echo "=== DEPLOY KE VPS ==="
    echo "  Target: $VPS_SSH"
    echo "  Branch: $BRANCH"
    echo ""
    ssh -t "$VPS_SSH" "sudo -u www-data bash $VPS_DEPLOY_SCRIPT $BRANCH"
else
    echo "=== ROLLBACK DI VPS ==="
    echo "  Target: $VPS_SSH"
    echo "  Steps:  $STEPS"
    echo ""
    ssh -t "$VPS_SSH" "sudo -u www-data bash $VPS_ROLLBACK_SCRIPT $STEPS"
fi
