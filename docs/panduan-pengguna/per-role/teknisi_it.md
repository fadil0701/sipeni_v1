# Panduan: Teknisi IT

> Dokumen ini untuk Administrator saat menyiapkan akun teknisi IT / IT Support. Operator teknisi **tidak** mengakses menu Panduan Pengguna di aplikasi.

## Profil role

| | |
|---|---|
| **Kode role** | `teknisi_it` |
| **Level** | Pusat |
| **Modul default** | Pemeliharaan, Aset (lihat) |
| **Jabatan pegawai terkait** | `Admin IT/IT Support (Teknisi IT)` — agar nama muncul di dropdown teknisi Internal |

## Kegunaan

Pelaksana **pemeliharaan / perbaikan aset IT** (PC, printer, jaringan lokal, dll.) setelah disposisi ke **Teknisi IT**.

## Menu yang harus tampil

| Menu | Kegunaan |
|------|----------|
| **Pemeliharaan → Daftar Permintaan** | Antrian kerja setelah disposisi |
| **Pemeliharaan → Laporan Servis** | Service Report + rekomendasi + spare part Pending |
| **Pemeliharaan → Jadwal Pemeliharaan** | Jadwal perawatan aset IT |
| **Pemeliharaan → Kalibrasi** | Bila aset IT masuk skema kalibrasi |

## Alur kerja singkat

1. Permintaan dari unit → approval
2. Pengurus Barang disposisi ke **Teknisi IT** (atau vendor bila eksternal)
3. Teknisi: **Daftar Permintaan** → **Laporan Servis**
4. Mode pelaksana di SR: **Internal** (pilih nama pegawai) atau **Vendor** (nama vendor + teknisi freetext)
5. Pending spare part → isi daftar + foto untuk pengajuan pembelian

## Checklist setup akun (Administrator)

1. User aktif + role **`teknisi_it`**
2. Modul **Pemeliharaan**
3. Master Pegawai: jabatan **Admin IT/IT Support (Teknisi IT)**
4. Uji dropdown teknisi di form Service Report menampilkan nama pegawai

## Bedakan dengan Admin IT sistem

| | Admin IT (`admin` / `administrator`) | Teknisi IT (`teknisi_it`) |
|---|--------------------------------------|---------------------------|
| Fokus | User, role, konfigurasi aplikasi | Servis aset IT / pemeliharaan |
| Menu Panduan | Ya | Tidak |
| Gudang / SBBK | Sesuai permission admin | Tidak (default) |
