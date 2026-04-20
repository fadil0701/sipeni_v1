<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceReport;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Models\RiwayatPemeliharaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceReport::with(['permintaanPemeliharaan', 'registerAset.inventory.dataBarang', 'creator']);

        if ($request->filled('status')) {
            $query->where('status_service', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_service', $request->jenis);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_service_report', 'like', "%{$search}%")
                  ->orWhereHas('registerAset', function($q) use ($search) {
                      $q->where('nomor_register', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $serviceReports = $query->latest('tanggal_service')->paginate($perPage)->appends($request->query());

        return view('maintenance.service-report.index', compact('serviceReports'));
    }

    public function create(Request $request)
    {
        $permintaans = PermintaanPemeliharaan::where('status_permintaan', 'DISETUJUI')
            ->whereDoesntHave('serviceReport')
            ->with(['registerAset.inventory.dataBarang'])
            ->get();

        $selectedPermintaan = $request->get('permintaan_id') 
            ? PermintaanPemeliharaan::with(['registerAset.inventory.dataBarang'])->find($request->get('permintaan_id'))
            : null;

        return view('maintenance.service-report.create', compact('permintaans', 'selectedPermintaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_permintaan_pemeliharaan' => 'required|exists:permintaan_pemeliharaan,id_permintaan_pemeliharaan',
            'tanggal_service' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_service',
            'jenis_service' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'vendor' => 'nullable|string|max:255',
            'teknisi' => 'nullable|string|max:255',
            'deskripsi_kerja' => 'nullable|string',
            'tindakan_yang_dilakukan' => 'nullable|string',
            'sparepart_yang_diganti' => 'nullable|string',
            'biaya_service' => 'nullable|numeric|min:0',
            'biaya_sparepart' => 'nullable|numeric|min:0',
            'kondisi_setelah_service' => 'nullable|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT,TIDAK_BISA_DIPERBAIKI',
            'file_laporan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil permintaan untuk mendapatkan register aset
            $permintaan = PermintaanPemeliharaan::findOrFail($request->id_permintaan_pemeliharaan);
            
            // Generate nomor service report
            $tahun = date('Y');
            $lastReport = ServiceReport::whereYear('created_at', $tahun)
                ->orderBy('id_service_report', 'desc')
                ->first();
            
            $urutan = $lastReport ? (int)substr($lastReport->no_service_report, -4) + 1 : 1;
            $noServiceReport = 'SRV/' . $tahun . '/' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $filePath = null;
            if ($request->hasFile('file_laporan')) {
                $filePath = $request->file('file_laporan')->store('service-report', 'public');
            }

            $totalBiaya = ($request->biaya_service ?? 0) + ($request->biaya_sparepart ?? 0);

            $serviceReport = ServiceReport::create([
                'no_service_report' => $noServiceReport,
                'id_permintaan_pemeliharaan' => $request->id_permintaan_pemeliharaan,
                'id_register_aset' => $permintaan->id_register_aset,
                'tanggal_service' => $request->tanggal_service,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_service' => $request->jenis_service,
                'status_service' => $request->status_service ?? 'MENUNGGU',
                'vendor' => $request->vendor,
                'teknisi' => $request->teknisi,
                'deskripsi_kerja' => $request->deskripsi_kerja,
                'tindakan_yang_dilakukan' => $request->tindakan_yang_dilakukan,
                'sparepart_yang_diganti' => $request->sparepart_yang_diganti,
                'biaya_service' => $request->biaya_service ?? 0,
                'biaya_sparepart' => $request->biaya_sparepart ?? 0,
                'total_biaya' => $totalBiaya,
                'kondisi_setelah_service' => $request->kondisi_setelah_service,
                'file_laporan' => $filePath,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
            ]);

            // Update status permintaan menjadi DIPROSES
            $permintaan->update(['status_permintaan' => 'DIPROSES']);

            DB::commit();
            return redirect()->route('maintenance.service-report.index')
                ->with('success', 'Service report berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat service report: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $serviceReport = ServiceReport::with([
            'permintaanPemeliharaan.registerAset',
            'registerAset.inventory.dataBarang',
            'creator'
        ])->findOrFail($id);

        return view('maintenance.service-report.show', compact('serviceReport'));
    }

    public function edit($id)
    {
        $serviceReport = ServiceReport::findOrFail($id);
        $permintaans = PermintaanPemeliharaan::where('status_permintaan', 'DISETUJUI')
            ->with(['registerAset.inventory.dataBarang'])
            ->get();

        return view('maintenance.service-report.edit', compact('serviceReport', 'permintaans'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_service' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_service',
            'jenis_service' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'status_service' => 'required|in:MENUNGGU,DIPROSES,SELESAI,DITOLAK,DIBATALKAN',
            'vendor' => 'nullable|string|max:255',
            'teknisi' => 'nullable|string|max:255',
            'deskripsi_kerja' => 'nullable|string',
            'tindakan_yang_dilakukan' => 'nullable|string',
            'sparepart_yang_diganti' => 'nullable|string',
            'biaya_service' => 'nullable|numeric|min:0',
            'biaya_sparepart' => 'nullable|numeric|min:0',
            'kondisi_setelah_service' => 'nullable|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT,TIDAK_BISA_DIPERBAIKI',
            'file_laporan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $serviceReport = ServiceReport::findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath = $serviceReport->file_laporan;
            if ($request->hasFile('file_laporan')) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('file_laporan')->store('service-report', 'public');
            }

            $totalBiaya = ($request->biaya_service ?? 0) + ($request->biaya_sparepart ?? 0);

            $serviceReport->update([
                'tanggal_service' => $request->tanggal_service,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_service' => $request->jenis_service,
                'status_service' => $request->status_service,
                'vendor' => $request->vendor,
                'teknisi' => $request->teknisi,
                'deskripsi_kerja' => $request->deskripsi_kerja,
                'tindakan_yang_dilakukan' => $request->tindakan_yang_dilakukan,
                'sparepart_yang_diganti' => $request->sparepart_yang_diganti,
                'biaya_service' => $request->biaya_service ?? 0,
                'biaya_sparepart' => $request->biaya_sparepart ?? 0,
                'total_biaya' => $totalBiaya,
                'kondisi_setelah_service' => $request->kondisi_setelah_service,
                'file_laporan' => $filePath,
                'keterangan' => $request->keterangan,
            ]);

            // Jika status SELESAI, update permintaan dan buat riwayat
            if ($request->status_service === 'SELESAI') {
                $serviceReport->permintaanPemeliharaan->update(['status_permintaan' => 'SELESAI']);
                
                // Buat riwayat pemeliharaan
                RiwayatPemeliharaan::create([
                    'id_register_aset' => $serviceReport->id_register_aset,
                    'id_permintaan_pemeliharaan' => $serviceReport->id_permintaan_pemeliharaan,
                    'id_service_report' => $serviceReport->id_service_report,
                    'tanggal_pemeliharaan' => $request->tanggal_selesai ?? $request->tanggal_service,
                    'jenis_pemeliharaan' => $request->jenis_service,
                    'status' => 'SELESAI',
                    'keterangan' => $request->keterangan,
                ]);
            }

            DB::commit();
            return redirect()->route('maintenance.service-report.index')
                ->with('success', 'Service report berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui service report: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $serviceReport = ServiceReport::findOrFail($id);
        
        if ($serviceReport->file_laporan && Storage::disk('public')->exists($serviceReport->file_laporan)) {
            Storage::disk('public')->delete($serviceReport->file_laporan);
        }

        $serviceReport->delete();

        return redirect()->route('maintenance.service-report.index')
            ->with('success', 'Service report berhasil dihapus.');
    }
}


