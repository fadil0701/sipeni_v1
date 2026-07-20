<?php

namespace App\Http\Controllers\Transaction;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReturBarang;
use App\Models\DetailReturBarang;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\User;
use App\Models\MasterGudang;
use App\Models\MasterSatuan;
use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\PrintTemplate;
use App\Services\PrintTemplateRenderer;
use App\Services\ReturPrintTemplateData;
use App\Services\StockGuardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class ReturBarangController extends Controller
{
    public function __construct(
        private readonly StockGuardService $stockGuard
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = ReturBarang::with(['unitKerja', 'gudangAsal', 'gudangTujuan', 'pegawaiPengirim']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan retur dari unit kerja user yang login
                $query->where('id_unit_kerja', $pegawai->id_unit_kerja);
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
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
            $query->where('status_retur', $request->status);
        }
        if ($request->filled('jenis_retur')) {
            $jenis = $request->string('jenis_retur')->toString();
            $query->where('alasan_retur', 'like', '[' . $jenis . ']%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_retur', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $returs = $query->latest('tanggal_retur')->paginate($perPage)->appends($request->query());

        $jenisReturOptions = ReturBarang::jenisReturOptions();

        return view('transaction.retur-barang.index', compact('returs', 'unitKerjas', 'jenisReturOptions'));
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                
                // Get gudang unit kerja user
                $gudangUnit = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->first();
                $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')->first();
                
                $gudangs = collect([$gudangUnit, $gudangPusat])->filter();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $gudangs = MasterGudang::all();
            $pegawais = MasterPegawai::all();
        }

        $satuans = MasterSatuan::all();
        $jenisReturOptions = ReturBarang::jenisReturOptions();
        $gudangPusatByKategori = $this->gudangPusatByKategoriMap();

        return view('transaction.retur-barang.create', compact('unitKerjas', 'gudangs', 'pegawais', 'satuans', 'jenisReturOptions', 'gudangPusatByKategori'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_retur' => 'required|date',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pengirim' => 'required|exists:master_pegawai,id',
            'submit_action' => 'nullable|in:draft,ajukan',
            'jenis_retur' => 'required|in:RUSAK,SISA,LAINNYA',
            'alasan_retur' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_retur' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_retur_item' => 'nullable|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawaiScope = MasterPegawai::where('user_id', $user->id)->first();
            if (! $pegawaiScope || (int) $pegawaiScope->id_unit_kerja !== (int) $validated['id_unit_kerja']) {
                return back()->withErrors(['id_unit_kerja' => 'Anda hanya dapat membuat retur untuk unit kerja Anda.'])->withInput();
            }
        }

        if (! $this->isPegawaiInUnit((int) $validated['id_pegawai_pengirim'], (int) $validated['id_unit_kerja'])) {
            return back()->withErrors(['id_pegawai_pengirim' => 'Pegawai pengirim harus berasal dari unit kerja yang dipilih.'])->withInput();
        }
        if (! $this->isGudangAsalInUnit((int) $validated['id_gudang_asal'], (int) $validated['id_unit_kerja'])) {
            return back()->withErrors(['id_gudang_asal' => 'Gudang asal harus gudang UNIT pada unit kerja yang dipilih.'])->withInput();
        }
        if (! $this->isGudangPusat((int) $validated['id_gudang_tujuan'])) {
            return back()->withErrors(['id_gudang_tujuan' => 'Gudang tujuan harus gudang PUSAT.'])->withInput();
        }

        $detailErrors = $this->validateReturDetails(
            $validated['detail'],
            (int) $validated['id_gudang_asal'],
            (int) $validated['id_gudang_tujuan']
        );
        if ($detailErrors !== []) {
            return back()->withErrors($detailErrors)->withInput();
        }

        $statusRetur = $request->input('submit_action') === 'ajukan' ? 'DIAJUKAN' : 'DRAFT';

        DB::beginTransaction();
        try {
            // Generate nomor retur
            $tahun = Carbon::parse($validated['tanggal_retur'])->format('Y');
            $lastRetur = ReturBarang::whereYear('tanggal_retur', $tahun)
                ->orderBy('no_retur', 'desc')
                ->first();

            $urut = 1;
            if ($lastRetur) {
                $parts = explode('/', $lastRetur->no_retur);
                $urut = (int)end($parts) + 1;
            }

            $noRetur = sprintf('RETUR/%s/%04d', $tahun, $urut);

            // Create retur
            $alasanText = trim((string) ($validated['alasan_retur'] ?? ''));
            $alasanWithJenis = '[' . $validated['jenis_retur'] . '] ' . ($alasanText !== '' ? $alasanText : '-');
            $retur = ReturBarang::create([
                'no_retur' => $noRetur,
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'],
                'tanggal_retur' => $validated['tanggal_retur'],
                'status_retur' => $statusRetur,
                'alasan_retur' => $alasanWithJenis,
            ]);

            // Create detail retur
            foreach ($validated['detail'] as $detail) {
                DetailReturBarang::create([
                    'id_retur' => $retur->id_retur,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_retur' => $detail['qty_retur'],
                    'id_satuan' => $detail['id_satuan'],
                    'alasan_retur_item' => $detail['alasan_retur_item'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('transaction.retur-barang.index')
                ->with('success', $statusRetur === 'DIAJUKAN'
                    ? 'Retur barang berhasil diajukan ke Admin Gudang Pusat.'
                    : 'Retur barang berhasil disimpan sebagai draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating retur barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $retur = ReturBarang::with([
            'unitKerja',
            'gudangAsal',
            'gudangTujuan',
            'pegawaiPengirim',
            'detailRetur.inventory.dataBarang',
            'detailRetur.satuan'
        ])->findOrFail($id);

        $this->assertReturUnitAccess($retur);

        return view('transaction.retur-barang.show', compact('retur'));
    }

    /**
     * Cetak dokumen pengembalian dari template aktif (key: retur.pengembalian).
     */
    public function printPengembalian(int|string $id): Response|RedirectResponse
    {
        $retur = ReturBarang::query()->findOrFail($id);

        $template = PrintTemplate::query()
            ->where('key', 'retur.pengembalian')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return redirect()
                ->route('transaction.retur-barang.show', $id)
                ->with('info', 'Template cetak retur belum tersedia atau nonaktif. Buat template dengan key retur.pengembalian di Admin → Template Cetak.');
        }

        $html = PrintTemplateRenderer::render($template, ReturPrintTemplateData::payload($retur));

        return response()
            ->view('admin.print-templates.preview-frame', [
                'title' => 'Retur '.$retur->no_retur,
                'html' => $html,
                'printTemplate' => $template,
                'allowPdfExport' => false,
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function edit($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $retur = ReturBarang::with([
            'detailRetur.inventory.dataBarang',
            'detailRetur.satuan',
        ])->findOrFail($id);

        $this->assertReturUnitAccess($retur);
        
        // Hanya bisa edit jika status DRAFT atau DIAJUKAN
        if (!in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN'])) {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Retur yang sudah DITERIMA atau DITOLAK tidak dapat diedit.');
        }

        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                
                $gudangUnit = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->first();
                $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')->first();
                $gudangs = collect([$gudangUnit, $gudangPusat])->filter();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $gudangs = MasterGudang::all();
            $pegawais = MasterPegawai::all();
        }

        $satuans = MasterSatuan::all();
        $jenisReturOptions = ReturBarang::jenisReturOptions();
        $gudangPusatByKategori = $this->gudangPusatByKategoriMap();

        return view('transaction.retur-barang.edit', compact('retur', 'unitKerjas', 'gudangs', 'pegawais', 'satuans', 'jenisReturOptions', 'gudangPusatByKategori'));
    }

    public function update(Request $request, $id)
    {
        $retur = ReturBarang::findOrFail($id);

        $this->assertReturUnitAccess($retur);

        // Hanya bisa update jika status DRAFT atau DIAJUKAN
        if (!in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN'])) {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Retur yang sudah DITERIMA atau DITOLAK tidak dapat diupdate.');
        }

        $validated = $request->validate([
            'tanggal_retur' => 'required|date',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pengirim' => 'required|exists:master_pegawai,id',
            'submit_action' => 'nullable|in:draft,ajukan',
            'jenis_retur' => 'required|in:RUSAK,SISA,LAINNYA',
            'alasan_retur' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_retur' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_retur_item' => 'nullable|string',
        ]);

        if (! $this->isPegawaiInUnit((int) $validated['id_pegawai_pengirim'], (int) $validated['id_unit_kerja'])) {
            return back()->withErrors(['id_pegawai_pengirim' => 'Pegawai pengirim harus berasal dari unit kerja yang dipilih.'])->withInput();
        }
        if (! $this->isGudangAsalInUnit((int) $validated['id_gudang_asal'], (int) $validated['id_unit_kerja'])) {
            return back()->withErrors(['id_gudang_asal' => 'Gudang asal harus gudang UNIT pada unit kerja yang dipilih.'])->withInput();
        }
        if (! $this->isGudangPusat((int) $validated['id_gudang_tujuan'])) {
            return back()->withErrors(['id_gudang_tujuan' => 'Gudang tujuan harus gudang PUSAT.'])->withInput();
        }

        $detailErrors = $this->validateReturDetails(
            $validated['detail'],
            (int) $validated['id_gudang_asal'],
            (int) $validated['id_gudang_tujuan']
        );
        if ($detailErrors !== []) {
            return back()->withErrors($detailErrors)->withInput();
        }

        $statusRetur = $retur->status_retur === 'DRAFT' && $request->input('submit_action') === 'ajukan'
            ? 'DIAJUKAN'
            : $retur->status_retur;

        DB::beginTransaction();
        try {
            // Update retur
            $alasanText = trim((string) ($validated['alasan_retur'] ?? ''));
            $alasanWithJenis = '[' . $validated['jenis_retur'] . '] ' . ($alasanText !== '' ? $alasanText : '-');
            $retur->update([
                'tanggal_retur' => $validated['tanggal_retur'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'],
                'status_retur' => $statusRetur,
                'alasan_retur' => $alasanWithJenis,
            ]);

            // Delete existing details
            $retur->detailRetur()->delete();

            // Create new details
            foreach ($validated['detail'] as $detail) {
                DetailReturBarang::create([
                    'id_retur' => $retur->id_retur,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_retur' => $detail['qty_retur'],
                    'id_satuan' => $detail['id_satuan'],
                    'alasan_retur_item' => $detail['alasan_retur_item'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('transaction.retur-barang.index')
                ->with('success', 'Retur barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating retur barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $retur = ReturBarang::findOrFail($id);

        $this->assertReturUnitAccess($retur);

        // Hanya bisa hapus jika status DRAFT atau DIAJUKAN
        if (!in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN'])) {
            return redirect()->route('transaction.retur-barang.index')
                ->with('error', 'Retur yang sudah DITERIMA atau DITOLAK tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $retur->detailRetur()->delete();
            $retur->delete();

            DB::commit();

            return redirect()->route('transaction.retur-barang.index')
                ->with('success', 'Retur barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting retur barang: ' . $e->getMessage());
            return redirect()->route('transaction.retur-barang.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Terima retur barang (untuk admin gudang pusat)
     * Update stock saat retur diterima
     */
    public function terima(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // Hanya admin dan admin_gudang yang bisa terima retur
        if (!(UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user))) {
            abort(403, 'Unauthorized');
        }
        
        $retur = ReturBarang::with(['detailRetur.inventory', 'gudangAsal', 'gudangTujuan'])->findOrFail($id);
        
        // Hanya bisa terima jika status DIAJUKAN
        if ($retur->status_retur != 'DIAJUKAN') {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Hanya retur dengan status DIAJUKAN yang dapat diterima.');
        }
        
        DB::beginTransaction();
        try {
            // Update status retur menjadi DITERIMA
            $retur->update([
                'status_retur' => 'DITERIMA',
            ]);
            
            // Update stock untuk setiap detail retur
            foreach ($retur->detailRetur as $detail) {
                $inventory = $detail->inventory;
                $context = "penerimaan retur {$retur->no_retur}";
                $this->stockGuard->ensureInventoryQty((int) $detail->id_inventory, (float) $detail->qty_retur, $context);
                
                if (in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'])) {
                    $this->stockGuard->ensureStockQty(
                        (int) $inventory->id_data_barang,
                        (int) $retur->id_gudang_asal,
                        (float) $detail->qty_retur,
                        $context
                    );

                    // Untuk PERSEDIAAN/FARMASI: Update DataStock
                    
                    // Kurangi stock di gudang asal (gudang unit)
                    $stockAsal = DataStock::where('id_data_barang', $inventory->id_data_barang)
                        ->where('id_gudang', $retur->id_gudang_asal)
                        ->first();
                    
                    if ($stockAsal) {
                        $stockAsal->qty_keluar += $detail->qty_retur;
                        $stockAsal->qty_akhir -= $detail->qty_retur;
                        $stockAsal->last_updated = now();
                        $stockAsal->save();
                    }
                    
                    // Tambah stock di gudang tujuan (gudang pusat)
                    $stockTujuan = DataStock::firstOrNew([
                        'id_data_barang' => $inventory->id_data_barang,
                        'id_gudang' => $retur->id_gudang_tujuan,
                    ]);
                    
                    if ($stockTujuan->exists) {
                        $stockTujuan->qty_masuk += $detail->qty_retur;
                        $stockTujuan->qty_akhir += $detail->qty_retur;
                    } else {
                        $stockTujuan->qty_awal = 0;
                        $stockTujuan->qty_masuk = $detail->qty_retur;
                        $stockTujuan->qty_keluar = 0;
                        $stockTujuan->qty_akhir = $detail->qty_retur;
                        $stockTujuan->id_satuan = $inventory->id_satuan;
                    }
                    
                    $stockTujuan->last_updated = now();
                    $stockTujuan->save();
                    
                    // Update atau pindahkan inventory ke gudang tujuan
                    // Jika qty_retur sama dengan qty_input, pindahkan seluruh inventory
                    // Jika tidak, buat inventory baru di gudang tujuan
                    if ($detail->qty_retur >= $inventory->qty_input) {
                        // Pindahkan seluruh inventory
                        $inventory->update([
                            'id_gudang' => $retur->id_gudang_tujuan,
                        ]);
                    } else {
                        // Buat inventory baru di gudang tujuan dengan qty_retur
                        DataInventory::create([
                            'id_data_barang' => $inventory->id_data_barang,
                            'id_gudang' => $retur->id_gudang_tujuan,
                            'id_anggaran' => $inventory->id_anggaran,
                            'id_sub_kegiatan' => $inventory->id_sub_kegiatan,
                            'jenis_inventory' => $inventory->jenis_inventory,
                            'jenis_barang' => $inventory->jenis_barang,
                            'tahun_anggaran' => $inventory->tahun_anggaran,
                            'qty_input' => $detail->qty_retur,
                            'id_satuan' => $inventory->id_satuan,
                            'harga_satuan' => $inventory->harga_satuan,
                            'total_harga' => $inventory->harga_satuan * $detail->qty_retur,
                            'merk' => $inventory->merk,
                            'tipe' => $inventory->tipe,
                            'spesifikasi' => $inventory->spesifikasi,
                            'tahun_produksi' => $inventory->tahun_produksi,
                            'no_seri' => $inventory->no_seri,
                            'no_batch' => $inventory->no_batch,
                            'tanggal_kedaluwarsa' => $inventory->tanggal_kedaluwarsa,
                            'status_inventory' => 'AKTIF',
                            'created_by' => Auth::id(),
                        ]);
                        
                        // Kurangi qty_input inventory asal
                        $inventory->qty_input -= $detail->qty_retur;
                        $inventory->total_harga = $inventory->harga_satuan * $inventory->qty_input;
                        $inventory->save();
                    }
                } elseif ($inventory->jenis_inventory === 'ASET') {
                    // Untuk ASET: Update InventoryItem (pindahkan ke gudang tujuan)
                    // Ambil inventory items yang terkait dengan inventory ini di gudang asal
                    $inventoryItems = \App\Models\InventoryItem::where('id_inventory', $inventory->id_inventory)
                        ->where('id_gudang', $retur->id_gudang_asal)
                        ->limit((int)$detail->qty_retur)
                        ->get();
                    
                    foreach ($inventoryItems as $item) {
                        $item->update([
                            'id_gudang' => $retur->id_gudang_tujuan,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('success', 'Retur barang berhasil diterima dan stock telah diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting retur barang: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menerima retur: ' . $e->getMessage());
        }
    }

    /**
     * Tolak retur barang
     */
    public function tolak(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // Hanya admin dan admin_gudang yang bisa tolak retur
        if (!(UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user))) {
            abort(403, 'Unauthorized');
        }
        
        $retur = ReturBarang::findOrFail($id);
        
        // Hanya bisa tolak jika status DIAJUKAN
        if ($retur->status_retur != 'DIAJUKAN') {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Hanya retur dengan status DIAJUKAN yang dapat ditolak.');
        }
        
        $validated = $request->validate([
            'keterangan' => 'nullable|string|max:1000',
        ]);
        $catatanTolak = trim((string) ($validated['keterangan'] ?? 'Tidak ada keterangan'));
        $existingAlasan = trim((string) ($retur->alasan_retur ?? ''));
        $retur->update([
            'status_retur' => 'DITOLAK',
            'alasan_retur' => trim($existingAlasan . "\n[DITOLAK] " . $catatanTolak),
        ]);
        
        return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
            ->with('success', 'Retur barang telah ditolak.');
    }

    /**
     * Ajukan retur untuk approval
     */
    public function ajukan($id)
    {
        $retur = ReturBarang::findOrFail($id);

        $this->assertReturUnitAccess($retur);
        
        // Hanya bisa ajukan jika status DRAFT
        if ($retur->status_retur != 'DRAFT') {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Hanya retur dengan status DRAFT yang dapat diajukan.');
        }
        
        $retur->update([
            'status_retur' => 'DIAJUKAN',
        ]);
        
        return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
            ->with('success', 'Retur barang berhasil diajukan untuk persetujuan.');
    }

    private function isPegawaiInUnit(int $idPegawai, int $idUnitKerja): bool
    {
        return MasterPegawai::query()
            ->where('id', $idPegawai)
            ->where('id_unit_kerja', $idUnitKerja)
            ->exists();
    }

    private function isGudangAsalInUnit(int $idGudang, int $idUnitKerja): bool
    {
        return MasterGudang::query()
            ->where('id_gudang', $idGudang)
            ->where('id_unit_kerja', $idUnitKerja)
            ->where('jenis_gudang', 'UNIT')
            ->exists();
    }

    private function isGudangPusat(int $idGudang): bool
    {
        return MasterGudang::query()
            ->where('id_gudang', $idGudang)
            ->where('jenis_gudang', 'PUSAT')
            ->exists();
    }

    /**
     * @return array<string, int>
     */
    private function gudangPusatByKategoriMap(): array
    {
        return MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->whereIn('kategori_gudang', ['PERSEDIAAN', 'FARMASI', 'ASET'])
            ->get()
            ->mapWithKeys(fn (MasterGudang $g) => [(string) $g->kategori_gudang => (int) $g->id_gudang])
            ->all();
    }

    private function assertReturUnitAccess(ReturBarang $retur): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! UserScope::mustScopeToUnitKerja($user)) {
            return;
        }

        $pegawai = MasterPegawai::where('user_id', $user->id)->first();
        if (! $pegawai || (int) $pegawai->id_unit_kerja !== (int) $retur->id_unit_kerja) {
            abort(403, 'Anda tidak memiliki akses ke retur unit kerja ini.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     * @return array<string, string>
     */
    private function validateReturDetails(array $details, int $idGudangAsal, int $idGudangTujuan): array
    {
        $errors = [];
        $gudangTujuan = MasterGudang::find($idGudangTujuan);

        foreach ($details as $index => $detail) {
            $qty = (float) ($detail['qty_retur'] ?? 0);
            if ($qty <= 0) {
                $errors["detail.{$index}.qty_retur"] = 'Qty retur harus lebih dari 0.';
                continue;
            }

            $inventory = DataInventory::with('gudang')->find($detail['id_inventory'] ?? null);
            if (! $inventory || $inventory->status_inventory !== 'AKTIF') {
                $errors["detail.{$index}.id_inventory"] = 'Barang tidak valid atau tidak aktif.';
                continue;
            }

            if ((int) $inventory->id_gudang !== $idGudangAsal) {
                $errors["detail.{$index}.id_inventory"] = 'Barang harus berasal dari gudang asal yang dipilih.';
                continue;
            }

            if ($qty > (float) $inventory->qty_input) {
                $errors["detail.{$index}.qty_retur"] = 'Qty retur melebihi stok tersedia ('.number_format((float) $inventory->qty_input, 2, ',', '.').').';
            }

            $kategoriPusat = match ((string) $inventory->jenis_inventory) {
                'FARMASI' => 'FARMASI',
                'ASET' => 'ASET',
                default => 'PERSEDIAAN',
            };

            if ($gudangTujuan && (string) $gudangTujuan->kategori_gudang !== $kategoriPusat) {
                $errors['id_gudang_tujuan'] = 'Gudang tujuan harus sesuai kategori barang ('.$kategoriPusat.').';
            }
        }

        return $errors;
    }
}



