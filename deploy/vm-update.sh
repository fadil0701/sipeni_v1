#!/usr/bin/env bash
# SI-MANTIK — snippet update rutin VM setelah git push
# Jalankan: ./deploy/vm-update.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

export HTTP_PROXY="${HTTP_PROXY:-http://10.15.3.20:80}"
export HTTPS_PROXY="${HTTPS_PROXY:-http://10.15.3.20:80}"
export NO_PROXY="${NO_PROXY:-localhost,127.0.0.1,mysql,simantik-mysql,.local}"

BRANCH="${DEPLOY_BRANCH:-main}"

echo "==> SI-MANTIK update production"
echo "    Path:   $ROOT"
echo "    Branch: $BRANCH"

if [ -d .git ]; then
  echo "==> git pull"
  git fetch origin
  git checkout "$BRANCH"
  git pull origin "$BRANCH"
else
  echo "WARN: bukan git repo — lewati pull"
fi

if [ -f package.json ] && command -v npm >/dev/null 2>&1; then
  echo "==> npm run build (jika ada perubahan frontend)"
  npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
  npm run build
fi

echo "==> docker compose up -d"
docker compose up -d

chmod +x deploy/vm-post-deploy.sh
./deploy/vm-post-deploy.sh migrate
./deploy/vm-post-deploy.sh sync-routes
./deploy/vm-post-deploy.sh cache

echo "==> restart queue"
docker compose restart queue

echo ""
echo "Selesai."
echo "  docker compose ps"
echo "  curl -f http://127.0.0.1:\${APP_PORT:-7001}/demo-simantik/up"
