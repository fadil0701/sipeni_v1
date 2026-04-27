<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalMaintenance;
use App\Models\MasterPegawai;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JadwalMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalMaintenance::with(['registerAset.inventory.dataBarang', 'registerAset.unitKerja', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_maintenance', $request->jenis);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('registerAset', function($q) use ($search) {
                $q->where('nomor_register', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit_kerja')) {
            $query->whereHas('registerAset', function ($q) use ($request) {
                $q->where('id_unit_kerja', $request->integer('unit_kerja'));
            });
        }

        if ($request->filled('jatuh_tempo')) {
            $today = now()->toDateString();
            $query->whereNotNull('tanggal_selanjutnya');
            if ($request->jatuh_tempo === 'OVERDUE') {
                $query->whereDate('tanggal_selanjutnya', '<', $today);
            } elseif ($request->jatuh_tempo === '7_HARI') {
                $query->whereBetween('tanggal_selanjutnya', [$today, now()->addDays(7)->toDateString()]);
            } elseif ($request->jatuh_tempo === '30_HARI') {
                $query->whereBetween('tanggal_selanjutnya', [$today, now()->addDays(30)->toDateString()]);
            }
        }

        $summary = [
            'overdue' => JadwalMaintenance::query()
                ->where('status', 'AKTIF')
                ->whereNotNull('tanggal_selanjutnya')
                ->whereDate('tanggal_selanjutnya', '<', now()->toDateString())
                ->count(),
            'due_7_days' => JadwalMaintenance::query()
                ->where('status', 'AKTIF')
                ->whereNotNull('tanggal_selanjutnya')
                ->whereBetween('tanggal_selanjutnya', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->count(),
        ];

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $jadwals = $query->latest('tanggal_mulai')->paginate($perPage)->appends($request->query());

        return view('maintenance.jadwal-maintenance.index', compact('jadwals', 'summary'));
    }

    public function create()
    {
        $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
            ->with(['inventory.dataBarang'])
            ->get();
        
        return view('maintenance.jadwal-maintenance.create', compact('registerAsets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'jenis_maintenance' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'periode' => 'required|in:HARIAN,MINGGUAN,BULANAN,3_BULAN,6_BULAN,TAHUNAN,CUSTOM',
            'interval_hari' => 'nullable|integer|min:1|required_if:periode,CUSTOM',
            'tanggal_mulai' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $tanggalSelanjutnya = $this->calculateNextDate($request->tanggal_mulai, $request->periode, $request->interval_hari);

        JadwalMaintenance::create([
            'id_register_aset' => $request->id_register_aset,
            'jenis_maintenance' => $request->jenis_maintenance,
            'periode' => $request->periode,
            'interval_hari' => $request->interval_hari,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selanjutnya' => $tanggalSelanjutnya,
            'status' => 'AKTIF',
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('maintenance.jadwal-maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function show($id)
    {
        $jadwal = JadwalMaintenance::with(['registerAset.inventory.dataBarang', 'creator'])->findOrFail($id);
        return view('maintenance.jadwal-maintenance.show', compact('jadwal'));
    }

    public function edit($id)
    {
        $jadwal = JadwalMaintenance::findOrFail($id);
        $registerAsets = RegisterAset::where('status_aset', 'AKTIF')
            ->with(['inventory.dataBarang'])
            ->get();
        
        return view('maintenance.jadwal-maintenance.edit', compact('jadwal', 'registerAsets'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'jenis_maintenance' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'periode' => 'required|in:HARIAN,MINGGUAN,BULANAN,3_BULAN,6_BULAN,TAHUNAN,CUSTOM',
            'interval_hari' => 'nullable|integer|min:1|required_if:periode,CUSTOM',
            'tanggal_mulai' => 'required|date',
            'status' => 'required|in:AKTIF,NONAKTIF,SELESAI',
            'keterangan' => 'nullable|string',
        ]);

        $jadwal = JadwalMaintenance::findOrFail($id);
        $tanggalSelanjutnya = $this->calculateNextDate($request->tanggal_mulai, $request->periode, $request->interval_hari);

        $jadwal->update([
            'id_register_aset' => $request->id_register_aset,
            'jenis_maintenance' => $request->jenis_maintenance,
            'periode' => $request->periode,
            'interval_hari' => $request->interval_hari,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selanjutnya' => $tanggalSelanjutnya,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('maintenance.jadwal-maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = JadwalMaintenance::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('maintenance.jadwal-maintenance.index')
            ->with('success', 'Jadwal maintenance berhasil dihapus.');
    }

    /**
     * Generate permintaan pemeliharaan rutin dari jadwal aktif.
     * Ini memungkinkan preventive maintenance berjalan tanpa menunggu user membuat permintaan manual.
     */
    public function generatePermintaan($id)
    {
        $jadwal = JadwalMaintenance::with(['registerAset.kartuInventarisRuangan'])->findOrFail($id);

        if ($jadwal->status !== 'AKTIF') {
            return back()->with('error', 'Hanya jadwal aktif yang bisa digenerate menjadi permintaan.');
        }

        if (!$jadwal->registerAset || (string) $jadwal->registerAset->status_aset !== 'AKTIF') {
            return back()->with('error', 'Register aset tidak valid atau tidak aktif.');
        }

        if ($jadwal->registerAset->kartuInventarisRuangan()->count() === 0) {
            return back()->with('error', 'Aset belum ditempatkan di KIR, tidak dapat generate permintaan rutin.');
        }

        DB::beginTransaction();
        try {
            $register = $jadwal->registerAset;
            $pemohon = MasterPegawai::query()
                ->where('id_unit_kerja', $register->id_unit_kerja)
                ->orderBy('id')
                ->first();

            if (!$pemohon) {
                throw new \RuntimeException('Tidak ditemukan pegawai pada unit kerja aset untuk dijadikan pemohon rutin.');
            }

            $tahun = date('Y');
            $lastPermintaan = PermintaanPemeliharaan::whereYear('created_at', $tahun)
                ->orderBy('id_permintaan_pemeliharaan', 'desc')
                ->first();
            $urutan = $lastPermintaan ? (int) substr($lastPermintaan->no_permintaan_pemeliharaan, -4) + 1 : 1;

            $permintaan = PermintaanPemeliharaan::create([
                'no_permintaan_pemeliharaan' => 'PMH/' . $tahun . '/' . str_pad($urutan, 4, '0', STR_PAD_LEFT),
                'id_register_aset' => $register->id_register_aset,
                'id_unit_kerja' => $register->id_unit_kerja,
                'id_pemohon' => $pemohon->id,
                'tanggal_permintaan' => now()->toDateString(),
                'jenis_pemeliharaan' => $jadwal->jenis_maintenance,
                'prioritas' => 'SEDANG',
                'status_permintaan' => 'DISETUJUI',
                'deskripsi_kerusakan' => 'Permintaan otomatis dari jadwal maintenance rutin.',
                'keterangan' => trim(($jadwal->keterangan ?? '') . ' [AUTO-RUTIN]'),
            ]);

            $baseDate = $jadwal->tanggal_selanjutnya ?: Carbon::parse($jadwal->tanggal_mulai);
            $next = $this->calculateNextDate($baseDate->format('Y-m-d'), $jadwal->periode, $jadwal->interval_hari);

            $jadwal->update([
                'tanggal_terakhir' => now()->toDateString(),
                'tanggal_selanjutnya' => $next,
            ]);

            DB::commit();

            return redirect()
                ->route('maintenance.service-report.create', ['permintaan_id' => $permintaan->id_permintaan_pemeliharaan])
                ->with('success', 'Permintaan rutin berhasil digenerate. Lanjutkan isi Laporan Servis.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate permintaan rutin: ' . $e->getMessage());
        }
    }

    private function calculateNextDate($tanggalMulai, $periode, $intervalHari = null)
    {
        $date = \Carbon\Carbon::parse($tanggalMulai);
        
        switch ($periode) {
            case 'HARIAN':
                return $date->addDay()->format('Y-m-d');
            case 'MINGGUAN':
                return $date->addWeek()->format('Y-m-d');
            case 'BULANAN':
                return $date->addMonth()->format('Y-m-d');
            case '3_BULAN':
                return $date->addMonths(3)->format('Y-m-d');
            case '6_BULAN':
                return $date->addMonths(6)->format('Y-m-d');
            case 'TAHUNAN':
                return $date->addYear()->format('Y-m-d');
            case 'CUSTOM':
                return $date->addDays($intervalHari ?? 30)->format('Y-m-d');
            default:
                return $date->addMonth()->format('Y-m-d');
        }
    }
}


