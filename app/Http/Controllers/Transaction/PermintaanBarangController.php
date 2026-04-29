<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermintaanBarang;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\MasterDataBarang;
use App\Models\MasterSatuan;
use App\Models\DataStock;
use App\Models\DataInventory;
use App\Models\MasterGudang;
use App\Models\ApprovalFlowDefinition;
use Illuminate\Support\Facades\Auth;
use App\Enums\PermintaanBarangStatus;
use App\Services\PermintaanService;
use App\Services\ApprovalService;
use Illuminate\Support\Collection;

class PermintaanBarangController extends Controller
{
    public function __construct(
        private readonly PermintaanService $permintaanService,
        private readonly ApprovalService $approvalService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PermintaanBarang::with([
            'unitKerja.gudang', // Load gudang unit melalui unit kerja
            'pemohon.jabatan' // Load jabatan pemohon
        ]);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan permintaan dari unit kerja user yang login
                $query->where('id_unit_kerja', $pegawai->id_unit_kerja);
                // Hanya tampilkan unit kerja user yang login di dropdown
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                // Jika user tidak memiliki unit kerja, tidak tampilkan data
                $query->whereRaw('1 = 0');
                $unitKerjas = collect([]);
            }
        } else {
            // Admin dan Admin Gudang melihat semua
            $unitKerjas = MasterUnitKerja::all();
        }

        // Filters
        if ($request->filled('unit_kerja')) {
            $query->where('id_unit_kerja', $request->unit_kerja);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            // Filter berdasarkan jenis_permintaan yang sekarang berupa JSON array
            $query->whereJsonContains('jenis_permintaan', $request->jenis);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_permintaan', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', function($q) use ($search) {
                      $q->where('nama_pegawai', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan tanggal mulai
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_permintaan', '>=', $request->tanggal_mulai);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_permintaan', '<=', $request->tanggal_akhir);
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $permintaans = $query->latest('tanggal_permintaan')->paginate($perPage)->appends($request->query());

        return view('transaction.permintaan-barang.index', [
            'permintaans' => $permintaans,
            'unitKerjas' => $unitKerjas,
            'permintaanStatuses' => PermintaanBarangStatus::cases(),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        
        // Filter unit kerja dan pegawai berdasarkan unit kerja user yang login
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan unit kerja user yang login
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                // Hanya tampilkan pegawai dari unit kerja yang sama
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $pegawais = collect([]);
            }
        } else {
            // Admin dan Admin Gudang melihat semua
            $unitKerjas = MasterUnitKerja::all();
            $pegawais = MasterPegawai::all();
        }
        
        $satuans = MasterSatuan::all();
        [
            'dataBarangs' => $dataBarangs,
            'stockPersediaanIds' => $stockPersediaanIds,
            'stockFarmasiIds' => $stockFarmasiIds,
        ] = $this->getBarangMasterFromInventory();

        // Stock data: hanya PERSEDIAAN/FARMASI (stock gudang pusat, wajib validasi)
        $stockPersediaanIdsInt = array_map('intval', $stockPersediaanIds);
        $stockFarmasiIdsInt = array_map('intval', $stockFarmasiIds);
        $stockData = [];
        foreach ($dataBarangs as $barang) {
            $key = (string) $barang->id_data_barang;
            $idBarang = (int) $barang->id_data_barang;
            $stockPusatPersediaan = in_array($idBarang, $stockPersediaanIdsInt)
                ? (float) DataStock::getStockGudangPusat($barang->id_data_barang, 'PERSEDIAAN') : 0;
            $stockPusatFarmasi = in_array($idBarang, $stockFarmasiIdsInt)
                ? (float) DataStock::getStockGudangPusat($barang->id_data_barang, 'FARMASI') : 0;

            $stockData[$key] = [
                'total' => (float) DataStock::getTotalStock($barang->id_data_barang),
                'stock_gudang_pusat_persediaan' => $stockPusatPersediaan,
                'stock_gudang_pusat_farmasi' => $stockPusatFarmasi,
                'per_gudang' => DataStock::getStockPerGudangPusat($barang->id_data_barang),
            ];
        }

        return view('transaction.permintaan-barang.create', compact(
            'unitKerjas', 
            'pegawais', 
            'dataBarangs', 
            'satuans', 
            'stockData',
            'stockPersediaanIds',
            'stockFarmasiIds'
        ));
    }

    public function store(Request $request)
    {
        // Debug: Log request data
        \Log::info('Store Permintaan Request:', [
            'all' => $request->all(),
            'jenis_permintaan' => $request->jenis_permintaan,
            'detail' => $request->detail,
        ]);

        try {
            $validated = $request->validate([
                'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
                'id_pemohon' => 'required|exists:master_pegawai,id',
                'tanggal_permintaan' => 'required|date',
                'tipe_permintaan' => 'required|in:RUTIN,CITO',
                'jenis_permintaan' => 'required|array|min:1',
                'jenis_permintaan.*' => 'required|in:PERSEDIAAN,FARMASI',
                'keterangan' => 'nullable|string',
                'detail' => 'required|array|min:1',
                'detail.*.id_data_barang' => 'nullable|exists:master_data_barang,id_data_barang',
                'detail.*.deskripsi_barang' => 'nullable|string|max:500',
                'detail.*.qty_diminta' => 'required|numeric|min:0.01',
                'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
                'detail.*.keterangan' => 'nullable|string',
            ], [
                'tipe_permintaan.required' => 'Tipe permintaan harus dipilih (Rutin atau CITO (Penting)).',
                'tipe_permintaan.in' => 'Tipe permintaan harus Rutin atau CITO (Penting).',
                'jenis_permintaan.required' => 'Sub jenis permintaan harus dipilih minimal satu (Persediaan atau Farmasi).',
                'jenis_permintaan.array' => 'Sub jenis permintaan harus berupa array.',
                'jenis_permintaan.min' => 'Sub jenis permintaan harus dipilih minimal satu.',
                'jenis_permintaan.*.in' => 'Sub jenis permintaan hanya Persediaan atau Farmasi (Aset tidak masuk permintaan rutin/cito).',
                'detail.required' => 'Detail permintaan harus diisi.',
                'detail.array' => 'Detail permintaan harus berupa array.',
                'detail.min' => 'Detail permintaan harus diisi minimal satu item.',
                'detail.*.qty_diminta.required' => 'Jumlah yang diminta harus diisi.',
                'detail.*.qty_diminta.numeric' => 'Jumlah yang diminta harus berupa angka.',
                'detail.*.qty_diminta.min' => 'Jumlah yang diminta minimal 0.01.',
                'detail.*.id_satuan.required' => 'Satuan harus dipilih.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->withErrors($e->errors());
        }

        // Setiap baris detail: wajib salah satu — dari master (id_data_barang) atau permintaan lainnya (deskripsi_barang)
        $detailErrors = [];
        foreach ($validated['detail'] as $index => $detail) {
            $hasMaster = !empty($detail['id_data_barang']);
            $hasLainnya = !empty(trim((string) ($detail['deskripsi_barang'] ?? '')));
            if (!$hasMaster && !$hasLainnya) {
                $detailErrors["detail.{$index}.id_data_barang"] = 'Pilih data barang dari master atau isi deskripsi untuk permintaan lainnya.';
            }
            if ($hasMaster && $hasLainnya) {
                $detailErrors["detail.{$index}.id_data_barang"] = 'Pilih salah satu: data barang dari master ATAU isi deskripsi permintaan lainnya, jangan keduanya.';
            }
        }
        if (!empty($detailErrors)) {
            return back()->withInput()->withErrors($detailErrors);
        }

        // Validasi stock hanya untuk baris yang dari master (id_data_barang). Permintaan lainnya tidak divalidasi stock.
        $stockPersediaanIds = DataInventory::where('jenis_inventory', 'PERSEDIAAN')->where('status_inventory', 'AKTIF')->pluck('id_data_barang')->unique()->toArray();
        $stockFarmasiIds = DataInventory::where('jenis_inventory', 'FARMASI')->where('status_inventory', 'AKTIF')->pluck('id_data_barang')->unique()->toArray();
        $stockErrors = [];
        foreach ($validated['detail'] as $index => $detail) {
            if (empty($detail['id_data_barang'])) {
                continue; // permintaan lainnya — skip stock check
            }
            $idDataBarang = (int) $detail['id_data_barang'];
            $qtyDiminta = (float) $detail['qty_diminta'];
            $stockPusat = null;
            $labelGudang = '';
            if (in_array($idDataBarang, array_map('intval', $stockFarmasiIds))) {
                $stockPusat = DataStock::getStockGudangPusat($detail['id_data_barang'], 'FARMASI');
                $labelGudang = 'Gudang Farmasi (Pusat)';
            } elseif (in_array($idDataBarang, array_map('intval', $stockPersediaanIds))) {
                $stockPusat = DataStock::getStockGudangPusat($detail['id_data_barang'], 'PERSEDIAAN');
                $labelGudang = 'Gudang Persediaan (Pusat)';
            }
            if ($stockPusat !== null && $qtyDiminta > $stockPusat) {
                $dataBarang = MasterDataBarang::find($detail['id_data_barang']);
                $stockErrors["detail.{$index}.qty_diminta"] = "Jumlah yang diminta ({$qtyDiminta}) melebihi stock di {$labelGudang} ({$stockPusat}) untuk barang {$dataBarang->nama_barang}.";
            }
        }

        if (!empty($stockErrors)) {
            return back()->withInput()->withErrors($stockErrors);
        }

        try {
            $this->permintaanService->createDraft($validated);

            return redirect()->route('transaction.permintaan-barang.index')
                ->with('success', 'Permintaan barang berhasil dibuat.');
        } catch (\Exception $e) {
            \Log::error('Error creating permintaan barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $permintaan = PermintaanBarang::with(['unitKerja', 'pemohon.jabatan', 'detailPermintaan.dataBarang', 'detailPermintaan.satuan', 'approval'])
            ->findOrFail($id);
        $approvalHistory = $this->approvalService->history('PERMINTAAN_BARANG', (int) $permintaan->id_permintaan);
        $approvalFlow = ApprovalFlowDefinition::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->with('role')
            ->orderBy('step_order')
            ->get();

        return view('transaction.permintaan-barang.show', compact('permintaan', 'approvalHistory', 'approvalFlow'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $permintaan = PermintaanBarang::with('detailPermintaan')->findOrFail($id);
        
        // Hanya bisa edit jika status DRAFT
        if ($permintaan->status !== PermintaanBarangStatus::Draft) {
            return redirect()->route('transaction.permintaan-barang.show', $id)
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat di-edit.');
        }

        // Filter unit kerja dan pegawai berdasarkan unit kerja user yang login
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan unit kerja user yang login
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                // Hanya tampilkan pegawai dari unit kerja yang sama
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $pegawais = collect([]);
            }
        } else {
            // Admin dan Admin Gudang melihat semua
            $unitKerjas = MasterUnitKerja::all();
            $pegawais = MasterPegawai::all();
        }

        [
            'dataBarangs' => $dataBarangs,
            'stockPersediaanIds' => $stockPersediaanIds,
            'stockFarmasiIds' => $stockFarmasiIds,
        ] = $this->getBarangMasterFromInventory();

        // Stock data: hanya PERSEDIAAN/FARMASI (stock gudang pusat)
        $stockPersediaanIdsInt = array_map('intval', $stockPersediaanIds);
        $stockFarmasiIdsInt = array_map('intval', $stockFarmasiIds);
        $stockData = [];
        foreach ($dataBarangs as $barang) {
            $key = (string) $barang->id_data_barang;
            $idBarang = (int) $barang->id_data_barang;
            $stockPusatPersediaan = in_array($idBarang, $stockPersediaanIdsInt)
                ? (float) DataStock::getStockGudangPusat($barang->id_data_barang, 'PERSEDIAAN') : 0;
            $stockPusatFarmasi = in_array($idBarang, $stockFarmasiIdsInt)
                ? (float) DataStock::getStockGudangPusat($barang->id_data_barang, 'FARMASI') : 0;

            $stockData[$key] = [
                'total' => (float) DataStock::getTotalStock($barang->id_data_barang),
                'stock_gudang_pusat_persediaan' => $stockPusatPersediaan,
                'stock_gudang_pusat_farmasi' => $stockPusatFarmasi,
                'per_gudang' => DataStock::getStockPerGudangPusat($barang->id_data_barang),
            ];
        }
        $satuans = MasterSatuan::all();

        return view('transaction.permintaan-barang.edit', compact(
            'permintaan', 
            'unitKerjas', 
            'pegawais', 
            'dataBarangs', 
            'satuans',
            'stockData',
            'stockPersediaanIds',
            'stockFarmasiIds'
        ));
    }

    public function update(Request $request, $id)
    {
        $permintaan = PermintaanBarang::findOrFail($id);

        // Hanya bisa edit jika status DRAFT
        if ($permintaan->status !== PermintaanBarangStatus::Draft) {
            return redirect()->route('transaction.permintaan-barang.show', $id)
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat di-edit.');
        }

        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_pemohon' => 'required|exists:master_pegawai,id',
            'tanggal_permintaan' => 'required|date',
            'tipe_permintaan' => 'required|in:RUTIN,CITO',
            'jenis_permintaan' => 'required|array|min:1',
            'jenis_permintaan.*' => 'required|in:PERSEDIAAN,FARMASI',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_data_barang' => 'nullable|exists:master_data_barang,id_data_barang',
            'detail.*.deskripsi_barang' => 'nullable|string|max:500',
            'detail.*.qty_diminta' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.keterangan' => 'nullable|string',
        ]);

        // Setiap baris detail: wajib salah satu — dari master atau permintaan lainnya
        $detailErrors = [];
        foreach ($validated['detail'] as $index => $detail) {
            $hasMaster = !empty($detail['id_data_barang']);
            $hasLainnya = !empty(trim((string) ($detail['deskripsi_barang'] ?? '')));
            if (!$hasMaster && !$hasLainnya) {
                $detailErrors["detail.{$index}.id_data_barang"] = 'Pilih data barang dari master atau isi deskripsi untuk permintaan lainnya.';
            }
            if ($hasMaster && $hasLainnya) {
                $detailErrors["detail.{$index}.id_data_barang"] = 'Pilih salah satu: data barang dari master ATAU isi deskripsi permintaan lainnya, jangan keduanya.';
            }
        }
        if (!empty($detailErrors)) {
            return back()->withInput()->withErrors($detailErrors);
        }

        // Validasi stock hanya untuk baris yang dari master
        $stockPersediaanIds = DataInventory::where('jenis_inventory', 'PERSEDIAAN')->where('status_inventory', 'AKTIF')->pluck('id_data_barang')->unique()->toArray();
        $stockFarmasiIds = DataInventory::where('jenis_inventory', 'FARMASI')->where('status_inventory', 'AKTIF')->pluck('id_data_barang')->unique()->toArray();
        $stockErrors = [];
        foreach ($validated['detail'] as $index => $detail) {
            if (empty($detail['id_data_barang'])) {
                continue;
            }
            $idDataBarang = (int) $detail['id_data_barang'];
            $qtyDiminta = (float) $detail['qty_diminta'];
            $stockPusat = null;
            $labelGudang = '';
            if (in_array($idDataBarang, array_map('intval', $stockFarmasiIds))) {
                $stockPusat = DataStock::getStockGudangPusat($detail['id_data_barang'], 'FARMASI');
                $labelGudang = 'Gudang Farmasi (Pusat)';
            } elseif (in_array($idDataBarang, array_map('intval', $stockPersediaanIds))) {
                $stockPusat = DataStock::getStockGudangPusat($detail['id_data_barang'], 'PERSEDIAAN');
                $labelGudang = 'Gudang Persediaan (Pusat)';
            }
            if ($stockPusat !== null && $qtyDiminta > $stockPusat) {
                $dataBarang = MasterDataBarang::find($detail['id_data_barang']);
                $stockErrors["detail.{$index}.qty_diminta"] = "Jumlah yang diminta ({$qtyDiminta}) melebihi stock di {$labelGudang} ({$stockPusat}) untuk barang {$dataBarang->nama_barang}.";
            }
        }
        if (!empty($stockErrors)) {
            return back()->withInput()->withErrors($stockErrors);
        }

        try {
            $this->permintaanService->updateDraft($permintaan, $validated);

            return redirect()->route('transaction.permintaan-barang.index')
                ->with('success', 'Permintaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating permintaan barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $permintaan = PermintaanBarang::findOrFail($id);

        // Hanya bisa hapus jika status DRAFT
        if ($permintaan->status !== PermintaanBarangStatus::Draft) {
            return redirect()->route('transaction.permintaan-barang.index')
                ->with('error', 'Permintaan yang sudah diajukan tidak dapat dihapus.');
        }

        $this->permintaanService->deleteDraft($permintaan);

        return redirect()->route('transaction.permintaan-barang.index')
            ->with('success', 'Permintaan barang berhasil dihapus.');
    }

    public function ajukan($id)
    {
        $permintaan = PermintaanBarang::findOrFail($id);

        if ($permintaan->status !== PermintaanBarangStatus::Draft) {
            return redirect()->route('transaction.permintaan-barang.show', $id)
                ->with('error', 'Permintaan sudah diajukan sebelumnya.');
        }

        try {
            $this->permintaanService->submit($permintaan);

            return redirect()->route('transaction.permintaan-barang.show', $id)
                ->with('success', 'Permintaan berhasil diajukan untuk persetujuan.');
        } catch (\Exception $e) {
            \Log::error('Error mengajukan permintaan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'permintaan_id' => $id,
            ]);
            return redirect()->route('transaction.permintaan-barang.show', $id)
                ->with('error', 'Terjadi kesalahan saat mengajukan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Sumber "Dari master" untuk form permintaan barang:
     * wajib berasal dari data yang sudah pernah diinput di Data Inventory,
     * bukan seluruh master_data_barang.
     *
     * @return array{
     *   dataBarangs: Collection<int, MasterDataBarang>,
     *   stockPersediaanIds: array<int, int>,
     *   stockFarmasiIds: array<int, int>
     * }
     */
    private function getBarangMasterFromInventory(): array
    {
        // Source of truth dropdown adalah data_stock (karena stok yang ditampilkan di UI
        // juga bersumber dari data_stock / getStockGudangPusat()).
        // Sebelumnya dropdown diambil dari data_inventory dengan status_inventory='AKTIF'
        // sehingga jika inventory belum AKTIF, dropdown bisa kosong.
        $gudangPusatPersediaanIds = MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'PERSEDIAAN')
            ->pluck('id_gudang')
            ->toArray();

        $gudangPusatFarmasiIds = MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'FARMASI')
            ->pluck('id_gudang')
            ->toArray();

        $stockPersediaanIds = empty($gudangPusatPersediaanIds)
            ? []
            : DataStock::query()
                ->whereIn('id_gudang', $gudangPusatPersediaanIds)
                ->whereNotNull('id_data_barang')
                ->distinct()
                ->pluck('id_data_barang')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

        $stockFarmasiIds = empty($gudangPusatFarmasiIds)
            ? []
            : DataStock::query()
                ->whereIn('id_gudang', $gudangPusatFarmasiIds)
                ->whereNotNull('id_data_barang')
                ->distinct()
                ->pluck('id_data_barang')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

        $inventoryBarangIds = array_values(array_unique(array_merge($stockPersediaanIds, $stockFarmasiIds)));

        $dataBarangs = empty($inventoryBarangIds)
            ? collect()
            : MasterDataBarang::query()
                ->with(['subjenisBarang', 'satuan'])
                ->whereIn('id_data_barang', $inventoryBarangIds)
                ->orderBy('kode_data_barang')
                ->get();

        return [
            'dataBarangs' => $dataBarangs,
            'stockPersediaanIds' => $stockPersediaanIds,
            'stockFarmasiIds' => $stockFarmasiIds,
        ];
    }
}
