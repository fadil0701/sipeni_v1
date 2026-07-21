Letakkan file master Kemendagri (format import sistem) di folder ini.

Default nama file yang dibaca seeder:
- `kemendagri_import.xlsx`

File kerja utama (sudah digabung):
- `kemendagri_import_sheet6_objek_filtered_v2.xlsx`
  - basis Sheet6 objek terfilter
  - + hierarki **Aset Lancar** dari `bmd_aset_lancar_lengkap.xlsx`
  - + data barang existing dari `KIB B.xlsx` (KOBAR yang belum ada di master)
  - + item **Persediaan** ATK / Kertas / Tinta / Cetakan / ART dari `Stock Opname ATK CETAKAN ART 2026 (5).xlsx`
  - + hierarki **Farmasi** (Obat Umum/Resep/Generik, Vaksin, BMHP) sampai kode data barang (placeholder siap diisi detail obat)

Enrich ulang Persediaan/Farmasi:
```bash
py scripts/enrich_kemendagri_persediaan_farmasi.py
py scripts/enrich_kemendagri_persediaan_farmasi.py --target "database/seeders/data/kemendagri_import_sheet6_objek_filtered_v2.xlsx"
```

Import inventory ASET dari KIB B:
- `import_inventory_aset_kib_b.xlsx` — siap diunggah di **Inventory → Import Data Inventory**
  - sumber sheet 2 `KIB B.xlsx` (`KIBB_00032_PUSAT_PELAYANAN_KESE`)
  - `jenis_inventory=ASET`
  - `id_data_barang` = kode KOBAR (importer resolve ke ID master)
  - default `id_gudang=1` (Gudang Aset Pusat), `id_anggaran=1` (APBD)
  - **agregasi**: KOBAR + merk + tahun pembelian + harga_satuan → `qty_input` dijumlahkan
  - harga berbeda (meski merk & tahun sama) tetap baris terpisah

Generate ulang dari KIB B terbaru:
```bash
py scripts/convert_kib_b_to_inventory_import.py
py scripts/convert_kib_b_to_inventory_import.py --source "path/ke/KIB B.xlsx" --output "database/seeders/data/import_inventory_aset_kib_b.xlsx"
# Agregasi ulang file import yang sudah ada:
py scripts/convert_kib_b_to_inventory_import.py --aggregate-import "database/seeders/data/import_inventory_aset_kib_b_fixed.xlsx" --output "database/seeders/data/import_inventory_aset_kib_b_fixed.xlsx"
```
Butuh: `pip install openpyxl`

Atau set path via `.env`:
- `KEMENDAGRI_IMPORT_FILE=database/seeders/data/kemendagri_import_sheet6_objek_filtered_v2.xlsx`

Jalankan seeder:
- `php artisan db:seed --class=KemendagriImportSeeder`

Format sheet yang didukung:
- wajib: `aset`, `kode_barang`, `kategori_barang`, `jenis_barang`, `subjenis_barang`, `data_barang`
- opsional: `permendagri_108`

