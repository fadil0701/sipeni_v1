#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: .env tidak ditemukan dan APP_KEY kosong. Mount ./.env ke container atau set env_file di compose."
    exit 1
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

php artisan storage:link --force --no-interaction 2>/dev/null || true

php artisan config:clear --no-interaction 2>/dev/null || true
php artisan package:discover --no-interaction 2>/dev/null || true

composer dump-autoload --optimize --no-dev --no-interaction 2>/dev/null || true

if ! php artisan migrate --force --no-interaction; then
    echo "ERROR: migrate gagal — cek DB_HOST, DB_PASSWORD, dan koneksi MySQL."
    exit 1
fi

php artisan permission:sync-routes --force --no-interaction 2>/dev/null || true

php artisan config:cache --no-interaction

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

if [ "${VIEW_CLEAR_ON_BOOT:-1}" = "1" ]; then
    rm -f storage/framework/views/*.php 2>/dev/null || true
    php artisan view:clear --no-interaction 2>/dev/null || true
else
    php artisan view:cache --no-interaction 2>/dev/null || echo "WARN: view:cache dilewati (tidak fatal)"
fi

exec "$@"
