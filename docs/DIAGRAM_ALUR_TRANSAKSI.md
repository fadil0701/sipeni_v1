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
│                    PROSES DISPOSISI → BUAT SBBK                  │
│  (Admin Gudang Kategori)                                          │
│                                                                  │
│  1. Lihat Daftar Permintaan (disposisi)                          │
│  2. Proses Disposisi                                             │
│  3. Pilih Inventory dari Gudang Pusat                           │
│  4. Tentukan Qty Distribusi + Gudang Tujuan                     │
│  5. Simpan SBBK (Status: DRAFT) — langsung di menu Distribusi   │
│                                                                  │
│  Catatan: tahap "Compile SBBK" terpisah sudah digabung ke sini. │
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

| Role | Permintaan | Approval | Disposisi | Buat SBBK | Distribusi | Penerimaan | Retur |
|------|-----------|----------|-----------|-----------|------------|------------|-------|
| **Pegawai** | ✅ Create | ❌ | ❌ | ❌ | ❌ | ✅ Receive | ✅ Create |
| **Kepala Unit** | ✅ Create | ✅ Approve | 👁️ View | ❌ | ❌ | ✅ Receive | ✅ Create |
| **Kasubbag TU** | ✅ Create | ✅ Verify | 👁️ View | ❌ | ❌ | ❌ | ❌ |
| **Kepala Pusat** | ✅ Create | ✅ Approve | 👁️ View | ❌ | ❌ | ❌ | ❌ |
| **Admin Gudang** | ✅ All | ✅ Disposisi | ✅ Process | ✅ Buat | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Aset** | ✅ All | ✅ View | ✅ Process | ✅ Buat | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Persediaan** | ✅ All | ✅ View | ✅ Process | ✅ Buat | ✅ Send | ✅ Receive | ✅ All |
| **Admin Gudang Farmasi** | ✅ All | ✅ View | ✅ Process | ✅ Buat | ✅ Send | ✅ Receive | ✅ All |
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

BUAT SBBK (dari proses disposisi / menu Distribusi)
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
| Buat SBBK (dari disposisi) | 5-10 menit | Langsung di menu Distribusi |
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

**Implementasi saat ini:** Setelah Kasubbag TU verifikasi, sistem membuat **disposisi ke Admin Gudang** (step 4) sesuai kategori (Persediaan/Farmasi). Admin Gudang memproses disposisi di **Daftar Permintaan** → **buat SBBK** di menu Distribusi → Kirim → Penerimaan. Tahap Compile SBBK terpisah tidak dipakai lagi (route hanya redirect).

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

- Sistem mendeteksi item yang **tidak ada di stock** (permintaan lainnya / `id_data_barang` null, atau stock gudang pusat = 0 / qty > stok).
- Setelah Kasubbag TU verifikasi: item tersebut masuk **persetujuan Kepala Pusat**, lalu disposisi ke role `pengadaan` + paket pengadaan.
- Item master dengan stok cukup dapat diproses distribusi **secara paralel** (bukan all-or-nothing).
- Route/menu **Compile SBBK** terpisah sudah digabung ke **Distribusi Barang (SBBK)** (hanya redirect).

---

**Last Updated:** 23/07/2026  
**Version:** 1.2


