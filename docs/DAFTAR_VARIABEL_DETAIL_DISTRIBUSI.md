# Daftar Variabel Detail Distribusi (SBBK)

Dokumen ini menjelaskan variabel-variabel yang digunakan pada file:

- `resources/views/transaction/distribusi/show.blade.php`

## 1) Variabel Utama dari Controller

Variabel di bawah dikirim dari `DistribusiController@show` ke view.

| Variabel | Tipe | Fungsi |
| --- | --- | --- |
| `$distribusi` | `App\Models\TransaksiDistribusi` | Objek utama transaksi distribusi yang ditampilkan pada halaman detail. Menyediakan data header (nomor, status, tanggal), relasi permintaan, gudang, pegawai, serta daftar item detail distribusi. |

## 2) Variabel Per Item Relasi pada `$distribusi`

| Variabel / Akses Properti | Fungsi |
| --- | --- |
| `$distribusi->no_sbbk` | Menampilkan nomor dokumen SBBK. |
| `$distribusi->status_distribusi` | Menentukan status transaksi (`draft`, `diproses`, `dikirim`, `selesai`) dan dipakai untuk pewarnaan badge status. |
| `$distribusi->tanggal_distribusi` | Menampilkan tanggal dan jam distribusi. |
| `$distribusi->keterangan` | Menampilkan catatan tambahan distribusi jika ada. |
| `$distribusi->permintaan` | Relasi ke dokumen permintaan barang (jika distribusi berasal dari permintaan). |
| `$distribusi->gudangAsal` | Relasi fallback gudang asal di level transaksi. |
| `$distribusi->gudangTujuan` | Relasi gudang tujuan distribusi. |
| `$distribusi->pegawaiPengirim` | Relasi pegawai yang mengirim barang. |
| `$distribusi->detailDistribusi` | Koleksi item detail distribusi yang ditampilkan pada tabel. |

## 3) Variabel Lokal di Blok Informasi

Variabel ini dibuat langsung di view untuk kebutuhan tampilan.

| Variabel | Fungsi |
| --- | --- |
| `$statusColor` | Menyimpan kelas CSS Tailwind sesuai nilai `$distribusi->status_distribusi`, agar badge status punya warna berbeda per status. |
| `$gudangAsalList` | Menyusun daftar nama gudang asal unik dari seluruh detail item. Dipakai untuk menampilkan 1 nama gudang (jika sama) atau gabungan beberapa gudang (jika berbeda antar item). |

## 4) Variabel Lokal di Tabel Detail Distribusi

Variabel ini dipakai saat render tabel detail barang.

| Variabel | Fungsi |
| --- | --- |
| `$total` | Akumulator total nilai distribusi. Diisi dengan penjumlahan setiap `$detail->subtotal`. |
| `$hasFarmasiPersediaan` | Flag boolean untuk menentukan apakah kolom `No. Batch` dan `Exp Date` perlu ditampilkan (muncul jika ada item dari kategori gudang `FARMASI` atau `PERSEDIAAN`). |
| `$hasAset` | Flag boolean untuk menentukan apakah kolom `No. Seri` perlu ditampilkan (muncul jika ada item kategori `ASET`). |
| `$index` | Indeks iterasi `@foreach` untuk nomor urut baris tabel. |
| `$detail` | Item detail distribusi yang sedang diproses pada loop. |
| `$inventory` | Shortcut dari `$detail->inventory` untuk mempermudah akses data inventory per item. |
| `$kategoriGudang` | Menyimpan nilai kategori gudang dari inventory item, dipakai untuk menentukan perilaku kolom dinamis. |
| `$isAset` | Flag boolean, `true` jika `$kategoriGudang === 'ASET'`. |
| `$isFarmasiPersediaan` | Flag boolean, `true` jika kategori termasuk `FARMASI`/`PERSEDIAAN`. |
| `$noSeriList` | Koleksi nomor seri untuk item aset berdasarkan `InventoryItem` aktif, dibatasi sesuai qty distribusi. |
| `$inventoryItems` | Hasil query `InventoryItem` untuk mendapatkan daftar nomor seri item aset. |
| `$colspanLabel` | Jumlah kolom gabungan untuk sel label `Total` pada `<tfoot>`, mengikuti ada/tidaknya kolom dinamis (`Batch/Exp Date/No. Seri`). |

## 5) Variabel Properti pada Setiap `$detail`

| Variabel / Properti | Fungsi |
| --- | --- |
| `$detail->qty_distribusi` | Menampilkan jumlah barang yang didistribusikan per item. |
| `$detail->harga_satuan` | Menampilkan harga satuan item distribusi. |
| `$detail->subtotal` | Menampilkan subtotal per item sekaligus sumber perhitungan `$total`. |
| `$detail->keterangan` | Menampilkan keterangan per item. |
| `$detail->satuan` | Relasi satuan barang untuk menampilkan nama satuan. |
| `$detail->inventory->dataBarang->nama_barang` | Menampilkan nama barang. |
| `$detail->inventory->jenis_barang` | Menampilkan jenis barang. |
| `$detail->inventory->gudang->nama_gudang` | Menampilkan nama gudang asal item. |
| `$detail->inventory->no_batch` | Menampilkan nomor batch untuk item farmasi/persediaan. |
| `$detail->inventory->tanggal_kedaluwarsa` | Menampilkan tanggal kedaluwarsa untuk item farmasi/persediaan. |
| `$detail->inventory->no_seri` | Fallback nomor seri jika daftar nomor seri dari `InventoryItem` tidak tersedia. |

## Catatan

- Beberapa variabel adalah hasil relasi Eloquent (`permintaan`, `detailDistribusi`, `inventory`, `satuan`) sehingga ketersediaannya bergantung pada data relasi di database.
- Variabel tampilan seperti `$hasAset`, `$hasFarmasiPersediaan`, dan `$colspanLabel` berfungsi menjaga struktur tabel tetap konsisten saat kolom dinamis aktif/nonaktif.
