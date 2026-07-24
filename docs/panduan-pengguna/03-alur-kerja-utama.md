# 03 — Alur Kerja Utama

Ringkasan alur bisnis end-to-end di SI-MANTIK. Untuk langkah detail per tombol, lihat juga [ALUR_TRANSAKSI.md](../ALUR_TRANSAKSI.md).

---

## 1. Permintaan Barang (unit → pusat)

### Diagram status

```
draft → diajukan → diverifikasi → [ditolak]
                              ↓
                    menunggu_pengadaan → proses_pengadaan → barang_tersedia
                              ↓
                    proses_distribusi → dikirim → diterima → selesai
```

### Tahap operasional

| # | Tahap | Pelaku | Menu |
|---|-------|--------|------|
| 1 | Buat & ajukan permintaan | Admin Unit | Transaksi → Permintaan Barang |
| 2 | Mengetahui | Kepala Unit | Approval |
| 3 | Verifikasi / kembalikan | Kasubbag TU | Approval |
| 4 | Approve / reject final | Kepala Pusat | Approval |
| 5 | Disposisi ke gudang | Pengurus Barang | Approval |
| 6 | Proses disposisi & buat SBBK | Admin Gudang Kategori | Distribusi → Daftar Permintaan → Proses |
| 7 | Proses & kirim SBBK | Admin Gudang / Pengurus Barang | Distribusi → Distribusi Barang (SBBK) |
| 8 | Terima barang | Admin Unit / Kepala Unit | Penerimaan Barang |
| 9 | Retur (opsional) | Admin Unit | Retur Barang Rusak |

### Kapan status "menunggu pengadaan"?

Jika barang **belum ada stok** di gudang pusat — permintaan masuk antrian pengadaan. Setelah barang tersedia, lanjut distribusi.

---

## 2. Peminjaman Barang antar unit

```
Diajukan → Verifikasi Unit A (peminjam) → Persetujuan Unit B (pemilik)
         → Approval Pengurus Barang → Diketahui Kasubbag TU → Serah Terima
         → Pengembalian → Selesai
```

**Pelaku:** Admin Unit / Kepala Unit kedua belah pihak, Pengurus Barang, Kasubbag TU.

---

## 3. RKU (Rencana Kebutuhan Unit)

### Status RKU

```
DRAFT → DIAJUKAN → DIPROSES → REVIEW (Kasubbag/Kepala Pusat)
      → DISETUJUI | DITOLAK | REVISION_REQUIRED
```

| Tahap | Pelaku |
|-------|--------|
| Input RKU + detail kebutuhan | Admin Unit |
| Review & disposisi | Perencana |
| Review administrasi | Kasubbag TU |
| Persetujuan akhir | Kepala Pusat |
| Monitoring | PPTK APBD/BLUD |

RKU disetujui menjadi dasar **paket pengadaan**.

---

## 4. Pengadaan → Stok → Distribusi

```
RKU disetujui
    ↓
Paket Pengadaan (menu Paket Berjalan)
    ↓
Mulai proses → DIPROSES / proses_pengadaan
    ↓
Barang tersedia → SELESAI + permintaan proses_distribusi
    ↓
Penerimaan stok di gudang pusat (jika perlu) → buat SBBK / distribusi
```

**Catatan:** Tombol **Mulai proses** dan **Barang tersedia** ada di detail paket pengadaan (dan aksi cepat di daftar Paket Berjalan). Setelah *Barang tersedia*, permintaan otomatis lanjut ke `proses_distribusi` agar bisa dibuat SBBK.

Detail: [alur_rku_pengadaan_pembayaran_serah_terima.md](../alur_rku_pengadaan_pembayaran_serah_terima.md)

---

## 5. Inventory & stok

### Alur data barang

```
Master Struktur Barang → Data Barang → Data Inventory (per gudang)
                                      → Data Stock (qty)
```

### Opname & adjustment

Admin gudang melakukan **stock opname** → selisih → **stock adjustment** → kartu stok terupdate.

Detail: [alur_inventory_pusat_distribusi_unit_kerja.md](../alur_inventory_pusat_distribusi_unit_kerja.md)

---

## 6. Aset & KIR

```
Register Aset (dari inventory/pengadaan)
    ↓
Assign ke ruangan / unit
    ↓
Cetak KIR
    ↓
Mutasi aset (pindah ruangan/unit) bila perlu
```

---

## 7. Pemeliharaan & kalibrasi

### Permintaan pemeliharaan (corrective / ad-hoc)

Acuan lengkap: [ALUR_PERMINTAAN_PEMELIHARAAN.md](../ALUR_PERMINTAAN_PEMELIHARAAN.md)

```
Ajukan (Transaksi) → Diketahui Ka.Unit → Approve Ka.PPKP
    → Disposisi Pengurus Barang → Teknisi kerjakan (Pemeliharaan → Daftar Permintaan)
    → Service Report → Diketahui PB / Ka.Unit / Ka.PPKP
    → Selesai  |  Pending spare part → beli → lanjut perbaikan  |  Pengembalian barang
```

### Jadwal & kalibrasi

```
Aset terdaftar → Jadwal Pemeliharaan
              → (auto) Permintaan Pemeliharaan
              → Kalibrasi / Service Report
              → Update kondisi aset
```

Detail tambahan: [alur_pemeliharaan_aset_maintenance_kalibrasi.md](../alur_pemeliharaan_aset_maintenance_kalibrasi.md)

---

## 8. Keuangan

> Modul pembayaran **belum tersedia** di aplikasi. Alur di bawah adalah target bisnis, bukan langkah UI aktif.

```
Proses Pengadaan selesai / tagihan
    ↓
(Modul Pembayaran — belum live)
    ↓
Rekonsiliasi dengan paket pengadaan
```

---

## Glossarium status permintaan barang

| Status | Arti untuk user |
|--------|-----------------|
| `draft` | Masih disimpan, belum diajukan |
| `diajukan` | Menunggu approval level 1 |
| `diverifikasi` | Lolos verifikasi TU / siap disposisi |
| `ditolak` | Ditolak — tidak dilanjutkan |
| `menunggu_pengadaan` | Disetujui tapi stok belum ada |
| `proses_pengadaan` | Sedang di pengadaan |
| `barang_tersedia` | Stok sudah ada di pusat |
| `proses_distribusi` | Sedang disiapkan SBBK |
| `dikirim` | Barang dikirim ke unit |
| `diterima` | Unit sudah terima |
| `selesai` | Transaksi closed |

---

## Troubleshooting singkat

| Masalah | Solusi |
|---------|--------|
| Menu tidak muncul | Minta admin cek role & permission |
| Tidak bisa approve | Pastikan status & level approval Anda sesuai |
| Barang tidak muncul di dropdown | Cek filter jenis (Persediaan/Farmasi) & stok |
| Stok unit tidak update | Pastikan penerimaan barang sudah diverifikasi |
| Form buat SBBK tidak terbuka dari disposisi | Pastikan permission `transaction.distribusi.create` / akses menu Distribusi |

---

## Dokumen terkait

- [DIAGRAM_ALUR_TRANSAKSI.md](../DIAGRAM_ALUR_TRANSAKSI.md) — diagram visual
- [DAFTAR_DOKUMEN_CETAK.md](../DAFTAR_DOKUMEN_CETAK.md) — SBBK, KIR, dll.
