# 📋 Alur Transaksi Barang - Step by Step Guide

Dokumentasi lengkap penggunaan sistem transaksi barang mulai dari permintaan hingga penerimaan dan retur.

---

## 🎯 Overview Alur Transaksi

```
Permintaan Barang → Persetujuan → Proses Disposisi → Compile SBBK → Distribusi → Penerimaan → Retur (Opsional)
```

---

## 📝 STEP 1: Permintaan Barang

**Role yang dapat mengakses:** `pegawai`, `kepala_unit`, `kasubbag_tu`, `kepala_pusat`, `admin`

### Langkah-langkah:

1. **Akses Menu**
   - Login ke sistem
   - Pilih menu **Transaksi** → **Permintaan Barang**

2. **Buat Permintaan Barang**
   - Klik tombol **"Tambah Permintaan Barang"** atau **"Buat Permintaan Barang"**
   - Isi form permintaan:
     - **Unit Kerja**: Pilih unit kerja pemohon
     - **Pemohon**: Pilih pegawai yang mengajukan permintaan
     - **Tanggal Permintaan**: Tanggal pengajuan (otomatis terisi)
     - **Jenis Permintaan**: Pilih jenis (BARANG, ASET, atau keduanya)
     - **Keterangan**: Catatan tambahan (opsional)

3. **Tambah Detail Permintaan**
   - Klik **"Tambah Item"** untuk menambahkan barang yang diminta
   - Untuk setiap item, isi:
     - **Barang**: Pilih dari master data barang
     - **Qty Diminta**: Jumlah yang diminta
     - **Satuan**: Unit satuan barang
     - **Keterangan**: Catatan khusus item (opsional)
   - Ulangi untuk semua barang yang diminta
   - Klik **"Hapus"** jika ingin menghapus item

4. **Simpan Draft**
   - Klik **"Simpan sebagai Draft"** untuk menyimpan sementara
   - Status permintaan akan menjadi **DRAFT**
   - Draft dapat diedit atau dihapus sebelum diajukan

5. **Ajukan Permintaan**
   - Setelah semua item lengkap, klik **"Ajukan Permintaan"**
   - Status akan berubah menjadi **DIAJUKAN**
   - Permintaan akan masuk ke workflow approval

---

## ✅ STEP 2: Persetujuan Permintaan

**Role yang dapat mengakses:** `kepala_unit`, `kasubbag_tu`, `kepala_pusat`, `admin_gudang`, `admin`, dan role approval lainnya

### Alur Approval Multi-Level:

#### 2.1. Kepala Unit - Mengetahui
**Role:** `kepala_unit`, `admin`

- Akses menu **Transaksi** → **Persetujuan**
- Pilih permintaan dengan status **MENUNGGU** untuk kepala unit
- Klik **"Lihat Detail"** untuk melihat detail permintaan
- Klik **"Mengetahui"** untuk memberikan persetujuan
- Status berubah menjadi **DIKETAHUI**

#### 2.2. Kasubbag TU - Verifikasi
**Role:** `kasubbag_tu`, `admin`

- Akses menu **Transaksi** → **Persetujuan**
- Pilih permintaan dengan status **MENUNGGU** untuk kasubbag
- Klik **"Lihat Detail"** untuk melihat detail permintaan
- Pilih aksi:
  - **"Verifikasi"**: Setujui dan lanjutkan ke kepala pusat
  - **"Kembalikan"**: Kembalikan ke pemohon untuk perbaikan
- Status berubah menjadi **DIVERIFIKASI** atau **DITOLAK**

#### 2.3. Kepala Pusat - Approve/Reject
**Role:** `kepala_pusat`, `admin`

- Akses menu **Transaksi** → **Persetujuan**
- Pilih permintaan dengan status **MENUNGGU** untuk kepala pusat
- Klik **"Lihat Detail"** untuk melihat detail permintaan
- Pilih aksi:
  - **"Approve"**: Setujui permintaan
  - **"Reject"**: Tolak permintaan dengan catatan
- Status berubah menjadi **DISETUJUI** atau **DITOLAK**

#### 2.4. Admin Gudang - Disposisi
**Role:** `admin_gudang`, `admin`

- Setelah permintaan **DISETUJUI**, Admin Gudang melakukan disposisi
- Akses menu **Transaksi** → **Persetujuan**
- Pilih permintaan yang sudah disetujui
- Klik **"Disposisi"**
- Pilih admin gudang kategori yang akan memproses:
  - **Admin Gudang Aset** (untuk kategori ASET)
  - **Admin Gudang Persediaan** (untuk kategori PERSEDIAAN)
  - **Admin Gudang Farmasi** (untuk kategori FARMASI)
- Status approval log berubah menjadi **MENUNGGU** untuk admin gudang kategori

---

## 🔄 STEP 3: Proses Disposisi

**Role yang dapat mengakses:** `admin_gudang_aset`, `admin_gudang_persediaan`, `admin_gudang_farmasi`, `admin`, `admin_gudang`

**Role view-only:** `kepala_unit`, `kepala_pusat`, `kasubbag_tu` (hanya melihat, tidak dapat memproses)

### Langkah-langkah:

1. **Akses Menu**
   - Login sebagai Admin Gudang Kategori atau Admin
   - Pilih menu **Transaksi** → **Proses Disposisi**

2. **Lihat Daftar Disposisi**
   - Sistem menampilkan daftar permintaan yang perlu diproses
   - Filter berdasarkan kategori gudang (jika admin)
   - Setiap item menampilkan:
     - No. Permintaan
     - Unit Kerja
     - Pemohon
     - Tanggal Permintaan
     - Status

3. **Proses Disposisi**
   - Klik tombol **"Proses"** pada permintaan yang ingin diproses
   - Sistem menampilkan:
     - **Informasi Permintaan**: Detail permintaan
     - **Detail Permintaan**: Daftar barang yang diminta (sudah difilter sesuai kategori)
     - **Detail Distribusi**: Form untuk menentukan inventory yang akan didistribusikan

4. **Tambah Item Distribusi**
   - Klik **"Tambah Item"** untuk menambahkan item distribusi
   - Untuk setiap item, isi:
     - **Inventory**: Pilih inventory yang tersedia (filter berdasarkan gudang asal)
     - **Gudang Asal**: Pilih gudang pusat yang memiliki stok
     - **Qty Distribusi**: Jumlah yang akan didistribusikan
     - **Satuan**: Unit satuan (otomatis terisi)
     - **Harga Satuan**: Harga per satuan (otomatis terisi dari inventory)
     - **Keterangan**: Catatan tambahan (opsional)
   - **Catatan**: 
     - Inventory hanya menampilkan stok yang tersedia (status AKTIF)
     - Qty distribusi tidak boleh melebihi stok tersedia
     - Harga satuan otomatis diambil dari inventory

5. **Simpan Draft Distribusi**
   - Setelah semua item lengkap, klik **"Simpan & Siapkan untuk Distribusi"**
   - Status draft detail distribusi menjadi **READY**
   - Approval log berubah menjadi **DIPROSES**
   - Draft siap untuk di-compile menjadi SBBK

---

## 📦 STEP 4: Compile SBBK (Surat Bukti Barang Keluar)

**Role yang dapat mengakses:** `admin_gudang`, `admin`

### Langkah-langkah:

1. **Akses Menu**
   - Login sebagai Admin Gudang atau Admin
   - Pilih menu **Transaksi** → **Compile SBBK**

2. **Lihat Daftar Draft Ready**
   - Sistem menampilkan daftar draft distribusi dengan status **READY**
   - Setiap item menampilkan:
     - No. Permintaan
     - Unit Kerja
     - Kategori Gudang
     - Jumlah Item Ready

3. **Compile Menjadi SBBK**
   - Klik **"Compile"** pada draft yang ingin di-compile
   - Sistem akan menggabungkan semua draft detail distribusi menjadi satu SBBK
   - Isi informasi distribusi:
     - **No. SBBK**: Otomatis ter-generate
     - **Gudang Asal**: Gudang pusat (otomatis)
     - **Gudang Tujuan**: Pilih gudang unit tujuan
     - **Pegawai Pengirim**: Pilih pegawai yang mengirim
     - **Tanggal Distribusi**: Tanggal pengiriman
     - **Keterangan**: Catatan tambahan (opsional)

4. **Simpan SBBK**
   - Klik **"Simpan SBBK"**
   - Status distribusi menjadi **DRAFT**
   - SBBK siap untuk dikirim

---

## 🚚 STEP 5: Distribusi Barang (Kirim SBBK)

**Role yang dapat mengakses:** `admin_gudang`, `admin_gudang_aset`, `admin_gudang_persediaan`, `admin_gudang_farmasi`, `admin`

### Langkah-langkah:

1. **Akses Menu**
   - Login sebagai Admin Gudang atau Admin
   - Pilih menu **Transaksi** → **Distribusi (SBBK)**

2. **Lihat Daftar SBBK**
   - Sistem menampilkan daftar SBBK dengan status **DRAFT**
   - Filter berdasarkan:
     - Gudang
     - Status
     - Tanggal
     - Search (No. SBBK, No. Permintaan)

3. **Kirim SBBK**
   - Klik **"Lihat Detail"** pada SBBK yang ingin dikirim
   - Review detail distribusi:
     - Informasi SBBK
     - Detail barang yang didistribusikan
   - Klik **"Kirim"** untuk mengirim barang
   - Status berubah menjadi **DIKIRIM**
   - Stok inventory di gudang asal berkurang sesuai qty distribusi

4. **Konfirmasi Pengiriman**
   - Setelah barang dikirim secara fisik, sistem akan menunggu konfirmasi penerimaan dari unit tujuan

---

## 📥 STEP 6: Penerimaan Barang

**Role yang dapat mengakses:** `pegawai`, `kepala_unit`, `admin_gudang`, `admin_gudang_aset`, `admin_gudang_persediaan`, `admin_gudang_farmasi`, `admin`

### Langkah-langkah:

1. **Akses Menu**
   - Login sebagai Pegawai, Kepala Unit, atau Admin Gudang
   - Pilih menu **Transaksi** → **Penerimaan Barang**

2. **Lihat Daftar Distribusi**
   - Sistem menampilkan daftar distribusi dengan status **DIKIRIM** untuk unit kerja user
   - Setiap item menampilkan:
     - No. SBBK
     - No. Permintaan
     - Gudang Asal
     - Gudang Tujuan
     - Tanggal Distribusi
     - Status

3. **Terima Barang**
   - Klik **"Lihat Detail"** pada distribusi yang ingin diterima
   - Review detail distribusi:
     - Informasi SBBK
     - Detail barang yang dikirim
   - Verifikasi fisik barang sesuai dengan detail distribusi
   - Klik **"Terima Barang"**
   - Isi form penerimaan:
     - **Tanggal Penerimaan**: Tanggal barang diterima
     - **Penerima**: Pilih pegawai yang menerima
     - **Status Penerimaan**: Pilih status (SESUAI, KURANG, LEBIH, RUSAK)
     - **Keterangan**: Catatan penerimaan (opsional)

4. **Detail Penerimaan**
   - Untuk setiap item, verifikasi:
     - **Qty Diterima**: Jumlah yang benar-benar diterima
     - **Kondisi**: Kondisi barang (BAIK, RUSAK, dll)
     - **Keterangan**: Catatan khusus item (opsional)

5. **Simpan Penerimaan**
   - Klik **"Simpan Penerimaan"**
   - Status distribusi berubah menjadi **SELESAI**
   - Stok inventory di gudang tujuan bertambah sesuai qty diterima
   - Jika ada selisih (kurang/lebih), sistem akan mencatat di detail penerimaan

---

## 🔙 STEP 7: Retur Barang (Opsional)

**Role yang dapat mengakses:** `pegawai`, `kepala_unit`, `admin_gudang`, `admin_gudang_aset`, `admin_gudang_persediaan`, `admin_gudang_farmasi`, `admin`

### Kapan Retur Dibutuhkan:
- Barang yang diterima tidak sesuai spesifikasi
- Barang rusak saat diterima
- Barang berlebih yang perlu dikembalikan
- Barang tidak terpakai dan perlu dikembalikan ke gudang pusat

### Langkah-langkah:

1. **Akses Menu**
   - Login sebagai Pegawai, Kepala Unit, atau Admin Gudang
   - Pilih menu **Transaksi** → **Retur Barang**

2. **Buat Retur Barang**
   - Klik **"Tambah Retur Barang"** atau **"Buat Retur"**
   - Pilih sumber retur:
     - **Dari Penerimaan**: Retur berdasarkan penerimaan barang
     - **Dari Distribusi**: Retur langsung dari distribusi (jika belum diterima)

3. **Isi Form Retur**
   - **No. Retur**: Otomatis ter-generate
   - **Sumber**: Pilih penerimaan atau distribusi yang akan diretur
   - **Unit Kerja**: Unit kerja yang melakukan retur (otomatis)
   - **Gudang Asal**: Gudang unit (otomatis)
   - **Gudang Tujuan**: Gudang pusat (otomatis)
   - **Pegawai Pengirim**: Pilih pegawai yang mengirim retur
   - **Tanggal Retur**: Tanggal retur dibuat
   - **Status Retur**: Pilih status (DRAFT, DIAJUKAN, DITERIMA, DITOLAK)
   - **Alasan Retur**: Alasan barang diretur
   - **Keterangan**: Catatan tambahan (opsional)

4. **Tambah Detail Retur**
   - Jika memilih "Dari Penerimaan", sistem akan memuat detail penerimaan
   - Pilih item yang akan diretur:
     - **Inventory**: Pilih inventory yang akan diretur
     - **Qty Retur**: Jumlah yang diretur
     - **Satuan**: Unit satuan (otomatis)
     - **Alasan Retur Item**: Alasan khusus item ini diretur
     - **Keterangan**: Catatan item (opsional)
   - Klik **"Tambah Item"** untuk menambahkan item retur lainnya

5. **Simpan Retur**
   - Jika status **DRAFT**, klik **"Simpan sebagai Draft"**
   - Jika siap diajukan, klik **"Ajukan Retur"**
   - Status berubah menjadi **DIAJUKAN**

6. **Proses Retur di Gudang Pusat**
   - Admin Gudang Pusat menerima retur
   - Verifikasi barang yang diretur
   - Klik **"Terima Retur"** untuk menerima retur
   - Status berubah menjadi **DITERIMA**
   - Stok inventory di gudang pusat bertambah
   - Stok inventory di gudang unit berkurang

---

## 📊 Status Transaksi

### Status Permintaan Barang:
- **DRAFT**: Masih dalam draft, dapat diedit/dihapus
- **DIAJUKAN**: Sudah diajukan, masuk workflow approval
- **DISETUJUI**: Disetujui oleh kepala pusat
- **DITOLAK**: Ditolak dalam proses approval
- **DIPROSES**: Sedang diproses oleh admin gudang kategori
- **SELESAI**: Sudah selesai didistribusikan dan diterima

### Status Approval:
- **MENUNGGU**: Menunggu persetujuan
- **DIKETAHUI**: Diketahui oleh kepala unit
- **DIVERIFIKASI**: Diverifikasi oleh kasubbag TU
- **DISETUJUI**: Disetujui oleh kepala pusat
- **DITOLAK**: Ditolak dalam proses approval
- **DIPROSES**: Sedang diproses disposisi

### Status Distribusi:
- **DRAFT**: SBBK masih dalam draft
- **DIKIRIM**: Barang sudah dikirim
- **SELESAI**: Barang sudah diterima

### Status Penerimaan:
- **SESUAI**: Barang sesuai dengan yang dikirim
- **KURANG**: Barang kurang dari yang dikirim
- **LEBIH**: Barang lebih dari yang dikirim
- **RUSAK**: Barang rusak saat diterima

### Status Retur:
- **DRAFT**: Retur masih dalam draft
- **DIAJUKAN**: Retur sudah diajukan
- **DITERIMA**: Retur diterima di gudang pusat
- **DITOLAK**: Retur ditolak

---

## 🔍 Tips & Best Practices

1. **Permintaan Barang**
   - Pastikan semua detail permintaan lengkap sebelum diajukan
   - Gunakan keterangan untuk memberikan informasi tambahan
   - Review ulang sebelum submit

2. **Proses Disposisi**
   - Pastikan stok inventory tersedia sebelum membuat draft distribusi
   - Periksa harga satuan sudah benar
   - Verifikasi qty distribusi tidak melebihi stok tersedia

3. **Penerimaan Barang**
   - Verifikasi fisik barang sesuai dengan detail distribusi
   - Catat jika ada selisih atau kerusakan
   - Simpan bukti penerimaan (foto, dokumen)

4. **Retur Barang**
   - Retur hanya untuk barang yang benar-benar perlu dikembalikan
   - Berikan alasan retur yang jelas
   - Pastikan barang dalam kondisi yang dapat diterima kembali

---

## 🚨 Troubleshooting

### Permintaan tidak muncul di Proses Disposisi?
- Pastikan permintaan sudah **DISETUJUI** oleh kepala pusat
- Pastikan Admin Gudang sudah melakukan **Disposisi**
- Pastikan role user sesuai dengan kategori gudang

### Inventory tidak muncul di dropdown?
- Pastikan inventory memiliki status **AKTIF**
- Pastikan inventory sesuai dengan kategori gudang
- Pastikan stok tersedia (qty_available > 0)

### Tidak bisa menyimpan draft distribusi?
- Pastikan semua field wajib sudah diisi
- Pastikan qty distribusi tidak melebihi stok tersedia
- Pastikan kategori gudang sudah ditentukan (untuk admin)

### Tidak bisa menerima barang?
- Pastikan distribusi memiliki status **DIKIRIM**
- Pastikan user login sesuai dengan unit kerja tujuan
- Pastikan semua detail penerimaan sudah diisi

---

## 📞 Support

Jika mengalami masalah atau butuh bantuan, hubungi:
- **Admin Sistem**: admin@example.com
- **IT Support**: it-support@example.com

---

**Last Updated:** {{ date('d/m/Y') }}
**Version:** 1.0


