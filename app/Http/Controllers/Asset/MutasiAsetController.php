<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiAset;
use App\Models\RegisterAset;
use App\Models\MasterRuangan;
use App\Models\KartuInventarisRuangan;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MutasiAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query mutasi aset dengan relationships
        $query = MutasiAset::with([
            'registerAset.inventory.dataBarang',
            'registerAset.unitKerja',
            'ruanganAsal.unitKerja',
            'ruanganTujuan.unitKerja'
        ]);
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->where(function($q) use ($pegawai) {
                    $q->whereHas('ruanganAsal', function($q2) use ($pegawai) {
                        $q2->where('id_unit_kerja', $pegawai->id_unit_kerja);
                    })->orWhereHas('ruanganTujuan', function($q2) use ($pegawai) {
                        $q2->where('id_unit_kerja', $pegawai->id_unit_kerja);
                    });
                });
            } else {
                $query->whereRaw('1 = 0'); // Tidak ada data jika user tidak punya unit kerja
            }
        }
        
        // Filter berdasarkan register aset jika ada
        if ($request->filled('id_register_aset')) {
            $query->where('id_register_aset', $request->id_register_aset);
        }
        
        // Filter berdasarkan tanggal jika ada
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_mutasi', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_mutasi', '<=', $request->tanggal_sampai);
        }
        
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 15);
        $mutasiAsets = $query->latest('tanggal_mutasi')->paginate($perPage)->appends($request->query());
        
        return view('asset.mutasi-aset.index', compact('mutasiAsets'));
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
        
        // Ambil register aset yang aktif dan sudah punya KIR (sudah ditempatkan di ruangan)
        $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
            ->whereHas('kartuInventarisRuangan')
            ->with(['inventory.dataBarang', 'unitKerja', 'kartuInventarisRuangan.ruangan'])
            ->orderBy('nomor_register')
            ->get();
        
        // Ambil semua ruangan
        $ruangans = MasterRuangan::with('unitKerja')->orderBy('nama_ruangan')->get();
        
        return view('asset.mutasi-aset.create', compact('registerAsets', 'ruangans'));
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
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'id_ruangan_asal' => 'required|exists:master_ruangan,id_ruangan',
            'id_ruangan_tujuan' => 'required|exists:master_ruangan,id_ruangan|different:id_ruangan_asal',
            'tanggal_mutasi' => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
        ]);
        
        // Cek apakah ruangan asal sesuai dengan KIR yang ada
        $kir = KartuInventarisRuangan::where('id_register_aset', $validated['id_register_aset'])->first();
        if (!$kir) {
            return back()->withErrors(['id_register_aset' => 'Register aset ini belum memiliki KIR. Buat KIR terlebih dahulu.'])->withInput();
        }
        
        if ($kir->id_ruangan != $validated['id_ruangan_asal']) {
            return back()->withErrors(['id_ruangan_asal' => 'Ruangan asal tidak sesuai dengan KIR yang ada.'])->withInput();
        }
        
        DB::beginTransaction();
        try {
            // Buat mutasi aset
            $mutasiAset = MutasiAset::create($validated);
            
            // Update KIR untuk pindah ke ruangan tujuan
            $kir->update([
                'id_ruangan' => $validated['id_ruangan_tujuan'],
                'tanggal_penempatan' => $validated['tanggal_mutasi']
            ]);
            
            // Sinkronkan ruangan ke Register Aset dan inventory_item
            $registerAset = RegisterAset::findOrFail($validated['id_register_aset']);
            $registerAset->update(['id_ruangan' => $validated['id_ruangan_tujuan']]);
            if ($registerAset->inventory) {
                \App\Models\InventoryItem::where('id_inventory', $registerAset->inventory->id_inventory)
                    ->update(['id_ruangan' => $validated['id_ruangan_tujuan']]);
            }
            
            DB::commit();
            
            return redirect()->route('asset.mutasi-aset.show', $mutasiAset->id_mutasi)
                ->with('success', 'Mutasi aset berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan mutasi aset: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mutasiAset = MutasiAset::with([
            'registerAset.inventory.dataBarang',
            'registerAset.unitKerja',
            'ruanganAsal.unitKerja',
            'ruanganTujuan.unitKerja'
        ])->findOrFail($id);
        
        $user = Auth::user();
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $idUkAsal = $mutasiAset->ruanganAsal?->id_unit_kerja;
                $idUkTujuan = $mutasiAset->ruanganTujuan?->id_unit_kerja;
                $isAuthorized = $idUkAsal == $pegawai->id_unit_kerja || $idUkTujuan == $pegawai->id_unit_kerja;
                if (!$isAuthorized) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat mutasi aset dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        return view('asset.mutasi-aset.show', compact('mutasiAset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mutasiAset = MutasiAset::with(['registerAset', 'ruanganAsal', 'ruanganTujuan'])->findOrFail($id);
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa edit
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Ambil semua ruangan
        $ruangans = MasterRuangan::with('unitKerja')->orderBy('nama_ruangan')->get();
        
        return view('asset.mutasi-aset.edit', compact('mutasiAset', 'ruangans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $mutasiAset = MutasiAset::findOrFail($id);
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa update
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Validasi input
        $validated = $request->validate([
            'id_ruangan_asal' => 'required|exists:master_ruangan,id_ruangan',
            'id_ruangan_tujuan' => 'required|exists:master_ruangan,id_ruangan|different:id_ruangan_asal',
            'tanggal_mutasi' => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
        ]);
        
        $oldIdRuanganTujuan = $mutasiAset->id_ruangan_tujuan;
        
        DB::beginTransaction();
        try {
            // Update mutasi aset
            $mutasiAset->update($validated);
            
            // Update KIR, Register Aset, dan inventory_item jika ruangan tujuan berubah
            if ($oldIdRuanganTujuan != $validated['id_ruangan_tujuan']) {
                $kir = KartuInventarisRuangan::where('id_register_aset', $mutasiAset->id_register_aset)->first();
                if ($kir) {
                    $kir->update([
                        'id_ruangan' => $validated['id_ruangan_tujuan'],
                        'tanggal_penempatan' => $validated['tanggal_mutasi']
                    ]);
                }
                
                $registerAset = $mutasiAset->registerAset;
                if ($registerAset) {
                    $registerAset->update(['id_ruangan' => $validated['id_ruangan_tujuan']]);
                    if ($registerAset->inventory) {
                        \App\Models\InventoryItem::where('id_inventory', $registerAset->inventory->id_inventory)
                            ->update(['id_ruangan' => $validated['id_ruangan_tujuan']]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('asset.mutasi-aset.show', $mutasiAset->id_mutasi)
                ->with('success', 'Mutasi aset berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui mutasi aset: ' . $e->getMessage()])->withInput();
        }
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
        
        $mutasiAset = MutasiAset::findOrFail($id);
        
        // Hapus mutasi aset (history tetap ada, tapi tidak bisa dihapus untuk audit trail)
        // Sebenarnya lebih baik tidak bisa dihapus, tapi jika diperlukan bisa di-soft delete
        $mutasiAset->delete();
        
        return redirect()->route('asset.mutasi-aset.index')
            ->with('success', 'Mutasi aset berhasil dihapus.');
    }
}
