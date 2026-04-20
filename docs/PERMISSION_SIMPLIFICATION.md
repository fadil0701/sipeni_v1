# Dokumentasi Penyederhanaan Permission

## ✅ Perubahan yang Dilakukan

### 1. Penggabungan Permission Duplikat
- **`store`** → digabungkan dengan **`create`**
- **`update`** → digabungkan dengan **`edit`**

### 2. Hasil Penyederhanaan
- **Sebelum**: 296 permissions (termasuk store dan update)
- **Sesudah**: 229 permissions
- **Dihapus**: 67 permission yang duplikat

### 3. Mapping Permission
Sistem sekarang otomatis memetakan:
- `transaction.permintaan-barang.store` → `transaction.permintaan-barang.create`
- `transaction.permintaan-barang.update` → `transaction.permintaan-barang.edit`

## 🔧 Cara Kerja

### PermissionHelper::canAccess()
Method ini sekarang mendukung mapping otomatis:

```php
// Jika user punya permission 'create', maka bisa akses 'store'
PermissionHelper::canAccess($user, 'transaction.permintaan-barang.store')
// Akan otomatis cek: transaction.permintaan-barang.create

// Jika user punya permission 'edit', maka bisa akses 'update'
PermissionHelper::canAccess($user, 'transaction.permintaan-barang.update')
// Akan otomatis cek: transaction.permintaan-barang.edit
```

### Middleware CheckRole
Middleware menggunakan `PermissionHelper::canAccess()` yang sudah mendukung mapping, jadi tidak perlu perubahan di middleware.

## 📋 Permission yang Tersedia

### CRUD Operations
Untuk setiap resource, sekarang hanya ada:
- `index` - Melihat daftar
- `create` - Membuat (mencakup store)
- `show` - Melihat detail
- `edit` - Mengedit (mencakup update)
- `destroy` - Menghapus

### Contoh: Transaction Permintaan Barang
- ✅ `transaction.permintaan-barang.index`
- ✅ `transaction.permintaan-barang.create` (mencakup store)
- ✅ `transaction.permintaan-barang.show`
- ✅ `transaction.permintaan-barang.edit` (mencakup update)
- ✅ `transaction.permintaan-barang.destroy`
- ✅ `transaction.permintaan-barang.ajukan` (action khusus)
- ✅ `transaction.permintaan-barang.*` (wildcard untuk semua)

## ✅ Fungsi yang Tetap Berjalan

### 1. Route Protection
Semua route tetap terlindungi dengan benar:
- Route `store` akan cek permission `create`
- Route `update` akan cek permission `edit`

### 2. View Permission Check
Semua view yang menggunakan `PermissionHelper::canAccess()` tetap berfungsi:
- Button "Tambah" cek `create` (bisa akses form create dan store)
- Button "Edit" cek `edit` (bisa akses form edit dan update)

### 3. Role Permission Assignment
Saat assign permission ke role:
- Cukup checklist `create` untuk memberikan akses create + store
- Cukup checklist `edit` untuk memberikan akses edit + update

## 🎯 Keuntungan

1. **Lebih Sederhana**: Tidak ada duplikasi permission
2. **Lebih Mudah Dikelola**: Admin hanya perlu checklist `create` dan `edit`
3. **Tetap Aman**: Semua fungsi tetap terlindungi dengan benar
4. **Backward Compatible**: Route yang menggunakan `store`/`update` tetap berfungsi

## 🔄 Migration Role Permissions

Saat command `permission:simplify-advanced` dijalankan:
- Role yang punya permission `store` → otomatis dapat `create`
- Role yang punya permission `update` → otomatis dapat `edit`
- Permission `store` dan `update` dihapus dari role

## 📝 Command yang Tersedia

### 1. Simplify Permissions
```bash
php artisan permission:simplify-advanced
```
Menyederhanakan permission dengan menggabungkan create+store dan edit+update.

### 2. Preview Changes (Dry Run)
```bash
php artisan permission:simplify-advanced --dry-run
```
Melihat preview perubahan tanpa melakukan perubahan.

### 3. Sync Permissions from Routes
```bash
php artisan permission:sync-routes
```
Menambahkan permission baru dari routes yang belum terdaftar.

## ⚠️ Catatan Penting

1. **Tidak Ada Fungsi yang Hilang**: Semua fungsi tetap berjalan normal
2. **Permission Mapping Otomatis**: Sistem otomatis memetakan store→create dan update→edit
3. **Wildcard Permission**: Permission dengan `.*` tetap berfungsi untuk full access
4. **Backward Compatible**: Route yang menggunakan store/update tetap berfungsi

## 🧪 Testing

Untuk memastikan semua berfungsi:
1. Login dengan user yang punya permission `create`
2. Coba akses route `create` → ✅ Harus bisa
3. Coba akses route `store` → ✅ Harus bisa (karena mapping)
4. Login dengan user yang punya permission `edit`
5. Coba akses route `edit` → ✅ Harus bisa
6. Coba akses route `update` → ✅ Harus bisa (karena mapping)


