<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PengadaanPaket;
use App\Models\MasterSubKegiatan;
use App\Models\RkuHeader;
use App\Services\PengadaanService;
use Illuminate\Http\Request;

class PaketPengadaanController extends Controller
{
    public function __construct(
        private readonly PengadaanService $pengadaanService
    ) {}

    public function index(Request $request)
    {
        $query = PengadaanPaket::with(['subKegiatan.kegiatan.program', 'rku'])
            ->orderByDesc('updated_at');

        if ($request->filled('status_paket')) {
            $query->where('status_paket', $request->status_paket);
        }
        if ($request->filled('id_sub_kegiatan')) {
            $query->where('id_sub_kegiatan', $request->id_sub_kegiatan);
        }
        if ($request->filled('metode_pengadaan')) {
            $query->where('metode_pengadaan', $request->metode_pengadaan);
        }

        $pakets = $query->paginate(15)->withQueryString();
        $subKegiatanList = MasterSubKegiatan::with('kegiatan.program')->orderBy('nama_sub_kegiatan')->get();

        return view('procurement.paket-pengadaan.index', compact('pakets', 'subKegiatanList'));
    }

    public function create()
    {
        $subKegiatanList = MasterSubKegiatan::with('kegiatan.program')->orderBy('nama_sub_kegiatan')->get();
        $rkuList = RkuHeader::whereIn('status_rku', ['DISETUJUI', 'DIPROSES'])->orderByDesc('tahun_anggaran')->get();
        return view('procurement.paket-pengadaan.create', compact('subKegiatanList', 'rkuList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sub_kegiatan' => 'required|exists:master_sub_kegiatan,id_sub_kegiatan',
            'id_rku' => 'nullable|exists:rku_header,id_rku',
            'no_paket' => 'required|string|max:100|unique:pengadaan_paket,no_paket',
            'nama_paket' => 'required|string|max:255',
            'deskripsi_paket' => 'nullable|string',
            'metode_pengadaan' => 'required|in:PEMILIHAN_LANGSUNG,PENUNJUKAN_LANGSUNG,TENDER,SWAKELOLA',
            'nilai_paket' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_paket' => 'nullable|in:DRAFT,DIAJUKAN,DIPROSES,SELESAI,DIBATALKAN',
            'keterangan' => 'nullable|string',
        ]);

        $validated['status_paket'] = $validated['status_paket'] ?? 'DRAFT';
        $paket = PengadaanPaket::create($validated);
        if ($paket->status_paket === 'DIPROSES') {
            $this->pengadaanService->processProcurement($paket);
        } elseif ($paket->status_paket === 'SELESAI') {
            $this->pengadaanService->markBarangTersedia($paket);
        }

        return redirect()->route('procurement.paket-pengadaan.index')
            ->with('success', 'Paket pengadaan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $paket = PengadaanPaket::with(['subKegiatan.kegiatan.program', 'rku', 'kontrak'])->findOrFail($id);
        return view('procurement.paket-pengadaan.show', compact('paket'));
    }

    public function edit(string $id)
    {
        $paket = PengadaanPaket::findOrFail($id);
        $subKegiatanList = MasterSubKegiatan::with('kegiatan.program')->orderBy('nama_sub_kegiatan')->get();
        $rkuList = RkuHeader::whereIn('status_rku', ['DISETUJUI', 'DIPROSES'])->orderByDesc('tahun_anggaran')->get();
        return view('procurement.paket-pengadaan.edit', compact('paket', 'subKegiatanList', 'rkuList'));
    }

    public function update(Request $request, string $id)
    {
        $paket = PengadaanPaket::findOrFail($id);

        $validated = $request->validate([
            'id_sub_kegiatan' => 'required|exists:master_sub_kegiatan,id_sub_kegiatan',
            'id_rku' => 'nullable|exists:rku_header,id_rku',
            'no_paket' => 'required|string|max:100|unique:pengadaan_paket,no_paket,' . $paket->id_paket . ',id_paket',
            'nama_paket' => 'required|string|max:255',
            'deskripsi_paket' => 'nullable|string',
            'metode_pengadaan' => 'required|in:PEMILIHAN_LANGSUNG,PENUNJUKAN_LANGSUNG,TENDER,SWAKELOLA',
            'nilai_paket' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_paket' => 'required|in:DRAFT,DIAJUKAN,DIPROSES,SELESAI,DIBATALKAN',
            'keterangan' => 'nullable|string',
        ]);

        $paket->update($validated);

        if ($paket->status_paket === 'DIPROSES') {
            $this->pengadaanService->processProcurement($paket);
        } elseif ($paket->status_paket === 'SELESAI') {
            $this->pengadaanService->markBarangTersedia($paket);
        }

        return redirect()->route('procurement.paket-pengadaan.index')
            ->with('success', 'Paket pengadaan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $paket = PengadaanPaket::findOrFail($id);
        if ($paket->kontrak) {
            return redirect()->route('procurement.paket-pengadaan.index')
                ->with('error', 'Paket tidak dapat dihapus karena sudah memiliki kontrak.');
        }
        $paket->delete();
        return redirect()->route('procurement.paket-pengadaan.index')
            ->with('success', 'Paket pengadaan berhasil dihapus.');
    }
}
