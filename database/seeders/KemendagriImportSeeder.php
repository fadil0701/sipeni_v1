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
use PhpOffice\PhpSpreadsheet\IOFactory;

class KemendagriImportSeeder extends Seeder
{
    /**
     * Jalankan dengan:
     * php artisan db:seed --class=KemendagriImportSeeder
     *
     * Opsional set env:
     * KEMENDAGRI_IMPORT_FILE=database/seeders/data/kemendagri_import.xlsx
     */
    public function run(): void
    {
        $relativePath = (string) env('KEMENDAGRI_IMPORT_FILE', 'database/seeders/data/kemendagri_import.xlsx');
        $filePath = base_path($relativePath);

        if (! file_exists($filePath)) {
            throw new \RuntimeException(
                "File import Kemendagri tidak ditemukan di '{$filePath}'. ".
                "Letakkan file xlsx sesuai format import sistem (sheet aset s/d data_barang, opsional permendagri_108)."
            );
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $sheets[$this->normalizeKey($worksheet->getTitle())] = $worksheet->toArray(null, true, true, false);
        }

        $requiredSheets = ['aset', 'kode_barang', 'kategori_barang', 'jenis_barang', 'subjenis_barang', 'data_barang'];
        foreach ($requiredSheets as $requiredSheet) {
            if (! isset($sheets[$requiredSheet])) {
                throw new \RuntimeException("Sheet '{$requiredSheet}' tidak ditemukan. Gunakan format import sistem.");
            }
        }

        DB::transaction(function () use ($sheets): void {
            $seenKodeBarang = [];
            $seenKategoriBarang = [];
            $seenJenisBarang = [];
            $seenSubjenisBarang = [];
            $seenDataBarang = [];

            foreach ($this->parseSheetRows($sheets['aset']) as $row) {
                $namaAset = $this->requiredValue($row, 'nama_aset', 'aset');
                MasterAset::firstOrCreate(['nama_aset' => $namaAset], ['nama_aset' => $namaAset]);
            }

            foreach ($this->parseSheetRows($sheets['kode_barang']) as $row) {
                $kodeBarang = $this->requiredValue($row, 'kode_barang', 'kode_barang');
                $kodeBarangKey = trim($kodeBarang);
                if (isset($seenKodeBarang[$kodeBarangKey])) {
                    continue;
                }
                $seenKodeBarang[$kodeBarangKey] = true;

                $namaKodeBarang = $this->requiredValue($row, 'nama_kode_barang', 'kode_barang');
                $namaAset = $this->requiredValue($row, 'nama_aset', 'kode_barang');

                $aset = MasterAset::query()->where('nama_aset', $namaAset)->first();
                if (! $aset) {
                    throw new \RuntimeException("Sheet kode_barang: nama_aset '{$namaAset}' tidak ditemukan.");
                }

                MasterKodeBarang::updateOrCreate(
                    ['kode_barang' => $kodeBarang],
                    ['id_aset' => $aset->id_aset, 'nama_kode_barang' => $namaKodeBarang]
                );
            }

            foreach ($this->parseSheetRows($sheets['kategori_barang']) as $row) {
                $kodeKategori = $this->requiredValue($row, 'kode_kategori_barang', 'kategori_barang');
                $kodeKategoriKey = trim($kodeKategori);
                if (isset($seenKategoriBarang[$kodeKategoriKey])) {
                    continue;
                }
                $seenKategoriBarang[$kodeKategoriKey] = true;

                $namaKategori = $this->requiredValue($row, 'nama_kategori_barang', 'kategori_barang');
                $kodeBarang = $this->requiredValue($row, 'kode_barang', 'kategori_barang');

                $masterKodeBarang = MasterKodeBarang::query()->where('kode_barang', $kodeBarang)->first();
                if (! $masterKodeBarang) {
                    throw new \RuntimeException("Sheet kategori_barang: kode_barang '{$kodeBarang}' tidak ditemukan.");
                }

                MasterKategoriBarang::updateOrCreate(
                    ['kode_kategori_barang' => $kodeKategori],
                    ['id_kode_barang' => $masterKodeBarang->id_kode_barang, 'nama_kategori_barang' => $namaKategori]
                );
            }

            foreach ($this->parseSheetRows($sheets['jenis_barang']) as $row) {
                $kodeJenis = $this->requiredValue($row, 'kode_jenis_barang', 'jenis_barang');
                $kodeJenisKey = trim($kodeJenis);
                if (isset($seenJenisBarang[$kodeJenisKey])) {
                    continue;
                }
                $seenJenisBarang[$kodeJenisKey] = true;

                $namaJenis = $this->requiredValue($row, 'nama_jenis_barang', 'jenis_barang');
                $kodeKategori = $this->requiredValue($row, 'kode_kategori_barang', 'jenis_barang');

                $kategori = MasterKategoriBarang::query()->where('kode_kategori_barang', $kodeKategori)->first();
                if (! $kategori) {
                    throw new \RuntimeException("Sheet jenis_barang: kode_kategori_barang '{$kodeKategori}' tidak ditemukan.");
                }

                MasterJenisBarang::updateOrCreate(
                    ['kode_jenis_barang' => $kodeJenis],
                    ['id_kategori_barang' => $kategori->id_kategori_barang, 'nama_jenis_barang' => $namaJenis]
                );
            }

            foreach ($this->parseSheetRows($sheets['subjenis_barang']) as $row) {
                $kodeSubjenis = $this->requiredValue($row, 'kode_subjenis_barang', 'subjenis_barang');
                $kodeSubjenisKey = trim($kodeSubjenis);
                if (isset($seenSubjenisBarang[$kodeSubjenisKey])) {
                    continue;
                }
                $seenSubjenisBarang[$kodeSubjenisKey] = true;

                $namaSubjenis = $this->requiredValue($row, 'nama_subjenis_barang', 'subjenis_barang');
                $kodeJenis = $this->requiredValue($row, 'kode_jenis_barang', 'subjenis_barang');

                $jenis = MasterJenisBarang::query()->where('kode_jenis_barang', $kodeJenis)->first();
                if (! $jenis) {
                    throw new \RuntimeException("Sheet subjenis_barang: kode_jenis_barang '{$kodeJenis}' tidak ditemukan.");
                }

                MasterSubjenisBarang::updateOrCreate(
                    ['kode_subjenis_barang' => $kodeSubjenis],
                    ['id_jenis_barang' => $jenis->id_jenis_barang, 'nama_subjenis_barang' => $namaSubjenis]
                );
            }

            foreach ($this->parseSheetRows($sheets['data_barang']) as $row) {
                $kodeDataBarang = $this->requiredValue($row, 'kode_data_barang', 'data_barang');
                $kodeDataBarangKey = trim($kodeDataBarang);
                if (isset($seenDataBarang[$kodeDataBarangKey])) {
                    continue;
                }
                $seenDataBarang[$kodeDataBarangKey] = true;

                $namaBarang = $this->requiredValue($row, 'nama_barang', 'data_barang');
                $kodeSubjenis = $this->requiredValue($row, 'kode_subjenis_barang', 'data_barang');
                $namaSatuan = $this->requiredValue($row, 'nama_satuan', 'data_barang');

                $subjenis = MasterSubjenisBarang::query()->where('kode_subjenis_barang', $kodeSubjenis)->first();
                if (! $subjenis) {
                    throw new \RuntimeException("Sheet data_barang: kode_subjenis_barang '{$kodeSubjenis}' tidak ditemukan.");
                }

                $satuan = MasterSatuan::query()->firstOrCreate(['nama_satuan' => $namaSatuan], ['nama_satuan' => $namaSatuan]);

                $dataBarang = MasterDataBarang::query()->updateOrCreate(
                    ['kode_data_barang' => $kodeDataBarang],
                    [
                        'id_subjenis_barang' => $subjenis->id_subjenis_barang,
                        'id_satuan' => $satuan->id_satuan,
                        'nama_barang' => $namaBarang,
                        'deskripsi' => $this->optionalValue($row, 'deskripsi'),
                        'foto_barang' => $this->optionalValue($row, 'foto_barang'),
                    ]
                );

                if (! isset($sheets['permendagri_108'])) {
                    continue;
                }

                // Mapping permendagri_108 diproses setelah loop ini.
            }

            if (isset($sheets['permendagri_108'])) {
                foreach ($this->parseSheetRows($sheets['permendagri_108']) as $row) {
                    $kodeDataBarang = $this->requiredValue($row, 'kode_data_barang', 'permendagri_108');
                    $dataBarang = MasterDataBarang::query()->where('kode_data_barang', $kodeDataBarang)->first();
                    if (! $dataBarang) {
                        throw new \RuntimeException("Sheet permendagri_108: kode_data_barang '{$kodeDataBarang}' tidak ditemukan.");
                    }

                    $kodeAkun = $this->normalizeSegment($this->requiredValue($row, 'kode_akun', 'permendagri_108'), 1, 'kode_akun');
                    $kodeKelompok = $this->normalizeSegment($this->requiredValue($row, 'kode_kelompok', 'permendagri_108'), 1, 'kode_kelompok');
                    $kodeJenis = $this->normalizeSegment($this->requiredValue($row, 'kode_jenis_108', 'permendagri_108'), 2, 'kode_jenis_108');
                    $kodeObjek = $this->normalizeSegment($this->requiredValue($row, 'kode_objek', 'permendagri_108'), 2, 'kode_objek');
                    $kodeRincianObjek = $this->normalizeSegment($this->requiredValue($row, 'kode_rincian_objek', 'permendagri_108'), 2, 'kode_rincian_objek');
                    $kodeSubRincianObjek = $this->normalizeSegment($this->requiredValue($row, 'kode_sub_rincian_objek', 'permendagri_108'), 3, 'kode_sub_rincian_objek');
                    $kodeSubSubRincianObjek = $this->normalizeSegment($this->requiredValue($row, 'kode_sub_sub_rincian_objek', 'permendagri_108'), 3, 'kode_sub_sub_rincian_objek');

                    $statusValidasi = strtoupper((string) ($this->optionalValue($row, 'status_validasi') ?? 'DRAFT'));
                    if (! in_array($statusValidasi, ['DRAFT', 'REVIEW', 'VALID'], true)) {
                        $statusValidasi = 'DRAFT';
                    }

                    $hasPlaceholder = in_array($kodeJenis, ['00'], true)
                        || in_array($kodeObjek, ['00'], true)
                        || in_array($kodeRincianObjek, ['00'], true)
                        || in_array($kodeSubRincianObjek, ['000'], true)
                        || in_array($kodeSubSubRincianObjek, ['000'], true);

                    if ($statusValidasi === 'VALID' && $hasPlaceholder) {
                        throw new \RuntimeException(
                            "Sheet permendagri_108: kode_data_barang '{$kodeDataBarang}' tidak bisa VALID karena segmen masih placeholder."
                        );
                    }

                    MasterDataBarangPermendagri::query()->updateOrCreate(
                        ['id_data_barang' => $dataBarang->id_data_barang],
                        [
                            'kode_barang_108' => implode('.', [
                                $kodeAkun,
                                $kodeKelompok,
                                $kodeJenis,
                                $kodeObjek,
                                $kodeRincianObjek,
                                $kodeSubRincianObjek,
                                $kodeSubSubRincianObjek,
                            ]),
                            'kode_akun' => $kodeAkun,
                            'kode_kelompok' => $kodeKelompok,
                            'kode_jenis_108' => $kodeJenis,
                            'kode_objek' => $kodeObjek,
                            'kode_rincian_objek' => $kodeRincianObjek,
                            'kode_sub_rincian_objek' => $kodeSubRincianObjek,
                            'kode_sub_sub_rincian_objek' => $kodeSubSubRincianObjek,
                            'sumber_mapping' => 'IMPORT',
                            'status_validasi' => $statusValidasi,
                            'catatan' => $this->optionalValue($row, 'catatan'),
                        ]
                    );
                }
            }
        });
    }

    private function parseSheetRows(array $rows): array
    {
        if (count($rows) < 2) {
            return [];
        }

        $headerRow = array_shift($rows);
        $headers = array_map(fn ($header) => $this->normalizeKey((string) $header), $headerRow);

        $result = [];
        foreach ($rows as $row) {
            $isEmpty = true;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                continue;
            }

            $item = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $item[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }
            $result[] = $item;
        }

        return $result;
    }

    private function requiredValue(array $row, string $field, string $sheetName): string
    {
        $value = $this->optionalValue($row, $field);
        if ($value === null) {
            throw new \RuntimeException("Sheet {$sheetName}: kolom '{$field}' wajib diisi.");
        }

        return $value;
    }

    private function optionalValue(array $row, string $field): ?string
    {
        if (! array_key_exists($field, $row)) {
            return null;
        }

        $value = trim((string) $row[$field]);
        return $value === '' ? null : $value;
    }

    private function normalizeKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace(['-', ' '], '_', $normalized);
        return preg_replace('/[^a-z0-9_]/', '', $normalized) ?? '';
    }

    private function normalizeSegment(string $value, int $length, string $fieldName): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            throw new \RuntimeException("Sheet permendagri_108: kolom '{$fieldName}' harus angka.");
        }
        // Be tolerant untuk data sumber yang kadang memiliki digit lebih panjang
        // (mis. 1000 pada field 3 digit): pakai digit paling depan sesuai panjang.
        if (strlen($digits) > $length) {
            $digits = substr($digits, 0, $length);
        }

        return str_pad((string) $digits, $length, '0', STR_PAD_LEFT);
    }
}

