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
- Services layer in `app/Services/` for complex business logic.
- Observers: `DataInventoryObserver`, `PermintaanBarangObserver`.
- Enums in `app/Enums/` — `PermintaanBarangStatus` is the canonical workflow source.
- Print templates: dynamic renderer (`PrintTemplateRenderer`), gated by `FEATURE_PRINT_TEMPLATES` env var (default `false`).

## Transaction Flow

```
Permintaan → Approval Multi-Level → Disposisi → Draft Distribusi → Compile SBBK → Distribusi/Kirim → Penerimaan → Retur (optional)
```

PermintaanBarang status enum: `draft → diajukan → diverifikasi → ditolak | menunggu_pengadaan → proses_pengadaan → barang_tersedia → proses_distribusi → dikirim → diterima → selesai`

## Middleware (registered aliases)

| Alias | Class | Purpose |
|-------|-------|---------|
| `role` | `CheckRole` | Checks route name as permission (not raw role) |
| `scope.unit` | `EnsureUnitScope` | Scopes access to user's unit_kerja |
| `feature.print-templates` | `EnsurePrintTemplatesFeatureEnabled` | Gates print template routes |

Global (`web` group): `LoadUserPermissions`, `AuditRequestActivity`.

## Docker

- Pola **dashboard-skrining**: `mysql` + `app` (PHP-FPM) + `web` (Nginx) + `queue`.
- Port host: **7001** (`APP_PORT` → service `web`).
- Subpath: `APP_SUBPATH=/demo-simantik` — Nginx container strip prefix sebelum ke Laravel.
- Proxy korporat: `10.15.3.20:80` (build args + runtime env).
- Panduan: `deploy/README.md`, template: `.env.production.example`.
- Setelah deploy: `docker compose exec app php artisan db:seed --force`.

## Key env vars

- `FEATURE_PRINT_TEMPLATES` — enable print template admin module (default `false`).
- `SUPERADMIN_BYPASS_SCOPE` — admin bypasses unit scope (default `true`).
- `APP_ROUTE_PREFIX` — subpath deployment support.
- `SBBK_KOP_*` — letterhead for SBBK PDF output.
- `OPENAI_API_KEY` — AI SDK.
- `APP_URL` — used for route prefix auto-detection via `config/path.php`.

## DB quirks

- Pivot `permission_role` instead of default `role_has_permissions`.
- 107 migration files, extensive schema.
- 24 seeders available.

## Tests

- PHPUnit, SQLite in-memory (`phpunit.xml`).
- Suites: Unit (`tests/Unit`), Feature (`tests/Feature`).
- Always run `php artisan config:clear` before tests (automated in `composer run test`).

## Generated / codegen

- Route permissions auto-synced on `composer dump-autoload` via `permission:sync-routes`.
- Cache: config + view cached during Docker build. Route cache intentionally skipped.
- Sidebar menu cache per user (24h TTL); invalidated via `PermissionHelper::bumpAccessibleMenusCacheGeneration()`.
