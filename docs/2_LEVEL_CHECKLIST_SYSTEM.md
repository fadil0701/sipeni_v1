# Dokumentasi Sistem 2 Level Checklist

## 📋 Konsep

Sistem ini menggunakan 2 level checklist untuk mengatur hak akses:

1. **Level 1: Manajemen User** - Checklist menu utama yang dapat diakses user
2. **Level 2: Manajemen Role** - Checklist sub-menu dan permission detail untuk menu yang dipilih di user

## 🔄 Alur Kerja

```
1. Admin buka Manajemen User
   └─ Checklist menu: ☑ Inventory, ☑ Transaction, ☑ Asset
   
2. Admin buka Manajemen Role
   └─ Sistem otomatis:
      ├─ Cari user yang menggunakan role ini
      ├─ Ambil modules dari user tersebut
      └─ Tampilkan hanya permission untuk modules tersebut
   
3. Admin checklist permission detail:
   └─ Inventory
      ├─ Data Stock
      │  ├─ ☑ View
      │  ├─ ☑ Create
      │  └─ ☐ Delete
      └─ Data Inventory
         ├─ ☑ View
         └─ ☑ Create
```

## 📊 Struktur Database

### Tabel `modules`
Menyimpan daftar menu utama:
- `name` (PK): 'inventory', 'transaction', 'asset', dll
- `display_name`: 'Inventory', 'Transaksi', 'Aset & KIR'
- `description`: Deskripsi menu
- `icon`: Icon untuk menu
- `sort_order`: Urutan tampilan

### Tabel `user_modules`
Pivot table untuk relasi user dan modules:
- `user_id` (FK)
- `module` (FK ke modules.name)
- Unique constraint: `(user_id, module)`

## 🎯 Cara Penggunaan

### 1. Assign Menu ke User

**Langkah:**
1. Buka **Manajemen User**
2. Pilih atau buat user
3. Di section **"Menu yang Dapat Diakses"**, checklist menu yang diinginkan:
   - ☑ Inventory
   - ☑ Transaction
   - ☑ Asset & KIR
   - ☐ Maintenance
   - ☐ Planning
4. Klik **Simpan**

**Hasil:**
- User sekarang memiliki akses ke menu yang dipilih
- Menu akan muncul di sidebar user

### 2. Assign Permission Detail ke Role

**Langkah:**
1. Buka **Manajemen Role**
2. Pilih role yang digunakan oleh user
3. Sistem akan otomatis menampilkan:
   - Info modules yang dipilih user
   - Permission yang tersedia untuk modules tersebut
4. Checklist permission detail yang diinginkan:
   - Inventory
     - Data Stock
       - ☑ View Data Stock
       - ☑ Create Data Stock
       - ☐ Delete Data Stock
     - Data Inventory
       - ☑ View Data Inventory
       - ☑ Create Data Inventory
5. Klik **Simpan**

**Hasil:**
- Role sekarang memiliki permission detail
- User dengan role tersebut bisa melakukan action sesuai permission

## 🔍 Fitur

### 1. Auto Filter Permission
Saat edit role, sistem otomatis:
- Mencari semua user yang menggunakan role tersebut
- Mengambil modules dari user-user tersebut
- Menampilkan hanya permission untuk modules yang dipilih user

### 2. Visual Indicator
- **Info Box**: Menampilkan modules yang dipilih user
- **Warning Box**: Jika tidak ada user atau user tidak punya modules
- **Module Badges**: Menampilkan modules yang tersedia

### 3. Select All
- **Select All Modules**: Checklist semua menu di form user
- **Select All Permissions**: Checklist semua permission di form role
- **Select All per Module**: Checklist semua permission dalam satu module

## 📝 Contoh Skenario

### Skenario 1: Admin Gudang Standar

**Step 1: Assign Menu ke User**
```
User: Budi Santoso
Menu:
☑ Inventory
☑ Transaction
☑ Asset & KIR
```

**Step 2: Assign Permission ke Role**
```
Role: Admin Gudang
Modules tersedia: Inventory, Transaction, Asset & KIR

Permission:
☑ inventory.data-stock.*
☑ inventory.data-inventory.*
☑ transaction.distribusi.*
☑ transaction.approval.disposisi
☑ asset.register-aset.*
```

**Hasil:**
- User bisa akses menu Inventory, Transaction, Asset & KIR
- User bisa melakukan semua action sesuai permission

### Skenario 2: Admin Gudang + Maintenance

**Step 1: Assign Menu ke User**
```
User: Budi Santoso
Menu:
☑ Inventory
☑ Transaction
☑ Asset & KIR
☑ Maintenance  ← Ditambahkan
```

**Step 2: Assign Permission ke Role**
```
Role: Admin Gudang
Modules tersedia: Inventory, Transaction, Asset & KIR, Maintenance

Permission:
☑ inventory.*
☑ transaction.*
☑ asset.*
☑ maintenance.permintaan-pemeliharaan.*  ← Muncul otomatis
☑ maintenance.jadwal-maintenance.*
```

**Hasil:**
- User bisa akses menu Inventory, Transaction, Asset & KIR, Maintenance
- User bisa melakukan semua action sesuai permission

## ⚠️ Catatan Penting

1. **Urutan Penting**: Assign menu ke user terlebih dahulu, baru assign permission ke role
2. **Auto Filter**: Permission di role hanya menampilkan permission untuk modules yang dipilih user
3. **Multiple Users**: Jika role digunakan oleh beberapa user dengan modules berbeda, semua modules akan ditampilkan
4. **Menu vs Permission**: 
   - Menu menentukan menu apa yang muncul di sidebar
   - Permission menentukan action apa yang bisa dilakukan

## 🔧 Troubleshooting

### Problem: Permission tidak muncul di form role
**Solusi:**
- Pastikan user sudah di-assign menu di Manajemen User
- Pastikan user menggunakan role tersebut
- Refresh halaman

### Problem: Menu tidak muncul di sidebar
**Solusi:**
- Pastikan user sudah di-assign menu di Manajemen User
- Pastikan role user memiliki permission untuk menu tersebut
- Clear cache: `php artisan view:clear`

### Problem: Permission tidak berfungsi
**Solusi:**
- Pastikan permission sudah di-assign ke role
- Pastikan user menggunakan role tersebut
- Pastikan menu sudah di-assign ke user
- Check PermissionHelper::canAccess() untuk debugging

## 📚 File yang Terlibat

### Models
- `app/Models/Module.php` - Model untuk modules
- `app/Models/User.php` - Updated dengan relationship modules

### Controllers
- `app/Http/Controllers/Admin/UserController.php` - Handle modules assignment
- `app/Http/Controllers/Admin/RoleController.php` - Filter permission berdasarkan modules

### Views
- `resources/views/admin/users/create.blade.php` - Form checklist menu
- `resources/views/admin/users/edit.blade.php` - Form checklist menu
- `resources/views/admin/roles/edit.blade.php` - Form permission dengan filter

### Helpers
- `app/Helpers/PermissionHelper.php` - Updated untuk filter menu berdasarkan modules

### Migrations
- `database/migrations/2026_01_22_145613_create_modules_table.php`
- `database/migrations/2026_01_22_145616_create_user_modules_table.php`

### Seeders
- `database/seeders/ModuleSeeder.php` - Seed data modules


