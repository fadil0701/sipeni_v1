<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KalibrasiAset;
use App\Models\RegisterAset;
use App\Models\PermintaanPemeliharaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KalibrasiAsetController extends Controller
{
    public function index(Request $request)
    {
        $query = KalibrasiAset::with(['registerAset.inventory.dataBarang', 'permintaanPemeliharaan', 'creator']);

        if ($request->filled('status')) {
            $query->where('status_kalibrasi', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_kalibrasi', 'like', "%{$search}%")
                  ->orWhere('no_sertifikat', 'like', "%{$search}%")
                  ->orWhereHas('registerAset', function($q) use ($search) {
                      $q->where('nomor_register', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $kalibrasis = $query->latest('tanggal_kalibrasi')->paginate($perPage)->appends($request->query());

        return view('maintenance.kalibrasi-aset.index', compact('kalibrasis'));
    }

    public function create(Request $request)
    {
        $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
            ->with(['inventory.dataBarang'])
            ->get();
        
        $permintaans = PermintaanPemeliharaan::where('jenis_pemeliharaan', 'KALIBRASI')
            ->where('status_permintaan', 'DISETUJUI')
            ->with(['registerAset'])
            ->get();

        $selectedPermintaan = $request->get('permintaan_id') 
            ? PermintaanPemeliharaan::with(['registerAset'])->find($request->get('permintaan_id'))
            : null;

        return view('maintenance.kalibrasi-aset.create', compact('registerAsets', 'permintaans', 'selectedPermintaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'id_permintaan_pemeliharaan' => 'nullable|exists:permintaan_pemeliharaan,id_permintaan_pemeliharaan',
            'tanggal_kalibrasi' => 'required|date',
            'tanggal_berlaku' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after:tanggal_berlaku',
            'lembaga_kalibrasi' => 'nullable|string|max:255',
            'no_sertifikat' => 'nullable|string|max:100',
            'biaya_kalibrasi' => 'nullable|numeric|min:0',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate nomor kalibrasi
            $tahun = date('Y');
            $lastKalibrasi = KalibrasiAset::whereYear('created_at', $tahun)
                ->orderBy('id_kalibrasi', 'desc')
                ->first();
            
            $urutan = $lastKalibrasi ? (int)substr($lastKalibrasi->no_kalibrasi, -4) + 1 : 1;
            $noKalibrasi = 'KAL/' . $tahun . '/' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $filePath = null;
            if ($request->hasFile('file_sertifikat')) {
                $filePath = $request->file('file_sertifikat')->store('kalibrasi', 'public');
            }

            $kalibrasi = KalibrasiAset::create([
                'no_kalibrasi' => $noKalibrasi,
                'id_register_aset' => $request->id_register_aset,
                'id_permintaan_pemeliharaan' => $request->id_permintaan_pemeliharaan,
                'tanggal_kalibrasi' => $request->tanggal_kalibrasi,
                'tanggal_berlaku' => $request->tanggal_berlaku,
                'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
                'lembaga_kalibrasi' => $request->lembaga_kalibrasi,
                'no_sertifikat' => $request->no_sertifikat,
                'status_kalibrasi' => 'VALID',
                'biaya_kalibrasi' => $request->biaya_kalibrasi ?? 0,
                'file_sertifikat' => $filePath,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('maintenance.kalibrasi-aset.index')
                ->with('success', 'Data kalibrasi berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat data kalibrasi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $kalibrasi = KalibrasiAset::with([
            'registerAset.inventory.dataBarang',
            'permintaanPemeliharaan',
            'creator'
        ])->findOrFail($id);

        return view('maintenance.kalibrasi-aset.show', compact('kalibrasi'));
    }

    public function edit($id)
    {
        $kalibrasi = KalibrasiAset::findOrFail($id);
        $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
            ->with(['inventory.dataBarang'])
            ->get();
        
        $permintaans = PermintaanPemeliharaan::where('jenis_pemeliharaan', 'KALIBRASI')
            ->where('status_permintaan', 'DISETUJUI')
            ->with(['registerAset'])
            ->get();

        return view('maintenance.kalibrasi-aset.edit', compact('kalibrasi', 'registerAsets', 'permintaans'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'id_permintaan_pemeliharaan' => 'nullable|exists:permintaan_pemeliharaan,id_permintaan_pemeliharaan',
            'tanggal_kalibrasi' => 'required|date',
            'tanggal_berlaku' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after:tanggal_berlaku',
            'lembaga_kalibrasi' => 'nullable|string|max:255',
            'no_sertifikat' => 'nullable|string|max:100',
            'status_kalibrasi' => 'required|in:VALID,KADALUARSA,MENUNGGU,DITOLAK',
            'biaya_kalibrasi' => 'nullable|numeric|min:0',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $kalibrasi = KalibrasiAset::findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath = $kalibrasi->file_sertifikat;
            if ($request->hasFile('file_sertifikat')) {
                // Hapus file lama jika ada
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('file_sertifikat')->store('kalibrasi', 'public');
            }

            $kalibrasi->update([
                'id_register_aset' => $request->id_register_aset,
                'id_permintaan_pemeliharaan' => $request->id_permintaan_pemeliharaan,
                'tanggal_kalibrasi' => $request->tanggal_kalibrasi,
                'tanggal_berlaku' => $request->tanggal_berlaku,
                'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
                'lembaga_kalibrasi' => $request->lembaga_kalibrasi,
                'no_sertifikat' => $request->no_sertifikat,
                'status_kalibrasi' => $request->status_kalibrasi,
                'biaya_kalibrasi' => $request->biaya_kalibrasi ?? 0,
                'file_sertifikat' => $filePath,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
            return redirect()->route('maintenance.kalibrasi-aset.index')
                ->with('success', 'Data kalibrasi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data kalibrasi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $kalibrasi = KalibrasiAset::findOrFail($id);
        
        // Hapus file jika ada
        if ($kalibrasi->file_sertifikat && Storage::disk('public')->exists($kalibrasi->file_sertifikat)) {
            Storage::disk('public')->delete($kalibrasi->file_sertifikat);
        }

        $kalibrasi->delete();

        return redirect()->route('maintenance.kalibrasi-aset.index')
            ->with('success', 'Data kalibrasi berhasil dihapus.');
    }
}


