<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\MasterDataBarang;
use App\Models\MasterGudang;
use App\Models\MasterKegiatan;
use App\Models\MasterProgram;
use App\Models\MasterSubKegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InventoryDataImportController extends Controller
{
    private function hasZipArchiveSupport(): bool
    {
        return class_exists(\ZipArchive::class);
    }

    public function index()
    {
        return view('inventory.data-inventory.import.index');
    }

    public function downloadTemplate()
    {
        if (! $this->hasZipArchiveSupport()) {
            return back()->withErrors([
                'file' => 'Ekstensi PHP zip (ZipArchive) belum aktif pada web server. Aktifkan extension=zip di php.ini (SAPI web), lalu restart server.',
            ]);
        }

        $spreadsheet = new Spreadsheet;

        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('data_inventory');

        $headers = [
            'id_data_barang',
            'id_gudang',
            'id_anggaran',
            'id_sub_kegiatan',
            'jenis_inventory',
            'jenis_barang',
            'tahun_anggaran',
            'qty_input',
            'id_satuan',
            'harga_satuan',
            'merk',
            'tipe',
            'spesifikasi',
            'tahun_produksi',
            'nama_penyedia',
            'no_seri',
            'no_batch',
            'tanggal_kedaluwarsa',
            'status_inventory',
            // Catatan: importer saat ini tidak memproses upload file dari path.
            'upload_foto',
            'upload_dokumen',
        ];

        $dataSheet->fromArray($headers, null, 'A1');

        // Isi 3 contoh baris (silakan ganti dengan ID dari database kamu).
        $exampleRows = [
            [
                1, 1, 1, 1, 'ASET', 'ALKES', date('Y'), 1, 1, 100000,
                'Merk Contoh', 'Tipe Contoh', 'Spesifikasi contoh', 2024, 'PT Contoh',
                'SN-001', '', '', 'AKTIF', '', '',
            ],
            [
                1, 1, 1, 1, 'PERSEDIAAN', 'ATK', date('Y'), 10, 1, 500,
                '', '', '', null, '',
                '', 'BATCH-001', '', 'DRAFT', '', '',
            ],
            [
                1, 1, 1, 1, 'FARMASI', 'OBAT', date('Y'), 20, 1, 2000,
                '', '', '', null, '',
                '', 'BCH-001', date('Y-m-d', strtotime('+180 days')), 'AKTIF', '', '',
            ],
        ];

        $dataSheet->fromArray($exampleRows, null, 'A2');

        // Sheet petunjuk
        $helpSheet = $spreadsheet->createSheet();
        $helpSheet->setTitle('petunjuk');
        $helpSheet->fromArray([
            'Isi data sesuai template (sheet: data_inventory).',
            'Header kolom tidak boleh diubah.',
            'Tanggal kedaluwarsa disarankan format YYYY-MM-DD (untuk sheet: data_inventory).',
            'Untuk jenis ASET: kolom no_batch dan tanggal_kedaluwarsa diabaikan (boleh kosong).',
            'Untuk jenis FARMASI: kolom no_batch dan tanggal_kedaluwarsa wajib terisi.',
            'Kolom upload_foto / upload_dokumen tidak diproses oleh importer saat ini.',
        ], null, 'A1');

        $tempPath = storage_path('app/template_import_inventory_data.xlsx');
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()
            ->download($tempPath, 'template_import_inventory_data.xlsx')
            ->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if (! $this->hasZipArchiveSupport()) {
            return back()->withErrors([
                'file' => 'Import Excel membutuhkan ekstensi PHP zip (ZipArchive). Aktifkan extension=zip pada php.ini yang dipakai web server, lalu restart server.',
            ]);
        }

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());

        $worksheet = $spreadsheet->getSheetByName('data_inventory');
        if (! $worksheet) {
            $worksheet = $spreadsheet->getActiveSheet();
        }

        $rows = $worksheet->toArray(null, true, true, false);
        if (! is_array($rows) || count($rows) < 2) {
            return back()->withErrors([
                'file' => 'File tidak berisi data yang cukup. Pastikan template sheet "data_inventory" digunakan.',
            ]);
        }

        $headerRow = array_shift($rows);
        $headers = array_map(function ($header) {
            $header = strtolower(trim((string) $header));
            $header = str_replace(['-', ' '], '_', $header);

            return preg_replace('/[^a-z0-9_]/', '', $header) ?? '';
        }, $headerRow);

        $parsedRows = [];
        foreach ($rows as $row) {
            $hasValue = false;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    $hasValue = true;
                    break;
                }
            }
            if (! $hasValue) {
                continue;
            }

            $item = [];
            foreach ($headers as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $value = $row[$index] ?? null;
                if (is_string($value)) {
                    $value = trim($value);
                    $item[$key] = $value === '' ? null : $value;
                } else {
                    $item[$key] = $value;
                }
            }
            $parsedRows[] = $item;
        }

        if (empty($parsedRows)) {
            return back()->withErrors([
                'file' => 'Tidak ada baris data yang terdeteksi. Pastikan isi data mulai dari baris 2 pada sheet "data_inventory".',
            ]);
        }

        $counters = [
            'ASET' => 0,
            'PERSEDIAAN' => 0,
            'FARMASI' => 0,
        ];

        try {
            DB::transaction(function () use ($parsedRows, &$counters) {
                foreach ($parsedRows as $rowIndex => $row) {
                    $excelRowNumber = $rowIndex + 2; // baris Excel (mulai dari A2)

                    $tanggalKedaluwarsa = $this->normalizeDateCell($row['tanggal_kedaluwarsa'] ?? null);

                    $input = [
                        'id_data_barang' => $row['id_data_barang'] ?? null,
                        'id_gudang' => $row['id_gudang'] ?? null,
                        'id_anggaran' => $row['id_anggaran'] ?? null,
                        'id_sub_kegiatan' => $row['id_sub_kegiatan'] ?? null,
                        'jenis_inventory' => strtoupper((string) ($row['jenis_inventory'] ?? '')),
                        'jenis_barang' => $row['jenis_barang'] ?? null,
                        'tahun_anggaran' => $row['tahun_anggaran'] ?? null,
                        'qty_input' => $row['qty_input'] ?? null,
                        'id_satuan' => $row['id_satuan'] ?? null,
                        'harga_satuan' => $row['harga_satuan'] ?? null,
                        'merk' => $row['merk'] ?? null,
                        'tipe' => $row['tipe'] ?? null,
                        'spesifikasi' => $row['spesifikasi'] ?? null,
                        'tahun_produksi' => $row['tahun_produksi'] ?? null,
                        'nama_penyedia' => $row['nama_penyedia'] ?? null,
                        'no_seri' => $row['no_seri'] ?? null,
                        'no_batch' => $row['no_batch'] ?? null,
                        'tanggal_kedaluwarsa' => $tanggalKedaluwarsa,
                        'status_inventory' => strtoupper((string) ($row['status_inventory'] ?? '')),
                    ];

                    foreach (['id_data_barang', 'id_gudang', 'id_anggaran', 'id_sub_kegiatan', 'tahun_anggaran', 'qty_input', 'id_satuan', 'harga_satuan', 'tahun_produksi'] as $key) {
                        if (array_key_exists($key, $input) && $input[$key] === '') {
                            $input[$key] = null;
                        }
                    }

                    // Excel sering membaca angka ID/tahun sebagai float (mis: 2026.0) sehingga rule `integer` bisa gagal.
                    foreach (['id_data_barang', 'id_gudang', 'id_anggaran', 'id_sub_kegiatan', 'tahun_anggaran', 'id_satuan', 'tahun_produksi'] as $intKey) {
                        if (array_key_exists($intKey, $input) && $input[$intKey] !== null && is_numeric($input[$intKey])) {
                            $input[$intKey] = (int) $input[$intKey];
                        }
                    }

                    $rules = [
                        'id_data_barang' => 'nullable|exists:master_data_barang,id_data_barang',
                        'id_gudang' => [
                            'required',
                            'exists:master_gudang,id_gudang',
                            function ($attribute, $value, $fail) {
                                $gudang = MasterGudang::find($value);
                                if ($gudang && $gudang->jenis_gudang !== 'PUSAT') {
                                    $fail('Data inventory hanya dapat disimpan di gudang PUSAT. Gudang UNIT hanya menerima distribusi barang.');
                                }
                            },
                        ],
                        'id_anggaran' => 'required|exists:master_sumber_anggaran,id_anggaran',
                        'id_sub_kegiatan' => 'nullable|exists:master_sub_kegiatan,id_sub_kegiatan',
                        'jenis_inventory' => 'required|in:ASET,PERSEDIAAN,FARMASI',
                        'jenis_barang' => 'nullable|string|max:50',
                        'tahun_anggaran' => 'required|integer|min:2000|max:2100',
                        'qty_input' => 'required|numeric|min:1',
                        'id_satuan' => 'required|exists:master_satuan,id_satuan',
                        'harga_satuan' => 'required|numeric|min:0',
                        'merk' => 'nullable|string|max:255',
                        'tipe' => 'nullable|string|max:255',
                        'spesifikasi' => 'nullable|string',
                        'tahun_produksi' => 'nullable|integer',
                        'nama_penyedia' => 'nullable|string|max:255',
                        'no_seri' => 'nullable|string|max:255',
                        'no_batch' => 'nullable|string|max:255',
                        'tanggal_kedaluwarsa' => 'nullable|date',
                        'status_inventory' => 'required|in:DRAFT,AKTIF,DISTRIBUSI,HABIS',
                    ];

                    $validator = Validator::make($input, $rules);
                    if ($validator->fails()) {
                        $first = $validator->errors()->first();
                        throw new \RuntimeException("Baris {$excelRowNumber}: {$first}");
                    }

                    if (($input['jenis_inventory'] ?? '') === 'ASET' && empty($input['id_data_barang'])) {
                        throw new \RuntimeException("Baris {$excelRowNumber}: Data Barang wajib diisi untuk inventory jenis ASET.");
                    }

                    if (in_array($input['jenis_inventory'], ['PERSEDIAAN', 'FARMASI'], true) && empty($input['id_data_barang'])) {
                        $input['id_data_barang'] = MasterDataBarang::query()->value('id_data_barang');
                        if (! $input['id_data_barang']) {
                            throw new \RuntimeException("Baris {$excelRowNumber}: Master Data Barang belum tersedia.");
                        }
                    }

                    if (empty($input['id_sub_kegiatan'])) {
                        $input['id_sub_kegiatan'] = $this->resolveDefaultSubKegiatanId();
                    }

                    $jenisBarangByJenis = [
                        'ASET' => ['ALKES', 'NON ALKES'],
                        'FARMASI' => ['OBAT', 'Vaksin', 'BHP', 'BMHP', 'REAGEN', 'ALKES'],
                        'PERSEDIAAN' => ['ATK', 'ART', 'CETAKAN UMUM', 'CETAK KHUSUS'],
                    ];
                    $allowedJenisBarang = $jenisBarangByJenis[$input['jenis_inventory']] ?? [];
                    if (! in_array((string) $input['jenis_barang'], $allowedJenisBarang, true)) {
                        $allowed = implode(', ', $allowedJenisBarang);
                        throw new \RuntimeException("Baris {$excelRowNumber}: Jenis barang tidak valid untuk jenis inventory '{$input['jenis_inventory']}'. Allowed: {$allowed}");
                    }

                    if (($input['jenis_inventory'] ?? '') === 'FARMASI') {
                        if (empty($input['no_batch'])) {
                            throw new \RuntimeException("Baris {$excelRowNumber}: Nomor batch wajib diisi untuk inventory Farmasi.");
                        }
                        if (empty($input['tanggal_kedaluwarsa'])) {
                            throw new \RuntimeException("Baris {$excelRowNumber}: Tanggal kedaluwarsa wajib diisi untuk inventory Farmasi.");
                        }
                    }

                    $input['total_harga'] = (float) $input['qty_input'] * (float) $input['harga_satuan'];
                    $input['created_by'] = Auth::id();

                    $validated = [
                        'id_data_barang' => $input['id_data_barang'] ?? null,
                        'id_gudang' => (int) $input['id_gudang'],
                        'id_anggaran' => (int) $input['id_anggaran'],
                        'id_sub_kegiatan' => (int) $input['id_sub_kegiatan'],
                        'jenis_inventory' => $input['jenis_inventory'],
                        'jenis_barang' => $input['jenis_barang'],
                        'tahun_anggaran' => (int) $input['tahun_anggaran'],
                        'qty_input' => (float) $input['qty_input'],
                        'id_satuan' => (int) $input['id_satuan'],
                        'harga_satuan' => (float) $input['harga_satuan'],
                        'total_harga' => (float) $input['total_harga'],
                        'merk' => $input['merk'],
                        'tipe' => $input['tipe'],
                        'spesifikasi' => $input['spesifikasi'],
                        'tahun_produksi' => $input['tahun_produksi'] === null ? null : (int) $input['tahun_produksi'],
                        'nama_penyedia' => $input['nama_penyedia'],
                        'no_seri' => $input['no_seri'],
                        'no_batch' => $input['no_batch'],
                        'tanggal_kedaluwarsa' => $input['tanggal_kedaluwarsa'],
                        'status_inventory' => $input['status_inventory'],
                        // upload fields: importer saat ini tidak mengisi.
                        'upload_foto' => null,
                        'upload_dokumen' => null,
                        'created_by' => $input['created_by'],
                    ];

                    DataInventory::create($validated);

                    if (in_array($input['jenis_inventory'], ['PERSEDIAAN', 'FARMASI'], true)) {
                        $this->updateStockForImportedInventory($validated);
                    }

                    $counters[$input['jenis_inventory']]++;
                }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'file' => 'Import gagal: '.$e->getMessage(),
            ]);
        }

        return redirect()
            ->route('inventory.data-inventory.import.index')
            ->with('success', 'Import berhasil. ASET: '.$counters['ASET'].', PERSEDIAAN: '.$counters['PERSEDIAAN'].', FARMASI: '.$counters['FARMASI'].'.');
    }

    private function normalizeDateCell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                return $value;
            }

            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_numeric($value)) {
            try {
                $dt = Date::excelToDateTimeObject((float) $value);

                return $dt->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function resolveDefaultSubKegiatanId(): int
    {
        $id = MasterSubKegiatan::query()->value('id_sub_kegiatan');
        if ($id) {
            return (int) $id;
        }

        $programDefaults = [];
        if (DB::getSchemaBuilder()->hasColumn('master_program', 'kode_program')) {
            $programDefaults['kode_program'] = 'SYS-DEFAULT-PROGRAM';
        }

        $program = MasterProgram::query()->firstOrCreate(
            ['nama_program' => 'PROGRAM DEFAULT SISTEM'],
            $programDefaults
        );

        $kegiatanDefaults = [];
        if (DB::getSchemaBuilder()->hasColumn('master_kegiatan', 'kode_kegiatan')) {
            $kegiatanDefaults['kode_kegiatan'] = 'SYS-DEFAULT-KEGIATAN';
        }

        $kegiatan = MasterKegiatan::query()->firstOrCreate(
            ['id_program' => $program->id_program, 'nama_kegiatan' => 'KEGIATAN DEFAULT SISTEM'],
            $kegiatanDefaults
        );

        $subKegiatan = MasterSubKegiatan::query()->firstOrCreate(
            ['kode_sub_kegiatan' => 'SYS-DEFAULT-SUB-KEGIATAN'],
            [
                'id_kegiatan' => $kegiatan->id_kegiatan,
                'nama_sub_kegiatan' => 'SUB KEGIATAN DEFAULT SISTEM',
            ]
        );

        return (int) $subKegiatan->id_sub_kegiatan;
    }

    private function updateStockForImportedInventory(array $validated): void
    {
        $dataQty = (float) $validated['qty_input'];
        $dataIdSatuan = (int) $validated['id_satuan'];

        $stock = DataStock::firstOrNew([
            'id_data_barang' => (int) $validated['id_data_barang'],
            'id_gudang' => (int) $validated['id_gudang'],
        ]);

        if ($stock->exists) {
            $stock->qty_masuk += $dataQty;
            $stock->qty_akhir += $dataQty;
        } else {
            $stock->qty_awal = 0;
            $stock->qty_masuk = $dataQty;
            $stock->qty_keluar = 0;
            $stock->qty_akhir = $dataQty;
            $stock->id_satuan = $dataIdSatuan;
        }

        $stock->last_updated = now();
        $stock->save();
    }
}
