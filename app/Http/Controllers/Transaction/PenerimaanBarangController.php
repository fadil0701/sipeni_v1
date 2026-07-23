<?php

namespace App\Http\Controllers\Transaction;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Helpers\PaginationHelper;
use App\Helpers\PermissionHelper;
use App\Support\Http\SafeUserMessage;
use App\Http\Controllers\Controller;
use App\Models\DataInventory;
use App\Models\DetailPenerimaanBarang;
use App\Models\InventoryItem;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\MasterSatuan;
use App\Models\MasterUnitKerja;
use App\Models\PenerimaanBarang;
use App\Models\RegisterAset;
use App\Models\TransaksiDistribusi;
use App\Services\DataStockSyncService;
use App\Services\DistribusiStockMutationService;
use App\Services\PenerimaanDistribusiInventoryTransferService;
use App\Services\PermintaanBarangStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PenerimaanBarangController extends Controller
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanBarangStatus
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PenerimaanBarang::with(['distribusi', 'unitKerja', 'pegawaiPenerima']);

        // Filter berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan penerimaan dari unit kerja user yang login
                $query->where('id_unit_kerja', $pegawai->id_unit_kerja);
                // Hanya tampilkan unit kerja user yang login di dropdown
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                // Jika user tidak memiliki unit kerja, tidak tampilkan data
                $query->whereRaw('1 = 0');
                $unitKerjas = collect([]);
            }
        } else {
            // Admin dan Admin Gudang melihat semua
            $unitKerjas = MasterUnitKerja::all();
        }

        // Filters
        if ($request->filled('unit_kerja')) {
            $query->where('id_unit_kerja', $request->unit_kerja);
        }

        if ($request->filled('status')) {
            $query->where('status_penerimaan', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_penerimaan', 'like', "%{$search}%")
                    ->orWhereHas('distribusi', function ($q) use ($search) {
                        $q->where('no_sbbk', 'like', "%{$search}%");
                    });
            });
        }

        // Filter berdasarkan tanggal mulai
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_penerimaan', '>=', $request->tanggal_mulai);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_penerimaan', '<=', $request->tanggal_akhir);
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $penerimaans = $query->latest('tanggal_penerimaan')->paginate($perPage)->appends($request->query());

        return view('transaction.penerimaan-barang.index', compact('penerimaans', 'unitKerjas'));
    }

    public function show($id)
    {
        $penerimaan = PenerimaanBarang::with([
            'distribusi.gudangAsal',
            'distribusi.gudangTujuan',
            'distribusi.permintaan',
            'distribusi.detailDistribusi.inventory.gudang', // Eager load detail distribusi untuk mendapatkan qty dikirim
            'unitKerja',
            'pegawaiPenerima',
            'detailPenerimaan.inventory.dataBarang',
            'detailPenerimaan.inventory.gudang', // Eager load gudang untuk mendapatkan kategori_gudang
            'detailPenerimaan.satuan',
        ])->findOrFail($id);

        $this->authorizePenerimaanAccess($penerimaan);

        $registersNeedRuangan = collect();
        if ($penerimaan->status_penerimaan === 'DITERIMA') {
            $inventoryIds = $penerimaan->detailPenerimaan
                ->pluck('id_inventory')
                ->filter()
                ->unique()
                ->values();

            if ($inventoryIds->isNotEmpty()) {
                $registersNeedRuangan = RegisterAset::query()
                    ->whereIn('id_inventory', $inventoryIds)
                    ->whereNull('id_ruangan')
                    ->with(['inventory.dataBarang'])
                    ->orderBy('nomor_register')
                    ->get();
            }
        }

        return view('transaction.penerimaan-barang.show', compact('penerimaan', 'registersNeedRuangan'));
    }

    public function verify(Request $request, PenerimaanBarang $penerimaan_barang)
    {
        $this->authorizePenerimaanAccess($penerimaan_barang);

        $user = Auth::user();
        $canVerify = PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.update')
            || PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.store');
        if (! $canVerify) {
            abort(403, 'Anda tidak memiliki izin untuk memverifikasi penerimaan.');
        }

        if ($penerimaan_barang->status_penerimaan !== 'MENUNGGU_VERIFIKASI') {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan_barang->id_penerimaan)
                ->with('error', $penerimaan_barang->status_penerimaan === 'MENUNGGU_BUKTI_SAMPAI'
                    ? 'Verifikasi belum dapat dilakukan. Pengirim belum mengunggah bukti sampai (foto + nama penerima).'
                    : 'Penerimaan ini sudah diverifikasi.');
        }

        if (! $penerimaan_barang->hasBuktiSampai()) {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan_barang->id_penerimaan)
                ->with('error', 'Verifikasi belum dapat dilakukan. Bukti sampai dari pengirim belum lengkap.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_detail_penerimaan' => 'required|integer',
            'items.*.hasil' => 'required|in:sesuai,tidak_sesuai',
            'items.*.keterangan' => 'nullable|string|max:2000',
        ], [
            'items.required' => 'Data verifikasi detail wajib diisi.',
            'items.*.hasil.required' => 'Pilih Sesuai atau Tidak sesuai untuk setiap barang.',
        ]);

        $detailIds = $penerimaan_barang->detailPenerimaan()->pluck('id_detail_penerimaan')->map(fn ($id) => (int) $id)->all();
        $postedIds = collect($validated['items'])->pluck('id_detail_penerimaan')->map(fn ($id) => (int) $id)->all();
        sort($detailIds);
        $postedSorted = $postedIds;
        sort($postedSorted);
        if ($detailIds !== $postedSorted) {
            return back()->withInput()->with('error', 'Data detail verifikasi tidak lengkap atau tidak valid.');
        }

        foreach ($validated['items'] as $index => $item) {
            if ($item['hasil'] === 'tidak_sesuai' && blank($item['keterangan'] ?? null)) {
                return back()->withInput()->withErrors([
                    "items.$index.keterangan" => 'Keterangan wajib diisi untuk barang yang tidak sesuai.',
                ]);
            }
        }

        $adaTidakSesuai = collect($validated['items'])->contains(fn ($item) => $item['hasil'] === 'tidak_sesuai');

        DB::beginTransaction();
        try {
            $pegawaiLogin = MasterPegawai::where('user_id', Auth::id())->first();
            if ($pegawaiLogin && (int) $pegawaiLogin->id_unit_kerja === (int) $penerimaan_barang->id_unit_kerja) {
                $penerimaan_barang->id_pegawai_penerima = $pegawaiLogin->id;
            }

            $catatanRingkas = [];
            foreach ($validated['items'] as $item) {
                $detail = DetailPenerimaanBarang::query()
                    ->with('inventory.dataBarang')
                    ->where('id_penerimaan', $penerimaan_barang->id_penerimaan)
                    ->where('id_detail_penerimaan', $item['id_detail_penerimaan'])
                    ->firstOrFail();

                $detail->hasil_verifikasi = $item['hasil'];
                if ($item['hasil'] === 'tidak_sesuai') {
                    $detail->keterangan = trim((string) ($item['keterangan'] ?? ''));
                    $namaBarang = $detail->inventory?->dataBarang?->nama_barang
                        ?? ('Item #'.$detail->id_detail_penerimaan);
                    $catatanRingkas[] = $namaBarang.': '.$detail->keterangan;
                } elseif (filled($item['keterangan'] ?? null)) {
                    $detail->keterangan = trim((string) $item['keterangan']);
                }
                $detail->save();
            }

            if ($adaTidakSesuai) {
                $penerimaan_barang->status_penerimaan = 'DITOLAK';
                $prev = (string) ($penerimaan_barang->keterangan ?? '');
                $extra = 'Verifikasi: ada barang tidak sesuai.'.($catatanRingkas !== [] ? ' '.implode('; ', $catatanRingkas) : '');
                $penerimaan_barang->keterangan = trim($prev.($prev !== '' ? "\n\n" : '').$extra);
                $penerimaan_barang->save();
            } else {
                $penerimaan_barang->status_penerimaan = 'DITERIMA';
                $penerimaan_barang->save();

                $distribusi = TransaksiDistribusi::with('gudangTujuan')->findOrFail($penerimaan_barang->id_distribusi);
                $this->permintaanBarangStatus->syncAfterDistribusiSelesai($distribusi->fresh());

                $penerimaanFresh = $penerimaan_barang->fresh(['detailPenerimaan']);
                $validatedForAset = [
                    'tanggal_penerimaan' => $penerimaanFresh->tanggal_penerimaan->format('Y-m-d'),
                    'detail' => $penerimaanFresh->detailPenerimaan->map(fn ($d) => [
                        'id_inventory' => $d->id_inventory,
                        'qty_diterima' => $d->qty_diterima,
                        'id_satuan' => $d->id_satuan,
                        'keterangan' => $d->keterangan,
                    ])->all(),
                ];
                $this->autoCreateRegisterAset($penerimaanFresh, $distribusi, $validatedForAset);

                if ($penerimaan_barang->id_distribusi) {
                    PenerimaanDistribusiInventoryTransferService::transferPersediaanFarmasiToGudangTujuanAfterDiterima(
                        $penerimaanFresh->fresh(['detailPenerimaan']),
                        $distribusi
                    );
                }
                DataStockSyncService::syncFromInventory();
            }

            DB::commit();

            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan_barang->id_penerimaan)
                ->with('success', $adaTidakSesuai
                    ? 'Verifikasi disimpan: ada barang yang tidak sesuai.'
                    : 'Semua barang dinyatakan sesuai dengan pengiriman.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Verify penerimaan gagal: '.$e->getMessage());

            return back()->with('error', SafeUserMessage::operationFailed('menyimpan verifikasi'));
        }
    }

    public function edit($id)
    {
        $user = Auth::user();

        // Check permission untuk edit
        if (! PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.edit')) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data penerimaan barang.');
        }

        $penerimaan = PenerimaanBarang::with('detailPenerimaan')->findOrFail($id);

        $this->authorizePenerimaanAccess($penerimaan);

        if ($penerimaan->status_penerimaan === 'MENUNGGU_VERIFIKASI') {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan);
        }

        if ($penerimaan->status_penerimaan === 'DITERIMA') {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan)
                ->with('error', 'Barang sudah diterima dan disahkan. Data tidak dapat diubah kecuali ada rekaman penolakan (tidak sesuai) yang memerlukan koreksi.');
        }

        $distribusiQuery = TransaksiDistribusi::where('status_distribusi', 'dikirim');

        // Filter distribusi berdasarkan unit kerja user yang login untuk pegawai/kepala_unit
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan distribusi yang ditujukan ke gudang unit kerja user
                $gudangUnitIds = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');

                $distribusiQuery->whereIn('id_gudang_tujuan', $gudangUnitIds);

                // Hanya tampilkan unit kerja user yang login
                $unitKerjas = MasterUnitKerja::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
                // Hanya tampilkan pegawai dari unit kerja yang sama
                $pegawais = MasterPegawai::where('id_unit_kerja', $pegawai->id_unit_kerja)->get();
            } else {
                $distribusiQuery->whereRaw('1 = 0');
                $unitKerjas = collect([]);
                $pegawais = collect([]);
            }
        } else {
            // Admin dan Admin Gudang melihat semua
            $unitKerjas = MasterUnitKerja::all();
            $pegawais = MasterPegawai::all();
        }

        $distribusis = $distribusiQuery->get();
        $satuans = MasterSatuan::all();

        return view('transaction.penerimaan-barang.edit', compact('penerimaan', 'distribusis', 'unitKerjas', 'pegawais', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Check permission untuk update
        if (! PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.update')) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui data penerimaan barang.');
        }

        $penerimaan = PenerimaanBarang::findOrFail($id);

        $this->authorizePenerimaanAccess($penerimaan);

        if ($penerimaan->status_penerimaan === 'MENUNGGU_VERIFIKASI') {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan)
                ->with('error', 'Selesaikan verifikasi melalui halaman detail terlebih dahulu.');
        }

        if ($penerimaan->status_penerimaan === 'DITERIMA') {
            return redirect()->route('transaction.penerimaan-barang.show', $penerimaan->id_penerimaan)
                ->with('error', 'Barang sudah diterima dan disahkan. Data tidak dapat diubah.');
        }

        $validated = $request->validate([
            'tanggal_penerimaan' => 'required|date',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_pegawai_penerima' => 'required|exists:master_pegawai,id',
            'status_penerimaan' => 'required|in:DITERIMA,DITOLAK',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_diterima' => 'required|numeric|min:0',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $penerimaan->status_penerimaan;

            // Update penerimaan
            $penerimaan->update([
                'tanggal_penerimaan' => $validated['tanggal_penerimaan'],
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_pegawai_penerima' => $validated['id_pegawai_penerima'],
                'status_penerimaan' => $validated['status_penerimaan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Delete existing details
            $penerimaan->detailPenerimaan()->delete();

            // Create new details
            foreach ($validated['detail'] as $detail) {
                DetailPenerimaanBarang::create([
                    'id_penerimaan' => $penerimaan->id_penerimaan,
                    'id_inventory' => $detail['id_inventory'],
                    'qty_diterima' => $detail['qty_diterima'],
                    'id_satuan' => $detail['id_satuan'],
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }

            // Update status distribusi
            if ($validated['status_penerimaan'] === 'DITERIMA') {
                $penerimaan->distribusi->update(['status_distribusi' => 'selesai']);

                $this->permintaanBarangStatus->syncAfterDistribusiSelesai($penerimaan->distribusi->fresh());

                // Jika status berubah dari DITOLAK ke DITERIMA, auto-create RegisterAset
                if ($oldStatus !== 'DITERIMA') {
                    $this->autoCreateRegisterAset($penerimaan, $penerimaan->distribusi, $validated);
                }

                if ($penerimaan->id_distribusi) {
                    PenerimaanDistribusiInventoryTransferService::transferPersediaanFarmasiToGudangTujuanAfterDiterima(
                        $penerimaan->fresh(['detailPenerimaan']),
                        $penerimaan->distribusi
                    );
                }
                DataStockSyncService::syncFromInventory();
            } else {
                $penerimaan->distribusi->update(['status_distribusi' => 'dikirim']);
            }

            DB::commit();

            return redirect()->route('transaction.penerimaan-barang.index')
                ->with('success', 'Penerimaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating penerimaan barang: '.$e->getMessage());

            return back()->withInput()->with('error', SafeUserMessage::operationFailed('memperbarui data'));
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Check permission untuk menghapus
        if (! PermissionHelper::canAccess($user, 'transaction.penerimaan-barang.destroy')) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data penerimaan barang.');
        }

        $penerimaan = PenerimaanBarang::findOrFail($id);

        $this->authorizePenerimaanAccess($penerimaan);

        if ($penerimaan->status_penerimaan === 'DITERIMA') {
            return redirect()->route('transaction.penerimaan-barang.index')
                ->with('error', 'Penerimaan yang sudah disahkan (diterima) tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            // Kembalikan status distribusi ke DIKIRIM
            $penerimaan->distribusi->update(['status_distribusi' => 'dikirim']);

            $penerimaan->detailPenerimaan()->delete();
            $penerimaan->delete();

            DB::commit();

            return redirect()->route('transaction.penerimaan-barang.index')
                ->with('success', 'Penerimaan barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting penerimaan barang: '.$e->getMessage());

            return redirect()->route('transaction.penerimaan-barang.index')
                ->with('error', SafeUserMessage::operationFailed('menghapus data'));
        }
    }

    public function getDistribusiDetail($id)
    {
        $distribusi = TransaksiDistribusi::with([
            'detailDistribusi.inventory.dataBarang',
            'detailDistribusi.inventory.gudang', // Eager load gudang untuk mendapatkan kategori_gudang
            'detailDistribusi.satuan',
            'gudangTujuan.unitKerja',
        ])->findOrFail($id);

        UserScope::assertCanAccessDistribusi(Auth::user(), $distribusi);

        $details = $distribusi->detailDistribusi->map(function ($detail) {
            $inventory = $detail->inventory;
            $kategoriGudang = $inventory->gudang->kategori_gudang ?? null;
            $isAset = $kategoriGudang === 'ASET';
            $isFarmasiPersediaan = in_array($kategoriGudang, ['FARMASI', 'PERSEDIAAN']);

            // Untuk ASET, ambil nomor seri dari inventory_item
            $noSeriList = [];
            if ($isAset) {
                $inventoryItems = InventoryItem::where('id_inventory', $inventory->id_inventory)
                    ->where('id_gudang', $inventory->id_gudang)
                    ->where('status_item', 'AKTIF')
                    ->limit((int) $detail->qty_distribusi)
                    ->get();
                $noSeriList = $inventoryItems->pluck('no_seri')->filter()->unique()->values();
            }

            return [
                'id_inventory' => $detail->id_inventory,
                'nama_barang' => $inventory->dataBarang->nama_barang ?? '-',
                'merk' => $inventory->merk ?: '-',
                'tipe' => $inventory->tipe ?: '-',
                'jenis_barang' => $inventory->jenis_barang ?? '-',
                'qty_distribusi' => $detail->qty_distribusi,
                'id_satuan' => $detail->id_satuan,
                'nama_satuan' => $detail->satuan->nama_satuan ?? '-',
                'kategori_gudang' => $kategoriGudang,
                'no_batch' => $isFarmasiPersediaan ? ($inventory->no_batch ?? null) : null,
                'tanggal_kedaluwarsa' => $isFarmasiPersediaan && $inventory->tanggal_kedaluwarsa ? $inventory->tanggal_kedaluwarsa->format('d/m/Y') : null,
                'no_seri' => $isAset ? ($noSeriList->count() > 0 ? $noSeriList->toArray() : ($inventory->no_seri ?? null)) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'distribusi' => [
                'id_distribusi' => $distribusi->id_distribusi,
                'no_sbbk' => $distribusi->no_sbbk,
                'gudang_tujuan' => $distribusi->gudangTujuan->nama_gudang ?? '-',
                'unit_kerja' => $distribusi->gudangTujuan->unitKerja->id_unit_kerja ?? null,
            ],
            'details' => $details,
        ]);
    }

    /**
     * Auto create RegisterAset saat penerimaan ASET dikonfirmasi.
     * - Anti double-register per id_item
     * - Pindah/split data_inventory ke gudang tujuan
     * - Pindah inventory_item ke gudang tujuan (id_ruangan tetap null → KIR via edit Register)
     */
    protected function autoCreateRegisterAset($penerimaan, $distribusi, $validated)
    {
        $gudangTujuan = $distribusi->gudangTujuan;
        $unitKerjaId = $gudangTujuan->id_unit_kerja ?? null;

        if (! $unitKerjaId) {
            \Log::warning('Gudang tujuan tidak memiliki unit kerja untuk auto-create RegisterAset', [
                'distribusi_id' => $distribusi->id_distribusi,
                'gudang_tujuan_id' => $distribusi->id_gudang_tujuan,
            ]);

            return;
        }

        $mutation = app(DistribusiStockMutationService::class);
        $idAsal = (int) $distribusi->id_gudang_asal;
        $idTujuan = (int) $distribusi->id_gudang_tujuan;
        $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
        $context = "penerimaan {$penerimaan->no_penerimaan}";

        foreach ($validated['detail'] as $detail) {
            $inventory = DataInventory::query()->find($detail['id_inventory']);

            if (! $inventory || $inventory->jenis_inventory !== 'ASET') {
                continue;
            }

            $qtyNeeded = (int) ceil((float) $detail['qty_diterima']);
            if ($qtyNeeded <= 0) {
                continue;
            }

            $relocated = $mutation->relocateAsetInventoryOnDiterima(
                $inventory,
                (float) $detail['qty_diterima'],
                $idTujuan,
                $context
            );

            if ((int) $relocated->id_inventory !== (int) $detail['id_inventory']) {
                DetailPenerimaanBarang::query()
                    ->where('id_penerimaan', $penerimaan->id_penerimaan)
                    ->where('id_inventory', $detail['id_inventory'])
                    ->update(['id_inventory' => $relocated->id_inventory]);

                if ($distribusi->id_distribusi) {
                    $distribusi->detailDistribusi()
                        ->where('id_inventory', $detail['id_inventory'])
                        ->update(['id_inventory' => $relocated->id_inventory]);
                }
            }

            $inventoryItemsQuery = InventoryItem::query()
                ->whereIn('id_inventory', array_unique([
                    (int) $inventory->id_inventory,
                    (int) $relocated->id_inventory,
                ]))
                ->whereIn('id_gudang', array_unique([$idAsal, $idTujuan]))
                ->where('status_item', 'AKTIF')
                ->orderBy('id_item');

            if ($hasIdItemColumn) {
                $inventoryItemsQuery->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('register_aset')
                        ->whereColumn('register_aset.id_item', 'inventory_item.id_item');
                });
            } else {
                $inventoryItemsQuery->whereDoesntHave('registerAset');
            }

            $inventoryItems = $inventoryItemsQuery->limit($qtyNeeded)->get();

            if ($inventoryItems->count() < $qtyNeeded) {
                throw new RuntimeException(
                    "Item aset tidak cukup untuk {$context}. Dibutuhkan {$qtyNeeded}, tersedia {$inventoryItems->count()} (inventory #{$inventory->id_inventory})."
                );
            }

            foreach ($inventoryItems as $item) {
                if ($hasIdItemColumn && DistribusiStockMutationService::registerExistsForItem((int) $item->id_item)) {
                    continue;
                }

                $item->update([
                    'id_gudang' => $idTujuan,
                    'id_inventory' => $relocated->id_inventory,
                ]);

                $nomorRegister = $this->generateNomorRegisterForPenerimaan(
                    $unitKerjaId,
                    null,
                    $validated['tanggal_penerimaan']
                );

                $registerData = [
                    'id_inventory' => $relocated->id_inventory,
                    'id_unit_kerja' => $unitKerjaId,
                    'id_ruangan' => null,
                    'nomor_register' => $nomorRegister,
                    'kondisi_aset' => $item->kondisi_item ?? 'BAIK',
                    'status_aset' => $item->status_item === 'AKTIF' ? 'AKTIF' : 'NONAKTIF',
                    'tanggal_perolehan' => $validated['tanggal_penerimaan'],
                ];

                if ($hasIdItemColumn) {
                    $registerData['id_item'] = $item->id_item;
                }

                RegisterAset::create($registerData);
            }
        }
    }

    /**
     * Generate nomor register otomatis untuk penerimaan barang
     * Format: [ID_UNIT_KERJA]/[ID_RUANGAN]/[URUT]
     * Jika tidak ada ruangan: [ID_UNIT_KERJA]/[URUT]
     */
    protected function generateNomorRegisterForPenerimaan($idUnitKerja, $idRuangan = null, $tanggalPenerimaan = null)
    {
        $tahun = $tanggalPenerimaan ? date('Y', strtotime($tanggalPenerimaan)) : date('Y');

        // Format baru: ID_UNIT_KERJA/ID_RUANGAN/URUT atau ID_UNIT_KERJA/URUT
        if ($idRuangan) {
            $prefix = sprintf('%03d/%03d', $idUnitKerja, $idRuangan);
        } else {
            $prefix = sprintf('%03d', $idUnitKerja);
        }

        // Cari nomor urut terakhir untuk kombinasi unit kerja + ruangan + tahun ini
        $lastRegister = RegisterAset::where('id_unit_kerja', $idUnitKerja)
            ->where(function ($q) use ($idRuangan) {
                if ($idRuangan) {
                    $q->where('id_ruangan', $idRuangan);
                } else {
                    $q->whereNull('id_ruangan');
                }
            })
            ->whereYear('tanggal_perolehan', $tahun)
            ->where('nomor_register', 'like', $prefix.'/%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_register, "/", -1) AS UNSIGNED) DESC')
            ->first();

        $urut = 1;
        if ($lastRegister) {
            // Extract nomor urut dari nomor register terakhir
            $parts = explode('/', $lastRegister->nomor_register);
            $lastUrut = (int) end($parts);
            $urut = $lastUrut + 1;
        }

        return sprintf('%s/%04d', $prefix, $urut);
    }

    /**
     * Pegawai/kepala unit hanya boleh mengakses penerimaan unit kerjanya; admin & admin gudang boleh semua.
     */
    private function authorizePenerimaanAccess(PenerimaanBarang $penerimaan): void
    {
        $user = Auth::user();
        if (UserScope::canViewCrossUnitData($user)) {
            return;
        }

        if (RbacRoles::userHasWarehousePusatAccess($user)) {
            return;
        }

        if (UserScope::mustScopeToUnitKerja($user)) {
            $unitId = UserScope::unitKerjaId($user);
            if ($unitId !== null && (int) $unitId === (int) $penerimaan->id_unit_kerja) {
                return;
            }
        }

        abort(403, 'Anda tidak dapat mengakses penerimaan untuk unit kerja ini.');
    }
}
