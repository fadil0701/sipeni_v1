# Panduan Pengguna SI-MANTIK

Panduan lengkap **Sistem Informasi Manajemen Terintegrasi (SI-MANTIK)**: cara kerja aplikasi, modul, alur bisnis, matriks role/permission, dan instruksi per role.

---

## Siapa yang bisa membuka Panduan di aplikasi?

| Role | Akses menu **Panduan Pengguna** (`/panduan`) |
|------|-----------------------------------------------|
| **Administrator** (`admin` / `administrator`) | Ya |
| **Super Administrator** | Ya |
| Role lain (unit, gudang, teknisi, dll.) | **Tidak** (menu disembunyikan, URL mengembalikan 403) |

File markdown di folder ini tetap ada di repositori untuk Admin IT / dokumentasi internal.

---

## Mulai dari mana? (untuk Administrator)

| Kebutuhan | Baca |
|-----------|------|
| Login & navigasi dasar | [01 — Pengenalan & Login](./01-pengenalan-dan-login.md) |
| Semua menu sidebar | [02 — Modul & Fitur](./02-modul-dan-fitur.md) |
| Alur bisnis utama | [03 — Alur Kerja Utama](./03-alur-kerja-utama.md) |
| **Matriks role × akses** | [04 — Matriks Akses Role](./04-matrik-akses-role.md) |
| **Cara kelola role, permission & user** | [05 — Mengelola Role, Permission & User](./05-mengelola-role-permission-dan-user.md) |
| Detail tiap role (termasuk teknisi) | [Panduan per Role](./per-role/README.md) |

---

## Daftar isi

| # | Dokumen | Isi |
|---|---------|-----|
| 1 | [Pengenalan & Login](./01-pengenalan-dan-login.md) | Login, password, navigasi, scope unit |
| 2 | [Modul & Fitur](./02-modul-dan-fitur.md) | Menu sidebar & kegunaan |
| 3 | [Alur Kerja Utama](./03-alur-kerja-utama.md) | Permintaan → distribusi, RKU, pemeliharaan, dll. |
| 4 | [Matriks Akses Role](./04-matrik-akses-role.md) | Permission matrix ramah pembaca |
| 5 | [Mengelola Role, Permission & User](./05-mengelola-role-permission-dan-user.md) | Langkah Admin IT |
| — | [per-role/](./per-role/README.md) | Panduan per role kanonik + teknisi |

---

## Role kanonik (ringkas)

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
| PPTK APBD / BLUD | [pptk_apbd](./per-role/pptk_apbd.md) / [pptk_blud](./per-role/pptk_blud.md) |
| Pengurus Barang | [per-role/pengurus_barang.md](./per-role/pengurus_barang.md) |
| **Teknisi ATEM** | [per-role/teknisi_atem.md](./per-role/teknisi_atem.md) |
| **Teknisi IT** | [per-role/teknisi_it.md](./per-role/teknisi_it.md) |
| Admin Gudang (pusat/aset/persediaan/farmasi) | [per-role/](./per-role/README.md) |
| Admin / Administrator IT | [per-role/admin-dan-administrator.md](./per-role/admin-dan-administrator.md) |

---

## Versi PDF

Folder [`pdf/`](./pdf/) — regenerasi: `php artisan panduan:export-pdf` (dari environment yang mendukung).

---

## Dokumen teknis terkait

- [HAK_AKSES_ROLE_DAN_DELEGASI.md](../HAK_AKSES_ROLE_DAN_DELEGASI.md) — detail teknis permission & delegasi
- [rbac-phase1/role-mapping.md](../rbac-phase1/role-mapping.md) — mapping role legacy
