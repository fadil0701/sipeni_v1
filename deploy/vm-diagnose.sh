#!/usr/bin/env bash
# Diagnosa cepat 500 di /up
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PORT="$(grep '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"

echo "==> APP_KEY di .env host"
grep '^APP_KEY=' .env || echo "(tidak ada)"

echo ""
echo "==> Permission storage & bootstrap/cache (host)"
ls -la storage/logs 2>/dev/null | head -5 || echo "storage/logs belum ada"
ls -la bootstrap/cache 2>/dev/null | head -5 || echo "bootstrap/cache belum ada"

echo ""
echo "==> Permission di container (www-data = uid 33)"
docker compose exec -T app sh -c 'id www-data; ls -la storage/logs 2>/dev/null | head -3; ls -la bootstrap/cache 2>/dev/null | head -5'

echo ""
echo "==> Log Laravel (daily)"
docker compose exec -T app sh -c 'ls -la storage/logs/ 2>/dev/null; f=$(ls -t storage/logs/laravel-*.log 2>/dev/null | head -1); [ -n "$f" ] && tail -20 "$f" || echo "belum ada log file"'

echo ""
echo "==> Test artisan sebagai www-data (sama seperti php-fpm)"
docker compose exec -u www-data -T app php artisan about 2>&1 | head -15 || echo "GAGAL sebagai www-data"

echo ""
echo "==> HTTP internal"
curl -s -o /dev/null -w ":${PORT}/up => HTTP %{http_code}\n" "http://127.0.0.1:${PORT}/up" || true
curl -s -o /dev/null -w ":${PORT}/demo-simantik/up => HTTP %{http_code}\n" "http://127.0.0.1:${PORT}/demo-simantik/up" || true
curl -s -o /dev/null -w ":8081/demo-simantik/up => HTTP %{http_code}\n" "http://127.0.0.1:8081/demo-simantik/up" || true

echo ""
echo "==> Nginx include simantik"
sudo grep -r "include.*simantik" /etc/nginx/sites-enabled/ /etc/nginx/sites-available/ 2>/dev/null || echo "BELUM ada include di vhost"
