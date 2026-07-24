# Daftar dokumen cetak SIPENI

Dokumen ini menyamakan persepsi **modul sumber** ↔ **nama dokumen yang dicetak** ↔ **key template** (untuk `PrintTemplate`, bila memakai mesin template admin) dan **status** di kode. Daftar boleh ditambah seiring kebutuhan.

## Ringkasan aturan penamaan `key` template

- Format disarankan: `domain.nama_dokumen` (huruf kecil, angka, titik, strip).
- Satu jenis dokumen = satu (atau beberapa varian aktif dengan key berbeda, jarang).
- Key dipakai di kode: `PrintTemplate::where('key', '…')->where('is_active', true)`.

## Daftar (versi saat ini)

| No | Modul / alur SIPENI | Nama dokumen cetak | Key template (disarankan) | Status di aplikasi (2026-05) |
|----|---------------------|--------------------|---------------------------|------------------------------|
| 1 | Permintaan Barang | Dokumen Permintaan Barang | `permintaan.dokumen` | **Belum** terhubung `PrintTemplate`; cetak biasanya perlu rute + payload baru. |
| 2 | Distribusi Barang | Dokumen SBBK (Surat Bukti Barang Keluar) | `distribusi.sbbk` | **Sudah**: admin template + `DistribusiController::printSbbk` + `SbbkPrintTemplateData`. |
| 3 | Retur Barang | Dokumen Pengembalian Barang | `retur.pengembalian` | **Sudah** (kode + UI) bila `FEATURE_PRINT_TEMPLATES=true` dan template aktif; default flag ON sejak Juli 2026. |
| 4 | Kartu Inventaris Ruangan (KIR) | Dokumen KIR | `aset.kir` | **Sebagian**: halaman KIR mendukung mode cetak browser (`?print=1`); belum memakai `PrintTemplate` / placeholder dinamis. |
| 5 | Laporan Service (Maintenance) | Dokumen Service Report | `maintenance.service_report` | **Belum** terhubung `PrintTemplate`; data ada di `ServiceReportController` (show/dll.). |
| 6 | Kalibrasi Aset | Dokumen Daftar kalibrasi aset | `maintenance.kalibrasi_daftar` | **Belum** terhubung `PrintTemplate`; modul kalibrasi ada, cetak daftar formal perlu rute + payload. |

## Yang “wajib” vs “opsional”

- **Wajib secara bisnis**: dokumen yang menjadi bukti administrasi (SBBK, permintaan, retur, KIR, dll.) menurut kebijakan instansi Anda — baris di atas mencerminkan kebutuhan yang Anda sebutkan.
- **Opsional / bisa bertambah**: contoh lain ke depan misalnya **BAST penerimaan**, **Berita Acara Stock Opname**, **Label rak**, **Surat jalan internal**, dll. Tambahkan baris baru di tabel ini + key baru + entri di `config/print_templates.php` bila perlu chip variabel dari kode.

## Integrasi teknis (ringkas)

1. **Admin** — Buat/aktifkan baris `print_templates` dengan **key** sesuai kolom di atas (boleh diubah asal konsisten dengan kode).
2. **Provider variabel (opsional)** — Untuk chip bantuan & struktur payload terstandarisasi, daftarkan class di `config/print_templates.php` (`variable_providers`), dengan method statis `variableGroups()` + biasanya `payload(...)` untuk data hidup.
3. **Rute cetak** — Tambahkan aksi “Cetak” di UI modul yang memuat template aktif lalu `PrintTemplateRenderer::render($template, $data)`.

## Riwayat perubahan

| Tanggal | Perubahan |
|---------|-----------|
| 2026-05-04 | Dokumen dibuat dari kesepakatan daftar 6 jenis dokumen awal; status disesuaikan dengan kode saat pencatatan. |
