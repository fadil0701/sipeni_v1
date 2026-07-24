# Panduan: Admin Unit

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Level** | Unit |
| **Scope** | Unit kerja sendiri |
| **Modul default** | Transaksi, Inventory, Aset, Planning |
| **Legacy setara** | `pegawai`, `pegawai_unit`, `admin_gudang_unit` |

## Kegunaan

**Operator utama unit** — mengajukan permintaan, RKU, menerima barang, mengelola stok & register aset unit.

## Menu & tugas

| Menu | Tugas Anda |
|------|------------|
| **Permintaan Barang** | Buat, edit draft, ajukan |
| **Permintaan Pemeliharaan** | Ajukan servis aset |
| **Peminjaman / Pengembalian** | Kelola pinjam antar unit |
| **Transaksi → RKU** | Input & monitoring Rencana Kebutuhan Unit sendiri |
| **Penerimaan Barang** | Terima & verifikasi distribusi |
| **Retur Barang** | Retur jika rusak |
| **Data Stock (unit)** | Monitor stok |
| **Register Aset** | Catat aset di unit |

## Langkah: Permintaan barang (lengkap)

1. Transaksi → Permintaan Barang → **Tambah**
2. Isi: Unit Kerja, Pemohon, Tanggal, Tipe (Rutin/CITO), Sub jenis (Persediaan/Farmasi)
3. **Tambah Item** → pilih barang, qty, satuan
4. **Simpan Draft** (opsional) atau **Ajukan Permintaan**
5. Tunggu approval kepala unit → TU → kepala pusat → gudang

## Langkah: RKU

1. Transaksi → **RKU** → **Buat RKU**
2. Lengkapi header + detail kebutuhan unit → simpan
3. **Submit** untuk review perencana/TU/kepala pusat
4. Monitor status di tab Aktif / Riwayat sampai selesai

## Langkah: Penerimaan

1. Distribusi → Penerimaan Barang
2. Pilih SBBK yang status **dikirim**
3. Cocokkan fisik → **Verifikasi**

## Tips

- Cek stok referensi di form permintaan sebelum qty berlebihan
- Gunakan CITO hanya untuk kebutuhan urgent
- Simpan draft jika menunggu data barang dari perencanaan

## Akun & panduan

- **Panduan interaktif:** menu **Panduan Pengguna** di sidebar aplikasi.
- Akun dan password dikelola administrator. Password baru minimal 12 karakter.
