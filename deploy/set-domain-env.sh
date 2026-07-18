#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
ENV_FILE="${1:-.env}"

if [ ! -f "$ENV_FILE" ]; then
    echo "ERROR: $ENV_FILE tidak ditemukan"
    exit 1
fi

BASE="${DOMAIN_BASE_URL:-https://puspelkes.jakarta.go.id/demo-simantik}"
BASE="${BASE%/}"

set_var() {
    local key="$1"
    local val="$2"
    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
    else
        echo "${key}=${val}" >> "$ENV_FILE"
    fi
}

set_var APP_URL "$BASE"
set_var ASSET_URL "$BASE"
set_var APP_SUBPATH /demo-simantik
set_var APP_USE_REQUEST_URL false
set_var APP_ROUTE_PREFIX false
set_var SESSION_PATH /demo-simantik/
set_var SESSION_SECURE_COOKIE true
set_var TRUSTED_PROXIES '*'

echo "Diperbarui $ENV_FILE untuk domain produksi:"
grep -E '^(APP_URL|ASSET_URL|APP_SUBPATH|APP_USE_REQUEST_URL|SESSION_PATH|SESSION_SECURE_COOKIE|TRUSTED_PROXIES)=' "$ENV_FILE"
echo ""
echo "Restart setelah perubahan:"
echo "  docker compose up -d app queue web"
echo "  docker compose exec app php artisan config:cache"
echo "  curl -fsS \"${BASE}/up\" | head -c 200"
