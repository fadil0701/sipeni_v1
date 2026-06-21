# Alur Pengajuan RKU hingga Pembelian, Pembayaran, dan Serah Terima Hasil Pekerjaan

## Overview

Dokumen ini menjelaskan alur proses bisnis mulai dari pengajuan RKU (Rencana Kebutuhan Unit) sampai dengan proses pembelian, pembayaran, dan serah terima hasil pekerjaan/barang/jasa.

Alur ini dirancang untuk mendukung sistem workflow enterprise pemerintahan yang memiliki:

- Approval berjenjang
- Validasi anggaran
- Pengendalian pengadaan
- Monitoring realisasi
- Audit trail lengkap
- Pemisahan kewenangan antar role

---

# 1. Struktur Role yang Terlibat

| Role | Fungsi |
|---|---|
| Administrator | Pengaturan sistem dan master data |
| Kepala Pusat | Approval akhir dan pengawasan |
| Kasubbag TU | Verifikasi administrasi dan anggaran |
| Kepala Unit | Pengajuan dan persetujuan kebutuhan unit |
| Pengurus Barang | Verifikasi barang/persediaan/aset |
| PPK / Pejabat Pengadaan | Proses pengadaan/pembelian |
| Bendahara | Proses pembayaran |
| PPTK / Tim Pemeriksa | Pemeriksaan hasil pekerjaan |
| Penyedia / Vendor | Penyedia barang/jasa |

---

# 2. Alur Besar Proses

```text
Pengajuan RKU
    ↓
Review Kepala Unit
    ↓
Verifikasi Pengurus Barang
    ↓
Verifikasi Kasubbag TU
    ↓
Approval Kepala Pusat
    ↓
Menjadi RKU Disetujui
    ↓
Pembuatan Permintaan Pembelian / Pengadaan
    ↓
Proses Pengadaan / Pemilihan Vendor
    ↓
Pembuatan PO / SPK
    ↓
Pelaksanaan Pekerjaan / Pengiriman Barang
    ↓
Pemeriksaan Hasil Pekerjaan
    ↓
Serah Terima Barang/Jasa
    ↓
Pengajuan Pembayaran
    ↓
Verifikasi Bendahara
    ↓
Pembayaran
    ↓
Selesai
```

---

# 3. Detail Workflow Pengajuan RKU

## 3.1 Draft Pengajuan RKU

### Aktor
Kepala Unit / Staff Unit

### Aktivitas
- Membuat draft RKU
- Menginput:
  - Nama kegiatan
  - Barang/jasa
  - Spesifikasi
  - Volume
  - Satuan
  - Estimasi harga
  - Total anggaran
  - Justifikasi kebutuhan
  - Target waktu
- Menyimpan draft

### Status
```text
DRAFT
```

### Hak Akses
- Edit: Kepala Unit
- Hapus: Kepala Unit
- Belum dapat diproses role lain

---

## 3.2 Pengajuan RKU

### Aktivitas
- Kepala Unit mengirim RKU
- Sistem melakukan validasi:
  - Kelengkapan data
  - Duplicated item
  - Ketersediaan pagu
  - Validasi satuan

### Status
```text
DIAJUKAN
```

### Notifikasi
- Pengurus Barang
- Kasubbag TU

---

## 3.3 Verifikasi Pengurus Barang

### Tujuan
Memastikan kebutuhan belum tersedia di stok/persediaan.

### Aktivitas
- Cek persediaan
- Cek aset existing
- Cek standar spesifikasi
- Memberikan catatan

### Keputusan

#### Jika tersedia di stok
Status:
```text
DITOLAK - TERSEDIA DI GUDANG
```

#### Jika valid
Status:
```text
VERIFIKASI PENGURUS BARANG
```

---

## 3.4 Verifikasi Kasubbag TU

### Tujuan
Validasi administrasi dan anggaran.

### Aktivitas
- Validasi akun belanja
- Validasi pagu anggaran
- Validasi prioritas
- Koreksi nominal
- Review kelayakan

### Keputusan

#### Revisi
```text
REVISI KASUBBAG TU
```

#### Disetujui
```text
VERIFIKASI KASUBBAG TU
```

---

## 3.5 Approval Kepala Pusat

### Aktivitas
- Review final
- Menentukan prioritas
- Menyetujui / menolak

### Keputusan

#### Ditolak
```text
DITOLAK KEPALA PUSAT
```

#### Disetujui
```text
RKU DISETUJUI
```

### Dampak Sistem
- RKU terkunci
- Tidak dapat diubah lagi
- Masuk daftar pengadaan
- Muncul pada menu “RKU & Aktivitas”

---

# 4. Workflow Pengadaan / Pembelian

## 4.1 Pembuatan Permintaan Pengadaan

### Aktor
PPK / Pejabat Pengadaan

### Aktivitas
- Membuat permintaan pengadaan berdasarkan RKU approved
- Mengelompokkan item
- Menentukan metode pengadaan:
  - Pembelian langsung
  - E-purchasing
  - Penunjukan langsung
  - Tender

### Status
```text
PERMINTAAN PENGADAAN
```

---

## 4.2 Pemilihan Vendor

### Aktivitas
- Input vendor
- Upload penawaran
- Perbandingan harga
- Evaluasi teknis
- Penetapan vendor

### Data yang disimpan
- Nama vendor
- NPWP
- Alamat
- PIC
- Nilai penawaran
- Dokumen penawaran

### Status
```text
VENDOR DITETAPKAN
```

---

## 4.3 Pembuatan PO / SPK

### Aktivitas
- Generate nomor PO/SPK
- Generate dokumen otomatis
- Menentukan:
  - Termin
  - Waktu pekerjaan
  - SLA
  - Denda
  - Garansi

### Dokumen
- Purchase Order
- SPK
- Surat Penunjukan

### Status
```text
PO / SPK TERBIT
```

---

# 5. Pelaksanaan Pekerjaan

## 5.1 Pengiriman Barang / Pelaksanaan Jasa

### Vendor
Melaksanakan pekerjaan sesuai SPK.

### Aktivitas Sistem
- Tracking progres
- Upload dokumentasi
- Monitoring timeline
- Reminder jatuh tempo

### Status
```text
PEKERJAAN BERJALAN
```

---

## 5.2 Pemeriksaan Hasil

### Aktor
PPTK / Tim Pemeriksa

### Aktivitas
- Pemeriksaan fisik
- Pemeriksaan spesifikasi
- Pemeriksaan jumlah
- Berita acara pemeriksaan
- Upload foto dokumentasi

### Keputusan

#### Tidak sesuai
```text
REVISI PEKERJAAN
```

#### Sesuai
```text
HASIL DIPERIKSA
```

---

# 6. Serah Terima

## 6.1 BAST (Berita Acara Serah Terima)

### Aktivitas
- Generate BAST otomatis
- Tanda tangan pihak terkait
- Upload dokumen final

### Pihak
- Vendor
- PPTK
- Pengurus Barang

### Status
```text
SERAH TERIMA SELESAI
```

### Dampak Sistem
- Barang masuk inventaris
- Stok bertambah
- Asset register update

---

# 7. Workflow Pembayaran

## 7.1 Pengajuan Pembayaran

### Aktor
PPK / Vendor / Admin Keuangan

### Dokumen
- Invoice
- Faktur
- BAST
- SPK
- Bukti pekerjaan
- Kwitansi

### Status
```text
PENGAJUAN PEMBAYARAN
```

---

## 7.2 Verifikasi Bendahara

### Aktivitas
- Verifikasi kelengkapan
- Verifikasi nominal
- Verifikasi rekening
- Validasi pajak

### Keputusan

#### Revisi
```text
REVISI PEMBAYARAN
```

#### Valid
```text
PEMBAYARAN DISETUJUI
```

---

## 7.3 Proses Pembayaran

### Aktivitas
- Input nomor SP2D/SPM
- Input bukti transfer
- Input tanggal pembayaran
- Generate bukti bayar

### Status
```text
LUNAS
```

### Dampak Sistem
- Realisasi anggaran bertambah
- Dashboard keuangan update
- Histori transaksi tersimpan

---

# 8. Status Workflow Lengkap

| Status | Keterangan |
|---|---|
| DRAFT | RKU masih disusun |
| DIAJUKAN | Sudah dikirim |
| VERIFIKASI PENGURUS BARANG | Sedang diverifikasi |
| REVISI KASUBBAG TU | Perlu perbaikan |
| VERIFIKASI KASUBBAG TU | Validasi administrasi selesai |
| RKU DISETUJUI | Final approved |
| PERMINTAAN PENGADAAN | Masuk proses pengadaan |
| VENDOR DITETAPKAN | Vendor dipilih |
| PO / SPK TERBIT | Dokumen pengadaan selesai |
| PEKERJAAN BERJALAN | Proses pengerjaan |
| HASIL DIPERIKSA | Pemeriksaan selesai |
| SERAH TERIMA SELESAI | BAST selesai |
| PENGAJUAN PEMBAYARAN | Menunggu pembayaran |
| PEMBAYARAN DISETUJUI | Verifikasi bendahara selesai |
| LUNAS | Pembayaran selesai |
| DITOLAK | Pengajuan ditolak |

---

# 9. Rekomendasi Fitur Sistem

## 9.1 Dashboard Monitoring

### Dashboard Pimpinan
- Total RKU
- Persentase approval
- Realisasi anggaran
- Pengadaan berjalan
- Vendor aktif
- Grafik bulanan

### Dashboard Unit
- Status pengajuan
- Riwayat revisi
- Sisa pagu
- Timeline approval

---

## 9.2 Audit Trail

Semua aktivitas wajib tercatat:

- Siapa melakukan
- Tanggal & waktu
- Perubahan data
- IP address
- Catatan approval/revisi

---

## 9.3 Notifikasi Sistem

### Email / WhatsApp
- Pengajuan baru
- Approval pending
- Revisi
- Deadline pekerjaan
- Pembayaran selesai

---

## 9.4 Dokumen Otomatis

Sistem dapat generate:

- RKU PDF
- Nota dinas
- SPK
- PO
- BAST
- Invoice recap
- Kwitansi
- Laporan realisasi

---

# 10. Rekomendasi Struktur Menu Sistem

```text
MASTER DATA
├── Unit Kerja
├── Vendor
├── Satuan
├── Jenis Belanja
├── Akun Anggaran
└── Role & Hak Akses

RKU
├── Draft RKU
├── Pengajuan RKU
├── Approval RKU
├── RKU Disetujui
└── Monitoring RKU

PENGADAAN
├── Permintaan Pengadaan
├── Vendor
├── PO / SPK
├── Monitoring Pengadaan
└── Pemeriksaan Hasil

KEUANGAN
├── Pengajuan Pembayaran
├── Verifikasi Bendahara
├── Pembayaran
└── Realisasi Anggaran

LAPORAN
├── Laporan RKU
├── Laporan Pengadaan
├── Laporan Pembayaran
├── Rekap Vendor
└── Audit Trail
```

---

# 11. Rekomendasi Validasi Sistem

## Pengajuan RKU
- Tidak boleh melebihi pagu
- Tidak boleh item duplikat
- Spesifikasi wajib diisi
- Volume wajib > 0

## Pengadaan
- Vendor wajib aktif
- Nilai SPK tidak boleh melebihi RKU
- Termin harus valid

## Pembayaran
- Tidak bisa dibayar sebelum BAST
- Tidak bisa dibayar ganda
- Pajak wajib tervalidasi

---

# 12. Rekomendasi Arsitektur Workflow

## Workflow Engine

Gunakan konsep:

```text
workflow_transitions
workflow_approvals
workflow_histories
workflow_statuses
```

Agar:

- Dinamis
- Mudah dikembangkan
- Multi level approval
- Mendukung delegasi
- Mendukung parallel approval

---

# 13. Alur Enterprise yang Direkomendasikan

## Prinsip Utama

### Separation of Duties

Pengusul tidak boleh:
- Menyetujui sendiri
- Membayar sendiri
- Memeriksa sendiri

---

### Locking Data

Data approved:
- Tidak dapat diubah
- Hanya dapat direvisi melalui workflow

---

### Full Traceability

Semua perubahan:
- Tersimpan permanen
- Tidak dapat dihapus
- Dapat diaudit

---

# 14. Penutup

Workflow ini dirancang agar:

- Sesuai tata kelola pemerintahan
- Memiliki kontrol internal yang baik
- Mendukung audit dan monitoring
- Mengurangi kesalahan manual
- Transparan dan terdokumentasi
- Mudah dikembangkan menjadi sistem enterprise skala besar

