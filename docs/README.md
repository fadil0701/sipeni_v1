# Dokumentasi SI-MANTIK

Sistem Informasi Manajemen Terintegrasi (SI-MANTIK) — indeks dokumentasi aktif.

---

## Audit & Perencanaan

| Dokumen | Isi |
|---------|-----|
| [Audit Sistem Lengkap (Juni 2026)](./AUDIT_SISTEM_LENGKAP_2026-06-21.md) | Temuan keamanan, fitur, operasional, roadmap perbaikan |

---

## Panduan Pengguna

Panduan end-user tersedia dalam **tiga bentuk**:

| Saluran | Lokasi |
|---------|--------|
| **Di aplikasi** | Sidebar → **Panduan Pengguna** (`/panduan`) — panduan role otomatis + bab umum + unduh PDF |
| **Markdown** | Folder [panduan-pengguna/](./panduan-pengguna/README.md) |
| **PDF** | [panduan-pengguna-simantik-lengkap.pdf](./panduan-pengguna/pdf/panduan-pengguna-simantik-lengkap.pdf) |

| Bab | Isi |
|-----|-----|
| [01 — Pengenalan & Login](./panduan-pengguna/01-pengenalan-dan-login.md) | Login, kebijakan password, navigasi, panduan in-app |
| [02 — Modul & Fitur](./panduan-pengguna/02-modul-dan-fitur.md) | Semua menu sidebar |
| [03 — Alur Kerja Utama](./panduan-pengguna/03-alur-kerja-utama.md) | Permintaan, RKU, distribusi, dll. |
| [04 — Matriks Role](./panduan-pengguna/04-matrik-akses-role.md) | Tabel akses per role (termasuk pemeliharaan & teknisi) |
| [05 — Role, Permission & User](./panduan-pengguna/05-mengelola-role-permission-dan-user.md) | Cara kelola akses (untuk Administrator) |
| [Panduan per Role](./panduan-pengguna/per-role/README.md) | Role kanonik + teknisi + Admin IT |

**Akses in-app:** menu Panduan Pengguna hanya Administrator / Super Administrator.

Regenerasi PDF: `php artisan panduan:export-pdf`

---

## Alur Bisnis & Transaksi

| Dokumen | Isi |
|---------|-----|
| [Alur Transaksi Barang](./ALUR_TRANSAKSI.md) | Step-by-step permintaan → penerimaan → retur |
| [Diagram Alur Transaksi](./DIAGRAM_ALUR_TRANSAKSI.md) | Flowchart visual |
| [Alur Inventory & Distribusi](./alur_inventory_pusat_distribusi_unit_kerja.md) | Gudang pusat → unit kerja |
| [Alur RKU → Pengadaan → Pembayaran](./alur_rku_pengadaan_pembayaran_serah_terima.md) | Perencanaan hingga serah terima |
| [Alur Permintaan Pemeliharaan](./ALUR_PERMINTAAN_PEMELIHARAAN.md) | Flowchart: ajukan → approve → teknisi → SR → selesai / spare part / pengembalian |
| [Alur Pemeliharaan & Kalibrasi](./alur_pemeliharaan_aset_maintenance_kalibrasi.md) | Maintenance aset (jadwal, kalibrasi, overview) |
| [Ekosistem Terintegrasi](./ekosistem_terintegrasi_rku_inventory_pengadaan_aset_maintenance.md) | Gambaran besar modul |

---

## RBAC & Keamanan Akses

| Dokumen | Isi |
|---------|-----|
| [Hak Akses, Role, dan Delegasi](./HAK_AKSES_ROLE_DAN_DELEGASI.md) | Permission = route name, panel admin |
| [RBAC Fase 1](./rbac-phase1/README.md) | Permission database-driven, wildcard Spatie |
| [RBAC Fase 2](./rbac-phase2/README.md) | UserScope, 16 role kanonik, technical debt Fase 3 |

Sub-dokumen RBAC: `permission-matrix.md`, `role-mapping.md`, `migration-plan.md`, `rollback-plan.md`, `testing-checklist.md` di masing-masing folder fase.

---

## Fitur Teknis

| Dokumen | Isi |
|---------|-----|
| [Daftar Dokumen Cetak](./DAFTAR_DOKUMEN_CETAK.md) | Template PDF/SBBK dan status integrasi |
| [TTE Desain Tahap 1](./TTE_DESAIN_TAHAP_1.md) | Tanda tangan elektronik KIR |
| [Modul RKU & Notifikasi](./MODUL_RKU_DAN_NOTIFIKASI.md) | RKU workflow + fondasi notifikasi |
| [Variabel Permission Sync](./DAFTAR_VARIABEL_PERMISSION_SYNC_ROUTES.md) | Referensi sync route → permission |
| [Variabel Detail Distribusi](./DAFTAR_VARIABEL_DETAIL_DISTRIBUSI.md) | Payload template SBBK/distribusi |

---

## Quick Start

**User baru:** Mulai dari [Panduan Pengguna](./panduan-pengguna/README.md).

**Developer:** Baca [HAK_AKSES](./HAK_AKSES_ROLE_DAN_DELEGASI.md) + [RBAC Fase 1](./rbac-phase1/README.md), lalu [Audit Sistem](./AUDIT_SISTEM_LENGKAP_2026-06-21.md) untuk gap terkini.

**Agent/CI context:** Lihat juga `AGENTS.md` di root proyek.

---

**Terakhir diperbarui:** Juni 2026
