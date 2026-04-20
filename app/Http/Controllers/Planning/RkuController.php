<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Models\RkuHeader;
use App\Models\MasterUnitKerja;
use Illuminate\Http\Request;

class RkuController extends Controller
{
    /**
     * Display a listing of the resource (Status Perencanaan - daftar RKU).
     */
    public function index(Request $request)
    {
        $query = RkuHeader::with(['unitKerja', 'subKegiatan.kegiatan.program', 'pengaju'])
            ->orderByDesc('updated_at');

        if ($request->filled('status_rku')) {
            $query->where('status_rku', $request->status_rku);
        }
        if ($request->filled('tahun_anggaran')) {
            $query->where('tahun_anggaran', $request->tahun_anggaran);
        }
        if ($request->filled('id_unit_kerja')) {
            $query->where('id_unit_kerja', $request->id_unit_kerja);
        }

        $rkus = $query->paginate(15)->withQueryString();

        $tahunList = RkuHeader::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran');

        $unitKerjaList = MasterUnitKerja::orderBy('nama_unit_kerja')->get();

        return view('planning.rku.index', compact('rkus', 'tahunList', 'unitKerjaList'));
    }

    /**
     * Rekap perencanaan tahunan per Program, Kegiatan, Sub Kegiatan.
     */
    public function rekapTahunan(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        $rkus = RkuHeader::with(['subKegiatan.kegiatan.program', 'unitKerja'])
            ->where('tahun_anggaran', $tahun)
            ->orderBy('id_rku')
            ->get();

        // Group by Program -> Kegiatan -> Sub Kegiatan
        $rekap = [];
        foreach ($rkus as $rku) {
            $sub = $rku->subKegiatan;
            if (!$sub) {
                continue;
            }
            $kegiatan = $sub->kegiatan;
            $program = $kegiatan?->program;

            $idProgram = $program?->id_program ?? 0;
            $idKegiatan = $kegiatan?->id_kegiatan ?? 0;
            $idSub = $sub->id_sub_kegiatan;

            if (!isset($rekap[$idProgram])) {
                $rekap[$idProgram] = [
                    'nama_program' => $program?->nama_program ?? '-',
                    'kegiatan' => [],
                ];
            }
            if (!isset($rekap[$idProgram]['kegiatan'][$idKegiatan])) {
                $rekap[$idProgram]['kegiatan'][$idKegiatan] = [
                    'nama_kegiatan' => $kegiatan?->nama_kegiatan ?? '-',
                    'sub_kegiatan' => [],
                ];
            }
            if (!isset($rekap[$idProgram]['kegiatan'][$idKegiatan]['sub_kegiatan'][$idSub])) {
                $rekap[$idProgram]['kegiatan'][$idKegiatan]['sub_kegiatan'][$idSub] = [
                    'nama_sub_kegiatan' => $sub->nama_sub_kegiatan,
                    'kode_sub_kegiatan' => $sub->kode_sub_kegiatan ?? '',
                    'jumlah_rku' => 0,
                    'total_anggaran' => 0,
                ];
            }
            $rekap[$idProgram]['kegiatan'][$idKegiatan]['sub_kegiatan'][$idSub]['jumlah_rku']++;
            $rekap[$idProgram]['kegiatan'][$idKegiatan]['sub_kegiatan'][$idSub]['total_anggaran'] += (float) $rku->total_anggaran;
        }

        $tahunList = RkuHeader::select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        return view('planning.rekap-tahunan', compact('rekap', 'tahun', 'tahunList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('planning.rku.index')
            ->with('info', 'Fitur tambah RKU akan dilengkapi pada tahap berikutnya.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: implementasi lengkap RKU create
        return redirect()->route('planning.rku.index')
            ->with('info', 'Fitur tambah RKU akan dilengkapi pada tahap berikutnya.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rku = RkuHeader::with(['unitKerja', 'subKegiatan.kegiatan.program', 'pengaju', 'approver', 'rkuDetail.dataBarang', 'rkuDetail.satuan'])
            ->findOrFail($id);
        return view('planning.rku.show', compact('rku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return redirect()->route('planning.rku.show', $id)
            ->with('info', 'Fitur edit RKU akan dilengkapi pada tahap berikutnya.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO: implementasi lengkap RKU update
        return redirect()->route('planning.rku.index')
            ->with('info', 'Fitur edit RKU akan dilengkapi pada tahap berikutnya.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('planning.rku.index')
            ->with('info', 'Fitur hapus RKU akan dilengkapi pada tahap berikutnya.');
    }
}
