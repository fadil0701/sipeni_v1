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

Import inventory ASET + PERSEDIAAN:
- `import_inventory_aset_kib_b_prod.xlsx` / `import_inventory_aset_persediaan_prod.xlsx`
  - ASET dari KIB B (`id_gudang=22` GUDANG ASET)
  - PERSEDIAAN dari Stock Opname ATK/Cetakan/ART (`id_gudang=24` GUDANG PERSEDIAAN)
  - mapping nama SO → `kode_data_barang` di file Kemendagri

Generate ulang PERSEDIAAN ke file import:
```bash
py scripts/convert_stock_opname_to_inventory_import.py --id-gudang 24
py scripts/convert_stock_opname_to_inventory_import.py --output "database/seeders/data/import_inventory_aset_persediaan_prod.xlsx"
```

Atau set path via `.env`:
- `KEMENDAGRI_IMPORT_FILE=database/seeders/data/kemendagri_import_sheet6_objek_filtered_v2.xlsx`

Jalankan seeder:
- `php artisan db:seed --class=KemendagriImportSeeder`

Format sheet yang didukung:
- wajib: `aset`, `kode_barang`, `kategori_barang`, `jenis_barang`, `subjenis_barang`, `data_barang`
- opsional: `permendagri_108`

