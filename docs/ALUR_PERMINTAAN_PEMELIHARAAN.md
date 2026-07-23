# Alur Proses Permintaan Pemeliharaan

Acuan bisnis resmi SI-MANTIK (selaras flowchart operasional).

## Ringkasan

```text
Permintaan Pemeliharaan
        ↓
Diketahui Kepala Unit
        ↓
Approval Kepala PPKP (Kepala Pusat)
        ↓
Disposisi Pengurus Barang  →  (pilih Teknisi ATEM / Teknisi IT / Kontrak Service / Vendor)
        ↓
Proses identifikasi & perbaikan oleh Teknisi / Vendor
        ↓
Service Report
        ↓
Laporan SR diketahui: Pengurus Barang → Kepala Unit → Kepala PPKP
        ↓
              ┌─────────────────────┼─────────────────────┐
              ▼                     ▼                     ▼
   Pending pembelian        Baik / tidak ada       Rusak berat /
   spare part               kendala                tidak bisa diperbaiki
              ↓                     ↓                     ↓
   Approve Kepala PPKP           Selesai           Pengembalian barang
   untuk pembelian                                     (ke Pengurus)
              ↓
   Disposisi Pengadaan
              ↓
   Setelah pembelian → lanjut perbaikan ──→ (kembali ke Service Report)
```

## Mapping ke modul / menu

| Tahap flowchart | Di sistem | Menu / aksi |
|-----------------|-----------|-------------|
| Permintaan Pemeliharaan | Status `DRAFT` → `DIAJUKAN` | **Transaksi → Permintaan Pemeliharaan** (Tambah / Ajukan) |
| Diketahui Kepala Unit | Approval step 2 | **Approval** |
| Approval Kepala PPKP | Approval step 3 → status `DISETUJUI` | **Approval** |
| Disposisi Pengurus Barang | Approval step 4 → status `DIPROSES` + isi pelaksana | **Approval** (disposisi pemeliharaan) |
| Identifikasi & perbaikan teknisi | Status `DIPROSES` | **Pemeliharaan → Daftar Permintaan** → tombol **Proses** |
| Service Report | Dokumen `service_report` | **Proses** membuka form Laporan Servis |
| SR diketahui Ka.Unit / PB / Ka.PPKP | Approval step 6–8 | **Approval** |
| Baik / tidak ada kendala | Rekomendasi `BAIK` / `TIDAK_ADA` → `SELESAI` | Otomatis setelah step 8 |
| Pending spare part | Rekomendasi `PENDING_SPAREPART` → `MENUNGGU_PENGADAAN` | Step 9–10 (approve pembelian + disposisi pengadaan) |
| Setelah pembelian lanjut perbaikan | Status kembali `DIPROSES` | **Daftar Permintaan** → **Lanjut Perbaikan**, lalu **Proses** (SR berikutnya) |
| Tidak bisa diperbaiki | Rekomendasi `TIDAK_BISA_DIPERBAIKI` → `DIKEMBALIKAN_PENGURUS` | Update kondisi register aset |

## Rekomendasi Service Report

| Nilai | Arti flowchart | Hasil |
|-------|----------------|-------|
| `BAIK` | Baik | Selesai |
| `TIDAK_ADA` | Tidak ada kendala | Selesai |
| `PENDING_SPAREPART` | Pending pembelian spare part | Rantai pembelian → lanjut perbaikan → SR lagi |
| `TIDAK_BISA_DIPERBAIKI` | Rusak berat / tidak bisa diperbaiki | Pengembalian ke Pengurus (`DIKEMBALIKAN_PENGURUS`) |

## Catatan peran

- **User unit:** mengajukan & memonitor di **Transaksi → Permintaan Pemeliharaan**.
- **Pengurus Barang:** disposisi pelaksana + mengetahui SR (via Approval).
- **Teknisi / Vendor:** mengerjakan dari **Pemeliharaan → Daftar Permintaan** setelah disposisi.
- **Kepala Unit / Kepala PPKP:** approval awal + mengetahui SR (+ approve pembelian bila pending spare part).
- **Pengadaan:** menerima disposisi pembelian spare part (step 10).
