# Operasional SI-MANTIK

Runbook deploy, monitoring, dan recovery untuk tim operasional.

## Pre-deploy checklist

1. `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`
2. `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`
3. `RBAC_LEGACY_USER_MODULES_FALLBACK=false`
4. Set akun awal lewat `.env` (`SIPENI_SUPER_ADMIN_*`, `SIPENI_ADMIN_IT_*`) — jangan pakai password lemah di production
5. Kebijakan password aplikasi: minimal **12 karakter**, huruf besar/kecil, angka, dan simbol
6. Set `TRUSTED_PROXIES` ke IP load balancer (jangan `*` di produksi)
7. Aktifkan 2FA untuk akun super admin & admin IT (`TWO_FACTOR_ENABLED=true`)
8. Backup database sebelum migrate

## Deploy (Docker)

```bash
git pull
docker compose build --no-cache simantik
docker compose up -d
docker exec simantik-web php artisan migrate --force
docker exec simantik-web php artisan view:clear
docker exec simantik-web php artisan config:cache
```

Health: `curl -f http://<host>:9001/up`

## Deploy (bare metal / Laragon)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
```

Jalankan queue worker terpisah: `php artisan queue:work --tries=3`

## Backup MySQL

```bash
mysqldump -u USER -p DATABASE > backup_$(date +%Y%m%d_%H%M).sql
```

Restore: `mysql -u USER -p DATABASE < backup.sql`

## Log & monitoring

| Lokasi | Isi |
|--------|-----|
| `storage/logs/laravel.log` | Error aplikasi |
| `php artisan pail` | Stream log (dev) |
| Tabel `activity_logs` / `audit_logs` | Audit HTTP & entitas |

Queue gagal: `php artisan queue:failed` → `php artisan queue:retry all`

## Permission & menu

Setelah deploy dengan route baru:

```bash
php artisan permission:sync-routes --force
php artisan config:clear
```

Sidebar cache per-user invalidasi otomatis saat role user diubah.

## Panduan pengguna

Setelah mengubah file di `docs/panduan-pengguna/`:

```bash
php artisan panduan:export-pdf
php artisan view:clear
```

Panduan tampil di aplikasi lewat `/panduan` (tanpa permission khusus — semua user login). Regenerasi PDF opsional jika server punya dependency DomPDF.

## Variabel env penting

| Variabel | Default | Catatan |
|----------|---------|---------|
| `FEATURE_PRINT_TEMPLATES` | false | Aktifkan di staging untuk UAT cetak SBBK |
| `NOTIFICATIONS_MAIL_ENABLED` | false | Email notifikasi (butuh SMTP) |
| `SECURITY_CSP_ENABLED` | false | CSP; mulai report-only |
| `TWO_FACTOR_REQUIRED_ROLES` | super_administrator,admin | Role wajib 2FA |
| `RBAC_LEGACY_USER_MODULES_FALLBACK` | false | Fase 3 RBAC |

## Incident response

1. **Login brute-force** — cek audit `login_failed`; throttle sudah 5/menit
2. **403 massal** — cek permission role di DB; jalankan `rbac:audit`
3. **Upload hilang (Docker)** — pastikan volume `simantik-storage` ter-mount
4. **Rollback migrate** — hati-hati: beberapa migrasi MySQL-specific; prefer restore backup

## Kontak & eskalasi

Dokumentasi teknis lengkap: [AGENTS.md](../AGENTS.md), audit: [AUDIT_SISTEM_LENGKAP_2026-06-21.md](./AUDIT_SISTEM_LENGKAP_2026-06-21.md)
