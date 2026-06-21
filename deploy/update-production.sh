#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
# shellcheck source=lib/env-proxy.sh
source "$ROOT/deploy/lib/env-proxy.sh"

load_proxy_from_env .env

BRANCH="${DEPLOY_BRANCH:-main}"

echo "==> SI-MANTIK — update production"
echo "    Branch: $BRANCH"
echo "    Path:   $ROOT"

if [ -d .git ]; then
    echo "==> git pull"
    if [ -n "$(git status --porcelain deploy/ 2>/dev/null)" ]; then
        echo "    Membuang perubahan lokal di deploy/ (sinkron dengan repo)…"
        git checkout -- deploy/ 2>/dev/null || git restore --source=HEAD --staged --worktree deploy/ 2>/dev/null || true
    fi
    git fetch origin
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
fi

echo "==> Build frontend (Vite)"
chmod +x deploy/build-frontend.sh
./deploy/build-frontend.sh

echo "==> Docker build & up"
export DOCKER_BUILDKIT=1
COMPOSE_PARALLEL_LIMIT=1 docker compose build app
docker compose up -d
docker compose restart app queue

echo "==> Laravel migrate, permission, cache"
chmod +x deploy/post-deploy.sh
./deploy/post-deploy.sh migrate
./deploy/post-deploy.sh sync-routes
./deploy/post-deploy.sh cache

echo "==> Verifikasi path"
chmod +x deploy/verify-path.sh
./deploy/verify-path.sh || true

PORT="$(grep '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"
echo ""
echo "==> Selesai. Verifikasi:"
echo "    docker compose ps"
echo "    curl -fsS http://127.0.0.1:${PORT}/up"
echo ""
echo "Bootstrap admin (sekali, jika belum ada):"
echo "  docker compose exec app php artisan db:seed --class=AdminUserSeeder --force"
