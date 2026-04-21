<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuInventarisRuangan;
use App\Models\RegisterAset;
use App\Models\MasterRuangan;
use App\Models\MasterPegawai;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class KartuInventarisRuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Query InventoryItem yang memiliki RegisterAset dengan ruangan (untuk KIR)
        // KIR = RegisterAset yang memiliki id_ruangan (tidak null)
        $query = \App\Models\InventoryItem::with([
            'inventory.dataBarang',
            'inventory.gudang.unitKerja',
            'ruangan.unitKerja',
            'gudang.unitKerja',
            'inventory.registerAset' => function($q) {
                $q->whereNotNull('id_ruangan')
                  ->with(['kartuInventarisRuangan.penanggungJawab', 'ruangan.unitKerja']);
            }
        ])->whereHas('inventory', function($q) {
            $q->where('jenis_inventory', 'ASET')
              ->whereHas('registerAset', function($registerQ) {
                  // Hanya tampilkan InventoryItem yang memiliki RegisterAset dengan ruangan
                  $registerQ->whereNotNull('id_ruangan');
              });
        });
        
        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query->whereHas('inventory.registerAset', function($q) use ($pegawai) {
                    $q->whereHas('ruangan', function($ruanganQ) use ($pegawai) {
                        $ruanganQ->where('id_unit_kerja', $pegawai->id_unit_kerja);
                    });
                });
            } else {
                $query->whereRaw('1 = 0'); // Tidak ada data jika user tidak punya unit kerja
            }
        }
        
        // Filter berdasarkan ruangan jika ada
        if ($request->filled('id_ruangan')) {
            $query->whereHas('inventory.registerAset', function($q) use ($request) {
                $q->where('id_ruangan', $request->id_ruangan);
            });
        }
        
        // Filter berdasarkan unit kerja jika ada
        if ($request->filled('id_unit_kerja')) {
            $query->whereHas('inventory.registerAset', function($q) use ($request) {
                $q->whereHas('ruangan', function($ruanganQ) use ($request) {
                    $ruanganQ->where('id_unit_kerja', $request->id_unit_kerja);
                });
            });
        }
        
        // Urutkan berdasarkan kode_register
        $query->orderBy('kode_register');
        
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 15);
        $inventoryItems = $query->paginate($perPage)->appends($request->query());
        
        // Preload KIR berdasarkan RegisterAset yang memiliki ruangan
        $inventoryIds = $inventoryItems->pluck('id_inventory')->unique()->toArray();
        
        // Ambil semua RegisterAset yang memiliki ruangan untuk inventory ini
        $registerAsets = RegisterAset::whereIn('id_inventory', $inventoryIds)
            ->whereNotNull('id_ruangan')
            ->with('kartuInventarisRuangan.penanggungJawab', 'ruangan')
            ->get();
        
        // Buat map untuk akses cepat: id_register_aset => KIR
        $kirs = $registerAsets->keyBy(function($registerAset) {
            return $registerAset->id_register_aset;
        })->map(function($registerAset) {
            return $registerAset->kartuInventarisRuangan->first();
        })->filter();
        
        // Data untuk filter
        $ruangans = MasterRuangan::with('unitKerja')->orderBy('nama_ruangan')->get();
        
        return view('asset.kartu-inventaris-ruangan.index', compact('inventoryItems', 'ruangans', 'kirs'));
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
        if ($registerAset->inventory) {
            \App\Models\InventoryItem::where('id_inventory', $registerAset->inventory->id_inventory)
                ->update(['id_ruangan' => $validated['id_ruangan']]);
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
                if ($registerAset->inventory) {
                    \App\Models\InventoryItem::where('id_inventory', $registerAset->inventory->id_inventory)
                        ->update(['id_ruangan' => $validated['id_ruangan']]);
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
            if ($registerAset->inventory) {
                \App\Models\InventoryItem::where('id_inventory', $registerAset->inventory->id_inventory)
                    ->update(['id_ruangan' => null]);
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
}
