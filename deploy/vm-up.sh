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

echo "==> Tunggu entrypoint app selesai (migrate + cache)..."
for i in $(seq 1 90); do
  if docker compose exec -T app sh -c 'pgrep -f "php-fpm: master" >/dev/null 2>&1'; then
    echo "   App siap (php-fpm aktif)."
    break
  fi
  if [ "$i" -eq 90 ]; then
    echo "ERROR: timeout menunggu app — cek log:"
    docker compose logs app --tail 80
    exit 1
  fi
  sleep 2
done

echo "==> Post-deploy (migrate + sync permission + seed)"
chmod +x deploy/vm-post-deploy.sh
./deploy/vm-post-deploy.sh all

echo ""
echo "Selesai."
echo "  Internal : http://127.0.0.1:${APP_PORT:-7001}/demo-simantik/up"
echo "  Publik   : https://puspelkes.jakarta.go.id/demo-simantik"
echo "  Proxy VM : deploy/nginx-puspelkes-demo-simantik.conf"
