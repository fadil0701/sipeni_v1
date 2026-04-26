<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceReport;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Models\RiwayatPemeliharaan;
use App\Models\JadwalMaintenance;
use App\Models\MasterPegawai;
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
        $permintaans = PermintaanPemeliharaan::whereIn('status_permintaan', ['DISETUJUI', 'DIPROSES'])
            ->whereDoesntHave('serviceReport')
            ->with(['registerAset.inventory.dataBarang'])
            ->get();

        $selectedPermintaan = $request->get('permintaan_id') 
            ? PermintaanPemeliharaan::with(['registerAset.inventory.dataBarang'])->find($request->get('permintaan_id'))
            : null;

        $teknisiPegawais = MasterPegawai::query()
            ->whereHas('jabatan', function ($q) {
                $q->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%teknisi%']);
            })
            ->orderBy('nama_pegawai')
            ->get(['id', 'nama_pegawai', 'id_unit_kerja']);

        return view('maintenance.service-report.create', compact('permintaans', 'selectedPermintaan', 'teknisiPegawais'));
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
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
            'file_laporan_kamera' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil permintaan untuk mendapatkan register aset
            $permintaan = PermintaanPemeliharaan::findOrFail($request->id_permintaan_pemeliharaan);
            if ((int) $permintaan->id_register_aset <= 0) {
                throw new \RuntimeException('Permintaan tidak memiliki referensi register aset yang valid.');
            }
            
            // Generate nomor service report
            $tahun = date('Y');
            $lastReport = ServiceReport::whereYear('created_at', $tahun)
                ->orderBy('id_service_report', 'desc')
                ->first();
            
            $urutan = $lastReport ? (int)substr($lastReport->no_service_report, -4) + 1 : 1;
            $noServiceReport = 'SRV/' . $tahun . '/' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $filePath = null;
            if ($request->hasFile('file_laporan_kamera')) {
                $filePath = $request->file('file_laporan_kamera')->store('service-report', 'public');
            } elseif ($request->hasFile('file_laporan')) {
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
        $teknisiPegawais = MasterPegawai::query()
            ->whereHas('jabatan', function ($q) {
                $q->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%teknisi%']);
            })
            ->orderBy('nama_pegawai')
            ->get(['id', 'nama_pegawai', 'id_unit_kerja']);

        return view('maintenance.service-report.edit', compact('serviceReport', 'permintaans', 'teknisiPegawais'));
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
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
            'file_laporan_kamera' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'keterangan' => 'nullable|string',
        ]);

        $serviceReport = ServiceReport::findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath = $serviceReport->file_laporan;
            if ($request->hasFile('file_laporan') || $request->hasFile('file_laporan_kamera')) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                if ($request->hasFile('file_laporan_kamera')) {
                    $filePath = $request->file('file_laporan_kamera')->store('service-report', 'public');
                } else {
                    $filePath = $request->file('file_laporan')->store('service-report', 'public');
                }
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

                // Sinkronkan kondisi aset sesuai hasil service.
                if (!empty($request->kondisi_setelah_service)) {
                    $serviceReport->registerAset->update([
                        'kondisi_aset' => $request->kondisi_setelah_service,
                    ]);
                }

                RiwayatPemeliharaan::updateOrCreate(
                    ['id_service_report' => $serviceReport->id_service_report],
                    [
                    'id_register_aset' => $serviceReport->id_register_aset,
                    'id_permintaan_pemeliharaan' => $serviceReport->id_permintaan_pemeliharaan,
                    'id_service_report' => $serviceReport->id_service_report,
                    'tanggal_pemeliharaan' => $request->tanggal_selesai ?? $request->tanggal_service,
                    'jenis_pemeliharaan' => $request->jenis_service,
                    'status' => 'SELESAI',
                    'keterangan' => $request->keterangan,
                    ]
                );

                // Geser jadwal aktif untuk jenis maintenance terkait aset ini.
                $this->rollForwardActiveSchedule(
                    (int) $serviceReport->id_register_aset,
                    (string) $request->jenis_service,
                    $request->tanggal_selesai ?? $request->tanggal_service
                );
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

    private function rollForwardActiveSchedule(int $registerAsetId, string $jenis, string $tanggalAcuan): void
    {
        $jadwal = JadwalMaintenance::query()
            ->where('id_register_aset', $registerAsetId)
            ->where('jenis_maintenance', $jenis)
            ->where('status', 'AKTIF')
            ->orderBy('tanggal_selanjutnya')
            ->first();

        if (!$jadwal) {
            return;
        }

        $nextDate = $this->calculateNextDate($tanggalAcuan, $jadwal->periode, $jadwal->interval_hari);

        $jadwal->update([
            'tanggal_terakhir' => $tanggalAcuan,
            'tanggal_selanjutnya' => $nextDate,
        ]);
    }

    private function calculateNextDate(string $tanggalMulai, string $periode, ?int $intervalHari = null): string
    {
        $date = \Carbon\Carbon::parse($tanggalMulai);
        return match ($periode) {
            'HARIAN' => $date->addDay()->format('Y-m-d'),
            'MINGGUAN' => $date->addWeek()->format('Y-m-d'),
            'BULANAN' => $date->addMonth()->format('Y-m-d'),
            '3_BULAN' => $date->addMonths(3)->format('Y-m-d'),
            '6_BULAN' => $date->addMonths(6)->format('Y-m-d'),
            'TAHUNAN' => $date->addYear()->format('Y-m-d'),
            'CUSTOM' => $date->addDays($intervalHari ?? 30)->format('Y-m-d'),
            default => $date->addMonth()->format('Y-m-d'),
        };
    }
}


