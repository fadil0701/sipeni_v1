<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockAdjustment;
use App\Models\DataStock;
use App\Models\MasterGudang;
use App\Models\MasterDataBarang;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query stock adjustment dengan relationships
        $query = StockAdjustment::with([
            'stock',
            'dataBarang',
            'gudang.unitKerja',
            'petugas',
            'approver'
        ]);
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->whereHas('gudang', function($q) use ($pegawai) {
                    $q->where('id_unit_kerja', $pegawai->id_unit_kerja);
                });
            } else {
                $query->whereRaw('1 = 0'); // Tidak ada data jika user tidak punya unit kerja
            }
        }
        
        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan gudang jika ada
        if ($request->filled('id_gudang')) {
            $query->where('id_gudang', $request->id_gudang);
        }
        
        // Filter berdasarkan barang jika ada
        if ($request->filled('id_data_barang')) {
            $query->where('id_data_barang', $request->id_data_barang);
        }
        
        // Filter berdasarkan tanggal jika ada
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_adjustment', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_adjustment', '<=', $request->tanggal_sampai);
        }
        
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 15);
        $adjustments = $query->latest('tanggal_adjustment')->paginate($perPage)->appends($request->query());
        
        // Data untuk filter
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangs = MasterGudang::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $gudangs = collect([]);
            }
        } else {
            $gudangs = MasterGudang::orderBy('nama_gudang')->get();
        }
        
        // Daftar barang untuk filter (barang yang punya stock di gudang Persediaan/Farmasi)
        $barangs = MasterDataBarang::whereHas('dataStock', function($q) {
            $q->whereHas('gudang', function($g) {
                $g->whereIn('kategori_gudang', ['PERSEDIAAN', 'FARMASI']);
            });
        })->orderBy('nama_barang')->get(['id_data_barang', 'kode_data_barang', 'nama_barang']);
        
        return view('inventory.stock-adjustment.index', compact('adjustments', 'gudangs', 'barangs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Hanya admin dan admin_gudang yang bisa create
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Ambil data stock yang tersedia (hanya gudang Persediaan & Farmasi)
        $user = Auth::user();
        $query = DataStock::with(['dataBarang', 'gudang', 'satuan'])
            ->whereHas('gudang', function($q) {
                $q->whereIn('kategori_gudang', ['PERSEDIAAN', 'FARMASI']);
            });
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->whereHas('gudang', function($q) use ($pegawai) {
                    $q->where('id_unit_kerja', $pegawai->id_unit_kerja);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $stocks = $query->orderBy('id_gudang')->orderBy('id_data_barang')->get();
        
        return view('inventory.stock-adjustment.create', compact('stocks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Hanya admin dan admin_gudang yang bisa store
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Validasi input
        $validated = $request->validate([
            'id_stock' => 'required|exists:data_stock,id_stock',
            'tanggal_adjustment' => 'required|date',
            'qty_sesudah' => 'required|numeric|min:0',
            'jenis_adjustment' => 'required|in:PENAMBAHAN,PENGURANGAN,KOREKSI,OPNAME',
            'alasan' => 'nullable|string|max:500',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'required|in:DRAFT,DIAJUKAN',
        ]);
        
        // Ambil stock yang akan di-adjust
        $stock = DataStock::findOrFail($validated['id_stock']);
        
        // Hitung selisih
        $qtySebelum = $stock->qty_akhir;
        $qtySesudah = $validated['qty_sesudah'];
        $qtySelisih = $qtySesudah - $qtySebelum;
        
        // Buat stock adjustment
        $adjustment = StockAdjustment::create([
            'id_stock' => $validated['id_stock'],
            'id_data_barang' => $stock->id_data_barang,
            'id_gudang' => $stock->id_gudang,
            'tanggal_adjustment' => $validated['tanggal_adjustment'],
            'qty_sebelum' => $qtySebelum,
            'qty_sesudah' => $qtySesudah,
            'qty_selisih' => $qtySelisih,
            'jenis_adjustment' => $validated['jenis_adjustment'],
            'alasan' => $validated['alasan'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'id_petugas' => Auth::id(),
            'status' => $validated['status'],
        ]);
        
        // Jika status DIAJUKAN dan user adalah admin, langsung approve
        if ($validated['status'] == 'DIAJUKAN' && Auth::user()->hasRole('admin')) {
            $this->approve($adjustment->id_adjustment);
            return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
                ->with('success', 'Stock adjustment berhasil dibuat dan disetujui.');
        }
        
        return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
            ->with('success', 'Stock adjustment berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $adjustment = StockAdjustment::with([
            'stock',
            'dataBarang',
            'gudang.unitKerja',
            'petugas',
            'approver'
        ])->findOrFail($id);
        
        $user = Auth::user();
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                if ($adjustment->gudang->id_unit_kerja != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat stock adjustment dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        return view('inventory.stock-adjustment.show', compact('adjustment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $adjustment = StockAdjustment::with(['stock', 'dataBarang', 'gudang'])->findOrFail($id);
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa edit
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Hanya bisa edit jika status DRAFT
        if ($adjustment->status != 'DRAFT') {
            return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
                ->with('error', 'Stock adjustment yang sudah diajukan tidak dapat diedit.');
        }
        
        return view('inventory.stock-adjustment.edit', compact('adjustment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $adjustment = StockAdjustment::findOrFail($id);
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa update
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Hanya bisa update jika status DRAFT
        if ($adjustment->status != 'DRAFT') {
            return back()->withErrors(['status' => 'Stock adjustment yang sudah diajukan tidak dapat diupdate.'])->withInput();
        }
        
        // Validasi input
        $validated = $request->validate([
            'tanggal_adjustment' => 'required|date',
            'qty_sesudah' => 'required|numeric|min:0',
            'jenis_adjustment' => 'required|in:PENAMBAHAN,PENGURANGAN,KOREKSI,OPNAME',
            'alasan' => 'nullable|string|max:500',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'required|in:DRAFT,DIAJUKAN',
        ]);
        
        // Ambil stock yang akan di-adjust
        $stock = $adjustment->stock;
        
        // Hitung selisih baru
        $qtySebelum = $stock->qty_akhir;
        $qtySesudah = $validated['qty_sesudah'];
        $qtySelisih = $qtySesudah - $qtySebelum;
        
        // Update stock adjustment
        $adjustment->update([
            'tanggal_adjustment' => $validated['tanggal_adjustment'],
            'qty_sebelum' => $qtySebelum,
            'qty_sesudah' => $qtySesudah,
            'qty_selisih' => $qtySelisih,
            'jenis_adjustment' => $validated['jenis_adjustment'],
            'alasan' => $validated['alasan'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => $validated['status'],
        ]);
        
        // Jika status DIAJUKAN dan user adalah admin, langsung approve
        if ($validated['status'] == 'DIAJUKAN' && Auth::user()->hasRole('admin')) {
            $this->approve($adjustment->id_adjustment);
            return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
                ->with('success', 'Stock adjustment berhasil diperbarui dan disetujui.');
        }
        
        return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
            ->with('success', 'Stock adjustment berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Hanya admin yang bisa delete
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        
        $adjustment = StockAdjustment::findOrFail($id);
        
        // Hanya bisa delete jika status DRAFT
        if ($adjustment->status != 'DRAFT') {
            return back()->with('error', 'Stock adjustment yang sudah diajukan tidak dapat dihapus.');
        }
        
        $adjustment->delete();
        
        return redirect()->route('inventory.stock-adjustment.index')
            ->with('success', 'Stock adjustment berhasil dihapus.');
    }

    /**
     * Approve stock adjustment
     */
    public function approve(string $id)
    {
        // Hanya admin dan kepala_pusat yang bisa approve
        if (!Auth::user()->hasAnyRole(['admin', 'kepala_pusat'])) {
            abort(403, 'Unauthorized');
        }
        
        $adjustment = StockAdjustment::findOrFail($id);
        
        // Hanya bisa approve jika status DIAJUKAN
        if ($adjustment->status != 'DIAJUKAN') {
            return back()->with('error', 'Hanya stock adjustment yang sudah diajukan yang dapat disetujui.');
        }
        
        DB::beginTransaction();
        try {
            // Update status adjustment
            $adjustment->update([
                'status' => 'DISETUJUI',
                'id_approver' => Auth::id(),
                'tanggal_approval' => now(),
            ]);
            
            // Update stock
            $stock = $adjustment->stock;
            $stock->qty_akhir = $adjustment->qty_sesudah;
            
            // Update qty_masuk atau qty_keluar berdasarkan jenis adjustment
            if ($adjustment->qty_selisih > 0) {
                // Penambahan stock
                $stock->qty_masuk += abs($adjustment->qty_selisih);
            } else {
                // Pengurangan stock
                $stock->qty_keluar += abs($adjustment->qty_selisih);
            }
            
            $stock->last_updated = now();
            $stock->save();
            
            DB::commit();
            
            return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
                ->with('success', 'Stock adjustment berhasil disetujui dan stock telah diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyetujui stock adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Reject stock adjustment
     */
    public function reject(Request $request, string $id)
    {
        // Hanya admin dan kepala_pusat yang bisa reject
        if (!Auth::user()->hasAnyRole(['admin', 'kepala_pusat'])) {
            abort(403, 'Unauthorized');
        }
        
        $adjustment = StockAdjustment::findOrFail($id);
        
        // Hanya bisa reject jika status DIAJUKAN
        if ($adjustment->status != 'DIAJUKAN') {
            return back()->with('error', 'Hanya stock adjustment yang sudah diajukan yang dapat ditolak.');
        }
        
        $validated = $request->validate([
            'catatan_approval' => 'required|string|max:1000',
        ]);
        
        $adjustment->update([
            'status' => 'DITOLAK',
            'id_approver' => Auth::id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $validated['catatan_approval'],
        ]);
        
        return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
            ->with('success', 'Stock adjustment telah ditolak.');
    }

    /**
     * Ajukan stock adjustment untuk approval
     */
    public function ajukan(string $id)
    {
        // Hanya admin dan admin_gudang yang bisa ajukan
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        $adjustment = StockAdjustment::findOrFail($id);
        
        // Hanya bisa ajukan jika status DRAFT
        if ($adjustment->status != 'DRAFT') {
            return back()->with('error', 'Stock adjustment ini sudah diajukan sebelumnya.');
        }
        
        $adjustment->update([
            'status' => 'DIAJUKAN',
        ]);
        
        return redirect()->route('inventory.stock-adjustment.show', $adjustment->id_adjustment)
            ->with('success', 'Stock adjustment berhasil diajukan untuk persetujuan.');
    }
}
