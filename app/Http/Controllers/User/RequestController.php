<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanBarang;
use App\Models\MasterDataBarang;
use App\Models\MasterPegawai;
use App\Models\User;
use App\Services\PermintaanService;


class RequestController extends Controller
{
    public function __construct(
        private readonly PermintaanService $permintaanService
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $pegawai = MasterPegawai::where('user_id', Auth::id())->first();
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $requests = PermintaanBarang::with(['pemohon', 'detailPermintaan'])
            ->when(! $user->hasRole('admin'), function ($q) use ($pegawai) {
                if ($pegawai) {
                    $q->where('id_pemohon', $pegawai->id);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
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

        return redirect()->route('user.requests.index')
            ->with('success', 'Permintaan berhasil diajukan.');
    }

    public function show($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $request = PermintaanBarang::with(['pemohon', 'detailPermintaan.dataBarang'])
            ->findOrFail($id);

        if (! $user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', Auth::id())->first();
            if (! $pegawai || (int) $request->id_pemohon !== (int) $pegawai->id) {
                abort(403, 'Anda tidak dapat mengakses permintaan ini.');
            }
        }

        return view('user.requests.show', compact('request'));
    }
}

