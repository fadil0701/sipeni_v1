# Deploy VM — SI-MANTIK (`/demo-simantik`)

Arsitektur mengikuti **dashboard-skrining**: `mysql` + `app` (PHP-FPM) + `web` (Nginx) + `queue`.

## Ringkasan

| Item | Nilai |
|------|--------|
| URL publik | `https://puspelkes.jakarta.go.id/demo-simantik` |
| Port Docker (`web`) | `7001` (`APP_PORT`) |
| Subpath | `APP_SUBPATH=/demo-simantik` |
| Proxy korporat | isi `HTTP_PROXY` / `HTTPS_PROXY` di `.env` |

## Instalasi pertama (VM)

```bash
cd /var/www/html/simantik
cp .env.production.example .env
# edit: DB_PASSWORD, MYSQL_ROOT_PASSWORD, proxy, akun admin

chmod +x deploy/*.sh
./deploy/set-domain-env.sh    # atau set-portal-env.sh / set-lan-env.sh
./deploy/install.sh
./deploy/install-nginx-snippet.sh
```

Tambahkan di server block nginx host (port 8081):

```nginx
include /etc/nginx/snippets/simantik.conf;
```

```bash
sudo nginx -t && sudo systemctl reload nginx
./deploy/verify-path.sh
```

## Update rutin

```bash
git pull origin main
./deploy/update-production.sh
```

## Script deploy

| Script | Fungsi |
|--------|--------|
| `deploy/install.sh` | Instalasi pertama: build, frontend, up, seed |
| `deploy/update-production.sh` | Git pull + build frontend + migrate/cache |
| `deploy/build-frontend.sh` | `npm run build` via container Node |
| `deploy/post-deploy.sh` | `migrate` / `sync-routes` / `seed` / `cache` / `key` |
| `deploy/set-domain-env.sh` | Env HTTPS domain produksi |
| `deploy/set-portal-env.sh` | Env portal HTTP `:8081/demo-simantik` |
| `deploy/set-lan-env.sh` | Env akses LAN `:7001` langsung |
| `deploy/verify-path.sh` | Cek `:7001/up` dan `/demo-simantik/up` |
| `deploy/install-nginx-snippet.sh` | Pasang `/etc/nginx/snippets/simantik.conf` |
| `deploy/vm-diagnose.sh` | Debug error 500 / permission |

## Subpath (pola dashboard-skrining)

1. **Host nginx** — `proxy_pass http://127.0.0.1:7001/` (strip prefix di host)
2. **Container nginx** — `rewrite ^/demo-simantik/(.*)$ /$1` (untuk akses langsung `:7001/demo-simantik/`)
3. **Laravel** — route tanpa prefix (`APP_ROUTE_PREFIX=false`)
4. **Vite** — asset di `/demo-simantik/build/` saat build (`APP_SUBPATH`)

## Post-deploy manual

```bash
./deploy/post-deploy.sh seed
docker compose exec app php artisan db:seed --class=AdminUserSeeder --force
```

## Troubleshooting

| Gejala | Solusi |
|--------|--------|
| Build gagal tanpa internet | Isi `HTTP_PROXY`/`HTTPS_PROXY` di `.env`; konfigurasi daemon: `deploy/docker-http-proxy.conf.example` |
| `key:generate` read-only | `./deploy/post-deploy.sh key` (tulis ke `.env` host) |
| HTTP 500, artisan OK | `sudo chown -R 33:33 storage bootstrap/cache`; `./deploy/post-deploy.sh cache` |
| 404 di `:8081` | Tambahkan `include` snippet di vhost nginx |
| Git dubious ownership | `git config --global --add safe.directory /var/www/html/simantik` |

## Reset container (hapus database)

```bash
docker compose down -v --remove-orphans
./deploy/install.sh
```
