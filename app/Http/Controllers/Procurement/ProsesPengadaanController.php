<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PengadaanPaket;
use Illuminate\Http\Request;

class ProsesPengadaanController extends Controller
{
    /**
     * Daftar paket pengadaan yang sedang dalam proses (DIAJUKAN, DIPROSES).
     */
    public function index(Request $request)
    {
        $query = PengadaanPaket::with(['subKegiatan.kegiatan.program', 'rku'])
            ->whereIn('status_paket', ['DIAJUKAN', 'DIPROSES'])
            ->orderByDesc('updated_at');

        if ($request->filled('status_paket')) {
            $query->where('status_paket', $request->status_paket);
        }
        if ($request->filled('metode_pengadaan')) {
            $query->where('metode_pengadaan', $request->metode_pengadaan);
        }

        $pakets = $query->paginate(15)->withQueryString();

        return view('procurement.proses-pengadaan.index', compact('pakets'));
    }

    /**
     * Detail proses - redirect ke detail paket.
     */
    public function show(string $id)
    {
        return redirect()->route('procurement.paket-pengadaan.show', $id);
    }
}
