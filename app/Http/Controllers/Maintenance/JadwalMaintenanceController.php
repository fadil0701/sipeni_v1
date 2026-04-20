<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalMaintenance;
use App\Models\RegisterAset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JadwalMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalMaintenance::with(['registerAset.inventory.dataBarang', 'creator']);

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

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $jadwals = $query->latest('tanggal_mulai')->paginate($perPage)->appends($request->query());

        return view('maintenance.jadwal-maintenance.index', compact('jadwals'));
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


