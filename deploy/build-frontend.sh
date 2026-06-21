#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
# shellcheck source=lib/env-proxy.sh
source "$ROOT/deploy/lib/env-proxy.sh"

load_proxy_from_env .env

APP_SUBPATH="${APP_SUBPATH:-/demo-simantik}"
if [ -f .env ] && grep -q '^APP_SUBPATH=' .env; then
    APP_SUBPATH="$(grep '^APP_SUBPATH=' .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'")"
fi

echo "Build frontend (APP_SUBPATH=${APP_SUBPATH})..."
mkdir -p public/build

# shellcheck disable=SC2046
docker run --rm \
    $(docker_proxy_env_args) \
    -e "APP_SUBPATH=${APP_SUBPATH}" \
    -v "$ROOT":/app \
    -w /app \
    node:22-bookworm-slim \
    bash -c '
        set -e
        npm ci --no-audit --no-fund
        npm run build
        echo ""
        echo "Selesai. manifest:"
        ls -la public/build/manifest.json
    '

echo ""
echo "Restart container agar mount aset aktif:"
echo "  docker compose up -d app web queue"
