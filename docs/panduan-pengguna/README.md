# Panduan Pengguna SI-MANTIK

Panduan lengkap penggunaan **Sistem Informasi Manajemen Terintegrasi (SI-MANTIK)** — mencakup cara kerja aplikasi, kegunaan setiap modul/fitur, alur bisnis, dan instruksi khusus per role.

---

## Panduan di dalam aplikasi (disarankan)

Setelah login, buka menu sidebar **Panduan Pengguna** (`/panduan`):

| Fitur | Keterangan |
|-------|------------|
| **Panduan role Anda** | Otomatis menampilkan panduan sesuai role login |
| **Bab umum** | Pengenalan, modul, alur kerja, matriks akses |
| **Unduh PDF** | Versi cetak per bab atau per role |

Dashboard juga menampilkan pintasan ke panduan role Anda.

---

## Untuk siapa?

| Audiens | Mulai dari |
|---------|------------|
| User baru | [01 — Pengenalan & Login](./01-pengenalan-dan-login.md) |
| Operator harian (unit/gudang) | [02 — Modul & Fitur](./02-modul-dan-fitur.md) + [Panduan per Role](./per-role/README.md) |
| Pimpinan / approval | [03 — Alur Kerja Utama](./03-alur-kerja-utama.md) |
| Admin IT | [per-role/admin-dan-administrator.md](./per-role/admin-dan-administrator.md) |

---

## Daftar isi panduan

| # | Dokumen | Isi |
|---|---------|-----|
| 1 | [Pengenalan & Login](./01-pengenalan-dan-login.md) | Apa itu SI-MANTIK, login, navigasi, mobile, scope unit |
| 2 | [Modul & Fitur Lengkap](./02-modul-dan-fitur.md) | Semua menu sidebar, kegunaan, halaman utama |
| 3 | [Alur Kerja Utama](./03-alur-kerja-utama.md) | Permintaan → approval → distribusi → penerimaan, RKU, pengadaan, dll. |
| 4 | [Matriks Akses Role](./04-matrik-akses-role.md) | Tabel ringkas: role × modul × tugas |
| 5 | [Panduan per Role](./per-role/README.md) | 15 role kanonik + Admin IT |

---

## Role kanonik (15)

| Role | Panduan |
|------|---------|
| Super Administrator | [per-role/super_administrator.md](./per-role/super_administrator.md) |
| Kepala Pusat | [per-role/kepala_pusat.md](./per-role/kepala_pusat.md) |
| Kasubbag TU | [per-role/kasubbag_tu.md](./per-role/kasubbag_tu.md) |
| Kepala Unit | [per-role/kepala_unit.md](./per-role/kepala_unit.md) |
| Admin Unit | [per-role/admin_unit.md](./per-role/admin_unit.md) |
| Perencana | [per-role/perencana.md](./per-role/perencana.md) |
| Pengadaan | [per-role/pengadaan.md](./per-role/pengadaan.md) |
| Keuangan | [per-role/keuangan.md](./per-role/keuangan.md) |
| PPTK APBD | [per-role/pptk_apbd.md](./per-role/pptk_apbd.md) |
| PPTK BLUD | [per-role/pptk_blud.md](./per-role/pptk_blud.md) |
| Pengurus Barang | [per-role/pengurus_barang.md](./per-role/pengurus_barang.md) |
| Admin Gudang Pusat | [per-role/admin_gudang_pusat.md](./per-role/admin_gudang_pusat.md) |
| Admin Gudang Aset | [per-role/admin_gudang_aset.md](./per-role/admin_gudang_aset.md) |
| Admin Gudang Persediaan | [per-role/admin_gudang_persediaan.md](./per-role/admin_gudang_persediaan.md) |
| Admin Gudang Farmasi | [per-role/admin_gudang_farmasi.md](./per-role/admin_gudang_farmasi.md) |
| Admin / Administrator IT | [per-role/admin-dan-administrator.md](./per-role/admin-dan-administrator.md) |

> **Catatan:** Role legacy (`pegawai`, `admin_gudang`, `perencanaan`, dll.) masih bisa dipakai di data lama tetapi secara fungsional setara dengan role kanonik di atas. Lihat [docs/rbac-phase1/role-mapping.md](../rbac-phase1/role-mapping.md).

---

## Versi PDF

Panduan tersedia dalam bentuk PDF di folder [`pdf/`](./pdf/).

| File | Isi |
|------|-----|
| [panduan-pengguna-simantik-lengkap.pdf](./pdf/panduan-pengguna-simantik-lengkap.pdf) | **Semua bab + panduan per role** (satu file) |
| [01-pengenalan-dan-login.pdf](./pdf/01-pengenalan-dan-login.pdf) | Bab 1 |
| [02-modul-dan-fitur.pdf](./pdf/02-modul-dan-fitur.pdf) | Bab 2 |
| [03-alur-kerja-utama.pdf](./pdf/03-alur-kerja-utama.pdf) | Bab 3 |
| [04-matrik-akses-role.pdf](./pdf/04-matrik-akses-role.pdf) | Bab 4 |
| [per-role/](./pdf/) | PDF per role (mis. `admin-gudang-aset.pdf`) |

**Regenerasi PDF** (setelah mengubah file `.md`):

```bash
php artisan panduan:export-pdf
```

Opsi: `--combined-only` (hanya PDF lengkap), `--file=per-role/admin_gudang_aset.md` (satu file).

---

## Dokumentasi teknis terkait

- [Alur Transaksi (detail step-by-step)](../ALUR_TRANSAKSI.md)
- [Diagram Alur Transaksi](../DIAGRAM_ALUR_TRANSAKSI.md)
- [Alur Inventory & Distribusi](../alur_inventory_pusat_distribusi_unit_kerja.md)
- [Alur RKU → Pengadaan → Pembayaran](../alur_rku_pengadaan_pembayaran_serah_terima.md)
- [Alur Pemeliharaan & Kalibrasi](../alur_pemeliharaan_aset_maintenance_kalibrasi.md)
- [Daftar Dokumen Cetak](../DAFTAR_DOKUMEN_CETAK.md)

---

**Versi panduan:** 1.0 · **Terakhir diperbarui:** Juni 2026 · **Aplikasi:** SI-MANTIK (sipeni_v1)
