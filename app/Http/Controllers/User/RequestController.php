<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanBarang;
use App\Models\MasterDataBarang;
use App\Models\MasterPegawai;
use App\Services\PermintaanService;

class RequestController extends Controller
{
    public function __construct(
        private readonly PermintaanService $permintaanService
    ) {}

    public function index(Request $request)
    {
        $pegawai = MasterPegawai::where('user_id', Auth::id())->first();
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $requests = PermintaanBarang::with(['pemohon', 'detailPermintaan'])
            ->when($pegawai, fn ($q) => $q->where('id_pemohon', $pegawai->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->paginate($perPage)->appends($request->query());

        return view('user.requests.index', compact('requests'));
    }

    public function create()
    {
        $barangs = MasterDataBarang::all();
        return view('user.requests.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_data_barang' => 'required|exists:master_data_barang,id_data_barang',
            'qty_permintaan' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $this->permintaanService->createAndSubmitFromUser((int) Auth::id(), $validated);

        return redirect()->route('user.requests')
            ->with('success', 'Permintaan berhasil diajukan.');
    }

    public function show($id)
    {
        $request = PermintaanBarang::with(['pemohon', 'detailPermintaan.dataBarang'])
            ->findOrFail($id);

        return view('user.requests.show', compact('request'));
    }
}

