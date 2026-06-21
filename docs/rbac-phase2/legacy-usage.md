# Legacy Role Usage

Role legacy **tetap valid** di database dan diperluas otomatis lewat `RbacRoles::expandWithLegacy()`.

## Mapping

Lihat `App\Support\Rbac\RoleCompatibility::LEGACY_TO_CANONICAL`.

## Assignment baru

Gunakan:

```php
$user->syncCanonicalRoles(['admin_unit'], $unitKerjaId);
// atau
$user->assignCanonicalRoles(['kepala_unit']);
```

Jangan assign `pegawai`, `admin_gudang_unit`, `perencanaan` untuk user baru.

## Scope unit

Role yang otomatis difilter `unit_kerja_id`:

- `admin_unit`, `kepala_unit`
- Legacy: `pegawai`, `pegawai_unit`, `admin_gudang_unit`

Cek di controller: `UserScope::mustScopeToUnitKerja($user)` lalu `UserScope::applyUnitKerja($query, $user)`.

## Admin / administrator

Tidak ada bypass. Gunakan permission di DB (`admin.roles.index`, wildcard `admin.*`, dll.).
