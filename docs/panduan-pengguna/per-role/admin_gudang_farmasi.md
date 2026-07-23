# Panduan: Admin Gudang Farmasi

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Level** | Pusat |
| **Modul default** | Inventory, Transaksi |
| **Kategori gudang** | FARMASI saja |

## Kegunaan

Mengelola gudang **farmasi** — stok obat/BMHP, **kedaluwarsa**, disposisi permintaan farmasi.

## Menu utama

| Menu | Kegunaan |
|------|----------|
| **Inventory → Data Inventory/Stock** | Stok farmasi (batch/expired) |
| **Inventory → Reminder Kedaluwarsa** | Monitor obat mendekati expired |
| **Distribusi → Daftar Permintaan** | Draft distribusi farmasi |

## Alur disposisi farmasi

1. Disposisi dari pengurus barang (kategori farmasi)
2. Cek stok + **tanggal kedaluwarsa** — prioritaskan FEFO (First Expired First Out)
3. **Daftar Permintaan** → Proses → buat **SBBK** (pilih inventory/batch + qty)
4. **Kirim** SBBK ke unit

## Tips

- Wajib cek **Reminder Kedaluwarsa** mingguan
- Tolak/kurangi qty jika batch hampir expired — koordinasi unit
- Permintaan unit harus centang jenis **Farmasi**

## Khusus farmasi

- Input batch & expired di data inventory
- Pisahkan gudang farmasi di master gudang (`kategori_gudang = FARMASI`)
