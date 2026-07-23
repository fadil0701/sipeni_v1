# 05 — Mengelola Role, Permission, dan User

Panduan ini ditulis untuk **Administrator** (Admin IT) dan **Super Administrator**: cara mengatur siapa boleh melihat apa di SI-MANTIK, tanpa harus membaca kode.

> Menu **Panduan Pengguna** di aplikasi **hanya** untuk role Administrator / Super Administrator. Operator lain tidak melihat menu ini.

---

## 1. Analogi sederhana

| Istilah | Analogi | Di SI-MANTIK |
|---------|---------|--------------|
| **User** | Kartu identitas orang | Akun login (email + password) |
| **Role** | Jabatan di sistem | Contoh: Admin Unit, Teknisi IT, Pengurus Barang |
| **Permission** | Kunci pintu ruangan | Nama sama dengan **nama route** halaman (mis. `maintenance.service-report.index`) |
| **Modul menu** | Denah gedung di papan petunjuk | Centang di user: Transaksi, Inventory, Pemeliharaan, … |

Satu user bisa punya **lebih dari satu role**. Permission dari semua role digabung.

---

## 2. Alur kerja yang disarankan

```
1. Sync permission dari route  →  2. Siapkan Role + centang permission
         ↓
3. Buat / edit User  →  pilih Role  →  centang Modul menu
         ↓
4. User login  →  sidebar & halaman sesuai pengaturan
```

### 2.1 Setelah menambah fitur baru (route baru)

Di server Docker:

```bash
docker compose exec app php artisan permission:sync-routes
```

Lalu buka **Akses & Kontrol → Role**, centang permission baru pada role yang tepat.

### 2.2 Role teknisi (ATEM / IT)

| Kebutuhan | Yang dilakukan |
|-----------|----------------|
| Bisa buka Daftar Permintaan, Laporan Servis, Jadwal, Kalibrasi | Assign role `teknisi_atem` atau `teknisi_it` |
| Nama muncul di dropdown Teknisi Internal di form Service Report | Di **Master Pegawai**, set jabatan **ATEM (Teknisi Alat Kesehatan)** atau **Admin IT/IT Support (Teknisi IT)** |
| Menu Pemeliharaan tampil | Centang modul **maintenance** / Pemeliharaan pada user |

Seed role + permission (jika belum):

```bash
docker compose exec app php artisan db:seed --class=TeknisiMaintenanceRoleSeeder
```

---

## 3. Manajemen Role (Akses & Kontrol → Role)

### Yang boleh Anda atur

- Nama tampilan & deskripsi role
- Daftar **permission** (dikelompokkan per modul)
- Beberapa aksi digabung di UI agar tidak membingungkan (`store`→create, `update`→edit, dll.)

### Tips

- Jangan berikan permission admin sistem ke role operasional (unit/gudang/teknisi).
- Untuk teknisi: cukup prefix pemeliharaan + lihat register aset bila perlu — jangan inventory penuh kecuali diminta.
- **Pengurus Barang** biasanya lebih luas (gudang + disposisi + pemeliharaan oversight).

### Delegasi

Jika yang mengedit role **bukan** admin penuh, daftar centang dibatasi ke permission yang ia sendiri boleh berikan (lihat juga [HAK_AKSES_ROLE_DAN_DELEGASI.md](../HAK_AKSES_ROLE_DAN_DELEGASI.md)).

---

## 4. Manajemen User (Akses & Kontrol → Users)

Untuk setiap akun, pastikan tiga hal:

1. **Aktif** — bisa login.
2. **Role** — menentukan permission.
3. **Modul menu** — menentukan grup sidebar yang tampil.
4. **Unit kerja** (jika role scoped unit) — membatasi data ke unit tersebut.

### Checklist teknisi baru

- [ ] User dibuat / aktif
- [ ] Role: `teknisi_atem` **atau** `teknisi_it`
- [ ] Modul: **Pemeliharaan** (dan Aset jika perlu)
- [ ] Pegawai terkait: jabatan ATEM atau IT Support
- [ ] Uji login: sidebar menampilkan Daftar Permintaan & Laporan Servis

### Checklist Admin Unit baru

- [ ] Role `admin_unit`
- [ ] Modul: Transaksi, Inventory, Aset, Planning (sesuai kebutuhan)
- [ ] Unit kerja terisi dengan benar

---

## 5. Permission = nama route

Contoh permission pemeliharaan:

| Permission | Artinya |
|------------|---------|
| `maintenance.daftar-permintaan-pemeliharaan.index` | Buka daftar kerja teknisi |
| `maintenance.service-report.create` | Form tambah laporan servis |
| `maintenance.jadwal-maintenance.index` | Daftar jadwal |
| `maintenance.kalibrasi-aset.index` | Daftar kalibrasi |
| `maintenance.permintaan-pemeliharaan.index` | Permintaan dari sisi unit (Transaksi) |

Wildcard Spatie dapat dipakai pada role tertentu; baseline seeder biasanya memakai daftar eksplisit / prefix.

---

## 6. Bypass Super Administrator

Role **`super_administrator`** (dan flag `users.is_superadmin` bila diaktifkan) **melewati** pemeriksaan permission biasa. Gunakan hemat — untuk admin sistem, bukan operator harian.

Role **`admin` / `administrator`** (Admin IT) **tidak** otomatis bypass seperti super admin; haknya mengikuti permission di database (sering hampir penuh untuk kelola sistem).

---

## 7. Masalah umum & solusi

| Gejala | Cek |
|--------|-----|
| Menu tidak muncul | Modul user belum dicentang |
| Menu ada, halaman 403 | Permission role belum ada / belum sync-routes |
| Dropdown teknisi kosong | Jabatan pegawai belum ATEM / IT Support |
| Seed gagal host `mysql` | Jalankan Artisan **di dalam** container: `docker compose exec app …` |
| Role teknisi tidak ada di daftar | Jalankan `TeknisiMaintenanceRoleSeeder` |

---

## 8. Referensi teknis

- Matriks role: [04-matrik-akses-role.md](./04-matrik-akses-role.md)
- Detail teknis RBAC & delegasi: [../HAK_AKSES_ROLE_DAN_DELEGASI.md](../HAK_AKSES_ROLE_DAN_DELEGASI.md)
- Mapping role legacy: [../rbac-phase1/role-mapping.md](../rbac-phase1/role-mapping.md)
- Panduan per role: [per-role/README.md](./per-role/README.md)
