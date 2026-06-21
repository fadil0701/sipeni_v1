# Alur Pemeliharaan Aset, Maintenance, dan Kalibrasi Alat

# 1. Overview

Dokumen ini menjelaskan workflow enterprise untuk:

- Pemeliharaan aset
- Maintenance rutin
- Corrective maintenance
- Preventive maintenance
- Permintaan maintenance
- Kalibrasi alat
- Monitoring kondisi aset
- Riwayat maintenance
- Pengendalian downtime
- Audit dan traceability

Workflow dirancang untuk mendukung sistem pemerintahan dan fasilitas layanan kesehatan yang memiliki:

- Banyak aset dan alat
- Jadwal maintenance berkala
- Kebutuhan kalibrasi alat medis/non medis
- SLA maintenance
- Monitoring vendor/service
- Histori aset lengkap

---

# 2. Jenis Aset yang Dikelola

| Jenis Aset | Contoh |
|---|---|
| Aset Medis | EKG, treadmill, spirometri, audiometri |
| Aset Non Medis | AC, genset, printer, komputer |
| Kendaraan | Ambulance, kendaraan operasional |
| Infrastruktur | Lift, panel listrik, jaringan |
| Laboratorium | Analyzer, centrifuge, mikroskop |

---

# 3. Jenis Maintenance

| Jenis | Tujuan |
|---|---|
| Preventive Maintenance | Pencegahan kerusakan |
| Corrective Maintenance | Perbaikan kerusakan |
| Predictive Maintenance | Berdasarkan monitoring kondisi |
| Emergency Maintenance | Penanganan darurat |
| Kalibrasi | Validasi akurasi alat |

---

# 4. Role yang Terlibat

| Role | Fungsi |
|---|---|
| Administrator | Pengaturan sistem |
| Kepala Pusat | Approval strategis |
| Kasubbag TU | Verifikasi administrasi |
| Kepala Unit | Pengajuan maintenance |
| Pengurus Barang | Pengelola aset |
| Teknisi Internal | Pelaksana maintenance |
| Vendor Service | Maintenance eksternal |
| Petugas Kalibrasi | Pelaksana kalibrasi |
| PPTK | Monitoring pekerjaan |
| Staff Unit | Pelapor kerusakan |

---

# 5. Struktur Besar Workflow

```text
Registrasi Aset
    ↓
Penjadwalan Maintenance
    ↓
Reminder Maintenance
    ↓
Pelaksanaan Maintenance
    ↓
Pemeriksaan Hasil
    ↓
Update Kondisi Aset
    ↓
Riwayat Maintenance
    ↓
Kalibrasi Berkala
    ↓
Monitoring SLA dan Downtime
```

---

# 6. Workflow Registrasi Aset

# 6.1 Registrasi Awal

## Aktivitas
- Input data aset
- Generate QR Code
- Generate nomor inventaris
- Input lokasi
- Input penanggung jawab
- Input vendor
- Input garansi
- Input jadwal maintenance
- Input jadwal kalibrasi

---

## Data yang disimpan

| Data | Keterangan |
|---|---|
| Nomor inventaris | Unique asset number |
| Serial number | Nomor seri pabrik |
| Lokasi aset | Lokasi fisik |
| Kondisi | Baik/rusak/perlu maintenance |
| Vendor | Vendor pengadaan |
| Garansi | Masa garansi |
| Jadwal maintenance | Berkala |
| Jadwal kalibrasi | Berkala |

---

# 7. Workflow Preventive Maintenance

# 7.1 Penjadwalan Otomatis

## Sistem otomatis generate:
- Jadwal harian
- Mingguan
- Bulanan
- Semester
- Tahunan

---

## Contoh

| Aset | Jadwal |
|---|---|
| AC | 3 bulan |
| Genset | 1 bulan |
| Audiometri | 6 bulan |
| Treadmill | 3 bulan |

---

## Status

```text
JADWAL MAINTENANCE AKTIF
```

---

# 7.2 Reminder Maintenance

## Sistem otomatis:

### Reminder:
- H-30
- H-14
- H-7
- H-1

### Kanal:
- Email
- WhatsApp
- Dashboard notification

---

# 7.3 Pelaksanaan Maintenance

## Aktor
- Teknisi internal
- Vendor service

---

## Aktivitas
- Pemeriksaan aset
- Cleaning
- Penggantian sparepart
- Pelumasan
- Pengetesan fungsi
- Upload dokumentasi
- Input hasil maintenance

---

## Dokumen
- Checklist maintenance
- Berita acara
- Dokumentasi foto
- Invoice service

---

## Status

```text
MAINTENANCE SELESAI
```

---

# 7.4 Pemeriksaan Hasil Maintenance

## Aktor
Pengurus Barang / Kepala Unit

---

## Aktivitas
- Pemeriksaan hasil
- Verifikasi fungsi
- Approval hasil
- Catatan perbaikan

---

## Keputusan

### Jika belum sesuai

```text
REVISI MAINTENANCE
```

### Jika sesuai

```text
MAINTENANCE VERIFIED
```

---

# 8. Workflow Corrective Maintenance

# 8.1 Pelaporan Kerusakan

## Aktor
Staff Unit / Operator

---

## Aktivitas
- Scan QR aset
- Input keluhan
- Input gejala kerusakan
- Upload foto/video
- Input tingkat urgensi

---

## Prioritas

| Prioritas | SLA |
|---|---|
| Critical | < 4 jam |
| High | 1 hari |
| Medium | 3 hari |
| Low | 7 hari |

---

## Status

```text
KERUSAKAN DILAPORKAN
```

---

# 8.2 Verifikasi Kerusakan

## Aktor
Pengurus Barang / Teknisi

---

## Aktivitas
- Pemeriksaan awal
- Diagnosis kerusakan
- Estimasi biaya
- Estimasi downtime
- Penentuan:
  - Internal repair
  - External vendor
  - Penggantian aset

---

## Keputusan

### Perbaikan internal

```text
PERBAIKAN INTERNAL
```

### Vendor eksternal

```text
PERBAIKAN EKSTERNAL
```

### Penghapusan aset

```text
USUL PENGHAPUSAN ASET
```

---

# 8.3 Pelaksanaan Perbaikan

## Aktivitas
- Repair
- Penggantian sparepart
- Pengetesan
- Dokumentasi
- Input biaya

---

## Status

```text
PERBAIKAN SELESAI
```

---

# 8.4 Update Kondisi Aset

## Sistem otomatis update:

| Field | Update |
|---|---|
| Kondisi | Baik/Rusak ringan/Rusak berat |
| Last maintenance | Tanggal terakhir |
| Total downtime | Akumulasi |
| Total biaya maintenance | Akumulasi |
| Histori maintenance | Bertambah |

---

# 9. Workflow Kalibrasi Alat

# 9.1 Penjadwalan Kalibrasi

## Sistem otomatis:
- Generate jadwal kalibrasi
- Monitoring masa berlaku sertifikat
- Reminder jatuh tempo

---

## Contoh

| Alat | Jadwal |
|---|---|
| Audiometri | 1 tahun |
| Spirometri | 1 tahun |
| Timbangan | 6 bulan |
| Tensi meter | 1 tahun |

---

## Status

```text
JADWAL KALIBRASI AKTIF
```

---

# 9.2 Persiapan Kalibrasi

## Aktivitas
- Penjadwalan vendor/laboratorium
- Penyiapan alat
- Penyiapan dokumen
- Penyiapan histori alat

---

## Dokumen
- Sertifikat sebelumnya
- Histori maintenance
- Manual alat
- Checklist kalibrasi

---

# 9.3 Pelaksanaan Kalibrasi

## Aktivitas
- Pengujian akurasi
- Penyesuaian alat
- Pengukuran parameter
- Pengujian hasil

---

## Output
- Sertifikat kalibrasi
- Hasil pengujian
- Masa berlaku sertifikat

---

## Status

```text
KALIBRASI SELESAI
```

---

# 9.4 Verifikasi Kalibrasi

## Aktivitas
- Verifikasi sertifikat
- Upload dokumen
- Validasi hasil
- Approval

---

## Sistem otomatis:
- Update next calibration date
- Update masa berlaku
- Simpan sertifikat digital

---

## Status

```text
KALIBRASI VERIFIED
```

---

# 10. Workflow Maintenance Vendor Eksternal

# 10.1 Penunjukan Vendor

## Aktivitas
- Pilih vendor
- Input penawaran
- Approval biaya
- Generate SPK maintenance

---

# 10.2 Monitoring Vendor

## Sistem mencatat:
- SLA response
- SLA completion
- Kualitas pekerjaan
- Histori vendor
- Nilai pekerjaan

---

# 11. Workflow Sparepart

# 11.1 Permintaan Sparepart

## Aktivitas
- Input sparepart
- Validasi stock
- Distribusi sparepart

---

## Status

```text
SPAREPART DIKELUARKAN
```

---

# 11.2 Update Inventory

## Sistem otomatis:
- Mengurangi stock sparepart
- Menyimpan histori penggunaan
- Menyimpan relasi ke aset

---

# 12. Monitoring Downtime

# 12.1 Sistem menghitung:

| Parameter | Fungsi |
|---|---|
| Downtime | Lama alat tidak aktif |
| MTTR | Mean time to repair |
| MTBF | Mean time between failure |
| Biaya maintenance | Total biaya |
| Frekuensi kerusakan | Jumlah kerusakan |

---

# 13. Dashboard Monitoring

# 13.1 Dashboard Maintenance

## Menampilkan:
- Jadwal maintenance hari ini
- Maintenance overdue
- Aset rusak
- Maintenance berjalan
- Downtime aset
- Grafik maintenance
- Biaya maintenance

---

# 13.2 Dashboard Kalibrasi

## Menampilkan:
- Kalibrasi jatuh tempo
- Sertifikat expired
- Jadwal bulan ini
- Vendor kalibrasi

---

# 14. Workflow Penghapusan Aset

# 14.1 Kriteria

- Rusak berat
- Tidak ekonomis diperbaiki
- Tidak lolos kalibrasi
- Obsolete

---

# 14.2 Aktivitas

- Pengajuan penghapusan
- Pemeriksaan tim
- Approval
- Berita acara
- Update status aset

---

## Status

```text
ASET DIHAPUSKAN
```

---

# 15. Status Workflow Lengkap

| Status | Keterangan |
|---|---|
| JADWAL MAINTENANCE AKTIF | Jadwal aktif |
| MAINTENANCE SELESAI | Maintenance selesai |
| MAINTENANCE VERIFIED | Sudah diverifikasi |
| KERUSAKAN DILAPORKAN | Laporan masuk |
| PERBAIKAN INTERNAL | Ditangani internal |
| PERBAIKAN EKSTERNAL | Ditangani vendor |
| PERBAIKAN SELESAI | Repair selesai |
| JADWAL KALIBRASI AKTIF | Jadwal aktif |
| KALIBRASI SELESAI | Kalibrasi selesai |
| KALIBRASI VERIFIED | Sertifikat valid |
| USUL PENGHAPUSAN ASET | Menunggu approval |
| ASET DIHAPUSKAN | Asset retired |

---

# 16. Struktur Menu Sistem

```text
MASTER DATA
├── Aset
├── Kategori Aset
├── Vendor Service
├── Vendor Kalibrasi
├── Sparepart
└── SLA Maintenance

MAINTENANCE
├── Jadwal Maintenance
├── Preventive Maintenance
├── Corrective Maintenance
├── Emergency Maintenance
├── Work Order
├── Monitoring Maintenance
└── Histori Maintenance

KALIBRASI
├── Jadwal Kalibrasi
├── Pelaksanaan Kalibrasi
├── Sertifikat Kalibrasi
├── Monitoring Expired
└── Histori Kalibrasi

SPAREPART
├── Stock Sparepart
├── Permintaan Sparepart
├── Distribusi Sparepart
└── Histori Penggunaan

ASET
├── Registrasi Aset
├── Mutasi Aset
├── Downtime Monitoring
├── Penghapusan Aset
└── Monitoring Kondisi

LAPORAN
├── Laporan Maintenance
├── Laporan Kalibrasi
├── Laporan Downtime
├── Laporan Vendor
├── Laporan Sparepart
└── Audit Trail
```

---

# 17. Audit Trail

Semua aktivitas wajib tersimpan:

- User
- Waktu
- Aset
- Lokasi
- Teknisi
- Vendor
- Sparepart
- Biaya
- Hasil maintenance
- Hasil kalibrasi
- Approval
- Downtime

---

# 18. Rekomendasi Struktur Database

## Tabel Utama

```text
assets
asset_maintenances
asset_maintenance_schedules
asset_maintenance_logs
asset_repair_requests
asset_repair_histories
asset_calibrations
asset_calibration_schedules
asset_calibration_certificates
asset_downtimes
asset_spareparts
asset_sparepart_usages
asset_vendors
asset_work_orders
workflow_histories
```

---

# 19. Prinsip Enterprise yang Direkomendasikan

## Preventive First

Sistem harus:
- Memprioritaskan preventive maintenance
- Mengurangi corrective maintenance
- Mengurangi downtime

---

## Full Traceability

Semua histori:
- Tidak boleh hilang
- Tidak boleh dihapus
- Dapat diaudit

---

## SLA Monitoring

Sistem wajib:
- Monitoring response time
- Monitoring completion time
- Monitoring vendor performance

---

## Asset Lifecycle Management

Sistem mendukung:
- Pengadaan
- Penggunaan
- Maintenance
- Kalibrasi
- Mutasi
- Penghapusan

---

# 20. Penutup

Workflow maintenance dan kalibrasi ini dirancang untuk:

- Sistem enterprise pemerintahan
- Fasilitas kesehatan
- Pengelolaan aset modern
- Monitoring lifecycle aset
- Mengurangi downtime alat
- Mendukung audit dan akreditasi
- Mendukung kalibrasi dan sertifikasi
- Meningkatkan kualitas layanan dan keamanan penggunaan alat

