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
# edit: DB_PASSWORD, MYSQL_ROOT_PASSWORD, proxy, akun admin,
#       SECURITY_PERMISSIONS_POLICY (geolocation=(self))

chmod +x deploy/*.sh deploy/lib/*.sh
# jika Permission denied: bash deploy/update-production.sh
./deploy/set-domain-env.sh    # atau set-portal-env.sh / set-lan-env.sh
./deploy/install.sh
./deploy/install-nginx-snippet.sh
```

Tambahkan di server block nginx host (port 8081 / HTTPS):

```nginx
include /etc/nginx/snippets/simantik.conf;
```

```bash
sudo nginx -t && sudo systemctl reload nginx
./deploy/verify-path.sh
```

## Update rutin

```bash
cd /var/www/html/simantik
git pull origin main
chmod +x deploy/*.sh deploy/lib/*.sh
bash deploy/update-production.sh
```

`update-production.sh` otomatis:

1. `git pull` branch `main` (default `DEPLOY_BRANCH`)
2. **Build frontend Vite** (`deploy/build-frontend.sh`) — wajib setelah perubahan CSS / `app-layout.js`
3. `docker compose build app web` + `up -d` (nginx ikut di-rebuild — penting untuk header GPS)
4. `migrate --force`
5. `permission:sync-routes` + `config:cache` + `view:clear`
6. `verify-path.sh`

Setelah ubah `.env` (proxy / Permissions-Policy), jalankan ulang:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
```

Jika snippet host berubah, pasang ulang:

```bash
bash deploy/install-nginx-snippet.sh
sudo nginx -t && sudo systemctl reload nginx
```

## Keamanan browser (kamera + GPS)

Di `.env` production **wajib**:

```env
SECURITY_PERMISSIONS_POLICY="camera=(self), microphone=(), geolocation=(self)"
```

Jangan pakai `geolocation=()` — browser akan menolak GPS.

Cek header setelah deploy:

```bash
curl -sI http://127.0.0.1:7001/ | grep -i permissions
curl -sI https://puspelkes.jakarta.go.id/demo-simantik/ | grep -i permissions
```

Harus memuat `geolocation=(self)`. Akses lewat **HTTPS** (GPS butuh secure context).

Sumber header:

| Lapisan | File |
|---------|------|
| Laravel (global middleware) | `SecurityHeaders` + `config/sipeni.php` |
| Nginx container | `docker/nginx/default.conf` (volume-mounted) |
| Nginx host (edge) | `deploy/nginx-snippets/simantik.conf` |

## Bukti sampai + GPS + nama tempat

Alur: **Kirim** → `MENUNGGU_BUKTI_SAMPAI` → foto + pegawai penerima + GPS → `MENUNGGU_VERIFIKASI`.

Perilaku UI (halaman distribusi → bukti sampai):

1. **Buka kamera** → ambil GPS **sekali**, lalu **dikunci** (tidak ikut geser perangkat).
2. Nama jalan/tempat diisi lewat reverse geocode (`GeocodeService` → Nominatim OSM).
3. Tombol **Ambil ulang lokasi** hanya jika titik tidak sesuai.
4. Fetch reverse geocode memakai header `X-Sipeni-Silent` agar **tidak** memunculkan overlay “Memproses data…”.

Kolom DB terkait (`penerimaan_barang`):

| Kolom | Keterangan |
|-------|------------|
| `foto_bukti_sampai`, `sumber_bukti_sampai` | Foto + sumber (`upload` / `kamera`) |
| `gps_latitude`, `gps_longitude`, `gps_akurasi` | Koordinat |
| `gps_alamat` | Nama jalan / tempat (reverse geocode) |
| `hasil_verifikasi` (detail) | Verifikasi per-item di klinik |

### Proxy untuk reverse geocode

Container app harus bisa akses `https://nominatim.openstreetmap.org` lewat proxy korporat:

```env
HTTP_PROXY=http://10.15.3.20:80
HTTPS_PROXY=http://10.15.3.20:80
NO_PROXY=localhost,127.0.0.1,mysql,.local,10.0.0.0/8
```

`GeocodeService` membaca proxy dari `config('sipeni.http.*')` (bukan hanya getenv PHP-FPM). Setelah ubah proxy: `config:cache`.

Uji dari container:

```bash
docker compose exec app php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo (new App\Services\GeocodeService)->reverse(-6.175392, 106.827153) ?: 'GAGAL';
echo PHP_EOL;
"
```

## Subpath & fetch AJAX

Di Blade, **jangan** hardcode `/api/...` (gagal di `/demo-simantik`). Pakai `route(...)`, contoh:

- `route('api.permintaan.detail', …)`
- `route('api.gudang.inventory', …)`
- `route('api.geocode.reverse')`
- `route('transaction.distribusi.api.gudang-tujuan', …)`

## Catatan rilis (distribusi → penerimaan)

| Migrasi / area | Efek |
|----------------|------|
| Bukti sampai + GPS + `gps_alamat` | Foto, koordinat, nama tempat |
| `hasil_verifikasi` | Verifikasi Sesuai / Tidak sesuai |
| Frontend (`app-layout.js`) | Silent fetch + loading overlay |
| Permissions-Policy | Kamera + geolocation `(self)` |

Jika CSS/JS lama: hard refresh, atau `./deploy/build-frontend.sh` lalu `./deploy/post-deploy.sh cache`.

## Script deploy

| Script | Fungsi |
|--------|--------|
| `deploy/install.sh` | Instalasi pertama: build, frontend, up, seed |
| `deploy/update-production.sh` | Git pull + build frontend + build app/web + migrate/cache |
| `deploy/build-frontend.sh` | `npm run build` via container Node |
| `deploy/post-deploy.sh` | `migrate` / `sync-routes` / `seed` / `cache` / `key` / `wait` |
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
| `./deploy/update-production.sh: Permission denied` | `chmod +x deploy/*.sh` atau `bash deploy/update-production.sh` |
| `sudo: ./deploy/...: command not found` | Bit `+x` hilang — `chmod +x` lalu `sudo bash deploy/update-production.sh` |
| Build gagal tanpa internet | Isi `HTTP_PROXY`/`HTTPS_PROXY` di `.env`; daemon: `deploy/docker-http-proxy.conf.example` |
| `key:generate` read-only | `./deploy/post-deploy.sh key` |
| HTTP 500, artisan OK | `sudo chown -R 33:33 storage bootstrap/cache`; `./deploy/post-deploy.sh cache` |
| App restart loop / timeout wait | Cek `docker compose ps` — jika `healthy`, biasanya OK; isi `DB_PASSWORD` + `MYSQL_ROOT_PASSWORD` |
| 404 di `:8081` | `include` snippet di vhost nginx |
| Detail item SBBK kosong setelah pilih permintaan | Fetch API hardcode `/api/...` — pastikan Blade memakai `route()` (sudah di main) |
| GPS ditolak / izin lokasi | `.env` `geolocation=(self)`, rebuild/reload **web** + snippet host, HTTPS, reset izin browser |
| Overlay “Memproses data…” terus muncul saat GPS | Pastikan build frontend terbaru (`app-layout.js` silent fetch) + GPS terkunci (tanpa `watchPosition`) |
| Nama tempat muncul lokal, kosong di VM | Proxy outbound: `config:cache` + uji `GeocodeService::reverse` di container |
| Warna UI / badge tidak berubah | `./deploy/build-frontend.sh` + `post-deploy.sh cache` + hard refresh |
| Form bukti sampai error kolom DB | `./deploy/post-deploy.sh migrate` |

## Reset container (hapus database)

```bash
docker compose down -v --remove-orphans
./deploy/install.sh
```
