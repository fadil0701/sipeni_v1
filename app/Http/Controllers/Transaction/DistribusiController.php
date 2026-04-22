<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
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
        // Jika dibuka dari "Proses Disposisi", resolve permintaan dari approval_log.
        if ($request->filled('approval_log') && !$request->filled('permintaan_id')) {
            $approvalLog = ApprovalLog::query()
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->find($request->approval_log);

            if ($approvalLog) {
                $request->merge(['permintaan_id' => $approvalLog->id_referensi]);
            }
        }

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
        $flowMode = $request->filled('approval_log') ? 'proses' : 'distribusi';
        $approvalLogId = $request->input('approval_log');

        return view('transaction.distribusi.create', compact('permintaans', 'gudangs', 'pegawais', 'satuans', 'selectedPermintaan', 'flowMode', 'approvalLogId'));
    }

    public function store(Request $request)
    {
        $isProsesMode = $request->input('flow_mode') === 'proses';

        // Mode proses harus menghasilkan SBBK terpisah per gudang pusat (kategori disposisi).
        // Jadi gudang asal/tujuan diset paksa dari approval log + unit permintaan.
        if ($isProsesMode && $request->filled('approval_log_id')) {
            $approvalLog = ApprovalLog::with('approvalFlow.role')->find($request->approval_log_id);

            if ($approvalLog && $request->filled('id_permintaan')) {
                $roleKategoriMap = [
                    'admin_gudang_aset' => 'ASET',
                    'admin_gudang_persediaan' => 'PERSEDIAAN',
                    'admin_gudang_farmasi' => 'FARMASI',
                ];

                $roleName = $approvalLog->approvalFlow?->role?->name;
                $kategori = $roleKategoriMap[$roleName] ?? null;

                if ($kategori) {
                    $gudangAsal = MasterGudang::query()
                        ->where('jenis_gudang', 'PUSAT')
                        ->where('kategori_gudang', $kategori)
                        ->orderBy('id_gudang')
                        ->first();

                    if ($gudangAsal) {
                        $request->merge(['id_gudang_asal' => $gudangAsal->id_gudang]);
                    }
                }

                $permintaan = PermintaanBarang::query()->find($request->id_permintaan);
                if ($permintaan) {
                    $gudangTujuan = MasterGudang::query()
                        ->where('id_unit_kerja', $permintaan->id_unit_kerja)
                        ->where('jenis_gudang', 'UNIT')
                        ->orderBy('id_gudang')
                        ->first();

                    if ($gudangTujuan) {
                        $request->merge(['id_gudang_tujuan' => $gudangTujuan->id_gudang]);
                    }
                }
            }
        }

        $validated = $this->validatePayload($request, true, ! $isProsesMode);
        $this->distribusiService->createDraft($validated);

        if ($isProsesMode && $request->filled('approval_log_id')) {
            ApprovalLog::query()
                ->where('id', $request->approval_log_id)
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->update([
                    'status' => 'DIPROSES',
                    'user_id' => Auth::id(),
                    'approved_at' => now(),
                ]);
        }

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
        $distribusi = TransaksiDistribusi::with([
            'detailDistribusi',
            'permintaan.unitKerja',
            'permintaan.detailPermintaan.dataBarang',
            'permintaan.detailPermintaan.satuan',
        ])->findOrFail($id);
        if (! in_array($distribusi->status_distribusi, [DistribusiStatus::Draft, DistribusiStatus::Diproses], true)) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi berstatus draft atau diproses yang bisa diedit.');
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
        $selectedPermintaan = $distribusi->permintaan;

        return view('transaction.distribusi.edit', compact('distribusi', 'permintaans', 'gudangs', 'pegawais', 'satuans', 'inventories', 'selectedPermintaan'));
    }

    public function update(Request $request, $id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if (! in_array($distribusi->status_distribusi, [DistribusiStatus::Draft, DistribusiStatus::Diproses], true)) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi berstatus draft atau diproses yang bisa diubah.');
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

    public function kirim(Request $request, $id)
    {
        $distribusi = TransaksiDistribusi::with('detailDistribusi')->findOrFail($id);
        $fromIndex = $request->input('kirim_from') === 'index';

        if (!in_array($distribusi->status_distribusi?->value, [DistribusiStatus::Draft->value, DistribusiStatus::Diproses->value], true)) {
            return $fromIndex
                ? redirect()->route('transaction.distribusi.index')->with('error', 'Status tidak valid untuk dikirim.')
                : redirect()->route('transaction.distribusi.show', $id)->with('error', 'Status tidak valid untuk dikirim.');
        }
        if (!$distribusi->id_pegawai_pengirim) {
            return redirect()->route('transaction.distribusi.edit', $id)
                ->with('error', 'Pilih pegawai pengirim terlebih dahulu sebelum mengirim distribusi.');
        }

        $this->distribusiService->kirim($distribusi);

        if ($fromIndex) {
            return redirect()->route('transaction.distribusi.index')->with('kirim_popup', 'Distribusi berhasil dikirim.');
        }

        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi dikirim.');
    }

    public function getGudangTujuanByPermintaan($permintaanId)
    {
        $permintaan = PermintaanBarang::with('unitKerja')->findOrFail($permintaanId);
        $gudangTujuan = MasterGudang::where('id_unit_kerja', $permintaan->id_unit_kerja)
            ->where('jenis_gudang', 'UNIT')
            ->get();

        $roleKategoriMap = [
            'admin_gudang_aset' => 'ASET',
            'admin_gudang_persediaan' => 'PERSEDIAAN',
            'admin_gudang_farmasi' => 'FARMASI',
        ];

        $selectedKategori = null;
        $approvalLogId = request()->query('approval_log');
        if ($approvalLogId) {
            $approvalLog = ApprovalLog::with('approvalFlow.role')->find($approvalLogId);
            $roleName = $approvalLog?->approvalFlow?->role?->name;
            $selectedKategori = $roleKategoriMap[$roleName] ?? null;
        }

        if (!$selectedKategori) {
            $jenisPermintaan = is_array($permintaan->jenis_permintaan)
                ? $permintaan->jenis_permintaan
                : (json_decode($permintaan->jenis_permintaan, true) ?? []);

            foreach (['ASET', 'PERSEDIAAN', 'FARMASI'] as $kategori) {
                if (in_array($kategori, $jenisPermintaan, true)) {
                    $selectedKategori = $kategori;
                    break;
                }
            }
        }

        $gudangAsalQuery = MasterGudang::query()->where('jenis_gudang', 'PUSAT');
        if ($selectedKategori) {
            $gudangAsalQuery->where('kategori_gudang', $selectedKategori);
        }
        $gudangAsal = $gudangAsalQuery->orderBy('nama_gudang')->get();

        return response()->json([
            'success' => true,
            'unit_kerja' => [
                'id_unit_kerja' => $permintaan->id_unit_kerja,
                'nama_unit_kerja' => $permintaan->unitKerja->nama_unit_kerja ?? null,
            ],
            'kategori_permintaan' => $selectedKategori,
            'gudang' => $gudangTujuan->map(fn ($gudang) => [
                'id_gudang' => $gudang->id_gudang,
                'nama_gudang' => $gudang->nama_gudang,
                'jenis_gudang' => $gudang->jenis_gudang,
                'kategori_gudang' => $gudang->kategori_gudang,
            ]),
            'gudang_asal' => $gudangAsal->map(fn ($gudang) => [
                'id_gudang' => $gudang->id_gudang,
                'nama_gudang' => $gudang->nama_gudang,
                'jenis_gudang' => $gudang->jenis_gudang,
                'kategori_gudang' => $gudang->kategori_gudang,
            ]),
        ]);
    }

    public function getInventoryByGudang($gudangId)
    {
        $gudang = MasterGudang::findOrFail($gudangId);

        $query = DataInventory::query()
            ->where('id_gudang', $gudangId)
            ->where('status_inventory', 'AKTIF');

        // Pastikan inventory yang ditampilkan konsisten dengan kategori gudang asal.
        $allowedKategori = ['ASET', 'PERSEDIAAN', 'FARMASI'];
        if (in_array($gudang->kategori_gudang, $allowedKategori, true)) {
            $query->where('jenis_inventory', $gudang->kategori_gudang);
        }

        $inventories = $query->with(['dataBarang', 'satuan'])->get();

        $result = $inventories->map(function ($inv) {
            $qtyDistributed = DetailDistribusi::where('id_inventory', $inv->id_inventory)
                ->whereHas('distribusi', fn ($q) => $q->whereIn('status_distribusi', ['draft', 'diproses', 'dikirim', 'selesai']))
                ->sum('qty_distribusi');

            $resolvedSatuanId = $inv->id_satuan ?? $inv->dataBarang?->id_satuan;

            return [
                'id_inventory' => $inv->id_inventory,
                'nama_barang' => $inv->dataBarang->nama_barang ?? '-',
                'kode_barang' => $inv->dataBarang->kode_data_barang ?? '-',
                'jenis_inventory' => $inv->jenis_inventory,
                'jenis_barang' => $inv->jenis_barang,
                'harga_satuan' => $inv->harga_satuan,
                'id_satuan' => $resolvedSatuanId,
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
            'qty_diminta' => number_format((float) ($detail->qty_diminta_awal ?? $detail->qty_diminta), 2),
            'qty_disetujui' => number_format((float) ($detail->qty_disetujui ?? $detail->qty_diminta), 2),
            'satuan' => $detail->satuan->nama_satuan ?? '-',
        ]);

        return response()->json(['success' => true, 'details' => $details]);
    }

    private function validatePayload(Request $request, bool $requirePermintaan = true, bool $requirePegawaiPengirim = true): array
    {
        $rules = [
            'tanggal_distribusi' => 'required|date',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang|different:id_gudang_asal',
            'id_pegawai_pengirim' => $requirePegawaiPengirim ? 'required|exists:master_pegawai,id' : 'nullable|exists:master_pegawai,id',
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
