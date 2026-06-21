# 04 — Matriks Akses per Role

Tabel ringkas **modul default**, **scope data**, dan **tugas utama** per role kanonik.

> **Permission aktual** di database bisa lebih granular (per halaman/aksi).

**Legenda scope:** `Pusat` = lintas unit (sesuai permission) · `Unit` = terbatas unit kerja sendiri

| Role | Scope | Modul default | Tugas utama |
|------|-------|---------------|-------------|
| **super_administrator** | Pusat | Semua | Kelola sistem, user, role, audit |
| **kepala_pusat** | Pusat | Transaksi, Laporan | Approve/reject permintaan & RKU final |
| **kasubbag_tu** | Pusat | Transaksi, Laporan | Verifikasi administrasi permintaan & RKU |
| **kepala_unit** | Unit | Transaksi, Inventory, Aset, Planning | Approve unit (mengetahui), penerimaan, oversight unit |
| **admin_unit** | Unit | Transaksi, Inventory, Aset, Planning | Input permintaan, RKU, stok unit, register aset unit |
| **perencana** | Pusat | Planning, Transaksi | Review RKU, master program/kegiatan, disposisi perencanaan |
| **pengadaan** | Pusat | Procurement, Transaksi | Paket & proses pengadaan |
| **keuangan** | Pusat | Finance, Transaksi | Pembayaran pengadaan |
| **pptk_apbd** | Pusat | Planning, Procurement, Reports | Monitoring kegiatan APBD |
| **pptk_blud** | Pusat | Planning, Procurement, Reports | Monitoring kegiatan BLUD |
| **pengurus_barang** | Pusat | Inventory, Transaksi, Aset | Disposisi, compile SBBK, distribusi, oversight gudang |
| **admin_gudang_pusat** | Pusat | Inventory, Transaksi, Aset | Semua kategori gudang pusat |
| **admin_gudang_aset** | Pusat | Inventory, Aset | Gudang kategori aset saja |
| **admin_gudang_persediaan** | Pusat | Inventory, Transaksi | Gudang kategori persediaan |
| **admin_gudang_farmasi** | Pusat | Inventory, Transaksi | Gudang farmasi + kedaluwarsa |
| **admin** / **administrator** | Pusat* | Master, Inventory, … | Admin IT — berbasis permission |

\*Scope admin IT mengikuti permission yang diberikan, bukan otomatis lintas unit.

---

## Matriks: Role × Tahap Permintaan Barang

| Tahap | Role yang bertindak |
|-------|---------------------|
| Ajukan permintaan | admin_unit |
| Mengetahui | kepala_unit |
| Verifikasi | kasubbag_tu |
| Approve/reject | kepala_pusat |
| Disposisi | pengurus_barang |
| Draft distribusi | admin_gudang_persediaan / farmasi / aset |
| Compile & kirim SBBK | pengurus_barang, admin_gudang_pusat |
| Penerimaan | admin_unit, kepala_unit |
| Retur | admin_unit, kepala_unit |

---

## Matriks: Role × Modul Inventory

| Aktivitas | Role |
|-----------|------|
| Master struktur barang | admin_gudang_pusat, super_administrator |
| Input inventory pusat | admin_gudang_* , pengurus_barang |
| Stock opname pusat | admin_gudang_* |
| Lihat stok unit | admin_unit (scoped) |
| Reminder expired farmasi | admin_gudang_farmasi |

---

## Matriks: Role × RKU & Pengadaan

| Aktivitas | Role |
|-----------|------|
| Buat/edit RKU | admin_unit |
| Review perencana | perencana |
| Review TU | kasubbag_tu |
| Approve RKU | kepala_pusat |
| Monitoring PPTK | pptk_apbd, pptk_blud |
| Paket pengadaan | pengadaan |
| Pembayaran | keuangan |

---

## Mapping role legacy → kanonik

| Legacy | Setara kanonik |
|--------|----------------|
| pegawai, pegawai_unit, admin_gudang_unit | admin_unit |
| perencanaan, admin_perencanaan | perencana |
| admin_gudang | admin_gudang_pusat |
| admin_pengadaan_apbd/blud | pengadaan |
| admin_keuangan | keuangan |
| admin_pptk_apbd/blud | pptk_apbd / pptk_blud |

---

## Panduan detail per role

Lihat folder [per-role/](./per-role/README.md) untuk instruksi langkah demi langkah per role.
