#!/usr/bin/env bash
# SI-MANTIK — snippet post-deploy VM (migrate, permission, seed, cache)
# Jalankan dari root proyek: ./deploy/vm-post-deploy.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  echo "ERROR: .env tidak ditemukan. cp .env.production.example .env"
  exit 1
fi

ACTION="${1:-all}"

dc() {
  docker compose exec -T app "$@"
}

wait_for_app() {
  echo "==> Tunggu container app (php-fpm)..."
  for i in $(seq 1 90); do
    if docker compose exec -T app sh -c 'pgrep -f "php-fpm: master" >/dev/null 2>&1'; then
      echo "   App siap."
      return 0
    fi
    if [ "$i" -eq 90 ]; then
      echo "ERROR: timeout — cek: docker compose logs app --tail 80"
      exit 1
    fi
    sleep 2
  done
}

run_migrate() {
  echo "==> php artisan migrate --force"
  dc php artisan migrate --force --no-interaction
}

run_sync_routes() {
  echo "==> php artisan permission:sync-routes --force"
  dc php artisan permission:sync-routes --force --no-interaction
}

run_seed() {
  echo "==> php artisan db:seed --force"
  dc php artisan db:seed --force --no-interaction
}

run_cache() {
  echo "==> config:cache + view:clear"
  dc php artisan config:cache --no-interaction
  dc php artisan view:clear --no-interaction
}

run_key() {
  if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "==> php artisan key:generate --force"
    dc php artisan key:generate --force --no-interaction
    dc php artisan config:cache --no-interaction
  fi
}

case "$ACTION" in
  all)
    wait_for_app
    run_migrate
    run_sync_routes
    run_seed
    run_cache
    ;;
  migrate)
    wait_for_app
    run_migrate
    ;;
  sync-routes|sync)
    wait_for_app
    run_sync_routes
    ;;
  seed)
    wait_for_app
    run_migrate
    run_sync_routes
    run_seed
    ;;
  cache)
    run_cache
    ;;
  key)
    run_key
    ;;
  wait)
    wait_for_app
    ;;
  *)
    echo "Usage: $0 [all|migrate|sync-routes|seed|cache|key|wait]"
    exit 1
    ;;
esac

echo ""
echo "Selesai. Cek: curl -f http://127.0.0.1:\${APP_PORT:-7001}/demo-simantik/up"
