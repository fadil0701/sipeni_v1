# Deploy VM — Puspelkes DKI (`/demo-simantik`)
# Arsitektur sama dashboard-skrining: mysql + app (php-fpm) + web (nginx) + queue

## Ringkasan

| Item | Nilai |
|------|--------|
| URL publik | `https://puspelkes.jakarta.go.id/demo-simantik` |
| Port Docker (`web`) | `7001` |
| Subpath | `APP_SUBPATH=/demo-simantik` |
| Proxy korporat | `10.15.3.20:80` |

## Services

| Service | Peran |
|---------|--------|
| `mysql` | Database MySQL 8 |
| `app` | PHP-FPM + migrate/cache saat boot |
| `web` | Nginx (strip `/demo-simantik`, expose port) |
| `queue` | `php artisan queue:work` |

## Snippet deploy VM

| Script | Kegunaan |
|--------|----------|
| `deploy/vm-first-install.sh` | Instalasi pertama (clone + `.env` + build + seed) |
| `deploy/vm-up.sh` | Build image + `docker compose up` + post-deploy |
| `deploy/vm-post-deploy.sh` | Migrate, sync permission, seed, cache |
| `deploy/vm-update.sh` | `git pull` + migrate + cache (update rutin) |

```bash
chmod +x deploy/*.sh

# Instalasi pertama
./deploy/vm-first-install.sh

# Perbaikan seed gagal (guard_name)
./deploy/vm-post-deploy.sh seed

# Update setelah git push
./deploy/vm-update.sh
```

Perintah `vm-post-deploy.sh`:

```bash
./deploy/vm-post-deploy.sh all          # migrate + sync + seed + cache
./deploy/vm-post-deploy.sh migrate
./deploy/vm-post-deploy.sh sync-routes
./deploy/vm-post-deploy.sh seed
./deploy/vm-post-deploy.sh cache
./deploy/vm-post-deploy.sh key          # generate APP_KEY jika kosong
```

Git di VM (sekali, jika `dubious ownership`):

```bash
git config --global --add safe.directory /var/www/html/simantik
```

## 1. Siapkan `.env`

```bash
cp .env.production.example .env
# Isi: APP_KEY, DB_PASSWORD, MYSQL_ROOT_PASSWORD, akun admin
php artisan key:generate   # di host jika ada PHP, atau setelah container up
```

## 2. Build & jalankan

```bash
chmod +x deploy/vm-up.sh
./deploy/vm-up.sh
```

Atau manual:

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan inventory:regenerate-qr-codes
```

## 3. Reverse proxy VM (di depan Docker)

Nginx container **sudah** mem-strip `/demo-simantik`. Proxy VM meneruskan path lengkap:

```nginx
location /demo-simantik/ {
    proxy_pass http://127.0.0.1:7001/demo-simantik/;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

File siap pakai: `deploy/nginx-puspelkes-demo-simantik.conf`

## 4. Health check

```bash
curl -f http://127.0.0.1:7001/demo-simantik/up
curl -f https://puspelkes.jakarta.go.id/demo-simantik/up
```

## 5. Update kode tanpa rebuild penuh

Bind-mount (seperti dashboard-skrining): `app/`, `config/`, `routes/`, `resources/` dari host.
Setelah `git pull`:

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan view:clear
docker compose restart queue
```

Rebuild asset frontend:

```bash
npm run build
docker compose restart web app queue
```

## 6. Seed / migrate gagal setelah deploy pertama

Jika muncul `Unknown column 'guard_name'` saat seed, migrasi belum selesai saat seed dijalankan:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan permission:sync-routes --force
docker compose exec app php artisan db:seed --force
```

## 7. Troubleshooting

| Gejala | Solusi |
|--------|--------|
| CSS/JS tidak load | `ASSET_URL` + `APP_SUBPATH`, jalankan `npm run build` |
| 404 semua route | Cek proxy VM meneruskan `/demo-simantik/` ke port 7001 |
| Migrate gagal | Cek `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, service `mysql` healthy |
| Seed gagal `guard_name` | Race: seed jalan sebelum migrate selesai — jalankan ulang `migrate` + `db:seed` (lihat §7) |
| Session hilang | `SESSION_PATH=/demo-simantik/` |
