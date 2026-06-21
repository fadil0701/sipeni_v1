#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
ENV_FILE="${1:-.env}"

if [ ! -f "$ENV_FILE" ]; then
    echo "ERROR: $ENV_FILE tidak ditemukan"
    exit 1
fi

BASE="${PORTAL_BASE_URL:-http://127.0.0.1:8081/demo-simantik}"
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
set_var SESSION_SECURE_COOKIE false
set_var SESSION_DOMAIN ""

echo "Diperbarui $ENV_FILE untuk portal /demo-simantik/:"
grep -E '^(APP_URL|ASSET_URL|APP_USE_REQUEST_URL|SESSION_PATH|SESSION_SECURE_COOKIE)=' "$ENV_FILE"
echo ""
echo "Pastikan nginx snippet sudah dipasang, lalu:"
echo "  docker compose up -d app queue web"
echo "  curl -I http://127.0.0.1:8081/demo-simantik/"
