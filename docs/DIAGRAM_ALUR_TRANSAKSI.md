# 📊 Diagram Alur Transaksi Barang

## Flowchart Lengkap Transaksi

```
┌─────────────────────────────────────────────────────────────────┐
│                    PERMINTAAN BARANG                             │
│  (Pegawai/Kepala Unit/Kasubbag)                                 │
│                                                                  │
│  1. Buat Permintaan                                              │
│  2. Tambah Detail Item                                           │
│  3. Simpan Draft / Ajukan                                        │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    WORKFLOW APPROVAL                             │
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐      │
│  │ Kepala Unit  │───▶│ Kasubbag TU  │───▶│ Kepala Pusat │      │
│  │  Mengetahui  │    │  Verifikasi  │    │   Approve    │      │
│  └──────────────┘    └──────────────┘    └──────────────┘      │
│                                                                  │
│  Status: DIAJUKAN → DIKETAHUI → DIVERIFIKASI → DISETUJUI        │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DISPOSISI                                     │
│  (Admin Gudang)                                                  │
│                                                                  │
│  Pilih Admin Gudang Kategori:                                    │
│  • Admin Gudang Aset                                             │
│  • Admin Gudang Persediaan                                       │
│  • Admin Gudang Farmasi                                          │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PROSES DISPOSISI                              │
│  (Admin Gudang Kategori)                                          │
│                                                                  │
│  1. Lihat Daftar Disposisi                                       │
│  2. Proses Disposisi                                             │
│  3. Pilih Inventory dari Gudang Pusat                           │
│  4. Tentukan Qty Distribusi                                     │
│  5. Simpan Draft Distribusi (Status: READY)                     │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    COMPILE SBBK                                  │
│  (Admin Gudang)                                                  │
│                                                                  │
│  1. Lihat Draft Ready                                            │
│  2. Compile menjadi SBBK                                        │
│  3. Tentukan Gudang Tujuan                                      │
│  4. Simpan SBBK (Status: DRAFT)                                 │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DISTRIBUSI (KIRIM)                            │
│  (Admin Gudang)                                                  │
│                                                                  │
│  1. Lihat Daftar SBBK                                           │
│  2. Review Detail Distribusi                                     │
│  3. Kirim Barang (Status: DIKIRIM)                              │
│  4. Stok Gudang Asal Berkurang                                  │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PENERIMAAN BARANG                             │
│  (Pegawai/Kepala Unit)                                           │
│                                                                  │
│  1. Lihat Daftar Distribusi DIKIRIM                              │
│  2. Verifikasi Fisik Barang                                      │
│  3. Terima Barang                                                │
│  4. Isi Detail Penerimaan                                        │
│  5. Simpan (Status: SELESAI)                                    │
│  6. Stok Gudang Tujuan Bertambah                                │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                    RETUR BARANG (OPSIONAL)                       │
│  (Pegawai/Kepala Unit)                                           │
│                                                                  │
│  1. Buat Retur dari Penerimaan                                   │
│  2. Pilih Item yang Diretur                                     │
│  3. Isi Alasan Retur                                             │
│  4. Ajukan Retur (Status: DIAJUKAN)                            │
│  5. Admin Gudang Terima Retur (Status: DITERIMA)               │
│  6. Stok Kembali ke Gudang Pusat                                │
└─────────────────────────────────────────────────────────────────┘
```

## Role & Permission Matrix

| Role | Permintaan | Approval | Disposisi | Compile | Distribusi | Penerimaan | Retur |
|------|-----------|----------|-----------|---------|------------|------------|-------|
| **Pegawai** | ✅ Create | ❌ | ❌ | ❌ | ❌ | ✅ Receive | ✅ Create |
| **Kepala Unit** | ✅ Create | ✅ Approve | 👁️ View | ❌ | ❌ | ✅ Receive | ✅ Create |
| **Kasubbag TU** | ✅ Create | ✅ Verify | 👁️ View | ❌ | ❌ | ❌ | ❌ |
| **Kepala Pusat** | ✅ Create | ✅ Approve | 👁️ View | ❌ | ❌ | ❌ | ❌ |
| **Admin Gudang** | ✅ All | ✅ Disposisi | ✅ Process | ✅ Compile | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Aset** | ✅ All | ✅ View | ✅ Process | ❌ | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Persediaan** | ✅ All | ✅ View | ✅ Process | ❌ | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Farmasi** | ✅ All | ✅ View | ✅ Process | ❌ | ✅ Send | ✅ Receive | ✅ All |
| **Admin** | ✅ All | ✅ All | ✅ All | ✅ All | ✅ All | ✅ All | ✅ All |

**Legend:**
- ✅ = Full Access
- 👁️ = View Only
- ❌ = No Access

## Status Flow Diagram

```
PERMINTAAN BARANG:
DRAFT → DIAJUKAN → DISETUJUI → DIPROSES → SELESAI
         ↓
      DITOLAK

APPROVAL:
MENUNGGU → DIKETAHUI → DIVERIFIKASI → DISETUJUI → DIPROSES
           ↓              ↓              ↓
         DITOLAK       DITOLAK       DITOLAK

DISTRIBUSI:
DRAFT → DIKIRIM → SELESAI

RETUR:
DRAFT → DIAJUKAN → DITERIMA
        ↓
     DITOLAK
```

## Decision Points

### 1. Setelah Permintaan Diajukan
```
Apakah Kepala Unit Setuju?
├─ YES → Lanjut ke Kasubbag TU
└─ NO → DITOLAK → Kembali ke Pemohon
```

### 2. Setelah Verifikasi Kasubbag
```
Apakah Kasubbag Setuju?
├─ YES → Lanjut ke Kepala Pusat
└─ NO → DITOLAK → Kembali ke Pemohon
```

### 3. Setelah Approval Kepala Pusat
```
Apakah Kepala Pusat Setuju?
├─ YES → Disposisi ke Admin Gudang Kategori
└─ NO → DITOLAK → Kembali ke Pemohon
```

### 4. Saat Proses Disposisi
```
Apakah Stok Tersedia?
├─ YES → Buat Draft Distribusi
└─ NO → Tidak bisa diproses (tunggu stok tersedia)
```

### 5. Saat Penerimaan
```
Apakah Barang Sesuai?
├─ SESUAI → Terima → SELESAI
├─ KURANG → Terima dengan catatan → SELESAI
├─ LEBIH → Terima dengan catatan → SELESAI
└─ RUSAK → Terima dengan catatan → SELESAI (atau Retur)
```

### 6. Setelah Penerimaan
```
Apakah Perlu Retur?
├─ YES → Buat Retur Barang
└─ NO → Transaksi Selesai
```

## Data Flow

```
PERMINTAAN BARANG
    │
    ├─→ detail_permintaan_barang (items)
    │
    └─→ approval_log (workflow)

DISPOSISI
    │
    └─→ draft_detail_distribusi (items ready)

COMPILE SBBK
    │
    ├─→ transaksi_distribusi (header)
    └─→ detail_distribusi (items)

DISTRIBUSI
    │
    ├─→ Update stok gudang asal (kurang)
    └─→ Status: DIKIRIM

PENERIMAAN
    │
    ├─→ penerimaan_barang (header)
    ├─→ detail_penerimaan_barang (items)
    ├─→ Update stok gudang tujuan (tambah)
    └─→ Status: SELESAI

RETUR (jika ada)
    │
    ├─→ retur_barang (header)
    ├─→ detail_retur_barang (items)
    ├─→ Update stok gudang pusat (tambah)
    └─→ Update stok gudang unit (kurang)
```

## Timeline Estimasi

| Tahap | Estimasi Waktu | Keterangan |
|-------|---------------|------------|
| Permintaan Barang | 5-10 menit | Tergantung jumlah item |
| Approval Kepala Unit | 1-2 hari | Tergantung ketersediaan |
| Verifikasi Kasubbag | 1-2 hari | Tergantung ketersediaan |
| Approval Kepala Pusat | 1-3 hari | Tergantung ketersediaan |
| Proses Disposisi | 15-30 menit | Tergantung jumlah item |
| Compile SBBK | 5-10 menit | Quick process |
| Distribusi (Kirim) | 1-3 hari | Tergantung jarak & logistik |
| Penerimaan Barang | 10-15 menit | Verifikasi fisik |
| Retur (jika ada) | 1-2 hari | Tergantung proses retur |

**Total Estimasi:** 5-12 hari kerja (tanpa retur)

---

---

## Alur Permintaan Barang – Dua Cabang (Barang Ada vs Tidak Ada di Stock)

Flowchart ini memetakan alur setelah **Kasubbag TU Verifikasi** menjadi dua cabang berdasarkan ketersediaan barang di stock.

### Cabang 1: Barang ADA di Stock (Pemenuhan dari Gudang)

```
Kasubbag TU Verif → Disetujui
    → SPPB (Surat Perintah Pengeluaran Barang)
    → Pengurus Barang (Admin Gudang)
    → Dispo Gudang (Farmasi / Persediaan)
    → Dokumen SBBK → Distribusi
    → Unit Kerja (Cek dan Terima Barang)
    → Cek Fisik: Sesuai → Selesai (SBBK ditandatangani, barang masuk data stock unit)
```

**Implementasi saat ini:** Setelah Kasubbag TU verifikasi, sistem membuat **disposisi ke Admin Gudang** (step 4) sesuai kategori (Persediaan/Farmasi). Admin Gudang memproses disposisi → Compile SBBK → Distribusi → Penerimaan.

### Cabang 2: Barang TIDAK ADA di Stock (Pengadaan Barang dan Jasa)

```
SPB (Permintaan yang tidak ada di Data Stock)
    → Kepala Pusat (Setuju / Tidak)
    → Jika Disetujui:
        → PPK (Pejabat Pembuat Komitmen) – Upload Dokumen Spektek
        → PPBJ (Pejabat Pengadaan Barang/Jasa) – Upload Dokumen Pengadaan
        → Selesai
```

**Implementasi saat ini:**

- Sistem mendeteksi item yang **tidak ada di stock** (permintaan lainnya / `id_data_barang` null, atau stock gudang pusat = 0).
- Jika ada item seperti itu, setelah Kasubbag TU verifikasi dibuat **disposisi ke Pengadaan Barang dan Jasa** (step 4 – role `pengadaan`). Role Pengadaan melihat permintaan tersebut di menu **Persetujuan** dan dapat memproses pengadaan.

**Catatan:** Di flowchart, cabang pengadaan melalui **persetujuan Kepala Pusat** dulu sebelum ke PPK/PPBJ. Di sistem saat ini, disposisi ke Pengadaan dibuat langsung setelah verifikasi Kasubbag TU (tanpa step persetujuan Kepala Pusat khusus untuk pengadaan). Jika diinginkan penambahan step “Persetujuan Kepala Pusat untuk Disposisi ke Pengadaan” sebelum item masuk ke bagian Pengadaan, dapat ditambahkan di kemudian hari.

---

**Last Updated:** 02/02/2026  
**Version:** 1.1


