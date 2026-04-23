Letakkan file master Kemendagri (format import sistem) di folder ini.

Default nama file yang dibaca seeder:
- `kemendagri_import.xlsx`

Atau set path via `.env`:
- `KEMENDAGRI_IMPORT_FILE=database/seeders/data/nama_file_anda.xlsx`

Jalankan seeder:
- `php artisan db:seed --class=KemendagriImportSeeder`

Format sheet yang didukung:
- wajib: `aset`, `kode_barang`, `kategori_barang`, `jenis_barang`, `subjenis_barang`, `data_barang`
- opsional: `permendagri_108`

