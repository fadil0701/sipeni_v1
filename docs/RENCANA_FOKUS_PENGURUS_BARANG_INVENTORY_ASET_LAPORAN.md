# Rencana Fokus Pengembangan
# Pengurus Barang, Inventory, Aset, dan Laporan

Dokumen ini adalah turunan fokus dari roadmap besar SI-PENI, khusus untuk area:

- Pengurus barang (alur operasional transaksi)
- Inventory (akurasi stok dan kontrol)
- Aset (lifecycle aset)
- Laporan (operasional dan manajerial)

## 1. Tujuan Utama

- Menjamin alur operasional barang berjalan rapi, konsisten, dan terkontrol.
- Menjaga akurasi stok serta mencegah inkonsistensi data transaksi.
- Membuat lifecycle aset dapat ditelusuri end-to-end.
- Menyediakan laporan yang relevan untuk pengurus barang, kepala unit, dan pimpinan.

## 2. Scope Pengembangan

## 2.1 Pengurus Barang

- Permintaan barang
- Approval permintaan
- Distribusi barang
- Pemakaian barang
- Retur barang
- Jejak status dokumen transaksi

## 2.2 Inventory

- Data stock per gudang/unit kerja
- Inventory item per barang
- Stock adjustment (koreksi stok)
- Rekonsiliasi stok dari transaksi

## 2.3 Aset

- Register aset
- KIR (Kartu Inventaris Ruangan)
- Mutasi aset antar ruang/unit
- Keterkaitan aset dengan inventory item
- Perubahan kondisi/status aset

## 2.4 Laporan

- Laporan stok barang
- Laporan pergerakan barang (masuk/keluar/retur/pemakaian)
- Laporan aset per unit/ruangan/kondisi
- Dashboard KPI operasional

## 3. Prinsip Pengembangan

- Validasi relasi master harus konsisten:
  - `unit kerja -> ruangan`
  - `unit kerja -> pegawai`
- Semua kontrol penting dilakukan di server-side (bukan hanya di UI).
- Setiap transaksi/aset kritis memiliki histori perubahan.
- Perubahan dibuat bertahap dengan UAT per fase.

## 4. Backlog Prioritas (High Impact)

## 4.1 Prioritas P0 (Wajib)

- Scope dropdown relasi unit kerja/ruangan/pegawai di semua create/edit.
- Validasi anti-stok minus tidak valid.
- Sinkronisasi stok terhadap seluruh transaksi barang.
- Sinkronisasi aset terhadap mutasi/KIR.
- Laporan inti operasional (stok, pemakaian, mutasi, retur, aset).

## 4.2 Prioritas P1 (Sangat Disarankan)

- Workflow approval yang seragam antar modul transaksi.
- Audit trail perubahan data penting.
- Dashboard KPI dasar.
- Ekspor laporan PDF/Excel standar.

## 4.3 Prioritas P2 (Tahap Lanjut)

- Notifikasi in-app/email untuk event penting.
- SLA monitoring (waktu approval, waktu proses transaksi).
- Optimasi performa query laporan volume besar.

## 5. Roadmap 8 Minggu

## Minggu 1-2: Hardening Pengurus Barang

**Target:**
- Relasi unit kerja/ruangan/pegawai konsisten di seluruh form transaksi.
- Status alur transaksi seragam (draft, diajukan, disetujui, diproses, selesai/ditolak).

**Task utama:**
- Audit form create/edit modul transaksi.
- Terapkan filtering dinamis + validasi server-side.
- Rapikan rule approval di permintaan/distribusi.

**Output:**
- Error relasi antar master data turun signifikan.

## Minggu 3-4: Stabilitas Inventory

**Target:**
- Stok sistem akurat dan dapat direkonsiliasi.

**Task utama:**
- Implement guard stok minus.
- Perkuat stock adjustment (alasan, otorisasi, histori).
- Buat job/fitur rekonsiliasi stok dari transaksi.

**Output:**
- Selisih stok sistem vs transaksi terkontrol.

## Minggu 5-6: Penyempurnaan Aset

**Target:**
- Lifecycle aset end-to-end konsisten.

**Task utama:**
- Sinkronkan register aset, KIR, mutasi, dan inventory item.
- Validasi unit kerja dan ruangan saat mutasi/KIR/update aset.
- Tambah histori perubahan aset (kondisi, status, lokasi, PJ).

**Output:**
- Data aset lebih valid dan mudah diaudit.

## Minggu 7-8: Laporan dan Quality Gate

**Target:**
- Laporan operasional siap pakai manajemen.
- Alur kritis punya regression test dasar.

**Task utama:**
- Bangun laporan prioritas + export.
- Dashboard KPI operasional tahap 1.
- Tambah test feature alur kritis.

**Output:**
- Keputusan operasional lebih cepat berbasis data.

## 6. Gap Matrix Khusus Fokus Modul

| Area | Kondisi Saat Ini | Gap | Prioritas | Estimasi |
|---|---|---|---|---|
| Pengurus Barang | Alur modul inti sudah tersedia | Workflow approval belum sepenuhnya seragam; histori status belum konsisten | P0 | 2-3 minggu |
| Inventory | Data stock/item/adjustment tersedia | Guard stok minus dan rekonsiliasi belum menyeluruh | P0 | 2-3 minggu |
| Aset | Register, KIR, mutasi sudah ada | Sinkronisasi lintas proses belum seragam di semua skenario; histori perubahan aset belum penuh | P0 | 2-4 minggu |
| Laporan | Halaman report sudah ada | Laporan terstandar + KPI operasional + export lintas modul belum lengkap | P1 | 2-3 minggu |
| Audit & Test | Ada logging dasar | Audit trail bisnis + feature test alur kritis masih minim | P1 | 3-4 minggu (bertahap) |

## 7. Estimasi Effort (Ringkas)

Asumsi tim kecil: 2 Fullstack Dev + 1 QA (part-time) + 1 PIC user untuk UAT.

- Pengurus barang: **3-4 minggu**
- Inventory: **3 minggu**
- Aset: **3-4 minggu**
- Laporan & dashboard: **2-3 minggu**
- QA regression + hardening: **2 minggu (paralel di akhir tiap fase)**

Total efektif (dengan overlap): **8-10 minggu**.

## 8. Deliverable Wajib

- Dokumen alur proses transaksi dan aset (SOP ringkas).
- Matriks validasi relasi master per modul.
- Paket laporan utama (stok, transaksi, aset) + export.
- Minimal 10-15 feature test untuk alur paling kritis.
- Daftar kontrol audit trail untuk entitas utama.

## 9. KPI Keberhasilan

- Error validasi relasi master menurun.
- Tidak ada stok minus tidak valid pada proses normal.
- Waktu proses approval lebih singkat.
- Selisih data stok hasil rekonsiliasi menurun.
- Laporan bulanan dapat dihasilkan otomatis dan konsisten.

## 10. Risiko dan Mitigasi

- **Data lama tidak konsisten**  
  Mitigasi: data cleanup bertahap + validasi ketat saat update.

- **Perubahan kebutuhan user saat implementasi**  
  Mitigasi: review mingguan backlog + freeze scope sprint aktif.

- **Beban laporan tinggi**  
  Mitigasi: indexing query + optimasi filter + batching export.

- **UAT terlambat**  
  Mitigasi: UAT per fase kecil, bukan menunggu keseluruhan selesai.

---

Dokumen ini dapat dipakai langsung untuk dasar eksekusi sprint pengembangan fokus operasional barang, inventory, aset, dan laporan.
