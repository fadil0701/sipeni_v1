# Matriks Permission (Tahap 1)

## Format saat ini (route-as-permission)

Middleware `role` memeriksa **nama route** sebagai permission, misalnya:

- `planning.rku.index`, `planning.rku.create`
- `transaction.permintaan-barang.index`, `transaction.approval.approve`
- `inventory.data-stock.index`, `inventory.farmasi-kedaluwarsa.index`

Wildcard di database (Spatie native, `config/permission.php` → `enable_wildcard_permission = true`):

- `planning.*` → mencakup `planning.rku.index`, dll.
- `inventory.*`, `transaction.*`, `admin.*`

## Format target (Tahap 3)

`module.action` atau `module.submodule.action`, contoh:

- `rku.view`, `rku.verify`, `rku.approve`
- `permintaan.barang.create`, `distribusi.receive`
- `inventory.farmasi.view`, `inventory.aset.maintenance`

## Sumber data

| Sumber | Penggunaan |
|--------|------------|
| Tabel `permissions` | Authorization runtime |
| `StaticRolePermissionMap` | Seeder `RoleLegacyPermissionSeeder` saja |
| `permission:sync-routes` | Menambah permission baru dari route |

## Aksi eksplisit (tanpa alias)

Tidak ada lagi mapping `store→create`, `update→edit`, `destroy→delete`. Role harus memiliki permission yang sama dengan nama route/aksi.

## Turunan route (bukan alias CRUD)

| Permission anak | Parent yang cukup |
|-----------------|-------------------|
| `inventory.data-stock.merk-breakdown` | `inventory.data-stock.index` |
| `reports.kartu-stok.merk-breakdown` | `reports.kartu-stok` |
| `inventory.farmasi-kedaluwarsa.export` | `inventory.farmasi-kedaluwarsa.index` |

## Sidebar

Menu tampil jika `PermissionHelper::canAccess()` untuk permission submenu (atau wildcard parent). Fallback `user_modules` jika `RBAC_LEGACY_USER_MODULES_FALLBACK=true`.
