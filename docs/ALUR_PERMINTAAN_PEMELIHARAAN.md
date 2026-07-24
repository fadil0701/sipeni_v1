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
   Approve Kepala PPKP           Selesai                 Selesai
   untuk pembelian                              (update kondisi aset)
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
| Baik / tidak ada kendala | Rekomendasi `BAIK` / `TIDAK_ADA` → `SELESAI` | Otomatis setelah step 8 (diketahui Kepala Pusat) |
| Pending spare part | Rekomendasi `PENDING_SPAREPART` → `MENUNGGU_PENGADAAN` | Step 8 = setujui/tolak pembelian → disposisi pengadaan (step 10) |
| Setelah pembelian lanjut perbaikan | Status kembali `DIPROSES` | **Daftar Permintaan** → **Lanjut Perbaikan**, lalu **Proses** (SR berikutnya) |
| Tidak bisa diperbaiki | Rekomendasi `TIDAK_BISA_DIPERBAIKI` → `SELESAI` | Diketahui Kepala Pusat; kondisi register aset di-update |

## Rekomendasi Service Report

| Nilai | Arti flowchart | Hasil setelah diketahui Kepala Pusat |
|-------|----------------|-------|
| `BAIK` | Baik | `SELESAI` |
| `TIDAK_ADA` | Tidak ada kendala | `SELESAI` |
| `PENDING_SPAREPART` | Pending pembelian spare part | Setujui/tolak pembelian → disposisi pengadaan → lanjut perbaikan → SR lagi |
| `TIDAK_BISA_DIPERBAIKI` | Rusak berat / tidak bisa diperbaiki | `SELESAI` (+ update kondisi register aset) |

## Catatan peran

- **User unit:** mengajukan & memonitor di **Transaksi → Permintaan Pemeliharaan**.
- **Pengurus Barang:** disposisi pelaksana + mengetahui SR (via Approval).
- **Teknisi / Vendor:** mengerjakan dari **Pemeliharaan → Daftar Permintaan** setelah disposisi.
- **Kepala Unit / Kepala PPKP:** approval awal + mengetahui SR (+ approve pembelian bila pending spare part).
- **Pengadaan:** menerima disposisi pembelian spare part (step 10).
