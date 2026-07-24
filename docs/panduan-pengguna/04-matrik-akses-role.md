# 04 — Matriks Akses Role (Permission Matrix)

Dokumen ini membantu **Administrator** memahami: role apa saja yang ada, modul apa yang mereka pakai, dan siapa yang bertindak di setiap tahap alur kerja.

> **Catatan akses:** Menu **Panduan Pengguna** di aplikasi hanya tampil untuk **Administrator** (`admin` / `administrator`) dan **Super Administrator**.

> Permission di database bisa lebih detail (per halaman/tombol). Tabel di bawah menggambarkan **pola default** dari seeder. Perubahan manual di **Akses & Kontrol → Role** selalu menang.

---

## 1. Konsep singkat (3 lapisan)

Bayangkan akses seperti pintu + peta menu:

| Lapisan | Pertanyaan | Diatur di |
|---------|------------|-----------|
| **Role** | Siapa orang ini di organisasi? | User → pilih role |
| **Permission** | Boleh buka halaman/aksi apa? | Role → centang permission |
| **Modul menu** | Menu sidebar mana yang ditampilkan? | User → centang modul |

**Contoh:** User punya permission Service Report, tetapi modul **Pemeliharaan** tidak dicentang → dia tidak melihat menu itu di sidebar (meski URL langsung mungkin tetap dicek permission).

---

## 2. Daftar role kanonik

**Legenda scope:** `Pusat` = lintas unit (sesuai permission) · `Unit` = terbatas unit kerja sendiri

| Role | Nama tampilan | Scope | Modul default | Tugas utama (bahasa sehari-hari) |
|------|---------------|-------|---------------|----------------------------------|
| `super_administrator` | Super Administrator | Pusat | Semua | Kunci master sistem; bypass pemeriksaan |
| `admin` / `administrator` | Admin IT / Administrator | Pusat* | Master + modul sesuai permission | Kelola user, role, konfigurasi |
| `kepala_pusat` | Kepala Pusat | Pusat | Transaksi, Laporan | Approve / reject, keputusan final |
| `kasubbag_tu` | Kasubbag TU | Pusat | Transaksi, Laporan | Verifikasi administrasi |
| `kepala_unit` | Kepala Unit | Unit | Transaksi, Inventory, Aset, Planning | Mengetahui pengajuan unit |
| `admin_unit` | Admin Unit | Unit | Transaksi, Inventory, Aset, Planning | Input permintaan, RKU, stok unit |
| `perencana` | Perencana | Pusat | Planning, Transaksi | Review RKU, disposisi perencanaan |
| `pengadaan` | Pengadaan | Pusat | Procurement, Transaksi | Paket & proses pengadaan |
| `keuangan` | Keuangan | Pusat | — (modul belum live) | Role disiapkan; menu pembayaran disembunyikan |
| `pptk_apbd` | PPTK APBD | Pusat | Planning, Procurement, Reports | Monitoring kegiatan APBD |
| `pptk_blud` | PPTK BLUD | Pusat | Planning, Procurement, Reports | Monitoring kegiatan BLUD |
| `pengurus_barang` | Pengurus Barang | Pusat | Inventory, Transaksi, Aset, **Pemeliharaan** | Disposisi, SBBK, oversight gudang + pemeliharaan |
| `teknisi_atem` | Teknisi ATEM | Pusat | **Pemeliharaan**, Aset | Servis alat kesehatan, jadwal, kalibrasi |
| `teknisi_it` | Teknisi IT | Pusat | **Pemeliharaan**, Aset | Servis aset IT, jadwal, kalibrasi |
| `admin_gudang_pusat` | Admin Gudang Pusat | Pusat | Inventory, Transaksi, Aset | Semua kategori gudang pusat |
| `admin_gudang_aset` | Admin Gudang Aset | Pusat | Inventory, Aset | Gudang aset saja |
| `admin_gudang_persediaan` | Admin Gudang Persediaan | Pusat | Inventory, Transaksi | Gudang persediaan |
| `admin_gudang_farmasi` | Admin Gudang Farmasi | Pusat | Inventory, Transaksi | Gudang farmasi + kedaluwarsa |

\*Scope Admin IT mengikuti permission yang diberikan, bukan otomatis lintas unit.

---

## 3. Matriks: Role × modul sidebar (ringkas)

| Modul | Role yang biasanya punya akses |
|-------|--------------------------------|
| Transaksi (permintaan, approval) | admin_unit, kepala_unit, kasubbag_tu, kepala_pusat, pengurus_barang, … |
| Distribusi / SBBK | pengurus_barang, admin_gudang_* |
| Inventory | admin_gudang_*, pengurus_barang, admin_unit (unit) |
| Aset | admin_gudang_aset, pengurus_barang, admin_unit, teknisi_* (lihat) |
| Planning (RKU) | admin_unit, perencana, kasubbag_tu, kepala_pusat, pptk_* |
| Pengadaan | pengadaan, pptk_* |
| Keuangan | — (belum live; role `keuangan` disiapkan) |
| **Pemeliharaan** | **pengurus_barang**, **teknisi_atem**, **teknisi_it**, super_admin |
| Akses & Kontrol (user/role) | super_administrator, admin / administrator |
| Panduan Pengguna (in-app) | **hanya** admin / administrator / super_administrator |

---

## 4. Matriks: Permintaan Barang

| Tahap | Role yang bertindak |
|-------|---------------------|
| Ajukan permintaan | `admin_unit` |
| Mengetahui | `kepala_unit` |
| Verifikasi | `kasubbag_tu` |
| Approve / reject | `kepala_pusat` |
| Disposisi | `pengurus_barang` |
| Proses item di gudang | `admin_gudang_persediaan` / `farmasi` / `aset` |
| Buat & kirim SBBK | `pengurus_barang`, `admin_gudang_pusat`, admin gudang kategori |
| Penerimaan di unit | `admin_unit`, `kepala_unit` |
| Retur | `admin_unit`, `kepala_unit` |

---

## 5. Matriks: Pemeliharaan aset

| Aktivitas | Menu | Role |
|-----------|------|------|
| Ajukan / monitor permintaan pemeliharaan | Transaksi → Permintaan Pemeliharaan | `admin_unit`, `kepala_unit`, `pengurus_barang`, … |
| Disposisi ke teknisi / vendor | Approval | `pengurus_barang` |
| Daftar kerja teknisi | Pemeliharaan → Daftar Permintaan | `teknisi_atem`, `teknisi_it`, `pengurus_barang` |
| Isi Laporan Servis (Service Report) | Pemeliharaan → Laporan Servis | `teknisi_atem`, `teknisi_it`, `pengurus_barang` |
| Jadwal pemeliharaan | Pemeliharaan → Jadwal | `teknisi_*`, `pengurus_barang` |
| Kalibrasi | Pemeliharaan → Kalibrasi | `teknisi_*`, `pengurus_barang` |
| Mengetahui SR / cabang pembelian | Approval | sesuai flow (pengurus, kepala pusat, …) |

**Bedakan dua hal:**

- **Role** (`teknisi_atem` / `teknisi_it`) = hak buka menu & halaman.
- **Jabatan pegawai** (ATEM / IT Support di Master Pegawai) = nama yang muncul di dropdown teknisi pada form Service Report.

---

## 6. Matriks: Inventory & gudang

| Aktivitas | Role |
|-----------|------|
| Master struktur barang | `admin_gudang_pusat`, `super_administrator` |
| Input inventory pusat | `admin_gudang_*`, `pengurus_barang` |
| Stock opname pusat | `admin_gudang_*` |
| Lihat stok unit | `admin_unit` (scoped) |
| Reminder expired farmasi | `admin_gudang_farmasi` |

---

## 7. Matriks: RKU & pengadaan

| Aktivitas | Role |
|-----------|------|
| Buat / edit RKU | `admin_unit` |
| Review perencana | `perencana` |
| Review TU | `kasubbag_tu` |
| Approve RKU | `kepala_pusat` |
| Monitoring PPTK | `pptk_apbd`, `pptk_blud` |
| Paket pengadaan | `pengadaan` |
| Pembayaran | — (modul belum live) |

---

## 8. Role legacy → kanonik

| Legacy | Setara kanonik |
|--------|----------------|
| pegawai, pegawai_unit, admin_gudang_unit | `admin_unit` |
| perencanaan, admin_perencanaan | `perencana` |
| admin_gudang | `admin_gudang_pusat` |
| admin_pengadaan_apbd / blud | `pengadaan` |
| admin_keuangan | `keuangan` |
| admin_pptk_apbd / blud | `pptk_apbd` / `pptk_blud` |

---

## 9. Cara mengelola (langkah Administrator)

Panduan langkah demi langkah: **[05 — Mengelola Role, Permission & User](./05-mengelola-role-permission-dan-user.md)**.

Ringkas:

1. Pastikan permission route terbaru: `php artisan permission:sync-routes` (di Docker: `docker compose exec app …`).
2. Atur **Role** → centang permission.
3. Buat / edit **User** → pilih role + modul menu.
4. Untuk teknisi: role `teknisi_atem` / `teknisi_it` + modul **Pemeliharaan** + (opsional) jabatan ATEM/IT Support di Master Pegawai.

---

## 10. Panduan detail per role

Lihat [per-role/](./per-role/README.md).
