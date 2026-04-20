<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Http\Controllers\Controller;
use App\Models\DetailDistribusi;
use App\Models\DataInventory;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\MasterSatuan;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use App\Services\DistribusiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistribusiController extends Controller
{
    public function __construct(
        private readonly DistribusiService $distribusiService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = TransaksiDistribusi::with(['gudangAsal', 'gudangTujuan', 'permintaan', 'pegawaiPengirim']);

        if ($user->hasRole('admin_gudang_aset')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'ASET')->pluck('id_gudang'));
        } elseif ($user->hasRole('admin_gudang_persediaan')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'PERSEDIAAN')->pluck('id_gudang'));
        } elseif ($user->hasRole('admin_gudang_farmasi')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'FARMASI')->pluck('id_gudang'));
        }

        if ($request->filled('gudang')) {
            $query->where(fn ($q) => $q->where('id_gudang_asal', $request->gudang)->orWhere('id_gudang_tujuan', $request->gudang));
        }
        if ($request->filled('status')) {
            $query->where('status_distribusi', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_sbbk', 'like', "%{$search}%")
                    ->orWhereHas('permintaan', fn ($qq) => $qq->where('no_permintaan', 'like', "%{$search}%"));
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $distribusis = $query->latest('tanggal_distribusi')->paginate($perPage)->appends($request->query());
        $gudangs = MasterGudang::all();

        return view('transaction.distribusi.index', compact('distribusis', 'gudangs'));
    }

    public function create(Request $request)
    {
        $permintaans = PermintaanBarang::whereIn('status', [
            PermintaanBarangStatus::Diverifikasi->value,
            PermintaanBarangStatus::ProsesDistribusi->value,
            PermintaanBarangStatus::Dikirim->value,
        ])->with(['unitKerja', 'pemohon', 'detailPermintaan.dataBarang'])->get();

        $gudangs = MasterGudang::all();
        $pegawais = MasterPegawai::all();
        $satuans = MasterSatuan::all();
        $selectedPermintaan = $request->filled('permintaan_id')
            ? PermintaanBarang::with(['detailPermintaan.dataBarang', 'detailPermintaan.satuan'])->find($request->permintaan_id)
            : null;

        return view('transaction.distribusi.create', compact('permintaans', 'gudangs', 'pegawais', 'satuans', 'selectedPermintaan'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $this->distribusiService->createDraft($validated);

        return redirect()->route('transaction.distribusi.index')->with('success', 'Distribusi draft berhasil dibuat.');
    }

    public function show($id)
    {
        $distribusi = TransaksiDistribusi::with([
            'permintaan.unitKerja', 'permintaan.pemohon', 'gudangAsal', 'gudangTujuan',
            'pegawaiPengirim', 'detailDistribusi.inventory.dataBarang', 'detailDistribusi.inventory.gudang', 'detailDistribusi.satuan',
        ])->findOrFail($id);

        return view('transaction.distribusi.show', compact('distribusi'));
    }

    public function edit($id)
    {
        $distribusi = TransaksiDistribusi::with('detailDistribusi')->findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi draft yang bisa diedit.');
        }

        $permintaans = PermintaanBarang::whereIn('status', [
            PermintaanBarangStatus::Diverifikasi->value,
            PermintaanBarangStatus::ProsesDistribusi->value,
            PermintaanBarangStatus::Dikirim->value,
        ])->get();
        $gudangs = MasterGudang::all();
        $pegawais = MasterPegawai::all();
        $satuans = MasterSatuan::all();
        $inventories = DataInventory::where('id_gudang', $distribusi->id_gudang_asal)->with('dataBarang')->get();

        return view('transaction.distribusi.edit', compact('distribusi', 'permintaans', 'gudangs', 'pegawais', 'satuans', 'inventories'));
    }

    public function update(Request $request, $id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi draft yang bisa diubah.');
        }

        $validated = $this->validatePayload($request, false);
        $this->distribusiService->updateDraft($distribusi, $validated);

        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.index')->with('error', 'Hanya distribusi draft yang bisa dihapus.');
        }

        $this->distribusiService->deleteDraft($distribusi);
        return redirect()->route('transaction.distribusi.index')->with('success', 'Distribusi dihapus.');
    }

    public function proses($id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Status tidak valid untuk diproses.');
        }

        $this->distribusiService->markDiproses($distribusi);
        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi diproses.');
    }

    public function kirim($id)
    {
        $distribusi = TransaksiDistribusi::with('detailDistribusi')->findOrFail($id);
        if (!in_array($distribusi->status_distribusi?->value, [DistribusiStatus::Draft->value, DistribusiStatus::Diproses->value], true)) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Status tidak valid untuk dikirim.');
        }

        $this->distribusiService->kirim($distribusi);
        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi dikirim.');
    }

    public function getGudangTujuanByPermintaan($permintaanId)
    {
        $permintaan = PermintaanBarang::with('unitKerja')->findOrFail($permintaanId);
        $gudangTujuan = MasterGudang::where('id_unit_kerja', $permintaan->id_unit_kerja)->where('jenis_gudang', 'UNIT')->get();

        return response()->json([
            'success' => true,
            'gudang' => $gudangTujuan->map(fn ($gudang) => [
                'id_gudang' => $gudang->id_gudang,
                'nama_gudang' => $gudang->nama_gudang,
                'jenis_gudang' => $gudang->jenis_gudang,
                'kategori_gudang' => $gudang->kategori_gudang,
            ]),
        ]);
    }

    public function getInventoryByGudang($gudangId)
    {
        $inventories = DataInventory::where('id_gudang', $gudangId)->where('status_inventory', 'AKTIF')->with(['dataBarang', 'satuan'])->get();

        $result = $inventories->map(function ($inv) {
            $qtyDistributed = DetailDistribusi::where('id_inventory', $inv->id_inventory)
                ->whereHas('distribusi', fn ($q) => $q->whereIn('status_distribusi', ['draft', 'diproses', 'dikirim', 'selesai']))
                ->sum('qty_distribusi');

            return [
                'id_inventory' => $inv->id_inventory,
                'nama_barang' => $inv->dataBarang->nama_barang ?? '-',
                'kode_barang' => $inv->dataBarang->kode_data_barang ?? '-',
                'jenis_inventory' => $inv->jenis_inventory,
                'jenis_barang' => $inv->jenis_barang,
                'harga_satuan' => $inv->harga_satuan,
                'id_satuan' => $inv->id_satuan,
                'qty_available' => max(0, $inv->qty_input - $qtyDistributed),
            ];
        })->filter(fn ($inv) => $inv['qty_available'] > 0);

        return response()->json(['inventory' => $result->values()]);
    }

    public function getPermintaanDetail($id)
    {
        $permintaan = PermintaanBarang::with(['detailPermintaan.dataBarang', 'detailPermintaan.satuan'])->findOrFail($id);
        $details = $permintaan->detailPermintaan->map(fn ($detail) => [
            'nama_barang' => $detail->dataBarang->nama_barang ?? '-',
            'qty_diminta' => number_format($detail->qty_diminta, 2),
            'satuan' => $detail->satuan->nama_satuan ?? '-',
        ]);

        return response()->json(['success' => true, 'details' => $details]);
    }

    private function validatePayload(Request $request, bool $requirePermintaan = true): array
    {
        $rules = [
            'tanggal_distribusi' => 'required|date',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang|different:id_gudang_asal',
            'id_pegawai_pengirim' => 'required|exists:master_pegawai,id',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_distribusi' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.harga_satuan' => 'required|numeric|min:0',
            'detail.*.keterangan' => 'nullable|string',
        ];

        if ($requirePermintaan) {
            $rules['id_permintaan'] = 'required|exists:permintaan_barang,id_permintaan';
        }

        return $request->validate($rules);
    }
}
