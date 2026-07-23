# 02 — Modul & Fitur Lengkap

Dokumen ini menjelaskan **setiap grup menu** di sidebar SI-MANTIK: kegunaan, halaman utama, dan role yang umumnya menggunakannya.

> Akses aktual bergantung permission yang di-assign ke role Anda — bukan hanya nama role.

---

## Dashboard

| Item | Keterangan |
|------|------------|
| **Menu** | Dashboard (MAIN) |
| **Route** | `user.dashboard` |
| **Kegunaan** | Halaman awal setelah login; ringkasan, pintasan, dan link **Panduan Pengguna** sesuai role |
| **Role umum** | Semua user login |

### Panduan Pengguna (sidebar)

| Item | Keterangan |
|------|------------|
| **Menu** | Panduan Pengguna (MAIN) |
| **Route** | `panduan.index`, `panduan.show` |
| **Kegunaan** | Panduan operasional per role + bab umum (modul, alur kerja, matriks akses); unduh PDF |
| **Role umum** | Semua user login — bab role otomatis disesuaikan |

---

## Transaksi

Modul operasional **unit kerja** dan permintaan lintas unit.

### Permintaan Barang

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Mengajukan kebutuhan barang persediaan/farmasi dari unit ke gudang pusat |
| **Halaman** | Daftar, Buat, Edit, Detail, Ajukan |
| **Role umum** | `admin_unit`, `kepala_unit` |
| **Fitur utama** | Multi-item, jenis permintaan (Persediaan/Farmasi), tipe Rutin/CITO, cek stok referensi, draft & ajukan |

**Langkah singkat:** Transaksi → Permintaan Barang → Tambah → isi header + detail barang → Simpan Draft / Ajukan.

### Permintaan Pemeliharaan

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Permintaan servis/pemeliharaan aset |
| **Halaman** | CRUD + ajukan |
| **Role umum** | `admin_unit`, `kepala_unit` |

### Peminjaman Barang

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Pinjam barang antar unit (unit peminjam ↔ unit pemilik) |
| **Halaman** | Buat permintaan, verifikasi unit A/B, approval pengurus, serah terima |
| **Role umum** | `admin_unit`, `kepala_unit`, `pengurus_barang`, `kasubbag_tu` |

### Pengembalian Barang

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Pengembalian barang hasil peminjaman |
| **Halaman** | Daftar, form pengembalian |
| **Role umum** | `admin_unit`, `kepala_unit` |

### Daftar RKU

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Rencana Kebutuhan Unit — perencanaan kebutuhan barang/jasa tahunan |
| **Halaman** | Index, create, edit, show, submit |
| **Role umum** | `admin_unit` (input), `perencana`, `kasubbag_tu`, `kepala_pusat` (review) |

---

## Approval

| Item | Keterangan |
|------|------------|
| **Kegunaan** | Persetujuan multi-level permintaan barang |
| **Halaman** | Riwayat / Status Approval, detail, aksi approval |
| **Role umum** | `kepala_unit`, `kasubbag_tu`, `kepala_pusat`, `pengurus_barang` |

**Aksi per level:**

| Level | Role | Aksi |
|-------|------|------|
| 1 | Kepala Unit | Mengetahui |
| 2 | Kasubbag TU | Verifikasi / Kembalikan |
| 3 | Kepala Pusat | Setujui / Tolak |
| 4 | Pengurus Barang | Disposisi ke gudang kategori |

---

## Perencanaan

| Submenu | Kegunaan |
|---------|----------|
| **Program** | Master program kerja |
| **Kegiatan** | Master kegiatan under program |
| **Sub Kegiatan** | Master sub kegiatan |
| **RKU & Aktivitas** | Kelola RKU (overlap dengan Transaksi → Daftar RKU) |
| **Rekap Tahunan** | Rekapitulasi kebutuhan tahun berjalan |

**Role umum:** `perencana`, `admin_unit`, `pptk_apbd`, `pptk_blud`, `kasubbag_tu`, `kepala_pusat`

---

## Pengadaan

| Submenu | Kegunaan |
|---------|----------|
| **Paket Pengadaan** | Definisi paket pengadaan barang/jasa |
| **Proses & Realisasi Pengadaan** | Monitoring pelaksanaan pengadaan |

**Role umum:** `pengadaan`, `pptk_apbd`, `pptk_blud`

**Hubungan:** RKU disetujui → masuk proses pengadaan → barang diterima di gudang pusat → stok tersedia untuk distribusi.

---

## Distribusi Barang

Modul **Pengurus Barang** — fulfillment setelah permintaan disetujui.

| Submenu | Kegunaan |
|---------|----------|
| **Daftar Permintaan** | Draft distribusi / proses disposisi per item |
| **Distribusi Barang (SBBK)** | Surat Bukti Barang Keluar — buat dari disposisi, proses, kirim, cetak PDF |
| **Penerimaan Barang** | Unit menerima & verifikasi barang datang |
| **Retur Barang Rusak** | Retur barang cacat/rusak ke gudang |

**Catatan:** Tahap **Compile SBBK** terpisah sudah digabung ke **Distribusi Barang (SBBK)**. Route `transaction.compile-distribusi.*` hanya redirect ke menu Distribusi (kompatibilitas lama).

**Role umum:** `pengurus_barang`, `admin_gudang_pusat`, admin gudang kategori (proses), `admin_unit`/`kepala_unit` (penerimaan & retur)

---

## Inventory

### Master Data

| Submenu | Kegunaan |
|---------|----------|
| Satuan | Satuan ukuran barang |
| Sumber Anggaran | APBD, BLUD, dll. |
| Gudang | Master gudang (pusat/unit, kategori) |

### Struktur Barang

| Submenu | Kegunaan |
|---------|----------|
| Klasifikasi Aset | Hierarki klasifikasi |
| Kode / Kategori / Jenis / Subjenis Barang | Taksonomi barang |
| Data Barang | Master item (nama, spesifikasi) |
| Import Struktur | Import massal struktur barang |

### Data Inventory

| Submenu | Kegunaan |
|---------|----------|
| Data Inventory | Record inventory per gudang (batch, harga, dll.) |
| Import Data Inventory | Import massal |
| Scan QR Code | Identifikasi item via QR |

### Stok & Transaksi

| Submenu | Kegunaan |
|---------|----------|
| Data Stock | Posisi stok per barang per gudang |
| Kartu Stok | Kartu stok per barang (mutasi masuk/keluar) |
| Stock Adjustment / Opname | Penyesuaian & opname stok |
| Reminder Kedaluwarsa | Monitor barang farmasi mendekati expired |

**Role umum:** Admin gudang pusat/kategori (full pusat); `admin_unit` (stok unit, scoped)

---

## Aset

| Submenu | Kegunaan |
|---------|----------|
| **Register Aset & Rincian** | Pencatatan aset tetap, nomor register |
| **Dokumen KIR (Cetak)** | Generate Kartu Inventaris Ruangan |
| **Mutasi Aset** | Perpindahan aset antar ruangan/unit |

**Role umum:** `pengurus_barang`, `admin_gudang_pusat`, `admin_gudang_aset`, `admin_unit` (register unit)

---

## Pemeliharaan

| Submenu | Kegunaan |
|---------|----------|
| **Jadwal Pemeliharaan** | Jadwal servis preventif (+ generate permintaan) |
| **Kalibrasi** | Kalibrasi alat/instrumen |
| **Laporan Servis** | Dokumentasi hasil servis |

**Role umum:** Tim maintenance/asset; permintaan dari unit via Transaksi → Permintaan Pemeliharaan

---

## Keuangan

| Submenu | Kegunaan |
|---------|----------|
| **Pembayaran** | Pencatatan pembayaran terkait pengadaan |

**Role umum:** `keuangan`

---

## Monitoring

| Submenu | Kegunaan |
|---------|----------|
| **Ringkasan Laporan** | Dashboard laporan |
| **Laporan Stok Gudang** | Stok per gudang, export |

**Laporan tambahan** (via permission): Kartu Stok, Ringkasan Transaksi, Ringkasan Aset, Ringkasan Pemeliharaan

**Role umum:** `kasubbag_tu`, pengurus barang, super admin

---

## Organisasi

| Submenu | Kegunaan |
|---------|----------|
| **Pegawai** | Master pegawai + link user |
| **Jabatan** | Struktur jabatan organisasi |
| **Unit Kerja** | Master unit kerja |
| **Ruangan** | Master ruangan per unit |

**Role umum:** `super_administrator`, admin dengan permission master-manajemen

---

## Akses & Kontrol (Administrasi)

| Submenu | Kegunaan |
|---------|----------|
| **User & Account Directory** | CRUD user, assign role |
| **Role & Workflow Authority** | CRUD role, permission matrix, workflow permission |
| **Workflow Template** | Template cetak (jika `FEATURE_PRINT_TEMPLATES=true`) |
| **Executive Activity Timeline** | Audit trail aktivitas sistem |

**Role umum:** `super_administrator`, `admin`/`administrator` (permission-based)

---

## Fitur yang tidak aktif / placeholder

| Fitur | Status |
|-------|--------|
| Pemakaian Barang | Route dinonaktifkan |
| RKU Aktivitas (sub-modul terpisah) | Placeholder sidebar |
| Notifikasi bell (header) | UI placeholder |

---

## Dokumen terkait

- [03 — Alur Kerja Utama](./03-alur-kerja-utama.md)
- [04 — Matriks Akses Role](./04-matrik-akses-role.md)
