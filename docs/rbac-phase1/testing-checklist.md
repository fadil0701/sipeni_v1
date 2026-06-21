# Checklist Testing RBAC Tahap 1

## Otomatis (PHPUnit)

**Laravel 13 membutuhkan PHP ≥ 8.3.** Pastikan terminal memakai PHP 8.3 (Laragon: Menu → PHP → `8.3.24`, lalu buka ulang terminal). Cek: `php -v`.

```bash
php artisan config:clear
php artisan test --filter=RbacPhase1
```

Atau:

```bash
composer run test:rbac
```

> Hindari `composer run test -- --filter=RbacPhase1` di Windows — argumen `--filter` bisa ikut ke `config:clear` dan gagal.

Jika `composer` memakai PHP 8.2 (error platform_check), jalankan Composer dengan PHP 8.3:

```powershell
D:\laragon\bin\php\php-8.3.24-nts-Win32-vs16-x64\php.exe D:\laragon\bin\composer\composer.phar run test:rbac
```

- `RbacPhase1AuthorizationTest` — bypass, wildcard, tanpa alias store/create.
- `SpatieRbacMigrationTest` — sync role + permission dasar.

## Manual — wajib

| # | Skenario | Harapan |
|---|----------|---------|
| 1 | `super_administrator` | Akses semua route & menu |
| 2 | `administrator` / `admin` | **Tidak** bypass; hanya route sesuai permission DB |
| 3 | `admin_unit` | Data terfilter `unit_kerja_id` |
| 4 | `kepala_unit` | Approval hanya unit sendiri |
| 5 | Sidebar | Menu sesuai permission (bukan hanya module) |
| 6 | Tombol create/edit/delete/approve | Sesuai `canAccess` / `@can` |
| 7 | RKU dibuat `admin_unit` | Berhasil jika permission `planning.rku.create` |
| 8 | RKU diverifikasi `kepala_unit` | Sesuai workflow + permission |
| 9 | Permintaan dibuat unit | OK |
| 10 | Distribusi diterima unit | OK |
| 11 | Gudang pusat vs unit | Tidak tercampur |
| 12 | Multi-role user | Gabungan permission role |
| 13 | Workflow approval | `EnsureWorkflowAccess` + `workflow_permissions` |
| 14 | Route lama | Tidak 403 massal setelah seed |
| 15 | Data existing | Tidak berubah |

## Setelah deploy

```bash
php artisan migrate
php artisan db:seed --class=RbacPhase1Seeder
php artisan permission:cache-reset
```

Periksa user `admin`/`administrator` punya permission di role setelah seed.
