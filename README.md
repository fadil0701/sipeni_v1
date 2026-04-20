# SI-MANTIK

## Sistem Informasi Manajemen Terintegrasi

Sistem manajemen aset dan inventori terintegrasi, dengan alur dari perencanaan (RKU), pengadaan, inventori, transaksi barang, aset/KIR, pemeliharaan, hingga laporan. Antarmuka utama berbasis **Laravel Blade** dengan layout tunggal dan menu dinamis sesuai **peran (role)** pengguna.

---

## Teknologi

| Lapisan | Teknologi |
|--------|-----------|
| Backend | PHP 8.3, **Laravel 13.x** |
| Database | MySQL 8+ (konfigurasi umum; dukungan PostgreSQL bergantung driver) |
| Frontend | **Vite**, **Tailwind CSS**, JavaScript (Alpine-style interaksi di layout), **Choices.js** (select pencarian) |
| Ekspor | Maatwebsite/Excel |
| QR | SimpleSoftwareIO/simple-qrcode |

---

## Fitur terbaru (master perencanaan)

- **Program**: field **`kode_program`** (unik) dan **nama program** pada form tambah/ubah, daftar, dan detail.
- **Kegiatan**: field **`kode_kegiatan`** (unik) bersama pemilihan program (menampilkan kode — nama), **nama kegiatan**, pada form tambah/ubah, daftar, dan detail.
- **Sub kegiatan**: tetap memakai **`kode_sub_kegiatan`** seperti sebelumnya.

Migrasi database: `2026_04_06_000001_add_kode_program_and_kode_kegiatan`. Setelah deploy, jalankan `php artisan migrate`.

---

## Ringkasan modul (sesuai menu samping)

Menu yang terlihat **bergantung pada hak akses** (`PermissionHelper` / konfigurasi menu per role). Daftar berikut mencerminkan grup di `resources/views/layouts/app.blade.php` dan halaman Blade terkait.

### Autentikasi

- **Login** — `resources/views/auth/login.blade.php` (`/login`)

### Beranda & portal pengguna

- **Dashboard** — `user/dashboard.blade.php` (`/`)
- **Aset** (daftar & detail) — `user/assets/index.blade.php`, `user/assets/show.blade.php` (`/assets`, `/assets/{id}`)
- **Permintaan** — `user/requests/index|create|show.blade.php` (`/requests`, …)

### Master Manajemen

| Menu | Tampilan (folder `resources/views`) |
|------|-------------------------------------|
| Master Pegawai | `master-manajemen/master-pegawai/` |
| Master Jabatan | `master-manajemen/master-jabatan/` |
| Unit Kerja | `master/unit-kerja/` |
| Gudang | `master/gudang/` |
| Ruangan | `master/ruangan/` |
| Program | `master/program/` — index, create, edit, show |
| Kegiatan | `master/kegiatan/` — index, create, edit, show |
| Sub Kegiatan | `master/sub-kegiatan/` — index, create, edit, show |

### Master Data

| Menu | Tampilan |
|------|----------|
| Aset | `master-data/aset/` |
| Kode Barang | `master-data/kode-barang/` |
| Kategori Barang | `master-data/kategori-barang/` |
| Jenis Barang | `master-data/jenis-barang/` |
| Subjenis Barang | `master-data/subjenis-barang/` |
| Data Barang | `master-data/data-barang/` |
| Satuan | `master-data/satuan/` |
| Sumber Anggaran | `master-data/sumber-anggaran/` |

### Inventory

| Menu | Tampilan |
|------|----------|
| Data Stock | `inventory/data-stock/index.blade.php` |
| Data Inventory | `inventory/data-inventory/` (index, create, edit, show) |
| Stock Adjustment | `inventory/stock-adjustment/` |
| Item inventori (registrasi per unit) | `inventory/inventory-item/edit.blade.php` |

### Permintaan & persetujuan

| Alur | Tampilan |
|------|----------|
| Permintaan Barang | `transaction/permintaan-barang/` |
| Approval Permintaan Barang | `transaction/approval/` (index, show, **diagram** `approval/diagram.blade.php`) |
| Draft Distribusi | `transaction/draft-distribusi/` |
| Compile / SBBK | `transaction/compile-distribusi/` |
| Distribusi (monitoring) | `transaction/distribusi/` |
| Penerimaan Barang | `transaction/penerimaan-barang/` |
| Retur Barang | `transaction/retur-barang/` |
| Pemakaian Barang | `transaction/pemakaian-barang/` |

### Aset & KIR

| Menu | Tampilan |
|------|----------|
| Register Aset | `asset/register-aset/` (+ `unit-kerja/show.blade.php`) |
| Kartu Inventaris Ruangan (KIR) | `asset/kartu-inventaris-ruangan/` |
| Mutasi Aset | `asset/mutasi-aset/` |

### Perencanaan

| Menu | Tampilan |
|------|----------|
| Status RKU / RKU | `planning/rku/` (index, show) |
| Rekap Tahunan | `planning/rekap-tahunan.blade.php` |

### Pengadaan

| Menu | Tampilan |
|------|----------|
| Proses Pengadaan | `procurement/proses-pengadaan/index.blade.php` (+ show via controller) |
| Paket Pengadaan | `procurement/paket-pengadaan/` |

### Keuangan

| Menu | Catatan |
|------|---------|
| Pembayaran | Route `finance/pembayaran` — pastikan controller dan view diisi jika modul akan dipakai penuh |

### Pemeliharaan (submenu + tautan dari Permintaan/Approval)

| Menu | Tampilan |
|------|----------|
| Permintaan Pemeliharaan | `maintenance/permintaan-pemeliharaan/` |
| Jadwal Maintenance | `maintenance/jadwal-maintenance/index.blade.php` |
| Kalibrasi Aset, Service Report | Route ada di `routes/web.php`; pastikan view/controller lengkap bila dipakai |

### Laporan

| Menu | Tampilan |
|------|----------|
| Laporan (indeks) | `report/index.blade.php` |
| Laporan Stock Gudang | `report/stock-gudang.blade.php` |

### Admin (role admin)

| Menu | Tampilan |
|------|----------|
| Manajemen Role | `admin/roles/` |
| Manajemen User | `admin/users/` |

### Layout & komponen bersama

- **Layout utama**: `layouts/app.blade.php` (sidebar biru, header, breadcrumb, notifikasi placeholder, menu pengguna).
- **Paginasi**: `components/pagination-per-page.blade.php`.

---

## Fitur khusus (bisnis)

- **Auto Register Aset**: saat inventori dengan jenis aset dan kuantitas N, sistem dapat membentuk entri per unit (lihat dokumentasi proyek terkait).
- **Role-Based Access Control**: banyak route dibungkus middleware `role:...`; menu disaring lewat `accessibleMenus`.
- **Alur approval** permintaan barang multi-level (Kepala Unit, Kasubbag TU, Kepala Pusat, Admin Gudang, dll.).

---

## Instalasi (lokal)

1. **Clone & masuk folder**

```bash
cd si-peni
```

2. **Dependensi**

```bash
composer install
npm install
```

3. **Environment**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Database** — sesuaikan `.env` (contoh):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simantik
DB_USERNAME=...
DB_PASSWORD=...
```

5. **Migrasi**

```bash
php artisan migrate
```

6. **User admin** — buat pengguna dan peran sesuai kebutuhan (mis. seeder atau modul **Admin → Manajemen User** setelah ada akun pertama).

7. **Asset front-end**

```bash
npm run build
# atau pengembangan:
npm run dev
```

8. **Jalankan aplikasi**

```bash
php artisan serve
```

Buka URL yang ditampilkan (biasanya `http://127.0.0.1:8000`) dan login.

---

## Docker (folder induk `PPKP - CMS`)

Orkestrasi menggunakan `docker-compose.yml` di **`D:\PPKP - CMS`** (service **si-peni**, image dari `si-peni/Dockerfile`). Port publik default dapat disetel di Compose (mis. **9002** → 80 di container). Setelah container berjalan:

```bash
docker exec si-peni-web php artisan migrate --force
docker exec si-peni-web php artisan view:clear
```

Pastikan `APP_URL` di `.env` sesuai host/port yang dipakai (mis. `http://localhost:9002`).

---

## Struktur project (ringkas)

```
app/
├── Http/Controllers/   # Modul per domain (Master, Inventory, Transaction, …)
├── Models/
├── Helpers/            # PermissionHelper, ImageHelper, …
resources/views/        # Blade per modul (lihat tabel di atas)
database/migrations/
routes/web.php          # Definisi route & middleware role
```

---

## Dokumentasi tambahan (di repositori)

- [DASHBOARD MODEL.MD](./DASHBOARD%20MODEL.MD)
- [ERD SISTEM.MD](./ERD%20SISTEM.MD)
- [FLOW AUTO ADD ROW.MD](./FLOW%20AUTO%20ADD%20ROW.MD)
- [TEKNOLOGI.MD](./TEKNOLOGI.MD)

---

## Support

Untuk pertanyaan teknis, hubungi tim pengembang.

---

**SI-MANTIK** — Sistem Informasi Manajemen Terintegrasi © 2026
