<?php

namespace Database\Seeders;

use App\Models\MasterAset;
use App\Models\MasterDataBarang;
use App\Models\MasterDataBarangPermendagri;
use App\Models\MasterJenisBarang;
use App\Models\MasterKategoriBarang;
use App\Models\MasterKodeBarang;
use App\Models\MasterSatuan;
use App\Models\MasterSubjenisBarang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KemendagriImportCsvSeeder extends Seeder
{
    public function run(): void
    {
        $relativeDir = (string) env('KEMENDAGRI_IMPORT_CSV_DIR', 'storage/app/kemendagri_import_kib_b_csv');
        $dir = base_path($relativeDir);

        $required = ['aset', 'kode_barang', 'kategori_barang', 'jenis_barang', 'subjenis_barang', 'data_barang'];
        foreach ($required as $name) {
            if (! file_exists($dir.DIRECTORY_SEPARATOR.$name.'.csv')) {
                throw new \RuntimeException("File {$name}.csv tidak ditemukan pada {$dir}");
            }
        }

        DB::transaction(function () use ($dir): void {
            foreach ($this->readCsv($dir.'/aset.csv') as $row) {
                $namaAset = $this->required($row, 'nama_aset', 'aset.csv');
                MasterAset::query()->firstOrCreate(['nama_aset' => $namaAset], ['nama_aset' => $namaAset]);
            }

            foreach ($this->readCsv($dir.'/kode_barang.csv') as $row) {
                $kodeBarang = $this->required($row, 'kode_barang', 'kode_barang.csv');
                $namaKodeBarang = $this->required($row, 'nama_kode_barang', 'kode_barang.csv');
                $namaAset = $this->required($row, 'nama_aset', 'kode_barang.csv');
                $aset = MasterAset::query()->where('nama_aset', $namaAset)->first();
                if (! $aset) {
                    throw new \RuntimeException("nama_aset {$namaAset} tidak ditemukan");
                }
                MasterKodeBarang::query()->updateOrCreate(
                    ['kode_barang' => $kodeBarang],
                    ['id_aset' => $aset->id_aset, 'nama_kode_barang' => $namaKodeBarang]
                );
            }

            foreach ($this->readCsv($dir.'/kategori_barang.csv') as $row) {
                $kodeKategori = $this->required($row, 'kode_kategori_barang', 'kategori_barang.csv');
                $namaKategori = $this->required($row, 'nama_kategori_barang', 'kategori_barang.csv');
                $kodeBarang = $this->required($row, 'kode_barang', 'kategori_barang.csv');
                $masterKode = MasterKodeBarang::query()->where('kode_barang', $kodeBarang)->first();
                if (! $masterKode) {
                    throw new \RuntimeException("kode_barang {$kodeBarang} tidak ditemukan");
                }
                MasterKategoriBarang::query()->updateOrCreate(
                    ['kode_kategori_barang' => $kodeKategori],
                    ['id_kode_barang' => $masterKode->id_kode_barang, 'nama_kategori_barang' => $namaKategori]
                );
            }

            foreach ($this->readCsv($dir.'/jenis_barang.csv') as $row) {
                $kodeJenis = $this->required($row, 'kode_jenis_barang', 'jenis_barang.csv');
                $namaJenis = $this->required($row, 'nama_jenis_barang', 'jenis_barang.csv');
                $kodeKategori = $this->required($row, 'kode_kategori_barang', 'jenis_barang.csv');
                $kategori = MasterKategoriBarang::query()->where('kode_kategori_barang', $kodeKategori)->first();
                if (! $kategori) {
                    throw new \RuntimeException("kode_kategori_barang {$kodeKategori} tidak ditemukan");
                }
                MasterJenisBarang::query()->updateOrCreate(
                    ['kode_jenis_barang' => $kodeJenis],
                    ['id_kategori_barang' => $kategori->id_kategori_barang, 'nama_jenis_barang' => $namaJenis]
                );
            }

            foreach ($this->readCsv($dir.'/subjenis_barang.csv') as $row) {
                $kodeSubjenis = $this->required($row, 'kode_subjenis_barang', 'subjenis_barang.csv');
                $namaSubjenis = $this->required($row, 'nama_subjenis_barang', 'subjenis_barang.csv');
                $kodeJenis = $this->required($row, 'kode_jenis_barang', 'subjenis_barang.csv');
                $jenis = MasterJenisBarang::query()->where('kode_jenis_barang', $kodeJenis)->first();
                if (! $jenis) {
                    throw new \RuntimeException("kode_jenis_barang {$kodeJenis} tidak ditemukan");
                }
                MasterSubjenisBarang::query()->updateOrCreate(
                    ['kode_subjenis_barang' => $kodeSubjenis],
                    ['id_jenis_barang' => $jenis->id_jenis_barang, 'nama_subjenis_barang' => $namaSubjenis]
                );
            }

            foreach ($this->readCsv($dir.'/data_barang.csv') as $row) {
                $kodeDataBarang = $this->required($row, 'kode_data_barang', 'data_barang.csv');
                $namaBarang = $this->required($row, 'nama_barang', 'data_barang.csv');
                $kodeSubjenis = $this->required($row, 'kode_subjenis_barang', 'data_barang.csv');
                $namaSatuan = $this->required($row, 'nama_satuan', 'data_barang.csv');
                $subjenis = MasterSubjenisBarang::query()->where('kode_subjenis_barang', $kodeSubjenis)->first();
                if (! $subjenis) {
                    throw new \RuntimeException("kode_subjenis_barang {$kodeSubjenis} tidak ditemukan");
                }
                $satuan = MasterSatuan::query()->firstOrCreate(['nama_satuan' => $namaSatuan], ['nama_satuan' => $namaSatuan]);
                MasterDataBarang::query()->updateOrCreate(
                    ['kode_data_barang' => $kodeDataBarang],
                    [
                        'id_subjenis_barang' => $subjenis->id_subjenis_barang,
                        'id_satuan' => $satuan->id_satuan,
                        'nama_barang' => $namaBarang,
                        'deskripsi' => $row['deskripsi'] ?? null,
                        'foto_barang' => $row['foto_barang'] ?? null,
                    ]
                );
            }

            $permFile = $dir.'/permendagri_108.csv';
            if (file_exists($permFile)) {
                foreach ($this->readCsv($permFile) as $row) {
                    $kodeDataBarang = $this->required($row, 'kode_data_barang', 'permendagri_108.csv');
                    $dataBarang = MasterDataBarang::query()->where('kode_data_barang', $kodeDataBarang)->first();
                    if (! $dataBarang) {
                        continue;
                    }
                    $kodeAkun = $this->padDigits($this->required($row, 'kode_akun', 'permendagri_108.csv'), 1);
                    $kodeKelompok = $this->padDigits($this->required($row, 'kode_kelompok', 'permendagri_108.csv'), 1);
                    $kodeJenis = $this->padDigits($this->required($row, 'kode_jenis_108', 'permendagri_108.csv'), 2);
                    $kodeObjek = $this->padDigits($this->required($row, 'kode_objek', 'permendagri_108.csv'), 2);
                    $kodeRincian = $this->padDigits($this->required($row, 'kode_rincian_objek', 'permendagri_108.csv'), 2);
                    $kodeSub = $this->padDigits($this->required($row, 'kode_sub_rincian_objek', 'permendagri_108.csv'), 3);
                    $kodeSubSub = $this->padDigits($this->required($row, 'kode_sub_sub_rincian_objek', 'permendagri_108.csv'), 3);
                    $status = strtoupper((string) ($row['status_validasi'] ?? 'DRAFT'));
                    if (! in_array($status, ['DRAFT', 'REVIEW', 'VALID'], true)) {
                        $status = 'DRAFT';
                    }
                    MasterDataBarangPermendagri::query()->updateOrCreate(
                        ['id_data_barang' => $dataBarang->id_data_barang],
                        [
                            'kode_barang_108' => implode('.', [$kodeAkun, $kodeKelompok, $kodeJenis, $kodeObjek, $kodeRincian, $kodeSub, $kodeSubSub]),
                            'kode_akun' => $kodeAkun,
                            'kode_kelompok' => $kodeKelompok,
                            'kode_jenis_108' => $kodeJenis,
                            'kode_objek' => $kodeObjek,
                            'kode_rincian_objek' => $kodeRincian,
                            'kode_sub_rincian_objek' => $kodeSub,
                            'kode_sub_sub_rincian_objek' => $kodeSubSub,
                            'sumber_mapping' => 'IMPORT',
                            'status_validasi' => $status,
                            'catatan' => $row['catatan'] ?? null,
                        ]
                    );
                }
            }
        });
    }

    private function readCsv(string $path): array
    {
        $fp = fopen($path, 'r');
        if (! $fp) {
            throw new \RuntimeException("Gagal membuka {$path}");
        }
        $header = fgetcsv($fp);
        if (! $header) {
            fclose($fp);
            return [];
        }
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $rows = [];
        while (($line = fgetcsv($fp)) !== false) {
            $isEmpty = true;
            foreach ($line as $cell) {
                if (trim((string) $cell) !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                continue;
            }
            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }
        fclose($fp);
        return $rows;
    }

    private function required(array $row, string $key, string $file): string
    {
        $value = isset($row[$key]) ? trim((string) $row[$key]) : '';
        if ($value === '') {
            throw new \RuntimeException("{$file}: kolom {$key} wajib diisi");
        }
        return $value;
    }

    private function padDigits(string $value, int $len): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        return str_pad(substr($digits, 0, $len), $len, '0', STR_PAD_LEFT);
    }
}

