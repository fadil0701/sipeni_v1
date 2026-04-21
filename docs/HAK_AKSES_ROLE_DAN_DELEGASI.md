# Dokumentasi Hak Akses, Role, dan Delegasi Permission

Dokumen ini menjelaskan cara sistem SI-PENI mengatur **siapa boleh mengakses route mana**, bagaimana **menu sidebar** difilter, serta aturan **pengaturan role dan user** di panel admin (termasuk delegasi permission).

---

## 1. Konsep inti

### 1.1 Nama route = permission

Untuk route yang dilindungi middleware `role` (implementasi: `App\Http\Middleware\CheckRole`), **nama route Laravel** dipakai sebagai **nama permission**. Contoh:

- Route bernama `transaction.permintaan-barang.index` memerlukan hak akses dengan permission yang sama (atau wildcard yang mencakupnya).
- Pengecekan dilakukan lewat `PermissionHelper::canAccess($user, 'transaction.permintaan-barang.index')`.

### 1.2 Dua lapisan untuk tampilan menu

Hak akses ke **halaman/aksi** dan **menu yang tampil di sidebar** saling melengkapi:

| Lapisan | Fungsi | Diatur di |
|--------|--------|-----------|
| **Permission pada role** | Menentukan route/aksi yang boleh dijalankan (validasi di middleware). | Manajemen Role (centang permission). |
| **Modul menu pada user** | Membatasi **grup menu sidebar** yang ditampilkan untuk akun tersebut. | Manajemen User (centang modul). |

Jika sebuah modul **tidak** dipilih untuk user, menu grup tersebut **disembunyikan** di sidebar meskipun role punya permission terkait (lihat `App\Helpers\PermissionHelper::computeAccessibleMenus`).

### 1.3 Role `admin`

User dengan role **`admin`** dianggap memiliki **semua** hak akses (shortcut di `PermissionHelper::canAccess` dan `User::hasPermission`). Untuk **delegasi** di panel admin (siapa boleh mencentang permission/modul untuk orang lain), lihat bagian 5.

---

## 2. Komponen utama di kode

| Komponen | Lokasi | Keterangan |
|----------|--------|------------|
| `CheckRole` | `app/Http/Middleware/CheckRole.php` | Memetakan nama route ke string permission dan memanggil `PermissionHelper::canAccess`. |
| `PermissionHelper` | `app/Helpers/PermissionHelper.php` | Pengecekan akses: database permission role, wildcard, mapping `store`→`create`, `update`→`edit`, serta fallback permission statis per role. |
| `PermissionModule` | `app/Support/PermissionModule.php` | Label modul dan urutan grup untuk UI Manajemen Role; normalisasi segmen route ke kunci `permissions.module`. |
| `AssignablePermissions` | `app/Support/AssignablePermissions.php` | Permission/modul mana yang boleh **didelegasikan** oleh user yang sedang login (bukan admin penuh). |
| Model `Permission` | `app/Models/Permission.php` | Baris per permission: `name`, `display_name`, `module`, `sort_order`, dll. |
| Model `Module` | `app/Models/Module.php` | Modul menu sidebar (primary key string `name`). |

---

## 3. Sinkronisasi permission dari route

Perintah Artisan:

```bash
php artisan permission:sync-routes
```

- Memindai route bernama di `routes/web.php` (kecuali route sistem seperti login, storage, dll.).
- Menambahkan baris ke tabel `permissions` jika belum ada.
- Kolom `module` diisi konsisten lewat `PermissionModule::moduleKeyFromRoutePrefix()` (misalnya prefix `user` → modul `dashboard`).

Setelah menambah route baru ke aplikasi, jalankan perintah ini agar permission dapat muncul di form Manajemen Role.

---

## 4. Manajemen Role (panel Admin)

### 4.1 Form create/edit

- Permission dikelompokkan per **modul** (`permissions.module`) dengan label dari `PermissionModule::LABELS` dan urutan `PermissionModule::sortModuleKeys`.
- **Admin penuh** (`role` bernama `admin`): melihat **semua** permission dan dapat mencentang semuanya.
- Modul teknis seperti `api` (endpoint bantu untuk dropdown/detail AJAX) **disembunyikan dari checklist role** agar tidak duplikatif dan tidak membingungkan operator.
- Action yang duplikatif disederhanakan di UI:
  - `store` digabung ke `create`
  - `update` digabung ke `edit`
  - `delete` digabung ke `destroy`
  - action proses (`ajukan/approve/reject/verifikasi/...`) digabung ke satu item **workflow** per resource.

### 4.2 Delegasi (non-admin)

Untuk user **bukan** `admin`, daftar centang hanya berisi permission yang **boleh diberikan** menurut `AssignablePermissions::assignablePermissionIds()` — yaitu permission yang sama dengan yang diizinkan untuk user tersebut lewat `PermissionHelper::canAccess` pada nama permission tersebut.

**Edit role:** jika role sudah memiliki permission yang **di luar** cakupan editor, permission tersebut:

- Ditampilkan di blok informasi **read-only** (tidak ada checkbox).
- Saat **Simpan**, ID permission tersebut **digabung** ke `sync()` agar tidak hilang (hanya bagian yang boleh diubah yang dikirim lewat form).

### 4.3 Validasi server

`RoleController` memanggil `assertEditorMayAssignPermissions` pada **store** dan **update**; percobaan mengirim ID permission yang tidak diizinkan mengakibatkan **403**.

---

## 5. Manajemen User dan modul sidebar

### 5.1 Centang modul menu

- Hanya modul yang **selaras** dengan permission yang dimiliki editor (atau semua modul jika editor `admin`) yang ditampilkan sebagai checkbox (`AssignablePermissions::assignableModuleNamesForUserForm`).
- Validasi server: `UserController::assertEditorMayAssignModules`.

### 5.2 Edit user (non-admin)

Modul yang sudah melekat pada user tetapi **di luar** hak editor ditampilkan sebagai daftar **terkunci** (informasi saja). Saat simpan, nama modul tersebut **digabung** ke `sync()` agar tidak terhapus tanpa sengaja.

---

## 6. Mapping permission (create/store, edit/update)

Ringkasan ada di [PERMISSION_SIMPLIFICATION.md](./PERMISSION_SIMPLIFICATION.md): `store` dipetakan ke permission `create`, `update` ke `edit`, sehingga daftar centang di role tidak perlu menggandakan baris.

---

## 7. Cache menu sidebar

Setelah mengubah permission role atau modul user, cache menu per user dapat diperbarui lewat:

- `PermissionHelper::bumpAccessibleMenusCacheGeneration()` (global generasi cache menu), atau
- `PermissionHelper::forgetAccessibleMenusCacheForUser($userId)` untuk satu user.

Manajemen Role/User memanggil helper ini setelah perubahan relevan.

---

## 8. Ringkasan cepat untuk operator

1. **Route baru** → jalankan `php artisan permission:sync-routes`, lalu berikan permission ke role yang sesuai.
   - Catatan: route berawalan `api.` (endpoint bantu) sengaja tidak disinkronkan ke checklist role.
2. **Hak akses halaman** → atur di **Manajemen Role** (permission).
3. **Menu sidebar yang tampil** → atur di **Manajemen User** (modul), selain permission role.
4. **Admin penuh** = role `admin` pada user; selain itu daftar centang permission/modul mengikuti **delegasi** (hanya yang boleh diberikan ke orang lain).

---

## 9. Referensi berkas terkait

- `routes/web.php` — definisi route dan nama route.
- `bootstrap/app.php` — alias middleware `role`, redirect guest ke login.
- `resources/views/layouts/app.blade.php` — sidebar memakai `accessibleMenus` dari view composer (`AppServiceProvider`).
- `docs/PERMISSION_SIMPLIFICATION.md` — penyederhanaan permission CRUD.

Jika ada perubahan besar pada struktur route atau role, sesuaikan dokumentasi ini dan jalankan ulang sinkronisasi permission.
