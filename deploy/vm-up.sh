#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  echo "Buat .env dulu: cp .env.production.example .env"
  exit 1
fi

export HTTP_PROXY="${HTTP_PROXY:-http://10.15.3.20:80}"
export HTTPS_PROXY="${HTTPS_PROXY:-http://10.15.3.20:80}"
export NO_PROXY="${NO_PROXY:-localhost,127.0.0.1,mysql,simantik-mysql,.local}"

echo "==> Build image (proxy: $HTTP_PROXY)"
docker compose build

echo "==> Start containers (mysql + app + web + queue)"
docker compose up -d

echo "==> Tunggu healthcheck..."
sleep 8

echo "==> Post-deploy"
docker compose exec -T app php artisan db:seed --force 2>/dev/null || true

echo ""
echo "Selesai."
echo "  Internal : http://127.0.0.1:${APP_PORT:-7001}/demo-simantik/up"
echo "  Publik   : https://puspelkes.jakarta.go.id/demo-simantik"
echo "  Proxy VM : deploy/nginx-puspelkes-demo-simantik.conf"
