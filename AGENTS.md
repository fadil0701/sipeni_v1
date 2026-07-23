# SI-MANTIK (sipeni_v1)

Asset & inventory management built with Laravel 13 + Blade + Tailwind CSS v4.

## Commands

| Command | Purpose |
|---------|---------|
| `composer run dev` | Dev server: `php artisan serve` + `queue:listen` + `pail` logs + `npm run dev` concurrently |
| `composer run test` | `artisan config:clear` then `artisan test` |
| `npm run build` / `npm run dev` | Vite build / dev server |
| `php artisan permission:sync-routes` | Sync route permissions (runs automatically on `composer dump-autoload`) |

Single test: `php artisan test --filter=TestName`

## RBAC (critical)

- **Permission = route name** (e.g. `inventory.data-stock.index`).
- `CheckRole` middleware uses route-name-as-permission check, not raw role names. Role list param is backward-compat.
- Static permission map in `app/Helpers/PermissionHelper.php` for roles; **database permissions (Spatie) take priority**.
- Enterprise bypass: **only** `super_administrator` (and optional `users.is_superadmin`) skips all checks. `admin` / `administrator` use normal DB permissions.
- Wildcard: Spatie native (`enable_wildcard_permission`); no custom SQL wildcard fallback.
- Pivot table uses legacy name `permission_role` (config in `config/permission.php`).
- Scope: `EnsureUnitScope` middleware scopes users to their `unit_kerja`. Admin bypasses via `sipeni.auth.superadmin_bypass_scope`.
- Module assignment (table `user_modules`) also filters sidebar menus.

## Key Architecture

- **Single Blade layout**: `resources/views/layouts/app.blade.php` (blue sidebar).
- Controllers under `app/Http/Controllers/<domain>/` organized by business domain.
- Services layer in `app/Services/` for complex business logic (`DistribusiService`, `GeocodeService`, …).
- Observers: `DataInventoryObserver`, `PermintaanBarangObserver`.
- Enums in `app/Enums/` — `PermintaanBarangStatus` is the canonical workflow source.
- Print templates: dynamic renderer (`PrintTemplateRenderer`), gated by `FEATURE_PRINT_TEMPLATES` env var (default `false`).
- **Semantic UI colors**: `App\Support\UiColor` — single source for button/badge/status tones (`primary`, `success`, `warning`, `danger`, `info`, `neutral`). Prefer `x-ui.btn` with `action=` (`proses`, `setujui`, `verifikasi`, `disposisi`, `mengetahui`, `tolak`, `detail`, …) or `UiColor::badgeForStatus()` / `x-ui.status-badge` instead of hardcoding Tailwind hues. CSS utilities: `.ui-btn-*`, `.ui-badge-*`, `.alert-box`.

## Transaction Flow

```
Permintaan → Approval Multi-Level → Disposisi → Buat SBBK (dari Daftar Permintaan) → Distribusi/Kirim → Bukti sampai → Penerimaan verifikasi → Retur (optional)
```

PermintaanBarang status enum: `draft → diajukan → diverifikasi → ditolak | menunggu_pengadaan → proses_pengadaan → barang_tersedia → proses_distribusi → dikirim → diterima → selesai`

Distribusi after **Kirim**: penerimaan `MENUNGGU_BUKTI_SAMPAI` → (foto + nama penerima + GPS) → `MENUNGGU_VERIFIKASI` → verifikasi per-item → `DITERIMA` / `DITOLAK`.

**Mutasi stok (Opsi A):**
- Persediaan/Farmasi saat **Kirim**: `data_stock` asal turun **dan** `data_inventory` pindah/split ke gudang tujuan (`DistribusiStockMutationService`).
- Aset saat **DITERIMA**: `inventory_item` + `data_inventory` ke tujuan + auto Register (`id_ruangan` null). KIR terbentuk saat ruangan diisi di Register Aset.

### Bukti sampai (GPS)

- UI: `resources/views/transaction/distribusi/show.blade.php` — buka kamera mengambil GPS **sekali lalu dikunci**; tombol **Ambil ulang lokasi** untuk koreksi manual.
- Koordinat + `gps_alamat` (nama jalan/tempat via `GeocodeService` / Nominatim).
- API: `api.geocode.reverse` (AJAX silent — header `X-Sipeni-Silent` agar tidak memicu global loading overlay).
- Production: `SECURITY_PERMISSIONS_POLICY` harus `geolocation=(self)`; outbound Nominatim butuh `HTTP_PROXY`/`HTTPS_PROXY` (lihat `config/sipeni.php` → `http`).

Daftar permintaan (draft-distribusi) tab **Riwayat** hanya status approval log `SELESAI`.

Di subpath (`/demo-simantik`), fetch AJAX **wajib** memakai `route(...)`, bukan path absolut `/api/...`.

## Middleware (registered aliases)

| Alias | Class | Purpose |
|-------|-------|---------|
| `role` | `CheckRole` | Checks route name as permission (not raw role) |
| `scope.unit` | `EnsureUnitScope` | Scopes access to user's unit_kerja |
| `feature.print-templates` | `EnsurePrintTemplatesFeatureEnabled` | Gates print template routes |

Global: `SecurityHeaders` (CSP / Permissions-Policy).  
`web` group: `LoadUserPermissions`, `AuditRequestActivity`.

## Docker

- Pola **dashboard-skrining**: `mysql` + `app` (PHP-FPM) + `web` (Nginx) + `queue`.
- Port host: **7001** (`APP_PORT` → service `web`).
- Subpath: `APP_SUBPATH=/demo-simantik` — host nginx strip prefix; container nginx juga rewrite untuk akses `:7001/demo-simantik/`.
- Proxy: `HTTP_PROXY` / `HTTPS_PROXY` di `.env` (lihat `deploy/lib/env-proxy.sh`). Dipakai build Docker **dan** outbound PHP (`GeocodeService`).
- Update: `bash deploy/update-production.sh` (build **app + web**, frontend Vite, migrate, cache).
- Frontend build: `./deploy/build-frontend.sh` (Node container, mount host).
- Panduan lengkap: `deploy/README.md`; template: `.env.production.example`, `.env.docker.local.example`.

## Key env vars

- `FEATURE_PRINT_TEMPLATES` — enable print template admin module (default `false`).
- `SUPERADMIN_BYPASS_SCOPE` — admin bypasses unit scope (default `true`).
- `APP_SUBPATH` / `APP_USE_REQUEST_URL` / `APP_PORT` — subpath & URL mode (portal vs LAN).
- `APP_ROUTE_PREFIX` — `false` di Docker (nginx strip subpath).
- `SECURITY_PERMISSIONS_POLICY` — wajib `camera=(self), microphone=(), geolocation=(self)` untuk bukti sampai GPS.
- `HTTP_PROXY` / `HTTPS_PROXY` / `NO_PROXY` — proxy korporat (build + reverse geocode).
- `SBBK_KOP_*` — letterhead for SBBK PDF output.
- `OPENAI_API_KEY` — AI SDK.
- `APP_URL` — used for route prefix auto-detection via `config/path.php`.

## DB quirks

- Pivot `permission_role` instead of default `role_has_permissions`.
- Extensive schema (100+ migrations).
- Seeders available under `database/seeders`.

## Tests

- PHPUnit, SQLite in-memory (`phpunit.xml`).
- Suites: Unit (`tests/Unit`), Feature (`tests/Feature`).
- Always run `php artisan config:clear` before tests (automated in `composer run test`).
- Geocode: `php artisan test --filter=GeocodeServiceTest`.

## Generated / codegen

- Route permissions auto-synced on `composer dump-autoload` via `permission:sync-routes`.
- Cache: config + view cached during Docker build. Route cache intentionally skipped.
- Sidebar menu cache per user (24h TTL); invalidated via `PermissionHelper::bumpAccessibleMenusCacheGeneration()`.
