# Rencana Migrasi RBAC

## Tahap 1 (selesai / berjalan)

1. Aktifkan Spatie wildcard native.
2. Authorization runtime 100% database (`User::hasPermission` → `checkPermissionTo`).
3. Bypass tunggal: `super_administrator`.
4. Static map → `StaticRolePermissionMap` + seeder DB.
5. Role kanonik + kolom `is_deprecated`, `maps_to_role`.
6. Sidebar permission-first; `user_modules` fallback opsional.

**Perintah:** `php artisan migrate` lalu `php artisan db:seed --class=RbacPhase1Seeder`.

## Tahap 2 (role)

- Rapikan assignment user ke role kanonik.
- UI admin role menampilkan role deprecated dengan label.
- Kurangi role duplikat di form user (tanpa hapus data).

## Tahap 3 (struktur besar, bertahap)

1. Satu sumber `user_roles` (gabung `model_has_roles`).
2. Workflow → permission utama (`permintaan.approve`, dll.).
3. Penamaan permission `module.action` + alias route sementara.
4. Hapus `user_modules` setelah semua menu permission-driven.
5. Opsional: `users.is_superadmin` sebagai satu-satunya bypass selain role.

## Yang tidak diubah di Tahap 1

- Layout Blade, CSS enterprise, struktur menu utama.
- Nomor dokumen, alur approval bisnis.
- Filter scope di controller yang masih memakai `hasRole('admin')` — ditindak di Tahap 2 (ganti ke permission/scope service).
