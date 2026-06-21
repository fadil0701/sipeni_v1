# Persiapan Tahap 3

## Checklist sebelum migrasi besar

1. Jalankan `php artisan rbac:audit` — nol temuan `hasRole('admin')` di app/ (kecuali audit command).
2. Semua user produksi punya permission DB lengkap per role kanonik.
3. `RBAC_LEGACY_USER_MODULES_FALLBACK=false` di staging, uji sidebar 1 minggu.
4. Dokumentasi matrix permission vs route 100% sinkron (`permission:sync-routes`).

## Urutan migrasi rencana

1. Alias route → permission baru (`rku.view` + backward alias di `PermissionHelper` sementara).
2. Middleware route: `role:` → `can:permission`.
3. Satu tabel assignment role (`user_roles` enriched).
4. Workflow status → permission `*.approve`, `*.verify`.
5. Deprecate & freeze role legacy (tidak hapus).

## Rollback

Tetap simpan backup `permission_role` dan `model_has_roles` sebelum cutover.
