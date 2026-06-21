# Technical Debt RBAC (pasca Tahap 2)

## Masih memakai role (bukan permission murni)

- `DataInventoryController` — filter kategori `admin_gudang_aset|persediaan|farmasi` (domain gudang).
- `DistribusiController` — filter kategori serupa.
- `PeminjamanBarangController::authorizeRole()` — middleware route sudah ada; refactor ke permission per aksi di Tahap 3.
- `ApprovalPermintaanService` — daftar role per step approval flow.
- Route middleware `role:admin,pegawai,...` di `routes/web.php` — tetap backward compatible; target Tahap 3: `permission` middleware saja.

## Scope data

- Controller yang sudah memakai `UserScope::mustScopeToUnitKerja()` masih duplikasi logika dropdown `unitKerjas` — bisa disatukan helper `UserScope::unitKerjaOptions($user)`.

## Belum dikerjakan (Tahap 3)

- Merge `model_has_roles` + `user_roles`.
- Rename permission ke `module.action`.
- Hapus `user_modules` fallback.
- Unifikasi `workflow_permissions` ke permission utama.
