#!/usr/bin/env bash
# SI-MANTIK — snippet instalasi pertama di VM (/var/www/html/simantik)
# Jalankan sekali setelah clone repo.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

export HTTP_PROXY="${HTTP_PROXY:-http://10.15.3.20:80}"
export HTTPS_PROXY="${HTTPS_PROXY:-http://10.15.3.20:80}"
export NO_PROXY="${NO_PROXY:-localhost,127.0.0.1,mysql,simantik-mysql,.local}"

echo "==> SI-MANTIK — instalasi pertama"

if [[ ! -f .env ]]; then
  echo "==> Buat .env dari template"
  cp .env.production.example .env
  echo "    EDIT .env: APP_KEY, DB_PASSWORD, MYSQL_ROOT_PASSWORD"
  echo "    Lalu jalankan ulang script ini."
  exit 1
fi

chmod +x deploy/vm-up.sh deploy/vm-post-deploy.sh

./deploy/vm-up.sh

./deploy/vm-post-deploy.sh key

echo ""
echo "Instalasi selesai."
echo "  Proxy VM: deploy/nginx-puspelkes-demo-simantik.conf"
echo "  URL     : https://puspelkes.jakarta.go.id/demo-simantik"
