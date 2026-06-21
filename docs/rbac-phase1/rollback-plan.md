# Rollback Plan — RBAC Tahap 1

## Cepat (config)

1. Set `permission.enable_wildcard_permission` → `false` di `config/permission.php` (jika wildcard menimbulkan akses berlebihan).
2. Set `RBAC_LEGACY_USER_MODULES_FALLBACK=true` dan pastikan `user_modules` terisi untuk user terdampak.
3. `php artisan permission:cache-reset` dan `PermissionHelper::bumpAccessibleMenusCacheGeneration()`.

## Kode (git)

Revert commit Tahap 1 pada file:

- `app/Helpers/PermissionHelper.php`
- `app/Models/User.php`
- `config/permission.php`, `config/sipeni.php`
- Seeders RBAC Tahap 1

## Database

Migration `2026_05_15_100000_rbac_phase1_role_compatibility_columns` dapat di-rollback:

```bash
php artisan migrate:rollback --step=1
```

Kolom `is_deprecated` / `maps_to_role` dihapus; data role tidak dihapus.

**Jangan** rollback seeder permission tanpa backup — assignment manual user bisa hilang jika memakai `sync` penuh. Seeder Tahap 1 hanya `syncWithoutDetaching`.

## Restore bypass lama (darurat)

Hanya jika lockout massal — sementara tambahkan `administrator` ke `sipeni.rbac.bypass_roles` di `.env` (perlu dukungan config array; saat ini di `config/sipeni.php`). Disarankan assign permission ke role `admin`/`administrator` di DB, bukan mengaktifkan bypass lama.

## Verifikasi pasca-rollback

- Login per role sampel dari testing checklist.
- Sidebar dan route kritis (permintaan, distribusi, RKU).
