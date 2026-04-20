<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReturBarang;
use App\Models\DetailReturBarang;
use App\Models\PenerimaanBarang;
use App\Models\TransaksiDistribusi;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\MasterGudang;
use App\Models\MasterSatuan;
use App\Models\DataInventory;
use App\Models\DataStock;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturBarangController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ReturBarang::with(['penerimaan', 'distribusi', 'unitKerja', 'gudangAsal', 'gudangTujuan', 'pegawaiPengirim']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_retur', 'like', "%{$search}%")
                  ->orWhereHas('penerimaan', function($q) use ($search) {
                      $q->where('no_penerimaan', 'like', "%{$search}%");
                  })
                  ->orWhereHas('distribusi', function($q) use ($search) {
                      $q->where('no_sbbk', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $returs = $query->latest('tanggal_retur')->paginate($perPage)->appends($request->query());

        return view('transaction.retur-barang.index', compact('returs', 'unitKerjas'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Filter penerimaan yang sudah diterima dan belum diretur semua
        $penerimaanQuery = PenerimaanBarang::where('status_penerimaan', 'DITERIMA')
            ->with(['distribusi', 'unitKerja', 'detailPenerimaan.inventory.dataBarang']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $penerimaanQuery->where('id_unit_kerja', $pegawai->id_unit_kerja);
                
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                
                // Get gudang unit kerja user
                $gudangUnit = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->first();
                $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')->first();
                
                $gudangs = collect([$gudangUnit, $gudangPusat])->filter();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $penerimaanQuery->whereRaw('1 = 0');
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $gudangs = MasterGudang::all();
            $pegawais = MasterPegawai::all();
        }

        $penerimaans = $penerimaanQuery->get();
        $satuans = MasterSatuan::all();

        // Jika ada penerimaan_id di request, load detail penerimaan
        $selectedPenerimaan = null;
        if ($request->filled('penerimaan_id')) {
            $selectedPenerimaan = PenerimaanBarang::with([
                'detailPenerimaan.inventory.dataBarang',
                'detailPenerimaan.satuan',
                'distribusi.gudangTujuan',
                'unitKerja'
            ])->find($request->penerimaan_id);
        }

        return view('transaction.retur-barang.create', compact('penerimaans', 'unitKerjas', 'gudangs', 'pegawais', 'satuans', 'selectedPenerimaan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_penerimaan' => 'nullable|exists:penerimaan_barang,id_penerimaan',
            'id_distribusi' => 'nullable|exists:transaksi_distribusi,id_distribusi',
            'tanggal_retur' => 'required|date',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pengirim' => 'required|exists:master_pegawai,id',
            'status_retur' => 'required|in:DRAFT,DIAJUKAN',
            'alasan_retur' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_retur' => 'required|numeric|min:0',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_retur_item' => 'nullable|string',
            'detail.*.keterangan' => 'nullable|string',
        ]);

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
            $retur = ReturBarang::create([
                'no_retur' => $noRetur,
                'id_penerimaan' => $validated['id_penerimaan'] ?? null,
                'id_distribusi' => $validated['id_distribusi'] ?? null,
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'],
                'tanggal_retur' => $validated['tanggal_retur'],
                'status_retur' => $validated['status_retur'],
                'alasan_retur' => $validated['alasan_retur'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Create detail retur
            foreach ($validated['detail'] as $detail) {
                DetailReturBarang::create([
                    'id_retur' => $retur->id_retur,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_retur' => $detail['qty_retur'],
                    'id_satuan' => $detail['id_satuan'],
                    'alasan_retur_item' => $detail['alasan_retur_item'] ?? null,
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('transaction.retur-barang.index')
                ->with('success', 'Retur barang berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating retur barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $retur = ReturBarang::with([
            'penerimaan.distribusi',
            'distribusi.gudangAsal',
            'distribusi.gudangTujuan',
            'unitKerja',
            'gudangAsal',
            'gudangTujuan',
            'pegawaiPengirim',
            'detailRetur.inventory.dataBarang',
            'detailRetur.satuan'
        ])->findOrFail($id);

        return view('transaction.retur-barang.show', compact('retur'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $retur = ReturBarang::with('detailRetur')->findOrFail($id);
        
        // Hanya bisa edit jika status DRAFT atau DIAJUKAN
        if (!in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN'])) {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Retur yang sudah DITERIMA atau DITOLAK tidak dapat diedit.');
        }

        $penerimaanQuery = PenerimaanBarang::where('status_penerimaan', 'DITERIMA');
        
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $penerimaanQuery->where('id_unit_kerja', $pegawai->id_unit_kerja);
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                
                $gudangUnit = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->first();
                $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')->first();
                $gudangs = collect([$gudangUnit, $gudangPusat])->filter();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $penerimaanQuery->whereRaw('1 = 0');
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::all();
            $gudangs = MasterGudang::all();
            $pegawais = MasterPegawai::all();
        }

        $penerimaans = $penerimaanQuery->get();
        $satuans = MasterSatuan::all();

        return view('transaction.retur-barang.edit', compact('retur', 'penerimaans', 'unitKerjas', 'gudangs', 'pegawais', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $retur = ReturBarang::findOrFail($id);

        // Hanya bisa update jika status DRAFT atau DIAJUKAN
        if (!in_array($retur->status_retur, ['DRAFT', 'DIAJUKAN'])) {
            return redirect()->route('transaction.retur-barang.show', $retur->id_retur)
                ->with('error', 'Retur yang sudah DITERIMA atau DITOLAK tidak dapat diupdate.');
        }

        $validated = $request->validate([
            'id_penerimaan' => 'nullable|exists:penerimaan_barang,id_penerimaan',
            'id_distribusi' => 'nullable|exists:transaksi_distribusi,id_distribusi',
            'tanggal_retur' => 'required|date',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pengirim' => 'required|exists:master_pegawai,id',
            'status_retur' => 'required|in:DRAFT,DIAJUKAN',
            'alasan_retur' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_retur' => 'required|numeric|min:0',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_retur_item' => 'nullable|string',
            'detail.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update retur
            $retur->update([
                'id_penerimaan' => $validated['id_penerimaan'] ?? null,
                'id_distribusi' => $validated['id_distribusi'] ?? null,
                'tanggal_retur' => $validated['tanggal_retur'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang_asal' => $validated['id_gudang_asal'],
                'id_gudang_tujuan' => $validated['id_gudang_tujuan'],
                'id_pegawai_pengirim' => $validated['id_pegawai_pengirim'],
                'status_retur' => $validated['status_retur'],
                'alasan_retur' => $validated['alasan_retur'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
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
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('transaction.retur-barang.index')
                ->with('success', 'Retur barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating retur barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $retur = ReturBarang::findOrFail($id);

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
            \Log::error('Error deleting retur barang: ' . $e->getMessage());
            return redirect()->route('transaction.retur-barang.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function getPenerimaanDetail($id)
    {
        $penerimaan = PenerimaanBarang::with([
            'detailPenerimaan.inventory.dataBarang',
            'detailPenerimaan.satuan',
            'distribusi.gudangTujuan',
            'unitKerja'
        ])->findOrFail($id);

        $details = $penerimaan->detailPenerimaan->map(function($detail) {
            return [
                'id_inventory' => $detail->id_inventory,
                'nama_barang' => $detail->inventory->dataBarang->nama_barang ?? '-',
                'qty_diterima' => $detail->qty_diterima,
                'id_satuan' => $detail->id_satuan,
                'nama_satuan' => $detail->satuan->nama_satuan ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'penerimaan' => [
                'id_penerimaan' => $penerimaan->id_penerimaan,
                'no_penerimaan' => $penerimaan->no_penerimaan,
                'id_distribusi' => $penerimaan->id_distribusi,
                'unit_kerja' => $penerimaan->unitKerja->id_unit_kerja ?? null,
                'gudang_tujuan' => $penerimaan->distribusi->gudangTujuan->id_gudang ?? null,
            ],
            'details' => $details,
        ]);
    }

    /**
     * Terima retur barang (untuk admin gudang pusat)
     * Update stock saat retur diterima
     */
    public function terima(Request $request, $id)
    {
        // Hanya admin dan admin_gudang yang bisa terima retur
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
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
                
                if (in_array($inventory->jenis_inventory, ['PERSEDIAAN', 'FARMASI'])) {
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
            \Log::error('Error accepting retur barang: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menerima retur: ' . $e->getMessage());
        }
    }

    /**
     * Tolak retur barang
     */
    public function tolak(Request $request, $id)
    {
        // Hanya admin dan admin_gudang yang bisa tolak retur
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
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
        
        $retur->update([
            'status_retur' => 'DITOLAK',
            'keterangan' => ($retur->keterangan ? $retur->keterangan . "\n\n" : '') . 'Ditolak: ' . ($validated['keterangan'] ?? 'Tidak ada keterangan'),
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
}



