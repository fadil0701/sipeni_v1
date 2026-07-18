#!/usr/bin/env bash
# SI-MANTIK — post-deploy (migrate, permission, seed, cache, key)
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
    status="$(docker compose ps app --format '{{.State}}' 2>/dev/null || echo unknown)"
    health="$(docker compose ps app --format '{{.Health}}' 2>/dev/null || echo '')"

    if [ "$status" = "restarting" ]; then
      if [ $((i % 5)) -eq 1 ]; then
        echo "   App restarting — cek log entrypoint (biasanya migrate/DB)..."
        docker compose logs app --tail 15 2>/dev/null || true
      fi
      sleep 3
      continue
    fi

    # Healthcheck Docker (healthy) sudah cukup — pgrep sering gagal di image minimal.
    if [ "$health" = "healthy" ] && [ "$status" = "running" ]; then
      echo "   App siap (healthy)."
      return 0
    fi

    if docker compose exec -T app sh -c 'pgrep -f "php-fpm: master" >/dev/null 2>&1 || pgrep php-fpm >/dev/null 2>&1' 2>/dev/null; then
      echo "   App siap."
      return 0
    fi

    if [ "$i" -eq 90 ]; then
      echo "ERROR: timeout — app tidak stabil."
      docker compose ps
      docker compose logs app --tail 40
      echo ""
      echo "Penyebab umum: DB_PASSWORD / MYSQL_ROOT_PASSWORD kosong di .env"
      echo "  grep -E '^(APP_KEY|DB_PASSWORD|MYSQL_ROOT_PASSWORD)=' .env"
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
  docker compose exec -T app chown -R www-data:www-data bootstrap/cache storage 2>/dev/null || true
}

run_key() {
  if grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "   APP_KEY sudah ada."
    return 0
  fi

  echo "==> Generate APP_KEY (tulis ke .env di host — mount container read-only)"
  KEY="$(dc php artisan key:generate --show --no-interaction 2>/dev/null | tr -d '\r' | tail -1)"
  if [[ -z "$KEY" || "$KEY" != base64:* ]]; then
    KEY="$(php -r "echo 'base64:'.base64_encode(random_bytes(32));" 2>/dev/null || true)"
  fi
  if [[ -z "$KEY" || "$KEY" != base64:* ]]; then
    echo "ERROR: gagal generate APP_KEY"
    exit 1
  fi

  if grep -q '^APP_KEY=' .env; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env
  else
    echo "APP_KEY=${KEY}" >> .env
  fi
  echo "   APP_KEY diset di .env host"

  dc php artisan config:clear --no-interaction
  dc php artisan config:cache --no-interaction
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

PORT="$(grep '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"
echo ""
echo "Selesai. Cek: curl -f http://127.0.0.1:${PORT}/up"
