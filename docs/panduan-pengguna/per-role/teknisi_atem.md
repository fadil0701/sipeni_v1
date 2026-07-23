# Panduan: Teknisi ATEM

> Dokumen ini untuk Administrator saat menyiapkan akun teknisi alat kesehatan. Operator teknisi **tidak** mengakses menu Panduan Pengguna di aplikasi.

## Profil role

| | |
|---|---|
| **Kode role** | `teknisi_atem` |
| **Level** | Pusat |
| **Modul default** | Pemeliharaan, Aset (lihat) |
| **Jabatan pegawai terkait** | `ATEM (Teknisi Alat Kesehatan)` — agar nama muncul di dropdown teknisi Internal |

## Kegunaan

Pelaksana **pemeliharaan / perbaikan aset alat kesehatan** setelah Pengurus Barang mendisposisi permintaan ke Teknisi ATEM.

## Menu yang harus tampil

| Menu | Kegunaan |
|------|----------|
| **Pemeliharaan → Daftar Permintaan** | Lihat pekerjaan yang ditugaskan / siap diproses |
| **Pemeliharaan → Laporan Servis** | Isi Service Report (merk, tipe, no seri, rekomendasi, spare part Pending) |
| **Pemeliharaan → Jadwal Pemeliharaan** | Kelola jadwal rutin / perbaikan |
| **Pemeliharaan → Kalibrasi** | Catat kalibrasi aset |

## Alur kerja singkat

1. Unit mengajukan **Permintaan Pemeliharaan**
2. Approval → Pengurus Barang **disposisi** ke **Teknisi ATEM**
3. Teknisi buka **Daftar Permintaan** → **Proses** / buat **Laporan Servis**
4. Jika rekomendasi **Pending spare part** → isi tabel spare part + foto
5. Setelah SR selesai → rantai **diketahui** (Pengurus / Kepala Pusat) sesuai flow
6. Setelah pembelian (jika ada) → **Lanjut perbaikan** lalu SR berikutnya

## Checklist setup akun (Administrator)

1. Buat user aktif
2. Assign role **`teknisi_atem`**
3. Centang modul **Pemeliharaan**
4. Hubungkan Master Pegawai dengan jabatan ATEM
5. Uji login: pastikan empat submenu pemeliharaan terlihat

## Yang tidak perlu diberikan

- Manajemen user/role
- Gudang / distribusi SBBK penuh (itu domain Pengurus / Admin Gudang)
- Approve Kepala Pusat
