# Rencana Pengembangan Sistem SI-PENI

Dokumen ini merangkum:

1. Rencana pengembangan menyeluruh lintas modul
2. Estimasi waktu implementasi
3. Daftar fitur yang perlu ditambahkan
4. Gap matrix per modul (sudah ada vs belum tersedia)

## 1) Tujuan Pengembangan

- Meningkatkan konsistensi data antar modul (master, transaksi, aset, inventory).
- Menjamin keamanan akses berbasis role dan unit kerja.
- Memperkuat keandalan operasional sistem melalui testing, audit trail, dan monitoring.
- Menyediakan visibilitas manajerial melalui dashboard KPI dan laporan terstandar.

## 2) Prioritas Pengembangan (Roadmap Tingkat Tinggi)

### Fase 1 - Stabilitas Fondasi (Bulan 1-2)

- Stabilisasi relasi data master (`unit kerja -> ruangan -> pegawai`) di semua form create/edit.
- Hardening validasi server-side untuk transaksi dan aset.
- Standardisasi permission per role dan per route.
- Konsistensi UX form dinamis (error handling, old input, dropdown terfilter).

**Output utama:**
- Data input lebih konsisten.
- Risiko bug relasi turun.
- Akses lintas unit kerja lebih aman.

### Fase 2 - Kontrol Proses Bisnis (Bulan 3-4)

- Workflow approval bertingkat lintas modul transaksi.
- Audit trail terstruktur (siapa, kapan, perubahan apa, nilai sebelum-sesudah).
- Notifikasi operasional (approval, stok minimum, jadwal maintenance).
- Dashboard KPI awal untuk pimpinan/unit.

**Output utama:**
- Proses bisnis lebih terukur dan dapat diaudit.
- Respons operasional lebih cepat.

### Fase 3 - Skalabilitas dan Integrasi (Bulan 5-6)

- Peningkatan performa query dan optimasi laporan.
- CI/CD dasar (lint, test, build, deploy).
- Monitoring & alerting aplikasi.
- Integrasi eksternal bertahap (jika dibutuhkan instansi).

**Output utama:**
- Rilis lebih stabil.
- Operasional produksi lebih aman.
- Siap scale untuk beban pengguna lebih besar.

## 3) Estimasi Waktu (Global)

- **Quick win (stabilitas inti):** 6-8 minggu
- **Menengah (workflow + monitoring):** 12-16 minggu
- **Lengkap (plus integrasi & DevOps):** 20-24 minggu

> Catatan: estimasi bergantung pada kapasitas tim, kualitas data existing, dan kecepatan UAT.

## 4) Fitur yang Harus Ditambahkan

### A. Integritas Data

- Validasi relasi master seragam di semua modul:
  - `id_unit_kerja` -> `id_ruangan`
  - `id_unit_kerja` -> `id_pegawai`
- Data quality tools:
  - deteksi data yatim (orphan)
  - pengecekan inkonsistensi relasi
  - laporan anomali data

### B. Workflow & Governance

- Approval bertingkat per jenis transaksi.
- Delegasi approval (PLH/PLT/cuti).
- SLA proses per tahap (draft, diajukan, disetujui, ditolak, selesai).

### C. Audit & Compliance

- Audit trail detail untuk modul kritis:
  - inventory
  - transaksi barang
  - register aset
  - mutasi/KIR
- Log akses data sensitif.

### D. Notifikasi

- In-app notification.
- Email notification.
- Opsional: WhatsApp Gateway.
- Trigger notifikasi:
  - approval pending
  - stok minimum
  - aset rusak
  - jadwal maintenance jatuh tempo

### E. Reporting & Dashboard

- Dashboard KPI lintas modul:
  - stok kritis
  - aset per kondisi
  - lead time approval
  - volume transaksi per unit
- Ekspor laporan standar (PDF/Excel).

### F. Quality Engineering & Operasional

- Unit test & feature test untuk alur kritis.
- Regression test dasar sebelum release.
- CI/CD pipeline.
- Backup-restore drill dan runbook incident.

## 5) Gap Matrix Per Modul

| Modul | Sudah Ada | Belum Ada (Gap) | Prioritas | Estimasi |
|---|---|---|---|---|
| **Master Data** (`unit kerja`, `gudang`, `ruangan`, `barang`, dll) | CRUD utama tersedia, relasi dasar berjalan | Validasi lintas master seragam di semua form; data quality check/deduplikasi | Tinggi | 1-2 minggu |
| **Master Manajemen** (`pegawai`, `jabatan`, role-permission) | CRUD role/user/pegawai/jabatan tersedia | Matriks permission terdokumentasi; audit akses lintas unit; delegasi otorisasi sementara | Tinggi | 1-2 minggu |
| **Inventory** (`data stock`, `inventory item`, `adjustment`, scan) | Proses stok dan item tersedia, QR scan tersedia | Guard stok negatif menyeluruh; rekonsiliasi stok vs transaksi; notifikasi stok minimum | Tinggi | 2-3 minggu |
| **Transaksi Barang** (`permintaan`, `penerimaan`, `distribusi`, `pemakaian`, `retur`) | Modul inti create/edit/index tersedia | Workflow approval seragam end-to-end; validasi integritas antar dokumen; histori status rapi | Sangat Tinggi | 3-4 minggu |
| **Aset** (`register`, `KIR`, `mutasi`) | Alur utama tersedia termasuk mutasi dan KIR | Penyapuan final relasi dinamis di semua form; histori perubahan aset; lifecycle aset lebih lengkap | Sangat Tinggi | 2-4 minggu |
| **Maintenance** (`jadwal`, `permintaan pemeliharaan`, `service report`, `kalibrasi`) | Struktur modul tersedia | PM schedule otomatis; reminder jatuh tempo; biaya maintenance per aset; KPI MTTR/MTBF | Sedang-Tinggi | 2-3 minggu |
| **Planning & Procurement** (`RKU`, `paket`, `proses`, `pembayaran`) | Struktur modul tersedia | Traceability penuh rencana -> pengadaan -> penerimaan -> aset/stok; kontrol pagu vs realisasi | Sedang | 3-4 minggu |
| **Reporting** | Halaman report tersedia | Dashboard KPI interaktif; ekspor standar lintas modul; filter analitik lanjutan | Tinggi | 2-3 minggu |
| **Portal User** (`dashboard`, `assets`, `requests`) | Fitur dasar user tersedia | Tracking status request lebih jelas; notifikasi personal; histori aktivitas user | Sedang | 1-2 minggu |
| **Notifikasi Sistem** | Belum ada pola terpusat yang matang | Notifikasi in-app/email/WA untuk proses kritis | Tinggi | 2 minggu |
| **Audit Trail & Compliance** | Logging aplikasi dasar ada | Audit trail bisnis terstruktur per entitas | Sangat Tinggi | 2-3 minggu |
| **Testing & QA** | Test default/contoh ada | Unit/feature test bisnis + regression suite | Sangat Tinggi | 3-5 minggu (bertahap) |
| **DevOps & Operasional** | Sistem berjalan dan deploy manual memungkinkan | CI/CD otomatis; backup-restore drill; monitoring & alerting produksi | Tinggi | 2-4 minggu |
| **Dokumentasi & SOP** | Dokumentasi parsial tersedia | Dokumen arsitektur, SOP operasional, playbook incident/release | Sedang | 1-2 minggu |

## 6) Rencana Implementasi 8 Minggu (Praktis)

### Minggu 1-2

- Finalisasi relasi master lintas form.
- Audit role/permission dan matriks akses.
- Perbaikan validasi server-side prioritas tinggi.

### Minggu 3-4

- Workflow approval inti transaksi.
- Histori status dokumen.
- Notifikasi dasar untuk approval pending.

### Minggu 5-6

- Audit trail entitas kritis.
- Dashboard KPI tahap 1.
- Ekspor laporan prioritas (PDF/Excel).

### Minggu 7-8

- Test otomatis alur kritis.
- Hardening performa query/laporan.
- Persiapan SOP rilis, backup, dan monitoring dasar.

## 7) KPI Keberhasilan

- Penurunan error input relasi master.
- Penurunan temuan data inkonsisten per minggu.
- Lead time approval menurun.
- Tidak ada stok minus tak valid.
- Meningkatnya coverage test pada modul kritis.
- Waktu pemulihan insiden (MTTR) membaik.

## 8) Risiko dan Mitigasi

- **Data legacy tidak konsisten**  
  Mitigasi: data cleanup script + validasi ketat + laporan anomali.

- **Perubahan kebutuhan role/approval saat berjalan**  
  Mitigasi: tetapkan baseline rule + change control mingguan.

- **Kapasitas tim terbatas**  
  Mitigasi: fokus modul high impact dulu (aset + transaksi + inventory).

- **UAT lambat**  
  Mitigasi: UAT per fase kecil, bukan menunggu semua fitur selesai.

---

Dokumen ini dapat dijadikan dasar penyusunan backlog sprint, penjadwalan tim, dan komunikasi ke stakeholder manajemen.
