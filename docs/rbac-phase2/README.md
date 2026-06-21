# RBAC Tahap 2 — Stabilisasi & Role Kanonik

Tahap 1 selesai (database-driven permission, wildcard Spatie, bypass tunggal `super_administrator`).

## Deliverables Tahap 2

| Komponen | Lokasi |
|----------|--------|
| Scope unit terpusat | `App\Support\Rbac\UserScope` |
| Konstanta role + legacy expand | `App\Support\Rbac\RbacRoles` |
| Blade `@canAccess('route.permission')` | `AppServiceProvider` |
| Gate `permission` | `Gate::define('permission', ...)` |
| Refactor controller scope | 18+ controller/helpers |
| Tombol workflow permission | `peminjaman-barang/show`, `approval/show`, dll. |
| Seeder role kanonik | `PegawaiUserPerJabatanSeeder` |
| Audit CLI | `php artisan rbac:audit` |
| User assign kanonik | `User::syncCanonicalRoles()` |

## Perintah

```bash
php artisan rbac:audit
composer run test:rbac
php artisan test --filter=RbacPhase2
```

## Dokumen

- [technical-debt.md](technical-debt.md)
- [legacy-usage.md](legacy-usage.md)
- [phase3-prep.md](phase3-prep.md)
