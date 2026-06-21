# RBAC Tahap 1 — Permission Database-Driven

Refactor bertahap tanpa mengubah UI, layout, atau alur bisnis utama. Route permission tetap mengikuti **nama route** (`transaction.permintaan-barang.index`) sampai migrasi penamaan `module.action` (Tahap 3).

## Perubahan inti

| Area | Sebelum | Sesudah |
|------|---------|---------|
| Authorization runtime | DB + static map + alias CRUD + custom wildcard SQL | **Database + Spatie wildcard native** |
| Bypass penuh | `super_administrator`, `administrator`, `admin` | **`super_administrator` saja** |
| Static map | `PermissionHelper::getRolePermissions()` | `StaticRolePermissionMap` — **hanya seeder** |
| Sidebar | Permission + wajib `user_modules` | **Permission utama**, `user_modules` fallback (config) |
| Role kanonik | Bercampur legacy | 15 role + mapping legacy (lihat `role-mapping.md`) |

## Deploy

```bash
php artisan migrate
php artisan db:seed --class=RbacPhase1Seeder
# atau fresh: php artisan migrate:fresh --seed
php artisan permission:cache-reset
```

## Dokumen terkait

- [role-mapping.md](role-mapping.md)
- [permission-matrix.md](permission-matrix.md)
- [migration-plan.md](migration-plan.md)
- [rollback-plan.md](rollback-plan.md)
- [testing-checklist.md](testing-checklist.md)
