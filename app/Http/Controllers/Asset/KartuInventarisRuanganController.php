<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuInventarisRuangan;
use App\Models\RegisterAset;
use App\Models\MasterRuangan;
use App\Models\MasterPegawai;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KartuInventarisRuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // KIR index = daftar dokumen KIR per unit kerja (siap cetak).
        $summaryQuery = RegisterAset::query()
            ->whereNotNull('id_unit_kerja')
            ->whereNotNull('id_ruangan')
            ->whereHas('kartuInventarisRuangan')
            ->selectRaw('id_unit_kerja, COUNT(*) as total_item, MAX(updated_at) as last_update')
            ->groupBy('id_unit_kerja');

        if ($request->filled('id_unit_kerja')) {
            $summaryQuery->where('id_unit_kerja', $request->id_unit_kerja);
        }

        // Kepala unit/pegawai hanya melihat dokumen unit kerjanya.
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $summaryQuery->where('id_unit_kerja', $pegawai->id_unit_kerja);
            } else {
                $summaryQuery->whereRaw('1 = 0');
            }
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 15);
        $summaries = $summaryQuery
            ->orderByDesc('last_update')
            ->paginate($perPage)
            ->appends($request->query());

        $unitKerjaIds = collect($summaries->items())->pluck('id_unit_kerja')->all();
        $units = \App\Models\MasterUnitKerja::query()
            ->whereIn('id_unit_kerja', $unitKerjaIds)
            ->orderBy('nama_unit_kerja')
            ->get()
            ->keyBy('id_unit_kerja');

        $gudangByUnit = \App\Models\MasterGudang::query()
            ->where('jenis_gudang', 'UNIT')
            ->whereIn('id_unit_kerja', $unitKerjaIds)
            ->get()
            ->keyBy('id_unit_kerja');

        $ruanganByUnit = RegisterAset::query()
            ->whereIn('id_unit_kerja', $unitKerjaIds)
            ->whereNotNull('id_ruangan')
            ->with('ruangan:id_ruangan,nama_ruangan')
            ->get()
            ->groupBy('id_unit_kerja')
            ->map(function ($items) {
                return $items
                    ->pluck('ruangan.nama_ruangan')
                    ->filter()
                    ->unique()
                    ->values();
            });

        $unitOptions = \App\Models\MasterUnitKerja::query()
            ->orderBy('nama_unit_kerja')
            ->get(['id_unit_kerja', 'nama_unit_kerja']);

        return view('asset.kartu-inventaris-ruangan.index', compact('summaries', 'units', 'gudangByUnit', 'ruanganByUnit', 'unitOptions'));
    }

    public function dokumenUnitKerja(Request $request, int $idUnitKerja)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if (!$pegawai || (int) $pegawai->id_unit_kerja !== $idUnitKerja) {
                abort(403, 'Unauthorized - Anda hanya dapat melihat dokumen KIR unit kerja Anda sendiri');
            }
        }

        $unitKerja = \App\Models\MasterUnitKerja::findOrFail($idUnitKerja);
        $rows = KartuInventarisRuangan::query()
            ->with([
                'registerAset.inventory.dataBarang',
                'registerAset.inventory.satuan',
                'registerAset.inventoryItem',
                'registerAset',
                'ruangan',
                'penanggungJawab',
            ])
            ->whereHas('registerAset', function ($q) use ($idUnitKerja) {
                $q->where('id_unit_kerja', $idUnitKerja)
                    ->whereNotNull('id_ruangan');
            })
            ->orderBy('tanggal_penempatan')
            ->orderBy('id_kir')
            ->get();

        $payload = [
            'unitKerja' => $unitKerja,
            'rows' => $rows,
            'printMode' => $request->boolean('print'),
            'downloadMode' => $request->boolean('download'),
            'signatories' => $this->resolveKirSignatories($idUnitKerja),
        ];

        if ($request->boolean('download')) {
            $html = view('asset.kartu-inventaris-ruangan.document-unit', $payload)->render();
            $filename = 'dokumen-kir-unit-'.preg_replace('/[^A-Za-z0-9_-]+/', '-', strtolower($unitKerja->nama_unit_kerja)).'.html';

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        return view('asset.kartu-inventaris-ruangan.document-unit', $payload);
    }

    private function resolveKirSignatories(int $idUnitKerja): array
    {
        $pegawaiQuery = MasterPegawai::query()->with('jabatan.role');

        $kepalaPusat = (clone $pegawaiQuery)
            ->whereHas('jabatan.role', function ($q) {
                $q->where('name', 'kepala_pusat');
            })
            ->orderBy('nama_pegawai')
            ->first();

        if (!$kepalaPusat) {
            $kepalaPusat = (clone $pegawaiQuery)
                ->whereHas('jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%kepala pusat%');
                })
                ->orderBy('nama_pegawai')
                ->first();
        }

        $pengurusBarang = (clone $pegawaiQuery)
            ->whereHas('jabatan.role', function ($q) {
                $q->whereIn('name', ['admin_gudang', 'admin_gudang_aset', 'admin_gudang_persediaan', 'admin_gudang_farmasi', 'admin_gudang_unit']);
            })
            ->orderBy('nama_pegawai')
            ->first();

        if (!$pengurusBarang) {
            $pengurusBarang = (clone $pegawaiQuery)
                ->whereHas('jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%pengurus barang%')
                        ->orWhere('nama_jabatan', 'like', '%admin gudang%');
                })
                ->orderBy('nama_pegawai')
                ->first();
        }

        $kepalaUnit = (clone $pegawaiQuery)
            ->where('id_unit_kerja', $idUnitKerja)
            ->whereHas('jabatan.role', function ($q) {
                $q->where('name', 'kepala_unit');
            })
            ->orderBy('nama_pegawai')
            ->first();

        if (!$kepalaUnit) {
            $kepalaUnit = (clone $pegawaiQuery)
                ->where('id_unit_kerja', $idUnitKerja)
                ->whereHas('jabatan', function ($q) {
                    $q->where('nama_jabatan', 'like', '%kepala unit%');
                })
                ->orderBy('nama_pegawai')
                ->first();
        }

        return [
            'kepala_pusat' => $kepalaPusat,
            'pengurus_barang' => $pengurusBarang,
            'kepala_unit' => $kepalaUnit,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        // Hanya admin dan admin_gudang yang bisa create
        if (!$user->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Register aset yang belum punya KIR — ruangan & pegawai diisi per id_unit_kerja register (lihat API + JS di view)
        $registerAsets = RegisterAset::with(['inventory.dataBarang', 'unitKerja'])
            ->where('status_aset', 'AKTIF')
            ->whereDoesntHave('kartuInventarisRuangan')
            ->orderBy('nomor_register')
            ->get();

        return view('asset.kartu-inventaris-ruangan.create', compact('registerAsets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Hanya admin dan admin_gudang yang bisa store
        if (!$user->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Validasi input
        $validated = $request->validate([
            'id_register_aset' => 'required|exists:register_aset,id_register_aset',
            'id_ruangan' => 'required|exists:master_ruangan,id_ruangan',
            'id_penanggung_jawab' => 'required|exists:master_pegawai,id',
            'tanggal_penempatan' => 'required|date',
        ]);
        
        // Cek apakah register aset sudah punya KIR
        $existingKIR = KartuInventarisRuangan::where('id_register_aset', $validated['id_register_aset'])->first();
        if ($existingKIR) {
            return back()->withErrors(['id_register_aset' => 'Register aset ini sudah memiliki KIR. Gunakan Mutasi Aset untuk memindahkan.'])->withInput();
        }
        
        $registerAset = RegisterAset::findOrFail($validated['id_register_aset']);

        $unitId = (int) $registerAset->id_unit_kerja;
        if (! $this->ruanganDiUnitKerja($validated['id_ruangan'], $unitId)) {
            return back()->withErrors(['id_ruangan' => 'Ruangan harus sesuai unit kerja register aset (master ruangan).'])->withInput();
        }
        if (! $this->pegawaiDiUnitKerja($validated['id_penanggung_jawab'], $unitId)) {
            return back()->withErrors(['id_penanggung_jawab' => 'Pegawai harus sesuai unit kerja register aset (master pegawai).'])->withInput();
        }

        // Sinkronkan ruangan ke Register Aset dan InventoryItem
        $registerAset->update(['id_ruangan' => $validated['id_ruangan']]);
        $inventoryItem = $this->resolveInventoryItemForRegisterAset($registerAset);
        if ($inventoryItem) {
            $inventoryItem->update(['id_ruangan' => $validated['id_ruangan']]);
        }
        
        $kir = KartuInventarisRuangan::create($validated);
        
        return redirect()->route('asset.kartu-inventaris-ruangan.show', $kir->id_kir)
            ->with('success', 'Kartu Inventaris Ruangan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kir = KartuInventarisRuangan::with([
            'registerAset.inventory.dataBarang',
            'registerAset.inventory.inventoryItems',
            'registerAset.unitKerja',
            'registerAset.ruangan',
            'ruangan.unitKerja',
            'penanggungJawab.unitKerja',
            'penanggungJawab.jabatan'
        ])->findOrFail($id);
        
        /** @var User $user */
        $user = Auth::user();
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $idUnitKerjaKir = $kir->ruangan?->id_unit_kerja;
                if ($idUnitKerjaKir === null || $idUnitKerjaKir != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat KIR dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        return view('asset.kartu-inventaris-ruangan.show', compact('kir'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kir = KartuInventarisRuangan::with(['registerAset', 'ruangan', 'penanggungJawab'])->findOrFail($id);
        /** @var User $user */
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa edit
        if (!$user->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $idUnitKerjaKir = $kir->ruangan?->id_unit_kerja;
                if ($idUnitKerjaKir === null || $idUnitKerjaKir != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengedit KIR dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        $kir->loadMissing('registerAset');
        $unitId = (int) ($kir->registerAset?->id_unit_kerja ?? 0);

        $ruangans = $unitId > 0
            ? MasterRuangan::with('unitKerja')->where('id_unit_kerja', $unitId)->orderBy('nama_ruangan')->get()
            : collect();

        $pegawais = $unitId > 0
            ? MasterPegawai::with('unitKerja')->where('id_unit_kerja', $unitId)->orderBy('nama_pegawai')->get()
            : collect();

        return view('asset.kartu-inventaris-ruangan.edit', compact('kir', 'ruangans', 'pegawais'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kir = KartuInventarisRuangan::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();
        
        // Hanya admin dan admin_gudang yang bisa update
        if (!$user->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $idUnitKerjaKir = $kir->ruangan?->id_unit_kerja;
                if ($idUnitKerjaKir === null || $idUnitKerjaKir != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengupdate KIR dari unit kerja Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        
        // Validasi input
        $validated = $request->validate([
            'id_ruangan' => 'required|exists:master_ruangan,id_ruangan',
            'id_penanggung_jawab' => 'required|exists:master_pegawai,id',
            'tanggal_penempatan' => 'required|date',
        ]);

        $kir->load('registerAset');
        $unitId = (int) ($kir->registerAset?->id_unit_kerja ?? 0);
        if ($unitId === 0) {
            return back()->withErrors(['id_ruangan' => 'Register aset tidak memiliki unit kerja.'])->withInput();
        }
        if (! $this->ruanganDiUnitKerja($validated['id_ruangan'], $unitId)) {
            return back()->withErrors(['id_ruangan' => 'Ruangan harus sesuai unit kerja register aset (master ruangan).'])->withInput();
        }
        if (! $this->pegawaiDiUnitKerja($validated['id_penanggung_jawab'], $unitId)) {
            return back()->withErrors(['id_penanggung_jawab' => 'Pegawai harus sesuai unit kerja register aset (master pegawai).'])->withInput();
        }

        // Jika ruangan berubah, sinkronkan ke Register Aset dan InventoryItem
        if ($kir->id_ruangan != $validated['id_ruangan']) {
            $registerAset = $kir->registerAset;
            if ($registerAset) {
                $registerAset->update(['id_ruangan' => $validated['id_ruangan']]);
                $inventoryItem = $this->resolveInventoryItemForRegisterAset($registerAset);
                if ($inventoryItem) {
                    $inventoryItem->update(['id_ruangan' => $validated['id_ruangan']]);
                }
            }
        }
        
        $kir->update($validated);
        
        return redirect()->route('asset.kartu-inventaris-ruangan.show', $kir->id_kir)
            ->with('success', 'Kartu Inventaris Ruangan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // Hanya admin dan admin_gudang yang bisa delete
        if (!$user->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        $kir = KartuInventarisRuangan::findOrFail($id);
        $registerAset = $kir->registerAset;
        
        // Lepas ruangan dari Register Aset dan InventoryItem
        if ($registerAset) {
            $registerAset->update(['id_ruangan' => null]);
            $inventoryItem = $this->resolveInventoryItemForRegisterAset($registerAset);
            if ($inventoryItem) {
                $inventoryItem->update(['id_ruangan' => null]);
            }
        }
        
        $kir->delete();
        
        return redirect()->route('asset.kartu-inventaris-ruangan.index')
            ->with('success', 'Kartu Inventaris Ruangan berhasil dihapus.');
    }

    private function ruanganDiUnitKerja(int|string $idRuangan, int $idUnitKerja): bool
    {
        return MasterRuangan::query()
            ->where('id_ruangan', $idRuangan)
            ->where('id_unit_kerja', $idUnitKerja)
            ->exists();
    }

    private function pegawaiDiUnitKerja(int|string $idPegawai, int $idUnitKerja): bool
    {
        return MasterPegawai::query()
            ->where('id', $idPegawai)
            ->where('id_unit_kerja', $idUnitKerja)
            ->exists();
    }

    private function resolveInventoryItemForRegisterAset(RegisterAset $registerAset): ?InventoryItem
    {
        $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
        if ($hasIdItemColumn && !empty($registerAset->id_item)) {
            return InventoryItem::query()->find($registerAset->id_item);
        }

        // Fallback untuk data lama (sebelum kolom id_item dipakai penuh).
        if (!$registerAset->id_inventory) {
            return null;
        }

        return InventoryItem::query()
            ->where('id_inventory', $registerAset->id_inventory)
            ->orderBy('id_item')
            ->first();
    }
}
