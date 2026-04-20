<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemakaianBarang;
use App\Models\DetailPemakaianBarang;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use App\Models\MasterGudang;
use App\Models\MasterSatuan;
use App\Models\DataInventory;
use App\Models\DataStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemakaianBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query pemakaian barang dengan relationships
        $query = PemakaianBarang::with([
            'unitKerja',
            'gudang.unitKerja',
            'pegawaiPemakai',
            'approver',
            'detailPemakaian'
        ]);
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->where('id_unit_kerja', $pegawai->id_unit_kerja);
            } else {
                $query->whereRaw('1 = 0'); // Tidak ada data jika user tidak punya unit kerja
            }
        }
        
        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status_pemakaian', $request->status);
        }
        
        // Filter berdasarkan gudang jika ada
        if ($request->filled('id_gudang')) {
            $query->where('id_gudang', $request->id_gudang);
        }
        
        // Filter berdasarkan tanggal jika ada
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }
        
        // Filter berdasarkan unit kerja (untuk admin)
        if ($request->filled('id_unit_kerja') && ($user->hasRole('admin') || $user->hasRole('admin_gudang'))) {
            $query->where('id_unit_kerja', $request->id_unit_kerja);
        }
        
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 15);
        $pemakaians = $query->latest('tanggal_pemakaian')->paginate($perPage)->appends($request->query());
        
        // Data untuk filter
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangs = MasterGudang::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $gudangs = collect([]);
            }
            $unitKerjas = collect([]);
        } else {
            $gudangs = MasterGudang::orderBy('nama_gudang')->get();
            $unitKerjas = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
        }
        
        return view('transaction.pemakaian-barang.index', compact('pemakaians', 'gudangs', 'unitKerjas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Ambil inventory yang tersedia di gudang unit (untuk pemakaian), hanya yang masih ada stok
        $query = DataInventory::with(['dataBarang', 'gudang', 'satuan'])
            ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
            ->where('status_inventory', 'AKTIF')
            ->where('qty_input', '>', 0);
        
        // Filter berdasarkan unit kerja untuk pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->whereHas('gudang', function($q) use ($pegawai) {
                    $q->where('id_unit_kerja', $pegawai->id_unit_kerja)
                      ->where('jenis_gudang', 'UNIT');
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            // Admin dan admin gudang bisa pilih dari semua gudang unit
            $query->whereHas('gudang', function($q) {
                $q->where('jenis_gudang', 'UNIT');
            });
        }
        
        $inventories = $query->orderBy('id_gudang')->orderBy('id_data_barang')->get();
        
        // Data untuk form
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $gudangs = MasterGudang::where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->where('jenis_gudang', 'UNIT')
                    ->get();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
            $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')->orderBy('nama_gudang')->get();
            $pegawais = MasterPegawai::orderBy('nama_pegawai')->get();
        }
        
        $satuans = MasterSatuan::all();
        
        return view('transaction.pemakaian-barang.create', compact('inventories', 'unitKerjas', 'gudangs', 'pegawais', 'satuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pemakai' => 'required|exists:master_pegawai,id',
            'tanggal_pemakaian' => 'required|date',
            'status_pemakaian' => 'required|in:DRAFT,DIAJUKAN',
            'keterangan' => 'nullable|string|max:1000',
            'alasan_pemakaian' => 'nullable|string|max:500',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_pemakaian' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_pemakaian_item' => 'nullable|string|max:500',
            'detail.*.keterangan' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Generate nomor pemakaian
            $tahun = Carbon::parse($validated['tanggal_pemakaian'])->format('Y');
            $lastPemakaian = PemakaianBarang::whereYear('tanggal_pemakaian', $tahun)
                ->orderBy('no_pemakaian', 'desc')
                ->first();
            
            $urut = 1;
            if ($lastPemakaian) {
                $parts = explode('/', $lastPemakaian->no_pemakaian);
                $urut = (int)end($parts) + 1;
            }
            
            $noPemakaian = sprintf('PAKAI/%s/%04d', $tahun, $urut);
            
            // Create pemakaian
            $pemakaian = PemakaianBarang::create([
                'no_pemakaian' => $noPemakaian,
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang' => $validated['id_gudang'],
                'id_pegawai_pemakai' => $validated['id_pegawai_pemakai'],
                'tanggal_pemakaian' => $validated['tanggal_pemakaian'],
                'status_pemakaian' => $validated['status_pemakaian'],
                'keterangan' => $validated['keterangan'] ?? null,
                'alasan_pemakaian' => $validated['alasan_pemakaian'] ?? null,
            ]);
            
            // Create detail pemakaian
            foreach ($validated['detail'] as $detail) {
                DetailPemakaianBarang::create([
                    'id_pemakaian' => $pemakaian->id_pemakaian,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_pemakaian' => $detail['qty_pemakaian'],
                    'id_satuan' => $detail['id_satuan'],
                    'alasan_pemakaian_item' => $detail['alasan_pemakaian_item'] ?? null,
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }
            
            // Jika status DIAJUKAN dan user adalah admin, coba approve; jika gagal (validasi stok), tampilkan error
            if ($validated['status_pemakaian'] == 'DIAJUKAN' && Auth::user()->hasRole('admin')) {
                $response = $this->approve($pemakaian->id_pemakaian);
                if (session()->has('error')) {
                    DB::rollBack();
                    return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                        ->with('error', session('error'));
                }
                DB::commit();
                return $response;
            }
            
            DB::commit();
            
            return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                ->with('success', 'Pemakaian barang berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating pemakaian barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pemakaian = PemakaianBarang::with([
            'unitKerja',
            'gudang.unitKerja',
            'pegawaiPemakai',
            'approver',
            'detailPemakaian.inventory.dataBarang',
            'detailPemakaian.satuan'
        ])->findOrFail($id);
        
        $user = Auth::user();
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                if ($pemakaian->id_unit_kerja != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat pemakaian dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        return view('transaction.pemakaian-barang.show', compact('pemakaian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pemakaian = PemakaianBarang::with(['detailPemakaian', 'unitKerja', 'gudang'])->findOrFail($id);
        $user = Auth::user();
        
        // Hanya bisa edit jika status DRAFT
        if ($pemakaian->status_pemakaian != 'DRAFT') {
            return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                ->with('error', 'Pemakaian yang sudah diajukan tidak dapat diedit.');
        }
        
        // Data untuk form
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                $gudangs = MasterGudang::where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->where('jenis_gudang', 'UNIT')
                    ->get();
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $unitKerjas = collect([]);
                $gudangs = collect([]);
                $pegawais = collect([]);
            }
        } else {
            $unitKerjas = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
            $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')->orderBy('nama_gudang')->get();
            $pegawais = MasterPegawai::orderBy('nama_pegawai')->get();
        }
        
        // Ambil inventory yang tersedia
        $inventories = DataInventory::with(['dataBarang', 'gudang', 'satuan'])
            ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
            ->where('status_inventory', 'AKTIF')
            ->whereHas('gudang', function($q) use ($pemakaian) {
                $q->where('id_gudang', $pemakaian->id_gudang);
            })
            ->orderBy('id_data_barang')
            ->get();
        
        $satuans = MasterSatuan::all();
        
        return view('transaction.pemakaian-barang.edit', compact('pemakaian', 'inventories', 'unitKerjas', 'gudangs', 'pegawais', 'satuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pemakaian = PemakaianBarang::findOrFail($id);
        
        // Hanya bisa update jika status DRAFT
        if ($pemakaian->status_pemakaian != 'DRAFT') {
            return back()->withErrors(['status' => 'Pemakaian yang sudah diajukan tidak dapat diupdate.'])->withInput();
        }
        
        $validated = $request->validate([
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang' => 'required|exists:master_gudang,id_gudang',
            'id_pegawai_pemakai' => 'required|exists:master_pegawai,id',
            'tanggal_pemakaian' => 'required|date',
            'status_pemakaian' => 'required|in:DRAFT,DIAJUKAN',
            'keterangan' => 'nullable|string|max:1000',
            'alasan_pemakaian' => 'nullable|string|max:500',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_pemakaian' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.alasan_pemakaian_item' => 'nullable|string|max:500',
            'detail.*.keterangan' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            // Update pemakaian
            $pemakaian->update([
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_gudang' => $validated['id_gudang'],
                'id_pegawai_pemakai' => $validated['id_pegawai_pemakai'],
                'tanggal_pemakaian' => $validated['tanggal_pemakaian'],
                'status_pemakaian' => $validated['status_pemakaian'],
                'keterangan' => $validated['keterangan'] ?? null,
                'alasan_pemakaian' => $validated['alasan_pemakaian'] ?? null,
            ]);
            
            // Delete existing details
            $pemakaian->detailPemakaian()->delete();
            
            // Create new details
            foreach ($validated['detail'] as $detail) {
                DetailPemakaianBarang::create([
                    'id_pemakaian' => $pemakaian->id_pemakaian,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_pemakaian' => $detail['qty_pemakaian'],
                    'id_satuan' => $detail['id_satuan'],
                    'alasan_pemakaian_item' => $detail['alasan_pemakaian_item'] ?? null,
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }
            
            // Jika status DIAJUKAN dan user adalah admin, coba approve; jika gagal (validasi stok), tampilkan error
            if ($validated['status_pemakaian'] == 'DIAJUKAN' && Auth::user()->hasRole('admin')) {
                $response = $this->approve($pemakaian->id_pemakaian);
                if (session()->has('error')) {
                    DB::rollBack();
                    return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                        ->with('error', session('error'));
                }
                DB::commit();
                return $response;
            }
            
            DB::commit();
            
            return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                ->with('success', 'Pemakaian barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating pemakaian barang: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pemakaian = PemakaianBarang::findOrFail($id);
        
        // Hanya bisa delete jika status DRAFT
        if ($pemakaian->status_pemakaian != 'DRAFT') {
            return back()->with('error', 'Pemakaian yang sudah diajukan tidak dapat dihapus.');
        }
        
        DB::beginTransaction();
        try {
            $pemakaian->detailPemakaian()->delete();
            $pemakaian->delete();
            
            DB::commit();
            
            return redirect()->route('transaction.pemakaian-barang.index')
                ->with('success', 'Pemakaian barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting pemakaian barang: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Approve pemakaian barang
     */
    public function approve(string $id)
    {
        // Hanya admin dan kepala_unit yang bisa approve (sesuai route middleware)
        if (!Auth::user()->hasAnyRole(['admin', 'kepala_unit'])) {
            abort(403, 'Unauthorized');
        }
        
        $pemakaian = PemakaianBarang::with(['detailPemakaian.inventory.dataBarang', 'gudang'])->findOrFail($id);
        
        // Hanya bisa approve jika status DIAJUKAN
        if ($pemakaian->status_pemakaian != 'DIAJUKAN') {
            return back()->with('error', 'Hanya pemakaian yang sudah diajukan yang dapat disetujui.');
        }
        
        // Validasi stok sebelum approve: per inventory qty_pemakaian <= qty_input, dan aggregate per barang <= DataStock qty_akhir
        foreach ($pemakaian->detailPemakaian as $detail) {
            $inventory = $detail->inventory;
            if (!$inventory) {
                return back()->with('error', 'Data inventory tidak ditemukan untuk salah satu detail.');
            }
            if ($detail->qty_pemakaian > $inventory->qty_input) {
                return back()->with('error', 'Qty pemakaian untuk ' . ($inventory->dataBarang->nama_barang ?? 'barang') . ' melebihi stok tersedia di inventory (' . number_format($inventory->qty_input, 2) . ').');
            }
        }
        $groupedByBarang = [];
        foreach ($pemakaian->detailPemakaian as $detail) {
            $idBarang = $detail->inventory->id_data_barang;
            if (!isset($groupedByBarang[$idBarang])) {
                $groupedByBarang[$idBarang] = 0;
            }
            $groupedByBarang[$idBarang] += $detail->qty_pemakaian;
        }
        foreach ($groupedByBarang as $idDataBarang => $totalQty) {
            $stock = DataStock::where('id_data_barang', $idDataBarang)
                ->where('id_gudang', $pemakaian->id_gudang)
                ->first();
            if (!$stock) {
                return back()->with('error', 'Stok gudang tidak ditemukan untuk salah satu barang.');
            }
            if ($totalQty > $stock->qty_akhir) {
                $namaBarang = $pemakaian->detailPemakaian->first(fn($d) => $d->inventory->id_data_barang == $idDataBarang)?->inventory->dataBarang->nama_barang ?? 'Barang';
                return back()->with('error', 'Total pemakaian ' . $namaBarang . ' (' . number_format($totalQty, 2) . ') melebihi stok gudang (' . number_format($stock->qty_akhir, 2) . ').');
            }
        }
        
        DB::beginTransaction();
        try {
            // Update status pemakaian
            $pemakaian->update([
                'status_pemakaian' => 'DISETUJUI',
                'id_approver' => Auth::id(),
                'tanggal_approval' => now(),
            ]);
            
            // Update stock untuk setiap detail pemakaian
            foreach ($pemakaian->detailPemakaian as $detail) {
                $inventory = $detail->inventory;
                
                // Update DataStock - kurangi qty_keluar dan qty_akhir
                $stock = DataStock::where('id_data_barang', $inventory->id_data_barang)
                    ->where('id_gudang', $pemakaian->id_gudang)
                    ->first();
                
                if ($stock) {
                    $stock->qty_keluar += $detail->qty_pemakaian;
                    $stock->qty_akhir -= $detail->qty_pemakaian;
                    $stock->last_updated = now();
                    $stock->save();
                }
                
                // Update atau kurangi inventory
                if ($detail->qty_pemakaian >= $inventory->qty_input) {
                    // Jika qty pemakaian >= qty_input, hapus atau nonaktifkan inventory
                    $inventory->update([
                        'status_inventory' => 'HABIS',
                        'qty_input' => 0,
                        'total_harga' => 0,
                    ]);
                } else {
                    // Kurangi qty_input inventory
                    $inventory->qty_input -= $detail->qty_pemakaian;
                    $inventory->total_harga = $inventory->harga_satuan * $inventory->qty_input;
                    $inventory->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
                ->with('success', 'Pemakaian barang berhasil disetujui dan stock telah diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving pemakaian barang: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyetujui pemakaian: ' . $e->getMessage());
        }
    }

    /**
     * Reject pemakaian barang
     */
    public function reject(Request $request, string $id)
    {
        // Hanya admin dan kepala_unit yang bisa reject (sesuai route middleware)
        if (!Auth::user()->hasAnyRole(['admin', 'kepala_unit'])) {
            abort(403, 'Unauthorized');
        }
        
        $pemakaian = PemakaianBarang::findOrFail($id);
        
        // Hanya bisa reject jika status DIAJUKAN
        if ($pemakaian->status_pemakaian != 'DIAJUKAN') {
            return back()->with('error', 'Hanya pemakaian yang sudah diajukan yang dapat ditolak.');
        }
        
        $validated = $request->validate([
            'catatan_approval' => 'required|string|max:1000',
        ]);
        
        $pemakaian->update([
            'status_pemakaian' => 'DITOLAK',
            'id_approver' => Auth::id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $validated['catatan_approval'],
        ]);
        
        return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
            ->with('success', 'Pemakaian barang telah ditolak.');
    }

    /**
     * Ajukan pemakaian untuk approval
     */
    public function ajukan(string $id)
    {
        $pemakaian = PemakaianBarang::findOrFail($id);
        
        // Hanya bisa ajukan jika status DRAFT
        if ($pemakaian->status_pemakaian != 'DRAFT') {
            return back()->with('error', 'Pemakaian ini sudah diajukan sebelumnya.');
        }
        
        $pemakaian->update([
            'status_pemakaian' => 'DIAJUKAN',
        ]);
        
        return redirect()->route('transaction.pemakaian-barang.show', $pemakaian->id_pemakaian)
            ->with('success', 'Pemakaian barang berhasil diajukan untuk persetujuan.');
    }
}
