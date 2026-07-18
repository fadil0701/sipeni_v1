#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
ENV_FILE="${1:-.env}"

if [ ! -f "$ENV_FILE" ]; then
    echo "ERROR: $ENV_FILE tidak ditemukan"
    exit 1
fi

LAN_IP="${LAN_IP:-127.0.0.1}"
PORT="$(grep '^APP_PORT=' "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"
LAN_URL="http://${LAN_IP}:${PORT}"

set_var() {
    local key="$1"
    local val="$2"
    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
    else
        echo "${key}=${val}" >> "$ENV_FILE"
    fi
}

set_var ASSET_URL "$LAN_URL"
set_var APP_USE_REQUEST_URL true
set_var APP_ROUTE_PREFIX false
set_var SESSION_PATH /
set_var SESSION_SECURE_COOKIE false

echo "Diperbarui $ENV_FILE untuk LAN:"
grep -E '^(ASSET_URL|APP_USE_REQUEST_URL|SESSION_PATH|SESSION_SECURE_COOKIE)=' "$ENV_FILE"
echo ""
echo "Jalankan: docker compose up -d app queue web"
