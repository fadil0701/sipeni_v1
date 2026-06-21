# Alur Inventory Pusat (Aset, Persediaan, Farmasi) hingga Distribusi dan Update Stock

# 1. Overview

Dokumen ini menjelaskan alur bisnis inventory pusat yang mencakup:

- Inventory Aset
- Inventory Persediaan
- Inventory Farmasi
- Permintaan barang dari unit kerja
- Approval permintaan
- Distribusi barang
- Penerimaan distribusi
- Update stock
- Update aset
- Monitoring dan audit trail

Workflow dirancang menggunakan pendekatan enterprise government system yang mendukung:

- Multi gudang
- Multi unit kerja
- FIFO/FEFO
- Asset tracking
- Expired management
- Stock opname
- Approval berjenjang
- Audit dan pelacakan distribusi

---

# 2. Struktur Inventory Pusat

## 2.1 Kategori Inventory

| Jenis | Karakteristik |
|---|---|
| Aset | Barang bernilai besar, memiliki nomor inventaris |
| Persediaan | Barang habis pakai/non aset |
| Farmasi | Obat, BMHP, reagen, item expired sensitive |

---

# 3. Role yang Terlibat

| Role | Fungsi |
|---|---|
| Administrator | Pengaturan sistem |
| Kepala Pusat | Approval strategis |
| Kasubbag TU | Verifikasi administrasi |
| Kepala Unit | Pengajuan permintaan unit |
| Pengurus Barang | Pengelola aset dan persediaan |
| Petugas Gudang | Distribusi dan stock |
| Petugas Farmasi | Pengelola farmasi |
| PPTK | Monitoring kegiatan |
| Bendahara Barang | Pengawasan inventaris |
| Staff Unit | Penerimaan barang |

---

# 4. Struktur Gudang

```text
INVENTORY PUSAT
├── Gudang Aset
├── Gudang Persediaan
├── Gudang Farmasi
├── Gudang BMHP
└── Gudang Unit Kerja
```

---

# 5. Alur Besar Inventory

```text
Pengadaan / Pembelian
    ↓
Penerimaan Barang Pusat
    ↓
Verifikasi Barang
    ↓
Input Inventory
    ↓
Update Stock Pusat
    ↓
Permintaan Unit Kerja
    ↓
Approval Permintaan
    ↓
Picking Barang
    ↓
Distribusi Barang
    ↓
Penerimaan Unit
    ↓
Update Stock Unit
    ↓
Monitoring Pemakaian
    ↓
Stock Opname
```

---

# 6. Workflow Penerimaan Barang ke Inventory Pusat

# 6.1 Barang Datang

## Aktor
- Pengurus Barang
- Petugas Gudang
- Petugas Farmasi

## Aktivitas
- Menerima barang dari vendor
- Scan barcode/QR
- Verifikasi jumlah
- Verifikasi kondisi
- Verifikasi expired date
- Verifikasi batch number
- Cocokkan dengan:
  - PO
  - SPK
  - Invoice
  - Surat jalan

---

## Status

```text
BARANG DITERIMA
```

---

# 6.2 Pemeriksaan Barang

## Pemeriksaan Persediaan
- Jumlah sesuai
- Kondisi sesuai
- Tidak rusak

## Pemeriksaan Farmasi
- Expired date
- Batch number
- FEFO
- Suhu penyimpanan

## Pemeriksaan Aset
- Nomor seri
- Spesifikasi
- Kelengkapan
- Garansi

---

## Keputusan

### Jika tidak sesuai

```text
RETUR / REVISI PENERIMAAN
```

### Jika sesuai

```text
VERIFIKASI PENERIMAAN
```

---

# 6.3 Input Inventory

## Aktivitas

### Persediaan
- Input stock
- Lokasi rak
- Satuan
- Harga

### Farmasi
- Batch
- Expired date
- FEFO queue
- Supplier

### Aset
- Nomor inventaris
- Kode barang
- Lokasi aset
- Penanggung jawab
- Nilai aset
- Masa manfaat
- Penyusutan

---

## Dampak Sistem

```text
STOCK BERTAMBAH
```

---

# 7. Workflow Permintaan Barang Unit Kerja

# 7.1 Pengajuan Permintaan

## Aktor
Kepala Unit / Staff Unit

## Aktivitas
- Membuat permintaan barang
- Pilih jenis inventory:
  - Aset
  - Persediaan
  - Farmasi
- Pilih item
- Input jumlah
- Input alasan kebutuhan
- Input prioritas

---

## Status

```text
PERMINTAAN DIAJUKAN
```

---

# 7.2 Validasi Sistem

## Sistem melakukan:

### Validasi stock
- Apakah stock tersedia
- Minimum stock
- Safety stock

### Validasi limit
- Kuota unit
- Maksimum permintaan
- Histori pemakaian

### Validasi farmasi
- Expired terdekat
- FEFO

---

## Keputusan

### Jika stock tidak cukup

```text
PENDING STOCK
```

### Jika valid

```text
MENUNGGU APPROVAL
```

---

# 7.3 Approval Permintaan

## Approval Kepala Unit

### Aktivitas
- Review kebutuhan
- Approve/revisi/tolak

---

## Approval Pengurus Barang

### Aktivitas
- Verifikasi stock
- Tentukan distribusi
- Tentukan substitusi jika diperlukan

---

## Approval Kasubbag TU (opsional)

Digunakan untuk:
- Barang bernilai besar
- Aset
- Permintaan khusus

---

## Status

```text
PERMINTAAN DISETUJUI
```

---

# 8. Workflow Picking dan Distribusi

# 8.1 Picking Barang

## Aktor
Petugas Gudang

## Aktivitas
- Generate picking list
- Ambil barang dari rak
- Scan barcode
- Validasi jumlah
- Packing

---

## Sistem otomatis:

### Persediaan
- Mengurangi stock tersedia
- Menandai stock outgoing

### Farmasi
- Menggunakan FEFO
- Mengurangi batch expired terdekat

### Aset
- Menandai aset dalam distribusi

---

## Status

```text
SEDANG DISTRIBUSI
```

---

# 8.2 Distribusi Barang

## Aktivitas
- Cetak surat distribusi
- Cetak tanda terima
- Pengiriman ke unit
- Tracking distribusi

---

## Dokumen

- Surat distribusi
- Delivery note
- BAST distribusi
- Checklist barang

---

# 9. Workflow Penerimaan Distribusi Unit

# 9.1 Penerimaan Barang

## Aktor
Staff Unit / Kepala Unit

## Aktivitas
- Menerima barang
- Scan barcode
- Verifikasi jumlah
- Verifikasi kondisi
- Verifikasi expired

---

## Keputusan

### Jika tidak sesuai

```text
RETUR DISTRIBUSI
```

### Jika sesuai

```text
DISTRIBUSI DITERIMA
```

---

# 9.2 Update Stock Unit

## Persediaan

### Sistem otomatis:
- Menambah stock unit
- Mengurangi stock pusat
- Menyimpan histori distribusi

---

## Farmasi

### Sistem otomatis:
- Menambah stock batch unit
- Menyimpan expired
- Menyimpan batch number
- Monitoring FEFO

---

## Aset

### Sistem otomatis:
- Memindahkan lokasi aset
- Mengubah penanggung jawab
- Menyimpan histori mutasi aset

---

## Status

```text
STOCK UNIT UPDATED
```

---

# 10. Workflow Pemakaian Inventory Unit

# 10.1 Pemakaian Persediaan

## Aktivitas
- Input pemakaian
- Input kegiatan
- Input pengguna

## Dampak

```text
STOCK BERKURANG
```

---

# 10.2 Pemakaian Farmasi

## Aktivitas
- Input dispensing
- Input pasien/kegiatan
- Input batch

## Sistem otomatis
- FEFO monitoring
- Expired monitoring
- Pengurangan stock batch

---

# 10.3 Penggunaan Aset

## Aktivitas
- Monitoring lokasi
- Maintenance
- Mutasi
- Penghapusan

---

# 11. Workflow Stock Opname

# 11.1 Stock Opname Berkala

## Jenis

| Jenis | Frekuensi |
|---|---|
| Harian | Farmasi tertentu |
| Bulanan | Persediaan |
| Semester | Aset |
| Tahunan | Semua inventory |

---

## Aktivitas
- Scan barcode
- Hitung fisik
- Bandingkan sistem
- Input selisih
- Generate berita acara

---

## Status

```text
STOCK OPNAME SELESAI
```

---

# 12. Workflow Retur

# 12.1 Retur ke Gudang Pusat

## Penyebab
- Barang rusak
- Salah distribusi
- Expired
- Tidak terpakai

---

## Aktivitas
- Input retur
- Pemeriksaan kondisi
- Update stock
- Update histori

---

# 12.2 Retur Vendor

## Aktivitas
- Generate retur
- Lampiran dokumentasi
- Tracking retur

---

# 13. Alur Aset Secara Khusus

# 13.1 Registrasi Aset

## Sistem otomatis generate:
- Nomor inventaris
- QR code
- Label aset

---

# 13.2 Mutasi Aset

## Aktivitas
- Perpindahan lokasi
- Perubahan penanggung jawab
- Perubahan kondisi

---

# 13.3 Maintenance Aset

## Aktivitas
- Jadwal maintenance
- Riwayat service
- Penggantian sparepart

---

# 13.4 Penghapusan Aset

## Aktivitas
- Pengajuan penghapusan
- Pemeriksaan
- Approval
- Berita acara

---

# 14. Alur Farmasi Secara Khusus

# 14.1 FEFO (First Expired First Out)

Sistem wajib:
- Mengeluarkan expired terdekat terlebih dahulu
- Warning expired
- Blocking expired

---

# 14.2 Monitoring Expired

## Reminder otomatis:
- 6 bulan
- 3 bulan
- 1 bulan
- Sudah expired

---

# 14.3 Cold Chain Monitoring

Untuk item tertentu:
- Monitoring suhu
- Alarm suhu
- Histori suhu

---

# 15. Status Workflow Lengkap

| Status | Keterangan |
|---|---|
| BARANG DITERIMA | Barang diterima pusat |
| VERIFIKASI PENERIMAAN | Pemeriksaan selesai |
| STOCK BERTAMBAH | Stock masuk |
| PERMINTAAN DIAJUKAN | Unit meminta barang |
| MENUNGGU APPROVAL | Menunggu approval |
| PERMINTAAN DISETUJUI | Approval selesai |
| SEDANG DISTRIBUSI | Barang dikirim |
| DISTRIBUSI DITERIMA | Unit menerima barang |
| STOCK UNIT UPDATED | Stock unit terupdate |
| STOCK BERKURANG | Barang digunakan |
| RETUR DISTRIBUSI | Retur dari unit |
| STOCK OPNAME SELESAI | SO selesai |

---

# 16. Dashboard Monitoring

# 16.1 Dashboard Inventory Pusat

## Menampilkan
- Total stock
- Minimum stock
- Barang hampir habis
- Barang expired
- Distribusi berjalan
- Permintaan pending
- Nilai aset
- Grafik pemakaian

---

# 16.2 Dashboard Unit

## Menampilkan
- Stock unit
- Permintaan terakhir
- Distribusi pending
- Barang akan habis
- Barang expired

---

# 17. Rekomendasi Validasi Sistem

# Persediaan
- Tidak boleh minus
- Tidak boleh distribusi melebihi stock
- Satuan wajib valid

---

# Farmasi
- Tidak boleh distribusi expired
- FEFO wajib aktif
- Batch wajib tersedia

---

# Aset
- Nomor inventaris unik
- Mutasi wajib approval
- Penghapusan wajib berita acara

---

# 18. Struktur Menu Sistem

```text
MASTER DATA
├── Barang
├── Kategori
├── Gudang
├── Satuan
├── Vendor
├── Batch
└── Role & Hak Akses

INVENTORY PUSAT
├── Penerimaan Barang
├── Stock Gudang
├── Distribusi
├── Retur
├── Stock Opname
└── Monitoring Inventory

ASET
├── Registrasi Aset
├── Mutasi Aset
├── Maintenance
├── Penghapusan
└── Monitoring Aset

FARMASI
├── Stock Farmasi
├── Batch & Expired
├── FEFO Monitoring
├── Distribusi Farmasi
└── Monitoring Expired

PERMINTAAN UNIT
├── Permintaan Barang
├── Approval Permintaan
├── Distribusi Unit
└── Riwayat Distribusi

LAPORAN
├── Laporan Stock
├── Laporan Distribusi
├── Laporan Expired
├── Laporan Aset
├── Laporan SO
└── Audit Trail
```

---

# 19. Audit Trail

Semua aktivitas wajib tercatat:

- User
- Waktu
- Gudang asal
- Gudang tujuan
- Jumlah barang
- Batch
- Expired
- Lokasi aset
- Approval
- Revisi
- Retur

---

# 20. Rekomendasi Arsitektur Database

## Tabel Utama

```text
inventories
inventory_stocks
inventory_transactions
inventory_distributions
inventory_distribution_items
inventory_requests
inventory_request_items
inventory_batches
inventory_expired_logs
inventory_stock_opnames
assets
asset_mutations
asset_maintenances
asset_depreciations
pharmacy_batches
pharmacy_dispensing
workflow_histories
```

---

# 21. Prinsip Enterprise yang Direkomendasikan

## Separation of Duties

- Pengaju tidak boleh approve sendiri
- Gudang tidak boleh audit sendiri
- Penerima tidak boleh validasi distribusi sendiri

---

## Traceability

Semua perpindahan barang:
- Harus terlacak
- Memiliki histori
- Tidak dapat dihapus

---

## Real Time Inventory

Stock harus:
- Realtime
- Sinkron antar gudang
- Mendukung multi lokasi

---

# 22. Penutup

Workflow inventory ini dirancang untuk:

- Sistem enterprise pemerintahan
- Mendukung pengelolaan aset dan persediaan
- Mendukung farmasi dan FEFO
- Memiliki kontrol distribusi yang kuat
- Mendukung audit dan monitoring
- Meminimalkan kehilangan dan selisih stock
- Siap dikembangkan menjadi sistem inventory terintegrasi skala besar

