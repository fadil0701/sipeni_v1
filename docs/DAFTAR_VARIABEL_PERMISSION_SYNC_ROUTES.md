# Daftar Variabel Output `permission:sync-routes`

Dokumen ini menjelaskan elemen/variabel yang muncul pada output command:

- `php artisan permission:sync-routes`

## Ringkasan Proses Command

Command ini melakukan:

1. Scan seluruh route aplikasi.
2. Membandingkan route name dengan data permission di database.
3. Menampilkan permission yang belum ada (`missing`).
4. Meminta konfirmasi.
5. Menambahkan permission baru ke database.

## Variabel/Kolom pada Tabel Output

Output utama menampilkan 4 kolom:

| Kolom | Fungsi |
| --- | --- |
| `Name` | Kunci unik permission (biasanya sama dengan nama route), contoh: `transaction.distribusi.show`. Ini yang dipakai saat pengecekan akses (`can`, middleware, role-permission mapping). |
| `Display Name` | Label manusiawi untuk ditampilkan di UI/admin panel, contoh: `View Detail Distribusi`. Memudahkan admin saat mengelola role & permission. |
| `Module` | Kategori modul besar, contoh: `transaction`, `inventory`, `master-data`, `admin`. Dipakai untuk pengelompokan menu/manajemen hak akses. |
| `Group` | Kelompok lebih spesifik di dalam modul, contoh: `transaction.distribusi`, `master-data.aset`. Umumnya dipakai untuk grouping pada halaman manajemen permission. |

## Variabel Status Ringkasan di Output Console

| Bagian Output | Fungsi |
| --- | --- |
| `Ditemukan 278 permission yang missing` | Jumlah permission yang terdeteksi belum ada di tabel permissions. |
| `Apakah Anda ingin menambahkan ... (yes/no)` | Konfirmasi interaktif sebelum write ke database. |
| `278/278 [============================] 100%` | Progress bar proses insert/update permission. |
| `Berhasil menambahkan 278 permission` | Hasil akhir proses sinkronisasi berhasil. |

## Pola Nilai `Name` (Permission Key)

Format umum:

- `<area>.<resource>.<action>`

Contoh:

- `master-data.aset.index`
- `transaction.retur-barang.update`
- `maintenance.service-report.destroy`

Makna action umum:

- `index`: lihat daftar data
- `show`: lihat detail
- `create`: buka form tambah
- `store`: simpan data baru
- `edit`: buka form edit
- `update`: simpan perubahan
- `destroy`: hapus data
- aksi khusus lain: `approve`, `reject`, `ajukan`, `kirim`, `proses`, `export`, dll.

## Catatan Penting

- Output tersebut **normal** dan menunjukkan sinkronisasi berhasil.
- Setelah permission ditambahkan, role yang membutuhkan akses tetap harus diberi permission terkait (manual/seed/fitur assign role).
- Jika command dijalankan lagi tanpa route baru, biasanya jumlah missing akan 0 atau jauh berkurang.

## Kenapa Terlihat Ada Yang "Sama"?

Ini biasanya terjadi karena beberapa pola berikut (dan ini normal):

1. `Display Name` sama, tetapi `Name` berbeda  
   Contoh: banyak baris bertuliskan `Create ...` untuk route `create` dan `store`.
2. `action` mirip dalam satu resource  
   Misalnya `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`.
3. Resource berbeda tetapi label mirip  
   Misalnya `View Detail` muncul di banyak modul.

Poin penting: yang dipakai sistem untuk validasi akses adalah **`Name`** (permission key), bukan hanya `Display Name`.

## Panduan Praktis Agar Admin Mudah Atur Role

Supaya tidak bingung saat assign permission, gunakan aturan ini:

| Fokus Saat Assign | Yang Dipakai | Alasan |
| --- | --- | --- |
| Identitas unik | `Name` | Tidak duplikat, pasti menunjuk 1 route/aksi spesifik. |
| Kelompok menu/modul | `Module` + `Group` | Memudahkan admin centang per area kerja (contoh: `transaction.distribusi`). |
| Label tampilan | `Display Name` | Hanya untuk memudahkan baca, jangan dijadikan patokan unik. |

### Rekomendasi Paket Permission per Role

Untuk role operasional, grouping per `Group` biasanya paling aman:

- **Viewer**: `index`, `show`
- **Editor**: `index`, `show`, `create`, `store`, `edit`, `update`
- **Approver/Processor**: tambah aksi khusus seperti `approve`, `reject`, `ajukan`, `kirim`, `proses`
- **Admin Penuh**: semua termasuk `destroy` (khusus role tepercaya)

## Saran Normalisasi (Opsional, untuk jangka panjang)

Jika ingin makin rapi di panel admin:

- Bedakan `Display Name` untuk pasangan aksi form vs submit:  
  `create` = "Open Create Form", `store` = "Store New Data"  
  `edit` = "Open Edit Form", `update` = "Update Existing Data"
- Tambahkan filter UI berdasarkan `Module` dan `Group`.
- Sembunyikan aksi teknis/API dari user non-teknis jika ada.

Dengan pola ini, meskipun label terlihat mirip, admin tetap bisa mengatur role akses dengan konsisten dan minim salah assign.

## Section Pemilihan Role (Template Praktis Admin)

Bagian ini bisa dijadikan acuan saat memilih permission untuk role `administrator`, `approver`, `operator`, dan `user`.

### Variabel Kunci Saat Memilih Permission

| Variabel | Cara Pakai untuk Seleksi Role |
| --- | --- |
| `Name` | Variabel utama untuk assign akses. Selalu pastikan keputusan role berdasarkan `Name`, bukan hanya label. |
| `Display Name` | Dipakai untuk memudahkan baca. Jika sama, cek `Name` dan `Group` agar tidak salah pilih. |
| `Module` | Filter area kerja besar (contoh: `transaction`, `inventory`, `master-data`, `admin`). |
| `Group` | Filter fitur spesifik dalam modul (contoh: `transaction.distribusi`, `master-data.aset`). |
| `Action` (turunan dari `Name`) | Menentukan level hak: baca (`index/show`), input (`create/store`), ubah (`edit/update`), hapus (`destroy`), proses (`approve/reject/ajukan/kirim/proses`). |

### Template Role dan Variabel Permission yang Direkomendasikan

| Role | Scope Umum | Variabel/Action yang Direkomendasikan |
| --- | --- | --- |
| `administrator` | Akses penuh lintas modul | Semua action termasuk `destroy` + aksi proses (`approve`, `reject`, `kirim`, `proses`, dll). |
| `approver` | Persetujuan proses | `index`, `show`, `approve`, `reject`, `disposisi`, `kembalikan`, plus action baca terkait modul approval. |
| `operator` | Input & operasional harian | `index`, `show`, `create`, `store`, `edit`, `update`, `ajukan`, `kirim`, `proses` (sesuai modul kerja). |
| `user` | Akses terbatas (self-service) | `index`, `show`, dan bila perlu `create` + `store` pada fitur pengajuan milik user. Hindari `destroy` dan approval action. |

### Aturan Pairing Action (Sangat Disarankan)

- `create` harus dipasangkan dengan `store`
- `edit` harus dipasangkan dengan `update`
- `index` umumnya dipasangkan dengan `show`
- `destroy` dipisah ketat (jangan otomatis ikut paket operator)

### Checklist Cepat Sebelum Simpan Role

1. Sudah filter `Module` dan `Group` sesuai area kerja role.
2. Sudah verifikasi berdasarkan `Name` (bukan label saja).
3. Pairing action sudah lengkap (`create/store`, `edit/update`).
4. Tidak memberi `destroy` ke role non-admin.
5. Role approver tidak diberi action operasional yang tidak diperlukan.
