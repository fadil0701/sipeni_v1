#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
# shellcheck source=lib/env-proxy.sh
source "$ROOT/deploy/lib/env-proxy.sh"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker belum terpasang. Pasang Docker Engine + Compose plugin terlebih dahulu."
    exit 1
fi

if [ ! -f .env ]; then
    cp .env.production.example .env
    echo "File .env dibuat dari .env.production.example — edit DB_PASSWORD, MYSQL_ROOT_PASSWORD, APP_KEY."
fi

load_proxy_from_env .env

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "Menghasilkan APP_KEY di .env host..."
    KEY="$(docker run --rm $(docker_proxy_env_args) -v "$ROOT":/app -w /app php:8.3-cli php artisan key:generate --show --no-interaction 2>/dev/null | tr -d '\r' | tail -1 || true)"
    if [[ -z "$KEY" || "$KEY" != base64:* ]]; then
        KEY="$(php -r "echo 'base64:'.base64_encode(random_bytes(32));" 2>/dev/null || true)"
    fi
    if [[ -n "$KEY" && "$KEY" == base64:* ]]; then
        if grep -q '^APP_KEY=' .env; then
            sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env
        else
            echo "APP_KEY=${KEY}" >> .env
        fi
    fi
fi

echo "Memeriksa jaringan untuk build Docker..."
if proxy_is_set; then
    echo "Proxy aktif: HTTPS_PROXY=${HTTPS_PROXY:-$HTTP_PROXY}"
else
    echo "PERINGATAN: HTTP_PROXY/HTTPS_PROXY belum diset di .env."
    echo "Jika VM wajib lewat proxy, isi dulu lalu jalankan ulang install.sh"
fi

if ! getent hosts deb.debian.org >/dev/null 2>&1 && ! host deb.debian.org >/dev/null 2>&1; then
    echo "ERROR: VM tidak bisa resolve deb.debian.org."
    echo "Perbaiki DNS VM atau set proxy di .env (HTTP_PROXY / HTTPS_PROXY)."
    exit 1
fi

if command -v curl >/dev/null 2>&1; then
    if ! curl -fsS --connect-timeout 20 -o /dev/null https://deb.debian.org; then
        echo "ERROR: tidak bisa akses https://deb.debian.org."
        if ! proxy_is_set; then
            echo "VM kemungkinan membutuhkan proxy — isi HTTP_PROXY dan HTTPS_PROXY di .env."
        else
            echo "Proxy sudah diset tapi akses masih gagal. Cek URL proxy dan NO_PROXY."
            echo "Pastikan Docker daemon juga dikonfigurasi: deploy/docker-http-proxy.conf.example"
        fi
        exit 1
    fi
fi

mkdir -p storage/logs storage/backups public/build
sudo chown -R "$(id -u)":"$(id -g)" storage 2>/dev/null || chmod -R ug+rwx storage 2>/dev/null || true

echo "Membangun image PHP dan menjalankan stack..."
export DOCKER_BUILDKIT=1
COMPOSE_PARALLEL_LIMIT=1 docker compose build app
chmod +x deploy/build-frontend.sh deploy/post-deploy.sh
./deploy/build-frontend.sh
docker compose up -d

echo "Menunggu php-fpm aktif..."
for i in $(seq 1 90); do
    if docker compose exec -T app sh -c 'pgrep -f "php-fpm: master" >/dev/null 2>&1'; then
        break
    fi
    sleep 2
done

./deploy/post-deploy.sh seed

PORT="$(grep '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"
echo ""
echo "Selesai. Cek status: docker compose ps"
echo "Aplikasi: http://127.0.0.1:${PORT}/demo-simantik/up"
echo "Portal  : https://puspelkes.jakarta.go.id/demo-simantik/"
