<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConvertKemendagriKibBToImportTemplate extends Command
{
    protected $signature = 'kemendagri:convert-kib-b
        {--source=C:/Users/pusda/Downloads/BMD KIB B.xlsx : Path file sumber KIB B}
        {--output=storage/app/kemendagri_import_kib_b.xlsx : Path file output template import}';

    protected $description = 'Konversi file BMD KIB B ke format template import struktur barang + permendagri_108';

    public function handle(): int
    {
        $sourcePath = (string) $this->option('source');
        $outputRelative = (string) $this->option('output');
        $outputPath = str_starts_with($outputRelative, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $outputRelative)
            ? $outputRelative
            : base_path($outputRelative);

        if (! file_exists($sourcePath)) {
            $this->error("File sumber tidak ditemukan: {$sourcePath}");
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($sourcePath);
        $sheet = $spreadsheet->getSheetByName('DATA');

        if (! $sheet) {
            $this->error("Sheet 'DATA' tidak ditemukan pada file sumber.");
            return self::FAILURE;
        }

        $rows = $sheet->toArray(null, true, true, false);
        if (count($rows) < 2) {
            $this->error('Data pada sheet DATA kosong.');
            return self::FAILURE;
        }

        $header = array_map(fn ($v) => $this->normalizeKey((string) $v), $rows[0]);
        $dataRows = array_slice($rows, 1);

        $asetRows = [];
        $kodeBarangRows = [];
        $kategoriRows = [];
        $jenisRows = [];
        $subjenisRows = [];
        $dataBarangRows = [];
        $permendagriRows = [];

        $asetSeen = [];
        $kodeSeen = [];
        $kategoriSeen = [];
        $jenisSeen = [];
        $subjenisSeen = [];
        $dataBarangSeen = [];

        foreach ($dataRows as $rawRow) {
            $row = $this->mapRow($header, $rawRow);
            $kodeBarangKemendagri = preg_replace('/\D+/', '', (string) ($row['kode_barang'] ?? '')) ?? '';
            $namaBarang = trim((string) ($row['nama_barang'] ?? ''));
            $objek = trim((string) ($row['objek'] ?? ''));
            $rincianObjek = trim((string) ($row['rincian_objek'] ?? ''));
            $subRincianObjek = trim((string) ($row['sub_rincian_objek'] ?? ''));
            $deskripsi = trim((string) ($row['deskripsi'] ?? ''));
            $idTafsir = preg_replace('/\D+/', '', (string) ($row['id_tafsir'] ?? '')) ?? '';

            if ($kodeBarangKemendagri === '' || $namaBarang === '') {
                continue;
            }

            [$kodeAkun, $kodeKelompok, $kodeJenis, $kodeObjek, $kodeRincianObjek, $kodeSubRincianObjek] =
                $this->splitKemendagriCode($kodeBarangKemendagri);

            $kodeSubSubRincianObjek = str_pad($idTafsir !== '' ? $idTafsir : '0', 3, '0', STR_PAD_LEFT);

            $namaAset = $kodeAkun === '1' ? 'ASET TETAP' : 'ASET';
            $kodeBarang = $kodeAkun . '.' . $kodeKelompok;
            $kodeKategori = $kodeBarang . '.' . $kodeJenis;
            $kodeJenisBarang = $kodeKategori . '.' . $kodeObjek;
            $kodeSubjenis = $kodeJenisBarang . '.' . $kodeRincianObjek;
            $kodeDataBarang = $kodeBarangKemendagri;

            if (! isset($asetSeen[$namaAset])) {
                $asetRows[] = [$namaAset];
                $asetSeen[$namaAset] = true;
            }

            if (! isset($kodeSeen[$kodeBarang])) {
                $kodeBarangRows[] = [$kodeBarang, $objek !== '' ? $objek : 'OBJEK '.$kodeBarang, $namaAset];
                $kodeSeen[$kodeBarang] = true;
            }

            if (! isset($kategoriSeen[$kodeKategori])) {
                $kategoriRows[] = [$kodeKategori, $rincianObjek !== '' ? $rincianObjek : 'RINCIAN '.$kodeKategori, $kodeBarang];
                $kategoriSeen[$kodeKategori] = true;
            }

            if (! isset($jenisSeen[$kodeJenisBarang])) {
                $jenisRows[] = [$kodeJenisBarang, $subRincianObjek !== '' ? $subRincianObjek : 'SUB RINCIAN '.$kodeJenisBarang, $kodeKategori];
                $jenisSeen[$kodeJenisBarang] = true;
            }

            if (! isset($subjenisSeen[$kodeSubjenis])) {
                $subjenisRows[] = [$kodeSubjenis, $subRincianObjek !== '' ? $subRincianObjek : 'SUBJENIS '.$kodeSubjenis, $kodeJenisBarang];
                $subjenisSeen[$kodeSubjenis] = true;
            }

            if (! isset($dataBarangSeen[$kodeDataBarang])) {
                $dataBarangRows[] = [$kodeDataBarang, $namaBarang, $deskripsi !== '' ? $deskripsi : null, $kodeSubjenis, 'Unit', null];
                $dataBarangSeen[$kodeDataBarang] = true;
            }

            $permendagriRows[] = [
                $kodeDataBarang,
                $kodeAkun,
                $kodeKelompok,
                $kodeJenis,
                $kodeObjek,
                $kodeRincianObjek,
                $kodeSubRincianObjek,
                $kodeSubSubRincianObjek,
                'REVIEW',
                'Dikonversi otomatis dari BMD KIB B',
            ];
        }

        $out = new Spreadsheet();
        $this->fillSheet($out->getActiveSheet(), 'aset', ['nama_aset'], $asetRows);
        $this->fillSheet($out->createSheet(), 'kode_barang', ['kode_barang', 'nama_kode_barang', 'nama_aset'], $kodeBarangRows);
        $this->fillSheet($out->createSheet(), 'kategori_barang', ['kode_kategori_barang', 'nama_kategori_barang', 'kode_barang'], $kategoriRows);
        $this->fillSheet($out->createSheet(), 'jenis_barang', ['kode_jenis_barang', 'nama_jenis_barang', 'kode_kategori_barang'], $jenisRows);
        $this->fillSheet($out->createSheet(), 'subjenis_barang', ['kode_subjenis_barang', 'nama_subjenis_barang', 'kode_jenis_barang'], $subjenisRows);
        $this->fillSheet($out->createSheet(), 'data_barang', ['kode_data_barang', 'nama_barang', 'deskripsi', 'kode_subjenis_barang', 'nama_satuan', 'foto_barang'], $dataBarangRows);
        $this->fillSheet($out->createSheet(), 'permendagri_108', [
            'kode_data_barang',
            'kode_akun',
            'kode_kelompok',
            'kode_jenis_108',
            'kode_objek',
            'kode_rincian_objek',
            'kode_sub_rincian_objek',
            'kode_sub_sub_rincian_objek',
            'status_validasi',
            'catatan',
        ], $permendagriRows);

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        (new Xlsx($out))->save($outputPath);

        $this->info('Konversi selesai.');
        $this->line("Output: {$outputPath}");
        $this->table(['Sheet', 'Rows'], [
            ['aset', count($asetRows)],
            ['kode_barang', count($kodeBarangRows)],
            ['kategori_barang', count($kategoriRows)],
            ['jenis_barang', count($jenisRows)],
            ['subjenis_barang', count($subjenisRows)],
            ['data_barang', count($dataBarangRows)],
            ['permendagri_108', count($permendagriRows)],
        ]);

        return self::SUCCESS;
    }

    private function fillSheet($sheet, string $title, array $headers, array $rows): void
    {
        $sheet->setTitle($title);
        $sheet->fromArray($headers, null, 'A1');
        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }
    }

    private function normalizeKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace(['-', ' '], '_', $normalized);
        return preg_replace('/[^a-z0-9_]/', '', $normalized) ?? '';
    }

    private function mapRow(array $headers, array $row): array
    {
        $result = [];
        foreach ($headers as $idx => $header) {
            if ($header === '') {
                continue;
            }
            $result[$header] = $row[$idx] ?? null;
        }
        return $result;
    }

    /**
     * Split 12-digit code: 1|1|2|2|2|3.
     *
     * @return array{0:string,1:string,2:string,3:string,4:string,5:string}
     */
    private function splitKemendagriCode(string $code): array
    {
        $code = str_pad(substr($code, 0, 12), 12, '0', STR_PAD_RIGHT);
        return [
            substr($code, 0, 1),
            substr($code, 1, 1),
            substr($code, 2, 2),
            substr($code, 4, 2),
            substr($code, 6, 2),
            substr($code, 8, 3),
        ];
    }
}

