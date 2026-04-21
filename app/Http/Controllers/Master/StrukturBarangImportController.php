<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterAset;
use App\Models\MasterDataBarang;
use App\Models\MasterJenisBarang;
use App\Models\MasterKategoriBarang;
use App\Models\MasterKodeBarang;
use App\Models\MasterSatuan;
use App\Models\MasterSubjenisBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StrukturBarangImportController extends Controller
{
    public function index()
    {
        return view('master-data.import-struktur-barang.index');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        $this->fillSheet(
            $spreadsheet->getActiveSheet(),
            'aset',
            ['nama_aset'],
            [
                ['ASET TETAP'],
                ['ASET LANCAR'],
            ]
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'kode_barang',
            ['kode_barang', 'nama_kode_barang', 'nama_aset'],
            [
                ['01.01', 'PERALATAN MEDIS', 'ASET TETAP'],
            ]
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'kategori_barang',
            ['kode_kategori_barang', 'nama_kategori_barang', 'kode_barang'],
            [
                ['01.01.001', 'ALAT UKUR', '01.01'],
            ]
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'jenis_barang',
            ['kode_jenis_barang', 'nama_jenis_barang', 'kode_kategori_barang'],
            [
                ['01.01.001.01', 'TENSIMETER', '01.01.001'],
            ]
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'subjenis_barang',
            ['kode_subjenis_barang', 'nama_subjenis_barang', 'kode_jenis_barang'],
            [
                ['01.01.001.01.01', 'TENSIMETER DIGITAL', '01.01.001.01'],
            ]
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'data_barang',
            ['kode_data_barang', 'nama_barang', 'deskripsi', 'kode_subjenis_barang', 'nama_satuan', 'foto_barang'],
            [
                ['DB-0001', 'Tensimeter Digital Omron', 'Untuk pemeriksaan tekanan darah', '01.01.001.01.01', 'Unit', ''],
            ]
        );

        $satuanRows = MasterSatuan::query()
            ->orderBy('nama_satuan')
            ->pluck('nama_satuan')
            ->map(fn ($namaSatuan) => [$namaSatuan])
            ->values()
            ->all();

        if (empty($satuanRows)) {
            $satuanRows = [['Unit'], ['Set'], ['Buah']];
        }

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'referensi_satuan',
            ['nama_satuan'],
            $satuanRows
        );

        $this->fillSheet(
            $spreadsheet->createSheet(),
            'petunjuk',
            ['keterangan'],
            [
                ['1. Isi data sesuai nama sheet yang tersedia.'],
                ['2. Header kolom tidak boleh diubah.'],
                ['3. Relasi antar sheet menggunakan kolom kode, bukan ID.'],
                ['4. Import berjalan berurutan: aset -> kode -> kategori -> jenis -> subjenis -> data barang.'],
                ['5. Jika data dengan kode sama sudah ada, sistem akan memperbarui data tersebut.'],
            ]
        );

        $tempPath = storage_path('app/template_import_struktur_barang.xlsx');
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()
            ->download($tempPath, 'template_import_struktur_barang.xlsx')
            ->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());

        $sheets = [];
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $sheets[$this->normalizeKey($worksheet->getTitle())] = $worksheet->toArray(null, true, true, false);
        }

        $requiredSheets = [
            'aset',
            'kode_barang',
            'kategori_barang',
            'jenis_barang',
            'subjenis_barang',
            'data_barang',
        ];

        foreach ($requiredSheets as $requiredSheet) {
            if (! isset($sheets[$requiredSheet])) {
                return back()->withErrors([
                    'file' => "Sheet '{$requiredSheet}' tidak ditemukan. Gunakan template resmi agar format sesuai.",
                ]);
            }
        }

        $counters = [
            'aset' => 0,
            'kode_barang' => 0,
            'kategori_barang' => 0,
            'jenis_barang' => 0,
            'subjenis_barang' => 0,
            'data_barang' => 0,
        ];

        try {
            DB::transaction(function () use ($sheets, &$counters) {
                $asetRows = $this->parseSheetRows($sheets['aset']);
                foreach ($asetRows as $row) {
                    $namaAset = $this->requiredValue($row, 'nama_aset', 'aset');

                    $aset = MasterAset::firstOrCreate(
                        ['nama_aset' => $namaAset],
                        ['nama_aset' => $namaAset]
                    );

                    if (! $aset->wasRecentlyCreated) {
                        $aset->update(['nama_aset' => $namaAset]);
                    }

                    $counters['aset']++;
                }

                $kodeRows = $this->parseSheetRows($sheets['kode_barang']);
                foreach ($kodeRows as $row) {
                    $kodeBarang = $this->requiredValue($row, 'kode_barang', 'kode_barang');
                    $namaKodeBarang = $this->requiredValue($row, 'nama_kode_barang', 'kode_barang');
                    $namaAset = $this->requiredValue($row, 'nama_aset', 'kode_barang');

                    $aset = MasterAset::where('nama_aset', $namaAset)->first();
                    if (! $aset) {
                        throw new \RuntimeException("Sheet kode_barang: nama_aset '{$namaAset}' tidak ditemukan.");
                    }

                    MasterKodeBarang::updateOrCreate(
                        ['kode_barang' => $kodeBarang],
                        [
                            'id_aset' => $aset->id_aset,
                            'nama_kode_barang' => $namaKodeBarang,
                        ]
                    );

                    $counters['kode_barang']++;
                }

                $kategoriRows = $this->parseSheetRows($sheets['kategori_barang']);
                foreach ($kategoriRows as $row) {
                    $kodeKategori = $this->requiredValue($row, 'kode_kategori_barang', 'kategori_barang');
                    $namaKategori = $this->requiredValue($row, 'nama_kategori_barang', 'kategori_barang');
                    $kodeBarang = $this->requiredValue($row, 'kode_barang', 'kategori_barang');

                    $masterKodeBarang = MasterKodeBarang::where('kode_barang', $kodeBarang)->first();
                    if (! $masterKodeBarang) {
                        throw new \RuntimeException("Sheet kategori_barang: kode_barang '{$kodeBarang}' tidak ditemukan.");
                    }

                    MasterKategoriBarang::updateOrCreate(
                        ['kode_kategori_barang' => $kodeKategori],
                        [
                            'id_kode_barang' => $masterKodeBarang->id_kode_barang,
                            'nama_kategori_barang' => $namaKategori,
                        ]
                    );

                    $counters['kategori_barang']++;
                }

                $jenisRows = $this->parseSheetRows($sheets['jenis_barang']);
                foreach ($jenisRows as $row) {
                    $kodeJenis = $this->requiredValue($row, 'kode_jenis_barang', 'jenis_barang');
                    $namaJenis = $this->requiredValue($row, 'nama_jenis_barang', 'jenis_barang');
                    $kodeKategori = $this->requiredValue($row, 'kode_kategori_barang', 'jenis_barang');

                    $kategori = MasterKategoriBarang::where('kode_kategori_barang', $kodeKategori)->first();
                    if (! $kategori) {
                        throw new \RuntimeException("Sheet jenis_barang: kode_kategori_barang '{$kodeKategori}' tidak ditemukan.");
                    }

                    MasterJenisBarang::updateOrCreate(
                        ['kode_jenis_barang' => $kodeJenis],
                        [
                            'id_kategori_barang' => $kategori->id_kategori_barang,
                            'nama_jenis_barang' => $namaJenis,
                        ]
                    );

                    $counters['jenis_barang']++;
                }

                $subjenisRows = $this->parseSheetRows($sheets['subjenis_barang']);
                foreach ($subjenisRows as $row) {
                    $kodeSubjenis = $this->requiredValue($row, 'kode_subjenis_barang', 'subjenis_barang');
                    $namaSubjenis = $this->requiredValue($row, 'nama_subjenis_barang', 'subjenis_barang');
                    $kodeJenis = $this->requiredValue($row, 'kode_jenis_barang', 'subjenis_barang');

                    $jenis = MasterJenisBarang::where('kode_jenis_barang', $kodeJenis)->first();
                    if (! $jenis) {
                        throw new \RuntimeException("Sheet subjenis_barang: kode_jenis_barang '{$kodeJenis}' tidak ditemukan.");
                    }

                    MasterSubjenisBarang::updateOrCreate(
                        ['kode_subjenis_barang' => $kodeSubjenis],
                        [
                            'id_jenis_barang' => $jenis->id_jenis_barang,
                            'nama_subjenis_barang' => $namaSubjenis,
                        ]
                    );

                    $counters['subjenis_barang']++;
                }

                $dataBarangRows = $this->parseSheetRows($sheets['data_barang']);
                foreach ($dataBarangRows as $row) {
                    $kodeDataBarang = $this->requiredValue($row, 'kode_data_barang', 'data_barang');
                    $namaBarang = $this->requiredValue($row, 'nama_barang', 'data_barang');
                    $kodeSubjenis = $this->requiredValue($row, 'kode_subjenis_barang', 'data_barang');
                    $namaSatuan = $this->requiredValue($row, 'nama_satuan', 'data_barang');

                    $subjenis = MasterSubjenisBarang::where('kode_subjenis_barang', $kodeSubjenis)->first();
                    if (! $subjenis) {
                        throw new \RuntimeException("Sheet data_barang: kode_subjenis_barang '{$kodeSubjenis}' tidak ditemukan.");
                    }

                    $satuan = MasterSatuan::firstOrCreate(
                        ['nama_satuan' => $namaSatuan],
                        ['nama_satuan' => $namaSatuan]
                    );

                    MasterDataBarang::updateOrCreate(
                        ['kode_data_barang' => $kodeDataBarang],
                        [
                            'id_subjenis_barang' => $subjenis->id_subjenis_barang,
                            'id_satuan' => $satuan->id_satuan,
                            'nama_barang' => $namaBarang,
                            'deskripsi' => $this->optionalValue($row, 'deskripsi'),
                            'foto_barang' => $this->optionalValue($row, 'foto_barang'),
                        ]
                    );

                    $counters['data_barang']++;
                }
            });
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'file' => 'Import gagal: ' . $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('master-data.import-struktur-barang.index')
            ->with('success', 'Import berhasil. Aset: ' . $counters['aset']
                . ', Kode Barang: ' . $counters['kode_barang']
                . ', Kategori: ' . $counters['kategori_barang']
                . ', Jenis: ' . $counters['jenis_barang']
                . ', Subjenis: ' . $counters['subjenis_barang']
                . ', Data Barang: ' . $counters['data_barang'] . '.');
    }

    private function fillSheet($sheet, string $title, array $headers, array $rows): void
    {
        $sheet->setTitle($title);
        $sheet->fromArray($headers, null, 'A1');
        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }
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
}

