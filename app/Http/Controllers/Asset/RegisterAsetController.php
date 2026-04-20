<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\RegisterAset;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\MasterGudang;
use App\Models\MasterRuangan;
use App\Models\DataInventory;
use Illuminate\Support\Facades\Auth;

class RegisterAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filter untuk user pegawai - hanya gudang unit mereka sendiri
        // Admin gudang dan pengurus barang bisa melihat semua gudang unit
        $pegawai = null;
        $userUnitKerjaId = null;
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasAnyRole(['admin', 'admin_gudang', 'pengurus_barang'])) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $userUnitKerjaId = $pegawai->id_unit_kerja;
            }
        }
        
        // Ambil semua gudang unit yang memiliki RegisterAset atau InventoryItem dengan jenis ASET
        // Ambil unit kerja yang punya RegisterAset
        $unitKerjaIdsWithRegisterAset = RegisterAset::whereHas('inventory', function($q) {
                $q->where('jenis_inventory', 'ASET');
            })
            ->pluck('id_unit_kerja')
            ->unique()
            ->filter()
            ->toArray();
        
        // Ambil gudang unit yang:
        // 1. Punya InventoryItem dengan jenis ASET, ATAU
        // 2. Unit kerjanya punya RegisterAset
        $gudangUnits = MasterGudang::where('jenis_gudang', 'UNIT')
            ->where(function($q) use ($unitKerjaIdsWithRegisterAset) {
                // Gudang yang punya InventoryItem dengan jenis ASET
                $q->whereHas('inventoryItems', function($q2) {
                    $q2->whereHas('inventory', function($q3) {
                        $q3->where('jenis_inventory', 'ASET');
                    });
                });
                
                // ATAU gudang yang unit kerjanya punya RegisterAset
                if (!empty($unitKerjaIdsWithRegisterAset)) {
                    $q->orWhereIn('id_unit_kerja', $unitKerjaIdsWithRegisterAset);
                }
            })
            ->when($userUnitKerjaId, function($q) use ($userUnitKerjaId) {
                $q->where('id_unit_kerja', $userUnitKerjaId);
            })
            ->with('unitKerja')
            ->get();
        
        // Hitung KIR untuk setiap gudang unit (hanya KIR, tidak perlu KIB)
        foreach ($gudangUnits as $gudang) {
            // KIR: Hanya RegisterAset yang SUDAH ter-register dan memiliki ruangan di unit kerja ini
            // Tidak menghitung aset yang belum ter-register
            $kirCount = RegisterAset::where('id_unit_kerja', $gudang->id_unit_kerja)
                ->whereNotNull('id_ruangan')
                ->count();
            
            $gudang->kir_count = $kirCount;
            $gudang->kib_count = 0; // Tidak perlu KIB untuk gudang unit
            $gudang->total_aset = $kirCount; // Total = KIR untuk gudang unit
        }
        
        // Ambil data gudang pusat (KIB saja) - HANYA untuk admin, admin_gudang, pengurus_barang, bukan untuk pegawai
        $gudangPusatData = null;
        if (!$userUnitKerjaId || $user->hasAnyRole(['admin', 'admin_gudang', 'pengurus_barang'])) {
            $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')
                ->where('kategori_gudang', 'ASET')
                ->first();
            
            if ($gudangPusat) {
                // KIB untuk gudang pusat: Total SEMUA aset di gudang pusat
                // Termasuk:
                // 1. InventoryItem yang BELUM ter-register (belum punya RegisterAset)
                // 2. InventoryItem yang SUDAH ter-register (sudah punya RegisterAset tapi masih di gudang pusat)
                
                // Hitung SEMUA InventoryItem yang masih di gudang pusat
                // TIDAK ada filter untuk status register - menghitung semua aset
                $kibCount = \App\Models\InventoryItem::whereHas('inventory', function($q) use ($gudangPusat) {
                        $q->where('id_gudang', $gudangPusat->id_gudang)
                          ->where('jenis_inventory', 'ASET');
                    })
                    ->where('id_gudang', $gudangPusat->id_gudang)
                    ->count();
                
                // Untuk gudang pusat: hanya KIB saja, tidak perlu KIR
                $kirCount = 0;
                
                $gudangPusatData = [
                    'id' => 'pusat',
                    'nama' => $gudangPusat->nama_gudang,
                    'total_aset' => $kibCount,
                    'kib_count' => $kibCount,
                    'kir_count' => $kirCount,
                ];
            }
        }
        
        return view('asset.register-aset.index', compact('gudangUnits', 'gudangPusatData'));
    }

    /**
     * Display register aset by unit kerja.
     */
    public function showUnitKerja(Request $request, $unitKerjaId)
    {
        $user = Auth::user();
        
        // Inisialisasi variabel
        $isPusat = false;
        $gudangUnit = null;
        $unitKerjas = collect([]);
        $ruangans = collect([]);
        
        // Jika 'pusat', ambil data gudang pusat
        if ($unitKerjaId == 'pusat') {
            $gudangPusat = MasterGudang::where('jenis_gudang', 'PUSAT')
                ->where('kategori_gudang', 'ASET')
                ->firstOrFail();
            
            // Untuk gudang pusat, ambil data dari InventoryItem (hasil auto add row)
            // KIB: InventoryItem yang id_gudang = gudang pusat atau belum didistribusikan
            // Hanya ambil yang belum di-register ke unit kerja
            // Logika: Ambil semua InventoryItem yang masih di gudang pusat
            // Lalu filter berdasarkan jumlah RegisterAset per id_inventory
            $allInventoryItems = \App\Models\InventoryItem::with([
                'inventory.dataBarang',
                'inventory.gudang.unitKerja',
                'gudang.unitKerja',
                'ruangan.unitKerja'
            ])->whereHas('inventory', function($q) use ($gudangPusat) {
                $q->where('jenis_inventory', 'ASET');
            });
            
            // Untuk KIB di gudang pusat: tampilkan SEMUA InventoryItem (baik yang sudah ter-register maupun belum)
            $query = \App\Models\InventoryItem::with([
                'inventory.dataBarang',
                'inventory.gudang.unitKerja',
                'gudang.unitKerja',
                'ruangan.unitKerja',
                'registerAset' // Load RegisterAset untuk menentukan badge KIB/KIR
            ])->whereHas('inventory', function($q) use ($gudangPusat) {
                $q->where('jenis_inventory', 'ASET');
            })->where(function($q) use ($gudangPusat) {
                $q->where('id_gudang', $gudangPusat->id_gudang)
                  ->orWhereNull('id_gudang'); // Belum didistribusikan
            });
            
            // Filter berdasarkan Unit Kerja (jika dipilih)
            if ($request->filled('filter_unit_kerja')) {
                $filterUnitKerjaId = $request->filter_unit_kerja;
                // Filter berdasarkan gudang yang memiliki unit kerja tersebut
                $gudangIds = MasterGudang::where('id_unit_kerja', $filterUnitKerjaId)
                    ->where('kategori_gudang', 'ASET')
                    ->pluck('id_gudang')
                    ->toArray();
                
                // Filter InventoryItem yang terkait dengan unit kerja tersebut
                $query->where(function($q) use ($gudangPusat, $gudangIds, $filterUnitKerjaId) {
                    // Aset yang masih di gudang pusat (belum didistribusikan) atau sudah ter-register ke unit kerja tersebut
                    $q->where(function($subQ) use ($gudangPusat, $filterUnitKerjaId) {
                        $subQ->where('id_gudang', $gudangPusat->id_gudang)
                             ->where(function($regQ) use ($filterUnitKerjaId) {
                                 // Belum ter-register atau sudah ter-register ke unit kerja tersebut
                                 $regQ->whereDoesntHave('registerAset')
                                      ->orWhereHas('registerAset', function($raQ) use ($filterUnitKerjaId) {
                                          $raQ->where('id_unit_kerja', $filterUnitKerjaId);
                                      });
                             });
                    })
                    // Atau aset yang sudah didistribusikan ke gudang unit tersebut
                    ->orWhereIn('id_gudang', $gudangIds);
                });
            }
            
            // Ambil daftar Unit Kerja yang memiliki aset di gudang pusat
            // Unit kerja yang memiliki RegisterAset dengan inventory di gudang pusat
            $unitKerjas = MasterUnitKerja::whereHas('registerAsets', function($q) use ($gudangPusat) {
                    $q->whereHas('inventory', function($invQ) use ($gudangPusat) {
                        $invQ->where('id_gudang', $gudangPusat->id_gudang)
                             ->where('jenis_inventory', 'ASET');
                    });
                })
                ->orWhereHas('gudang', function($q) {
                    $q->where('kategori_gudang', 'ASET')
                      ->where('jenis_gudang', 'UNIT');
                })
                ->orderBy('nama_unit_kerja')
                ->get();
            
            // Urutkan berdasarkan kode_register
            $query->orderBy('kode_register');
            
            $title = $gudangPusat->nama_gudang;
            $isPusat = true;
            $filter = 'kib'; // Untuk gudang pusat selalu KIB
        } else {
            // Ambil data gudang unit
            $gudangUnit = MasterGudang::where('id_gudang', $unitKerjaId)
                ->where('jenis_gudang', 'UNIT')
                ->with('unitKerja')
                ->firstOrFail();
            
            // Untuk gudang unit, ambil data dari RegisterAset yang id_unit_kerja = unit kerja gudang ini
            // Hanya ambil yang sudah memiliki ruangan (KIR)
            $registerAsetQuery = RegisterAset::where('id_unit_kerja', $gudangUnit->id_unit_kerja)
                ->whereNotNull('id_ruangan'); // Hanya KIR yang ditampilkan
            
            // Filter berdasarkan Ruangan (jika dipilih)
            if ($request->filled('filter_ruangan')) {
                $registerAsetQuery->where('id_ruangan', $request->filter_ruangan);
            }
            
            $registerAsets = $registerAsetQuery->get();
            
            // Ambil daftar Ruangan yang memiliki KIR di unit kerja ini
            // Ambil dari RegisterAset yang memiliki ruangan di unit kerja ini
            $ruanganIds = RegisterAset::where('id_unit_kerja', $gudangUnit->id_unit_kerja)
                ->whereNotNull('id_ruangan')
                ->pluck('id_ruangan')
                ->unique()
                ->toArray();
            
            $ruangans = \App\Models\MasterRuangan::whereIn('id_ruangan', $ruanganIds)
                ->with('unitKerja')
                ->orderBy('nama_ruangan')
                ->get();
            
            if ($registerAsets->isEmpty()) {
                // Jika tidak ada RegisterAset, return empty collection
                $query = \App\Models\InventoryItem::whereRaw('1 = 0');
            } else {
                // Ambil InventoryItem berdasarkan mapping RegisterAset menggunakan id_item
                // Mapping: RegisterAset.id_item -> InventoryItem.id_item (lebih tepat)
                $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
                
                if ($hasIdItemColumn) {
                    $registerAsetItemIds = $registerAsets->pluck('id_item')
                        ->filter() // Hapus null
                        ->unique()
                        ->toArray();
                    
                    if (!empty($registerAsetItemIds)) {
                        // Ambil InventoryItem yang sudah dipilih dengan eager loading
                        $query = \App\Models\InventoryItem::with([
                            'inventory.dataBarang',
                            'inventory.gudang.unitKerja',
                            'inventory.registerAset' => function($q) use ($gudangUnit) {
                                $q->where('id_unit_kerja', $gudangUnit->id_unit_kerja);
                            },
                            'gudang.unitKerja',
                            'ruangan.unitKerja'
                        ])->whereIn('id_item', $registerAsetItemIds);
                    } else {
                        // Jika tidak ada id_item, gunakan fallback
                        $query = \App\Models\InventoryItem::whereRaw('1 = 0');
                    }
                } else {
                    // Fallback untuk data lama yang belum punya id_item: gunakan id_inventory
                    $registerAsetInventoryIds = $registerAsets->pluck('id_inventory')->unique()->toArray();
                    
                    // Ambil semua InventoryItem untuk id_inventory tersebut
                    $allInventoryItems = \App\Models\InventoryItem::whereIn('id_inventory', $registerAsetInventoryIds)
                        ->whereHas('inventory', function($q) {
                            $q->where('jenis_inventory', 'ASET');
                        })
                        ->orderBy('id_item')
                        ->get();
                    
                    // Mapping: untuk setiap RegisterAset, ambil InventoryItem yang sesuai
                    // Strategi: ambil InventoryItem pertama yang belum digunakan untuk id_inventory tersebut
                    $usedItemIds = [];
                    $selectedItemIds = [];
                    
                    foreach ($registerAsets as $registerAset) {
                        // Cari InventoryItem untuk id_inventory ini yang belum digunakan
                        $availableItems = $allInventoryItems->where('id_inventory', $registerAset->id_inventory)
                            ->whereNotIn('id_item', $usedItemIds)
                            ->first();
                        
                        if ($availableItems) {
                            $selectedItemIds[] = $availableItems->id_item;
                            $usedItemIds[] = $availableItems->id_item;
                        }
                    }
                    
                    if (!empty($selectedItemIds)) {
                        // Ambil InventoryItem yang sudah dipilih dengan eager loading
                        $query = \App\Models\InventoryItem::with([
                            'inventory.dataBarang',
                            'inventory.gudang.unitKerja',
                            'inventory.registerAset' => function($q) use ($gudangUnit) {
                                $q->where('id_unit_kerja', $gudangUnit->id_unit_kerja);
                            },
                            'gudang.unitKerja',
                            'ruangan.unitKerja'
                        ])->whereIn('id_item', $selectedItemIds);
                    } else {
                        $query = \App\Models\InventoryItem::whereRaw('1 = 0');
                    }
                }
            }
            
            // Urutkan berdasarkan kode_register
            $query->orderBy('kode_register');
            
            $title = $gudangUnit->nama_gudang . ($gudangUnit->unitKerja ? ' (' . $gudangUnit->unitKerja->nama_unit_kerja . ')' : '');
            $isPusat = false;
            $filter = 'kir'; // Untuk gudang unit selalu KIR
            
            // Filter berdasarkan gudang unit untuk kepala_unit dan pegawai
            if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasAnyRole(['admin', 'admin_gudang', 'pengurus_barang'])) {
                $pegawai = MasterPegawai::where('user_id', $user->id)->first();
                if ($pegawai && $gudangUnit->id_unit_kerja && $pegawai->id_unit_kerja != $gudangUnit->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat aset dari gudang unit Anda sendiri');
                }
            }
        }
        
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $inventoryItems = $query->paginate($perPage)->appends($request->except(['id_gudang', 'page']));
        
        // Preload RegisterAset untuk setiap InventoryItem untuk menghindari N+1 query
        $inventoryItemIds = $inventoryItems->pluck('id_item')->toArray();
        $registerAsetsMap = []; // Map berdasarkan id_inventory -> array of RegisterAset
        $registerAsetItemMap = []; // Map berdasarkan id_item -> RegisterAset (untuk mapping yang lebih tepat)
        
        if (!empty($inventoryItemIds)) {
            // Untuk gudang pusat, ambil semua RegisterAset (tidak filter berdasarkan unit kerja)
            // Untuk gudang unit, ambil RegisterAset berdasarkan unit kerja
            // Ambil RegisterAset berdasarkan id_item (lebih tepat) atau id_inventory (untuk backward compatibility)
            $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
            
            if ($isPusat) {
                // Untuk gudang pusat: hanya ambil RegisterAset yang masih di gudang pusat (belum didistribusikan)
                $registerAsetsQuery = RegisterAset::whereNull('id_unit_kerja');
                
                if ($hasIdItemColumn) {
                    $registerAsetsQuery->where(function($q) use ($inventoryItemIds, $inventoryItems) {
                        // Cari berdasarkan id_item dulu (lebih tepat)
                        $q->whereIn('id_item', $inventoryItemIds)
                          // Atau berdasarkan id_inventory untuk data lama yang belum punya id_item
                          ->orWhereIn('id_inventory', $inventoryItems->pluck('id_inventory')->unique());
                    });
                } else {
                    // Fallback untuk data lama
                    $registerAsetsQuery->whereIn('id_inventory', $inventoryItems->pluck('id_inventory')->unique());
                }
                
                $registerAsets = $registerAsetsQuery->with([
                        'ruangan.unitKerja', 
                        'kartuInventarisRuangan.penanggungJawab',
                        'unitKerja.gudang' => function($q) {
                            $q->where('jenis_gudang', 'UNIT')->where('kategori_gudang', 'ASET');
                        }
                    ])
                    ->orderBy('created_at')
                    ->get();
            } else {
                $unitKerjaIdForQuery = isset($gudangUnit) && $gudangUnit->unitKerja ? $gudangUnit->unitKerja->id_unit_kerja : null;
                $registerAsetsQuery = RegisterAset::where('id_unit_kerja', $unitKerjaIdForQuery);
                
                if ($hasIdItemColumn) {
                    $registerAsetsQuery->where(function($q) use ($inventoryItemIds, $inventoryItems) {
                        // Cari berdasarkan id_item dulu (lebih tepat)
                        $q->whereIn('id_item', $inventoryItemIds)
                          // Atau berdasarkan id_inventory untuk data lama yang belum punya id_item
                          ->orWhereIn('id_inventory', $inventoryItems->pluck('id_inventory')->unique());
                    });
                } else {
                    // Fallback untuk data lama
                    $registerAsetsQuery->whereIn('id_inventory', $inventoryItems->pluck('id_inventory')->unique());
                }
                
                $registerAsets = $registerAsetsQuery->with([
                        'ruangan.unitKerja', 
                        'kartuInventarisRuangan.penanggungJawab',
                        'unitKerja.gudang' => function($q) {
                            $q->where('jenis_gudang', 'UNIT')->where('kategori_gudang', 'ASET');
                        }
                    ])
                    ->orderBy('created_at')
                    ->get();
            }
            
            // Map RegisterAset berdasarkan id_inventory untuk backward compatibility
            foreach ($registerAsets as $register) {
                if ($register->id_inventory) {
                    if (!isset($registerAsetsMap[$register->id_inventory])) {
                        $registerAsetsMap[$register->id_inventory] = collect([]);
                    }
                    $registerAsetsMap[$register->id_inventory]->push($register);
                }
            }
            
            // Buat mapping yang lebih tepat: InventoryItem -> RegisterAset berdasarkan id_item
            // Mapping langsung berdasarkan id_item (lebih tepat dan menghindari masalah multiple register)
            $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
            
            foreach ($inventoryItems as $item) {
                if ($hasIdItemColumn) {
                    // Cari RegisterAset berdasarkan id_item (lebih tepat)
                    $registerAset = RegisterAset::where('id_item', $item->id_item)->first();
                    
                    if ($registerAset) {
                        $registerAsetItemMap[$item->id_item] = $registerAset;
                        continue;
                    }
                }
                
                // Fallback untuk data lama atau jika id_item belum ada
                if (isset($registerAsetsMap[$item->id_inventory])) {
                    // Fallback untuk data lama yang belum punya id_item: mapping berdasarkan urutan
                    // Ambil semua InventoryItem dengan id_inventory yang sama, urutkan berdasarkan id_item
                    $itemsForInventory = $inventoryItems->where('id_inventory', $item->id_inventory)
                        ->sortBy('id_item')
                        ->values();
                    
                    // Ambil semua RegisterAset dengan id_inventory yang sama dan id_item null, urutkan berdasarkan created_at
                    $registersForInventory = $registerAsetsMap[$item->id_inventory]
                        ->whereNull('id_item')
                        ->sortBy('created_at')
                        ->values();
                    
                    // Cari index dari item ini dalam daftar items untuk inventory yang sama
                    $itemIndex = $itemsForInventory->search(function($invItem) use ($item) {
                        return $invItem->id_item === $item->id_item;
                    });
                    
                    // Jika ditemukan dan ada RegisterAset di index yang sama, pasangkan
                    if ($itemIndex !== false && isset($registersForInventory[$itemIndex])) {
                        $registerAsetItemMap[$item->id_item] = $registersForInventory[$itemIndex];
                    }
                }
            }
        }
        
        // Pass gudangUnit ke view untuk digunakan di view
        $gudangUnitForView = isset($gudangUnit) ? $gudangUnit : null;
        
        return view('asset.register-aset.unit-kerja.show', compact('inventoryItems', 'title', 'filter', 'unitKerjaId', 'isPusat', 'gudangUnitForView', 'registerAsetsMap', 'registerAsetItemMap', 'unitKerjas', 'ruangans'));
    }

    /**
     * Show the form for creating a new resource.
     * 
     * NOTE: RegisterAset sekarang dibuat otomatis saat penerimaan barang dikonfirmasi.
     * Form ini hanya untuk kasus khusus (misalnya aset yang sudah ada di gudang unit tanpa melalui distribusi).
     */
    public function create()
    {
        // Hanya admin dan admin_gudang yang bisa create
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Query: Ambil InventoryItem yang belum punya RegisterAset
        // RegisterAset dibuat otomatis saat penerimaan, jadi ini untuk kasus khusus saja
        // Filter: InventoryItem yang belum punya RegisterAset (berdasarkan id_item, bukan id_inventory)
        
        // Ambil semua InventoryItem ASET yang aktif dan belum ter-register
        // Filter: InventoryItem yang belum punya RegisterAset (berdasarkan id_item)
        $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
        $registeredItemIds = [];
        
        if ($hasIdItemColumn) {
            $registeredItemIds = RegisterAset::whereNotNull('id_item')
                ->pluck('id_item')
                ->toArray();
        }
        
        $inventoryItemsQuery = \App\Models\InventoryItem::with('inventory.dataBarang', 'inventory.gudang', 'gudang')
            ->whereHas('inventory', function($q) {
                $q->where('jenis_inventory', 'ASET')
                  ->where('status_inventory', 'AKTIF');
            });
        
        if ($hasIdItemColumn && !empty($registeredItemIds)) {
            $inventoryItemsQuery->whereNotIn('id_item', $registeredItemIds);
        } elseif (!$hasIdItemColumn) {
            // Fallback untuk data lama: gunakan whereDoesntHave
            $inventoryItemsQuery->whereDoesntHave('registerAset');
        }
        
        $inventoryItems = $inventoryItemsQuery->orderBy('kode_register')->get();
        
        // Ambil semua unit kerja
        $unitKerjas = MasterUnitKerja::orderBy('nama_unit_kerja')->get();
        
        // Ambil semua ruangan (akan difilter berdasarkan unit kerja di frontend)
        $ruangans = \App\Models\MasterRuangan::with('unitKerja')
            ->orderBy('nama_ruangan')
            ->get();
        
        return view('asset.register-aset.create', compact('inventoryItems', 'unitKerjas', 'ruangans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Hanya admin dan admin_gudang yang bisa store
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        // Validasi input
        $validated = $request->validate([
            'id_item' => 'required|exists:inventory_item,id_item',
            'id_inventory' => 'required|exists:data_inventory,id_inventory',
            'id_unit_kerja' => 'required|exists:master_unit_kerja,id_unit_kerja',
            'id_ruangan' => 'nullable|exists:master_ruangan,id_ruangan',
            'nomor_register' => 'nullable|string|max:100', // Bisa kosong, akan di-generate otomatis
            'kondisi_aset' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT',
            'status_aset' => 'required|in:AKTIF,NONAKTIF',
            'tanggal_perolehan' => 'required|date',
        ]);
        
        // Cek apakah inventory item sudah ter-register
        $inventoryItem = \App\Models\InventoryItem::findOrFail($validated['id_item']);
        
        // Cek apakah kolom id_item sudah ada
        $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
        if ($hasIdItemColumn) {
            $existingRegister = RegisterAset::where('id_item', $validated['id_item'])->first();
            if ($existingRegister) {
                return back()->withErrors(['id_item' => 'Inventory item ini sudah ter-register'])->withInput();
            }
        } else {
            // Fallback untuk data lama: cek berdasarkan id_inventory (kurang akurat tapi tetap berfungsi)
            $existingRegister = RegisterAset::where('id_inventory', $validated['id_inventory'])->first();
            if ($existingRegister) {
                return back()->withErrors(['id_item' => 'Inventory ini sudah ter-register. Silakan jalankan migration terlebih dahulu.'])->withInput();
            }
        }
        
        // Cek apakah inventory adalah jenis ASET
        $inventory = DataInventory::findOrFail($validated['id_inventory']);
        if ($inventory->jenis_inventory !== 'ASET') {
            return back()->withErrors(['id_item' => 'Inventory yang dipilih harus berjenis ASET'])->withInput();
        }
        
        // Pastikan id_item sesuai dengan id_inventory
        if ($inventoryItem->id_inventory != $validated['id_inventory']) {
            return back()->withErrors(['id_item' => 'Inventory item tidak sesuai dengan inventory yang dipilih'])->withInput();
        }
        
        // Jika kolom id_item belum ada, hanya simpan id_inventory (backward compatibility)
        $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
        if (!$hasIdItemColumn) {
            unset($validated['id_item']);
        }
        
        // Generate nomor register otomatis jika tidak diisi atau masih mengandung "XXXX"
        if (empty($validated['nomor_register']) || strpos($validated['nomor_register'], 'XXXX') !== false) {
            $validated['nomor_register'] = $this->generateNomorRegister(
                $validated['id_unit_kerja'],
                $validated['id_ruangan'] ?? null,
                $validated['tanggal_perolehan']
            );
        } else {
            // Jika nomor register sudah ada, buat yang unik dengan menambahkan suffix
            $nomorRegister = $validated['nomor_register'];
            $counter = 1;
            while (RegisterAset::where('nomor_register', $nomorRegister)->exists()) {
                $nomorRegister = $validated['nomor_register'] . '-' . $counter;
                $counter++;
            }
            $validated['nomor_register'] = $nomorRegister;
        }
        
        // Buat register aset
        $registerAset = RegisterAset::create($validated);
        
        // Jika RegisterAset dibuat dengan ruangan, buat KartuInventarisRuangan otomatis
        if (!empty($validated['id_ruangan'])) {
            // Cek apakah sudah ada KIR untuk RegisterAset dan ruangan ini
            $existingKir = \App\Models\KartuInventarisRuangan::where('id_register_aset', $registerAset->id_register_aset)
                ->where('id_ruangan', $validated['id_ruangan'])
                ->first();
            
            if (!$existingKir) {
                // Buat KIR baru
                \App\Models\KartuInventarisRuangan::create([
                    'id_register_aset' => $registerAset->id_register_aset,
                    'id_ruangan' => $validated['id_ruangan'],
                    'id_penanggung_jawab' => null, // Bisa diisi nanti
                    'tanggal_penempatan' => $validated['tanggal_perolehan'] ?? now(),
                ]);
            }
            
            // Update InventoryItem spesifik yang ter-register untuk set id_ruangan
            $inventoryItem->update(['id_ruangan' => $validated['id_ruangan']]);
        }
        
        return redirect()->route('asset.register-aset.show', $registerAset->id_register_aset)
            ->with('success', 'Register aset berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $registerAset = RegisterAset::with([
            'inventory.dataBarang',
            'inventory.gudang',
            'inventory.satuan',
            'inventory.sumberAnggaran',
            'unitKerja',
            'kartuInventarisRuangan',
            'mutasiAset',
            'permintaanPemeliharaan',
            'jadwalMaintenance'
        ])->findOrFail($id);
        $user = Auth::user();

        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                if ($registerAset->id_unit_kerja != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat aset dari gudang unit Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki gudang unit');
            }
        }

        return view('asset.register-aset.show', compact('registerAset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $registerAset = RegisterAset::with([
            'inventory.dataBarang', 
            'unitKerja',
            'ruangan',
            'kartuInventarisRuangan.penanggungJawab'
        ])->findOrFail($id);
        $user = Auth::user();

        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                if ($registerAset->id_unit_kerja != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengedit aset dari gudang unit Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki gudang unit');
            }
        }

        // Ambil ruangan untuk dropdown (filter berdasarkan unit kerja)
        $ruangans = \App\Models\MasterRuangan::where('id_unit_kerja', $registerAset->id_unit_kerja)
            ->orderBy('nama_ruangan')
            ->get();

        // Ambil pegawai untuk dropdown penanggung jawab (filter berdasarkan unit kerja)
        $pegawais = MasterPegawai::where('id_unit_kerja', $registerAset->id_unit_kerja)
            ->orderBy('nama_pegawai')
            ->get();

        // Ambil KIR jika ada
        $kir = $registerAset->kartuInventarisRuangan->first();

        return view('asset.register-aset.edit', compact('registerAset', 'ruangans', 'pegawais', 'kir'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $registerAset = RegisterAset::findOrFail($id);
        $user = Auth::user();

        // Filter berdasarkan unit kerja untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                if ($registerAset->id_unit_kerja != $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengupdate aset dari gudang unit Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki gudang unit');
            }
        }

        // Validasi input
        $validated = $request->validate([
            'id_ruangan' => 'nullable|exists:master_ruangan,id_ruangan',
            'kondisi_aset' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT',
            'status_aset' => 'required|in:AKTIF,NONAKTIF',
            'tanggal_perolehan' => 'required|date',
            'id_penanggung_jawab' => 'nullable|exists:master_pegawai,id',
            'regenerate_nomor_register' => 'nullable|boolean', // Flag untuk regenerate nomor register
        ]);

        DB::beginTransaction();
        try {
            // Simpan id_ruangan lama untuk update InventoryItem jika ruangan dihapus
            $oldIdRuangan = $registerAset->id_ruangan;
            // Jika ruangan berubah atau flag regenerate, regenerate nomor register
            $shouldRegenerate = $request->has('regenerate_nomor_register') && $request->regenerate_nomor_register;
            $ruanganChanged = (int) $registerAset->id_ruangan !== (int) ($validated['id_ruangan'] ?? 0);
            
            if ($shouldRegenerate || $ruanganChanged) {
                // Generate nomor register baru dengan format baru
                $newNomorRegister = $this->generateNomorRegister(
                    $registerAset->id_unit_kerja,
                    $validated['id_ruangan'] ?? null,
                    $validated['tanggal_perolehan']
                );
                
                // Update nomor register
                $registerAset->update([
                    'id_ruangan' => $validated['id_ruangan'],
                    'kondisi_aset' => $validated['kondisi_aset'],
                    'status_aset' => $validated['status_aset'],
                    'tanggal_perolehan' => $validated['tanggal_perolehan'],
                    'nomor_register' => $newNomorRegister,
                ]);
            } else {
                // Update register aset tanpa mengubah nomor register
                $registerAset->update([
                    'id_ruangan' => $validated['id_ruangan'],
                    'kondisi_aset' => $validated['kondisi_aset'],
                    'status_aset' => $validated['status_aset'],
                    'tanggal_perolehan' => $validated['tanggal_perolehan'],
                ]);
            }

            // Jika ada ruangan, update InventoryItem dan buat/update KIR
            if ($validated['id_ruangan']) {
                // Update InventoryItem spesifik yang ter-register (berdasarkan id_item)
                $inventoryItem = null;
                $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
                
                if ($hasIdItemColumn && $registerAset->id_item) {
                    // Gunakan id_item jika ada (lebih tepat)
                    $inventoryItem = \App\Models\InventoryItem::find($registerAset->id_item);
                } else {
                    // Fallback untuk data lama: ambil InventoryItem pertama yang belum punya ruangan
                    $inventoryItem = \App\Models\InventoryItem::where('id_inventory', $registerAset->id_inventory)
                        ->where(function($q) {
                            $q->whereNull('id_ruangan')
                              ->orWhere('id_ruangan', '');
                        })
                        ->first();
                }

                if ($inventoryItem) {
                    $inventoryItem->update(['id_ruangan' => $validated['id_ruangan']]);
                }

                // Buat atau Update KIR
                $kir = \App\Models\KartuInventarisRuangan::firstOrCreate([
                    'id_register_aset' => $registerAset->id_register_aset,
                    'id_ruangan' => $validated['id_ruangan'],
                ], [
                    'id_penanggung_jawab' => $validated['id_penanggung_jawab'] ?? null,
                    'tanggal_penempatan' => $validated['tanggal_perolehan'] ?? now(),
                ]);

                // Update penanggung jawab jika diubah
                if (!$kir->wasRecentlyCreated && isset($validated['id_penanggung_jawab'])) {
                    $kir->update(['id_penanggung_jawab' => $validated['id_penanggung_jawab']]);
                }
            } else {
                // Jika ruangan dihapus: update InventoryItem spesifik yang ter-register
                if ($oldIdRuangan) {
                    $inventoryItem = null;
                    $hasIdItemColumn = Schema::hasColumn('register_aset', 'id_item');
                    
                    if ($hasIdItemColumn && $registerAset->id_item) {
                        // Gunakan id_item jika ada (lebih tepat)
                        $inventoryItem = \App\Models\InventoryItem::find($registerAset->id_item);
                    } else {
                        // Fallback untuk data lama: ambil InventoryItem pertama dengan ruangan tersebut
                        $inventoryItem = \App\Models\InventoryItem::where('id_inventory', $registerAset->id_inventory)
                            ->where('id_ruangan', $oldIdRuangan)
                            ->first();
                    }
                    
                    if ($inventoryItem) {
                        $inventoryItem->update(['id_ruangan' => null]);
                    }
                }
                \App\Models\KartuInventarisRuangan::where('id_register_aset', $registerAset->id_register_aset)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('asset.register-aset.show', $registerAset->id_register_aset)
                ->with('success', 'Register aset berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating register aset: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Hanya admin dan admin_gudang yang bisa delete
        if (!Auth::user()->hasAnyRole(['admin', 'admin_gudang'])) {
            abort(403, 'Unauthorized');
        }
        
        $registerAset = RegisterAset::findOrFail($id);
        $registerAset->delete();

        return redirect()->route('asset.register-aset.index')
            ->with('success', 'Register aset berhasil dihapus.');
    }

    /**
     * Generate nomor register otomatis
     * Format: [ID_UNIT_KERJA]/[ID_RUANGAN]/[URUT]
     * Jika tidak ada ruangan: [ID_UNIT_KERJA]/[URUT]
     */
    protected function generateNomorRegister($idUnitKerja, $idRuangan = null, $tanggalPerolehan = null)
    {
        $tahun = $tanggalPerolehan ? date('Y', strtotime($tanggalPerolehan)) : date('Y');
        
        // Format baru: ID_UNIT_KERJA/ID_RUANGAN/URUT atau ID_UNIT_KERJA/URUT
        if ($idRuangan) {
            $prefix = sprintf('%03d/%03d', $idUnitKerja, $idRuangan);
        } else {
            $prefix = sprintf('%03d', $idUnitKerja);
        }
        
        // Cari nomor urut terakhir untuk kombinasi unit kerja + ruangan + tahun ini
        // Exclude nomor register yang masih mengandung XXXX
        $lastRegister = RegisterAset::where('id_unit_kerja', $idUnitKerja)
            ->where(function($q) use ($idRuangan) {
                if ($idRuangan) {
                    $q->where('id_ruangan', $idRuangan);
                } else {
                    $q->whereNull('id_ruangan');
                }
            })
            ->whereYear('tanggal_perolehan', $tahun)
            ->where('nomor_register', 'like', $prefix . '/%')
            ->where('nomor_register', 'not like', '%XXXX%') // Exclude yang masih XXXX
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_register, "/", -1) AS UNSIGNED) DESC')
            ->first();
        
        $urut = 1;
        if ($lastRegister) {
            // Extract nomor urut dari nomor register terakhir
            $parts = explode('/', $lastRegister->nomor_register);
            $lastUrut = (int)end($parts);
            $urut = $lastUrut + 1;
        }
        
        return sprintf('%s/%04d', $prefix, $urut);
    }
}
