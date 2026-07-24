# Perbaikan audit UI & cetak (Juli 2026)

Dokumen ini mencatat perbaikan yang diterapkan setelah audit fitur/menu SI-MANTIK (Juli 2026).  
Prinsip: **jangan menampilkan fitur kosong**; aktifkan cetak SBBK; rapikan label yang menyesatkan.

## Ringkasan perubahan

| # | Perbaikan | Keputusan | Perubahan kode / ops |
|---|-----------|-----------|----------------------|
| 1 | Menu **Keuangan → Pembayaran** | Sembunyikan sampai modul siap | Hapus dari sidebar & `PermissionHelper` menu map; `PembayaranController` abort 404 kecuali `FEATURE_FINANCE_PEMBAYARAN=true` |
| 2 | Tombol **Pesan** di header | Hapus | Hanya bell **Notifikasi** yang tersisa |
| 3 | Link **Lupa Password?** di login | Ganti teks bantuan | Teks: hubungi Administrator (belum ada reset email) |
| 4 | Cetak SBBK / Print Templates | Default ON | `FEATURE_PRINT_TEMPLATES` default `true` di `config/sipeni.php` + `.env*.example` |
| 5 | Label **RKU & Aktivitas** | Rename | Sidebar → **RKU**; highlight route diperbaiki |
| 6 | Label **Proses & Realisasi Pengadaan** | Rename | Sidebar + halaman → **Paket Berjalan** |
| 7 | Kartu laporan **Pemakaian = 0** | Hapus | Hanya Distribusi + Retur di laporan transaksi |
| 8 | File orphan | Hapus | Lihat daftar di bawah |
| 9 | Tombol **Barang tersedia** / **Mulai proses** | Aktifkan alur pengadaan → distribusi | Route + UI di detail paket & daftar Paket Berjalan; memanggil `PengadaanService` |
| 10 | Portal **user.requests** ganda | Satukan ke Transaksi | Redirect ke `transaction.permintaan-barang`; alias permission legacy |

## Flag env baru / diubah

| Variabel | Default | Arti |
|----------|---------|------|
| `FEATURE_PRINT_TEMPLATES` | `true` | Menu Admin → Workflow Template + cetak SBBK/retur via template |
| `FEATURE_FINANCE_PEMBAYARAN` | `false` | Cadangan aktifkan modul pembayaran (tetap butuh implementasi view) |
| `FEATURE_PEMAKAIAN_BARANG` | `false` | Tidak diubah (tetap off) |

**Production:** pastikan `.env` punya `FEATURE_PRINT_TEMPLATES=true`, lalu `php artisan config:clear` (atau rebuild cache). Pastikan template key `distribusi.sbbk` aktif di DB.

## File yang dihapus (tidak relevan / orphan)

| Path | Alasan |
|------|--------|
| `resources/views/inventory/mutasi-stok/index.blade.php` | Tidak ada route; form `action="#"` |
| `resources/views/transaction/compile-distribusi/*.blade.php` | Compile digabung ke Distribusi; controller hanya redirect |
| `resources/views/components/enterprise/sidebar.blade.php` | Tidak dipakai layout live (`layouts/app.blade.php`) |

## Dokumentasi yang diselaraskan

- Panduan pengguna: label menu, status Keuangan “belum tersedia”, hapus klaim Lupa Password self-service
- `AGENTS.md`, `docs/OPERATIONS.md`, `docs/README.md`
- Banner pada `docs/AUDIT_SISTEM_LENGKAP_2026-06-21.md` (klaim kelengkapan fitur Juni 2026 sebagian usang)

## Pemisahan menu RKU (Juli 2026)

| Menu | Lokasi sidebar | Siapa | Isi |
|------|----------------|-------|-----|
| **RKU** | Transaksi | Unit kerja (role unit-scoped) | Form & monitoring RKU **unit sendiri** |
| **Daftar RKU** | Perencanaan | Perencana / pusat | Seluruh RKU lintas unit; bisa tambah RKU |

Implementasi: query `context=unit` \| `context=daftar` pada `planning.rku.index` / `create`.

## Batch berikutnya (Juli 2026) — pengadaan & portal

### Barang tersedia / Mulai proses

| Aksi | Route | Efek |
|------|-------|------|
| **Mulai proses** | `POST procurement.paket-pengadaan.process` | Paket → `DIPROSES`; permintaan → `proses_pengadaan` |
| **Barang tersedia** | `POST procurement.paket-pengadaan.mark-barang-tersedia` | Paket → `SELESAI`; permintaan → `barang_tersedia` lalu auto `proses_distribusi` (`resumeToDistribusi`) |

UI: detail paket + aksi cepat di **Pengadaan → Paket Berjalan**.

**Ops:** reseeder permission role `pengadaan` / `pptk_*` (atau sync + assign) agar permission route baru ada:

- `procurement.paket-pengadaan.process`
- `procurement.paket-pengadaan.mark-barang-tersedia`

### Portal user.requests

Route `user.requests.*` tetap ada (kompatibilitas bookmark/permission lama) tetapi **redirect** ke `transaction.permintaan-barang.*`.  
`PermissionHelper` memetakan legacy `user.requests.{index,create,store,show}` sebagai parent akses ke route transaksi setara.

## Backlog tersisa

- Implementasi penuh modul Pembayaran + Kontrak
- Aktifkan Pemakaian Barang (jika dibutuhkan bisnis)
- Password reset email
- PrintTemplate untuk permintaan / SR / kalibrasi
- Opsional: `NOTIFICATIONS_MAIL_ENABLED` + SMTP
- Integrasi PSrE/BSrE

## Verifikasi cepat

1. Login → sidebar **tanpa** Keuangan; header **tanpa** ikon pesan
2. Login page → teks “Hubungi Administrator” (bukan link mati)
3. Dengan flag print ON + template SBBK aktif → tombol cetak di detail distribusi
4. Perencanaan → menu **RKU** / **Daftar RKU**; Pengadaan → **Paket Berjalan**
5. Detail paket (status DIAJUKAN/DIPROSES) → tombol **Mulai proses** / **Barang tersedia**
6. Setelah Barang tersedia → status permintaan menjadi `proses_distribusi` (muncul di alur SBBK)
7. URL `/requests` → redirect ke daftar Permintaan Barang transaksi
