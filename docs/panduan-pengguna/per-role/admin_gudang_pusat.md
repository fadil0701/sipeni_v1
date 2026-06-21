# Panduan: Admin Gudang Pusat

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Level** | Pusat |
| **Modul default** | Inventory, Transaksi, Aset |
| **Legacy setara** | `admin_gudang` |

## Kegunaan

Admin gudang **semua kategori** (Persediaan, Farmasi, Aset) — setara operasional pengurus barang di level gudang.

## Menu utama

- **Inventory** (master, data inventory, stock, opname, kartu stok)
- **Distribusi** (draft, SBBK, penerimaan view)
- **Aset** (register, KIR, mutasi)

## Tugas

1. Kelola **data inventory & stock** semua gudang pusat
2. **Proses disposisi** permintaan (draft distribusi) untuk semua kategori
3. Dukung **compile & kirim SBBK**
4. **Stock opname** & adjustment berkala
5. **Import** data inventory bila ada batch penerimaan besar

## Perbedaan dengan admin gudang kategori

Anda akses **lintas kategori**; admin aset/persediaan/farmasi hanya satu kategori.

## Tips

- Pisahkan gudang by `kategori_gudang` saat input inventory
- Scan QR untuk verifikasi fisik barang
