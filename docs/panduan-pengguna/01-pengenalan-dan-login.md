# 01 — Pengenalan Sistem & Cara Login

## Apa itu SI-MANTIK?

**SI-MANTIK** (*Sistem Informasi Manajemen Terintegrasi*) adalah aplikasi web untuk mengelola:

- **Permintaan & distribusi barang** antar unit kerja dan gudang pusat
- **Perencanaan kebutuhan** (RKU) dan pengadaan
- **Inventory & stok** (persediaan, farmasi, aset)
- **Register aset & KIR** (Kartu Inventaris Ruangan)
- **Pemeliharaan, kalibrasi, dan peminjaman barang**
- **Monitoring & laporan**

> Modul **Keuangan / Pembayaran** belum tersedia di UI (sedang disiapkan). Role keuangan boleh ada di RBAC tanpa menu aktif.

Alur inti: kebutuhan unit → perencanaan → pengadaan → stok pusat → distribusi → penerimaan unit.

---

## Prasyarat

- Browser modern (Chrome, Edge, Firefox — versi terbaru)
- Koneksi jaringan ke server SI-MANTIK
- Akun user yang sudah dibuat administrator
- Role dan permission sudah ditetapkan oleh admin

---

## Login

1. Buka URL aplikasi (contoh: `http://simantik.local` atau URL produksi).
2. Masukkan **email** dan **password**.
3. Klik ikon **mata** di field password jika ingin melihat/menyembunyikan teks saat mengetik.
4. Klik **Login**.

### Kebijakan password

Password **baru** atau **reset** harus memenuhi:

| Syarat | Keterangan |
|--------|------------|
| Panjang | Minimal **12 karakter** |
| Huruf besar | Minimal satu (A–Z) |
| Huruf kecil | Minimal satu (a–z) |
| Angka | Minimal satu (0–9) |
| Simbol | Minimal satu (!@#$%^&* dll.) |

Ganti password lewat **Profil Saya → Ganti Password** setelah login pertama di production.

### Akun awal (environment dev/staging)

Akun administrator dan demo dibuat oleh administrator sistem melalui konfigurasi server sebelum seed database. Hubungi Admin IT jika belum memiliki akun.

---

## Panduan di dalam aplikasi (khusus Administrator)

Menu sidebar **Panduan Pengguna** hanya tersedia untuk:

- **Administrator** / Admin IT (`admin`, `administrator`)
- **Super Administrator**

Role operasional (unit, gudang, teknisi, dll.) **tidak** melihat menu ini. Jika URL `/panduan` dibuka tanpa hak, sistem menolak (403).

Isi panduan mencakup tugas tiap role, matriks akses, dan cara mengelola user — untuk dibaca Admin IT saat onboarding atau konfigurasi.

---

## Tampilan utama

Setelah login, Anda melihat:

| Area | Fungsi |
|------|--------|
| **Sidebar kiri** | Menu modul (Transaksi, Inventory, …) — hanya menu yang Anda punya akses |
| **Header atas** | Nama aplikasi, notifikasi, profil user |
| **Konten utama** | Halaman modul (daftar, form, detail) |

### Navigasi sidebar

- Menu **grup** (Transaksi, Inventory, …) bisa dibuka/tutup — **hanya satu grup terbuka** sekaligus (accordion).
- Submenu aktif ditandai highlight biru.
- Di **mobile/tablet** (< 1024px): tap ikon **☰** di header untuk membuka sidebar drawer.

### Profil & logout

- Klik **avatar/nama** di kanan atas → **Profil Saya**, **Pengaturan Akun**, atau **Logout**.

---

## Scope unit kerja

Beberapa role hanya melihat data **unit kerja sendiri**:

- `admin_unit`, `kepala_unit` (dan alias legacy `pegawai`)

Role **pusat** (gudang, perencana, pengadaan, dll.) melihat data lintas unit sesuai permission.

Jika data tidak muncul padahal seharusnya ada, hubungi admin untuk memastikan:

1. Role benar
2. `unit_kerja_id` pada akun pegawai sudah sesuai
3. Permission route sudah di-assign ke role

---

## Hak akses (singkat)

- **Menu** yang tampil = kombinasi **permission role** yang diberikan administrator.
- Setiap role hanya melihat modul dan data sesuai penugasan.
- Jika menu tidak muncul, hubungi Admin IT untuk pengecekan role dan permission.

Detail: [04 — Matriks Akses Role](./04-matrik-akses-role.md).

---

## Tips penggunaan

1. **Gunakan filter** di halaman daftar (status, unit, tanggal) sebelum mencari manual.
2. **Select/search dropdown** — ketik minimal beberapa huruf untuk mempercepat pencarian barang/pegawai.
3. **Simpan draft** permintaan/RKU sebelum ajukan jika data belum lengkap.
4. **Perhatikan badge status** (warna) di kolom status untuk tahu langkah berikutnya.
5. **Hard refresh** (`Ctrl+Shift+R`) jika tampilan CSS/JS terasa tidak update setelah deploy.
6. Buka **Panduan Pengguna** di sidebar jika lupa langkah operasional role Anda.

---

## Langkah berikutnya

- Pelajari modul: [02 — Modul & Fitur](./02-modul-dan-fitur.md)
- Pelajari alur bisnis: [03 — Alur Kerja Utama](./03-alur-kerja-utama.md)
- Panduan khusus role Anda: [per-role/README.md](./per-role/README.md)
