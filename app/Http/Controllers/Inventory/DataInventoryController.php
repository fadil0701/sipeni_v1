<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\DataInventory;
use App\Models\InventoryItem;
use App\Models\DataStock;
use App\Models\RegisterAset;
use App\Models\MasterDataBarang;
use App\Models\MasterGudang;
use App\Models\MasterSumberAnggaran;
use App\Models\MasterSubKegiatan;
use App\Models\MasterSatuan;
use App\Models\MasterUnitKerja;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DataInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // GUDANG PUSAT (Admin/Admin Gudang): Melihat SEMUA data inventory (global view)
        // GUDANG UNIT (Kepala Unit/Pegawai/Admin Gudang Unit): Hanya melihat data di unitnya saja (local view)
        // ADMIN GUDANG PER KATEGORI: Hanya melihat gudang sesuai kategori (Aset/Persediaan/Farmasi) agar tidak konflik
        if ($user->hasAnyRole(['kepala_unit', 'pegawai', 'admin_gudang_unit']) && !$user->hasRole('admin')) {
            // GUDANG UNIT: Hanya melihat data yang ada di gudang UNIT mereka
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Ambil id gudang UNIT yang terkait dengan unit kerja mereka
                $gudangUnitIds = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');
                
                if ($gudangUnitIds->isEmpty()) {
                    // Jika tidak ada gudang unit, tidak tampilkan data
                    $query = DataInventory::whereRaw('1 = 0');
                } else {
                    // Untuk GUDANG UNIT:
                    // - PERSEDIAAN/FARMASI: melihat data_inventory yang id_gudang = gudang UNIT mereka
                    // - ASET: melihat data_inventory yang memiliki inventory_item di gudang UNIT mereka
                    $query = DataInventory::with([
                        'dataBarang', 
                        'gudang', 
                        'sumberAnggaran', 
                        'subKegiatan', 
                        'satuan',
                        // Eager load inventoryItems yang ada di gudang unit mereka untuk ASET
                        'inventoryItems' => function($q) use ($gudangUnitIds) {
                            $q->whereIn('id_gudang', $gudangUnitIds)
                              ->where('status_item', 'AKTIF')
                              ->with(['gudang', 'ruangan']);
                        }
                    ])
                        ->where(function($q) use ($gudangUnitIds) {
                            // PERSEDIAAN/FARMASI: inventory yang langsung di gudang UNIT
                            $q->where(function($subQ) use ($gudangUnitIds) {
                                $subQ->whereIn('id_gudang', $gudangUnitIds)
                                      ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI']);
                            })
                            // ASET: inventory yang memiliki inventory_item di gudang UNIT (sudah didistribusikan)
                            ->orWhere(function($subQ) use ($gudangUnitIds) {
                                $subQ->where('jenis_inventory', 'ASET')
                                      ->whereHas('inventoryItems', function($itemQ) use ($gudangUnitIds) {
                                          $itemQ->whereIn('id_gudang', $gudangUnitIds)
                                                ->where('status_item', 'AKTIF');
                                      });
                            });
                        });
                }
            } else {
                // Jika user tidak memiliki pegawai atau unit kerja, tidak tampilkan data
                $query = DataInventory::whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('admin_gudang_aset') || $user->hasRole('admin_gudang_persediaan') || $user->hasRole('admin_gudang_farmasi')) {
            // Admin Gudang per kategori: hanya inventory di gudang dengan kategori yang sama (tidak konflik)
            $kategori = $user->hasRole('admin_gudang_aset') ? 'ASET' : ($user->hasRole('admin_gudang_persediaan') ? 'PERSEDIAAN' : 'FARMASI');
            $query = DataInventory::with(['dataBarang', 'gudang', 'sumberAnggaran', 'subKegiatan', 'satuan', 'inventoryItems.gudang', 'inventoryItems.ruangan'])
                ->whereHas('gudang', fn ($q) => $q->where('kategori_gudang', $kategori));
        } else {
            // Admin / Admin Gudang (umum): Melihat SEMUA data inventory
            $query = DataInventory::with(['dataBarang', 'gudang', 'sumberAnggaran', 'subKegiatan', 'satuan', 'inventoryItems.gudang', 'inventoryItems.ruangan']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_data_barang', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_inventory')) {
            $query->where('jenis_inventory', $request->jenis_inventory);
        }

        if ($request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
        }

        if ($request->filled('merk')) {
            $query->where('merk', 'like', "%{$request->merk}%");
        }

        if ($request->filled('no_batch')) {
            $query->where('no_batch', 'like', "%{$request->no_batch}%");
        }

        if ($request->filled('jenis_barang')) {
            $query->where('jenis_barang', $request->jenis_barang);
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $inventories = $query->latest()->paginate($perPage)->appends($request->query());
        
        // Untuk GUDANG UNIT: Hitung ulang qty dan update gudang berdasarkan inventory_item untuk ASET
        if ($user->hasAnyRole(['kepala_unit', 'pegawai', 'admin_gudang_unit']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangUnitIds = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');
                
                // Untuk ASET: Hitung ulang qty dan update gudang berdasarkan inventory_item yang sudah di-eager load
                foreach ($inventories as $inventory) {
                    if ($inventory->jenis_inventory === 'ASET' && $inventory->relationLoaded('inventoryItems')) {
                        // Gunakan inventoryItems yang sudah di-eager load (sudah difilter untuk gudang unit)
                        $itemsInUnit = $inventory->inventoryItems;
                        
                        if ($itemsInUnit->count() > 0) {
                            // Update qty_input berdasarkan jumlah inventory_item di gudang unit
                            $inventory->qty_input = $itemsInUnit->count();
                            
                            // Update gudang berdasarkan gudang dari inventory_item pertama
                            $firstItem = $itemsInUnit->first();
                            if ($firstItem->relationLoaded('gudang') && $firstItem->gudang) {
                                // Set gudang dari inventory_item
                                $inventory->setRelation('gudang', $firstItem->gudang);
                            }
                        }
                    }
                }
            }
        }
        
        // Filter gudang yang ditampilkan di dropdown berdasarkan role (agar tidak konflik)
        if ($user->hasAnyRole(['kepala_unit', 'pegawai', 'admin_gudang_unit']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->get();
            } else {
                $gudangs = collect([]);
            }
        } elseif ($user->hasRole('admin_gudang_aset')) {
            $gudangs = MasterGudang::where('kategori_gudang', 'ASET')->get();
        } elseif ($user->hasRole('admin_gudang_persediaan')) {
            $gudangs = MasterGudang::where('kategori_gudang', 'PERSEDIAAN')->get();
        } elseif ($user->hasRole('admin_gudang_farmasi')) {
            $gudangs = MasterGudang::where('kategori_gudang', 'FARMASI')->get();
        } else {
            $gudangs = MasterGudang::all();
        }
        
        $dataBarangs = MasterDataBarang::all();

        return view('inventory.data-inventory.index', compact('inventories', 'gudangs', 'dataBarangs'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // GUDANG UNIT: Tidak bisa menambah data inventory baru
        // Hanya bisa menerima melalui distribusi
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized - Gudang unit tidak dapat menambah data inventory baru. Inventory hanya dapat ditambahkan melalui distribusi dari gudang pusat.');
        }
        
        $dataBarangs = MasterDataBarang::all();
        // Hanya tampilkan gudang PUSAT untuk input inventory
        $gudangs = MasterGudang::where('jenis_gudang', 'PUSAT')->get();
        $sumberAnggarans = MasterSumberAnggaran::all();
        $subKegiatans = MasterSubKegiatan::all();
        $satuans = MasterSatuan::all();
        $unitKerjas = MasterUnitKerja::all();

        return view('inventory.data-inventory.create', compact(
            'dataBarangs', 'gudangs', 'sumberAnggarans', 'subKegiatans', 'satuans', 'unitKerjas'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_data_barang' => 'required|exists:master_data_barang,id_data_barang',
            'id_gudang' => [
                'required',
                'exists:master_gudang,id_gudang',
                function ($attribute, $value, $fail) {
                    $gudang = MasterGudang::find($value);
                    if ($gudang && $gudang->jenis_gudang !== 'PUSAT') {
                        $fail('Data inventory hanya dapat disimpan di gudang PUSAT. Gudang UNIT hanya menerima distribusi barang.');
                    }
                },
            ],
            'id_anggaran' => 'required|exists:master_sumber_anggaran,id_anggaran',
            'id_sub_kegiatan' => 'required|exists:master_sub_kegiatan,id_sub_kegiatan',
            'jenis_inventory' => 'required|in:ASET,PERSEDIAAN,FARMASI',
            'jenis_barang' => 'nullable|string|max:50',
            'tahun_anggaran' => 'required|integer|min:2000|max:2100',
            'qty_input' => 'required|numeric|min:1',
            'id_satuan' => 'required|exists:master_satuan,id_satuan',
            'harga_satuan' => 'required|numeric|min:0',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'spesifikasi' => 'nullable|string',
            'tahun_produksi' => 'nullable|integer',
            'nama_penyedia' => 'nullable|string|max:255',
            'no_seri' => 'nullable|string|max:255',
            'no_batch' => 'nullable|string|max:255',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'status_inventory' => 'required|in:DRAFT,AKTIF,DISTRIBUSI,HABIS',
            'upload_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'upload_dokumen' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // Untuk jenis inventory FARMASI, No Batch dan Tanggal Kedaluwarsa wajib
        if (($validated['jenis_inventory'] ?? '') === 'FARMASI') {
            $request->validate([
                'no_batch' => 'required|string|max:255',
                'tanggal_kedaluwarsa' => 'required|date',
            ], [
                'no_batch.required' => 'Nomor batch wajib diisi untuk inventory Farmasi.',
                'tanggal_kedaluwarsa.required' => 'Tanggal kedaluwarsa wajib diisi untuk inventory Farmasi.',
            ]);
        }

        // Validasi jenis_barang sesuai jenis_inventory
        $jenisBarangByJenis = [
            'ASET' => ['ALKES', 'NON ALKES'],
            'FARMASI' => ['OBAT', 'Vaksin', 'BHP', 'BMHP', 'REAGEN', 'ALKES'],
            'PERSEDIAAN' => ['ATK', 'ART', 'CETAKAN UMUM', 'CETAK KHUSUS'],
        ];
        $allowedJenisBarang = $jenisBarangByJenis[$validated['jenis_inventory']] ?? [];
        if (!empty($allowedJenisBarang)) {
            $request->validate([
                'jenis_barang' => 'required|in:' . implode(',', $allowedJenisBarang),
            ], [
                'jenis_barang.required' => 'Jenis barang wajib dipilih.',
                'jenis_barang.in' => 'Jenis barang tidak valid untuk jenis inventory yang dipilih.',
            ]);
        }

        $validated['total_harga'] = $validated['qty_input'] * $validated['harga_satuan'];
        $validated['created_by'] = auth()->id();

        // Handle file uploads
        if ($request->hasFile('upload_foto')) {
            $validated['upload_foto'] = $request->file('upload_foto')->store('foto-inventory', 'public');
        }

        if ($request->hasFile('upload_dokumen')) {
            $validated['upload_dokumen'] = $request->file('upload_dokumen')->store('dokumen-inventory', 'public');
        }

        DB::beginTransaction();
        try {
            // Insert ke data_inventory
            $inventory = DataInventory::create($validated);

            // Logika berdasarkan jenis inventory:
            // - ASET → masuk ke InventoryItem (untuk RegisterAset/KIR) via Observer, TIDAK masuk ke DataStock
            // - PERSEDIAAN/FARMASI → masuk ke DataStock, TIDAK masuk ke InventoryItem
            // 
            // CATATAN: Pembuatan InventoryItem untuk ASET dilakukan oleh DataInventoryObserver
            // yang otomatis dipanggil saat DataInventory::create() berhasil
            
            if ($validated['jenis_inventory'] === 'ASET') {
                // ASET: InventoryItem akan dibuat otomatis oleh DataInventoryObserver
                // CATATAN: RegisterAset TIDAK dibuat otomatis di sini
                // RegisterAset dibuat secara manual oleh user melalui form "Tambah Register Aset"
                // ASET TIDAK masuk ke DataStock
            } elseif (in_array($validated['jenis_inventory'], ['PERSEDIAAN', 'FARMASI'])) {
                // PERSEDIAAN/FARMASI: Update atau create data_stock
                $this->updateStock($inventory, $validated);
                // PERSEDIAAN/FARMASI TIDAK masuk ke InventoryItem
            }

            DB::commit();

            return redirect()->route('inventory.data-inventory.index')
                ->with('success', 'Data Inventory berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error untuk debugging
            \Log::error('Error saving DataInventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan periksa kembali data yang diinput.');
        }
    }

    // Method autoRegisterAset telah dipindahkan ke DataInventoryObserver
    // untuk menghindari duplikasi pembuatan InventoryItem

    private function updateStock(DataInventory $inventory, array $data)
    {
        $stock = DataStock::firstOrNew([
            'id_data_barang' => $inventory->id_data_barang,
            'id_gudang' => $inventory->id_gudang,
        ]);

        if ($stock->exists) {
            $stock->qty_masuk += $data['qty_input'];
            $stock->qty_akhir += $data['qty_input'];
        } else {
            $stock->qty_awal = 0;
            $stock->qty_masuk = $data['qty_input'];
            $stock->qty_keluar = 0;
            $stock->qty_akhir = $data['qty_input'];
            $stock->id_satuan = $data['id_satuan'];
        }

        $stock->last_updated = now();
        $stock->save();
    }

    private function createRegisterAset(DataInventory $inventory, array $data)
    {
        // Cek apakah RegisterAset sudah ada untuk inventory ini
        $existingRegister = RegisterAset::where('id_inventory', $inventory->id_inventory)->first();
        if ($existingRegister) {
            return; // Sudah ada, tidak perlu dibuat lagi
        }

        $gudang = $inventory->gudang;
        $unitKerja = $gudang->unitKerja ?? MasterUnitKerja::first();
        
        if (!$unitKerja) {
            \Log::warning('Unit kerja tidak ditemukan untuk membuat RegisterAset', [
                'id_inventory' => $inventory->id_inventory,
                'id_gudang' => $inventory->id_gudang
            ]);
            return;
        }

        // Generate nomor register dari kode register pertama di InventoryItem yang belum ter-register
        $hasIdItemColumn = \Schema::hasColumn('register_aset', 'id_item');
        $registeredItemIds = [];
        
        if ($hasIdItemColumn) {
            $registeredItemIds = RegisterAset::whereNotNull('id_item')
                ->pluck('id_item')
                ->toArray();
        }
        
        $firstInventoryItemQuery = InventoryItem::where('id_inventory', $inventory->id_inventory);
        
        if ($hasIdItemColumn && !empty($registeredItemIds)) {
            $firstInventoryItemQuery->whereNotIn('id_item', $registeredItemIds);
        } elseif (!$hasIdItemColumn) {
            $firstInventoryItemQuery->whereDoesntHave('registerAset');
        }
        
        $firstInventoryItem = $firstInventoryItemQuery->orderBy('id_item')->first();
        
        if (!$firstInventoryItem) {
            // Jika semua InventoryItem sudah ter-register, skip
            return;
        }
        
        $nomorRegister = $firstInventoryItem->kode_register;

        $registerData = [
            'id_inventory' => $inventory->id_inventory,
            'id_unit_kerja' => $unitKerja->id_unit_kerja,
            'nomor_register' => $nomorRegister,
            'kondisi_aset' => 'BAIK',
            'tanggal_perolehan' => $data['tanggal_perolehan'] ?? now(),
            'status_aset' => 'AKTIF',
        ];
        
        // Tambahkan id_item jika kolom sudah ada
        if ($hasIdItemColumn) {
            $registerData['id_item'] = $firstInventoryItem->id_item;
        }
        
        RegisterAset::create($registerData);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // GUDANG PUSAT: Load semua inventoryItems
        // GUDANG UNIT: Load hanya inventoryItems di gudang unit mereka
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangUnitIds = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');
                
                // Load inventory dengan filter inventoryItems untuk gudang unit mereka
                $inventory = DataInventory::with([
                    'dataBarang', 
                    'gudang', 
                    'sumberAnggaran', 
                    'subKegiatan', 
                    'satuan',
                    'inventoryItems' => function($q) use ($gudangUnitIds) {
                        $q->whereIn('id_gudang', $gudangUnitIds)
                          ->where('status_item', 'AKTIF')
                          ->with(['gudang', 'ruangan']);
                    }
                ])->findOrFail($id);
                
                // Validasi akses
                $hasAccess = false;
                
                if ($inventory->jenis_inventory === 'ASET') {
                    // Untuk ASET: cek apakah ada inventory_item di gudang unit mereka
                    $hasAccess = $inventory->inventoryItems->count() > 0;
                } else {
                    // Untuk PERSEDIAAN/FARMASI: cek apakah id_gudang mengarah ke gudang unit mereka
                    $hasAccess = $gudangUnitIds->contains($inventory->id_gudang);
                }
                
                if (!$hasAccess) {
                    abort(403, 'Unauthorized - Anda hanya dapat melihat inventory dari gudang unit Anda sendiri');
                }
                
                // Untuk ASET: Update gudang dan qty berdasarkan inventoryItems di gudang unit
                if ($inventory->jenis_inventory === 'ASET' && $inventory->inventoryItems->count() > 0) {
                    // Update qty_input berdasarkan jumlah inventory_item di gudang unit
                    $inventory->qty_input = $inventory->inventoryItems->count();
                    
                    // Update gudang berdasarkan gudang dari inventory_item pertama
                    $firstItem = $inventory->inventoryItems->first();
                    if ($firstItem->gudang) {
                        $inventory->setRelation('gudang', $firstItem->gudang);
                    }
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        } else {
            // GUDANG PUSAT: Load semua inventoryItems
            $inventory = DataInventory::with(['dataBarang', 'gudang', 'sumberAnggaran', 'subKegiatan', 'satuan', 'inventoryItems.gudang', 'inventoryItems.ruangan'])
                ->findOrFail($id);
        }

        return view('inventory.data-inventory.show', compact('inventory'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $dataInventory = DataInventory::with(['gudang', 'inventoryItems.gudang', 'inventoryItems.ruangan'])->findOrFail($id);

        // GUDANG UNIT: Pastikan inventory ini bisa diedit oleh unit mereka
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangUnitIds = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');
                
                $canEdit = false;
                
                if ($dataInventory->jenis_inventory === 'ASET') {
                    // Untuk ASET: cek apakah ada inventory_item di gudang unit mereka
                    // Catatan: Untuk ASET yang sudah didistribusikan, edit dilakukan di level inventory_item
                    $canEdit = $dataInventory->inventoryItems()
                        ->whereIn('id_gudang', $gudangUnitIds)
                        ->where('status_item', 'AKTIF')
                        ->exists();
                    
                    // Jika ASET sudah didistribusikan (data_inventory masih di gudang pusat), redirect ke show
                    if ($canEdit && $dataInventory->gudang->jenis_gudang === 'PUSAT') {
                        return redirect()->route('inventory.data-inventory.show', $id)
                            ->with('info', 'Untuk ASET yang sudah didistribusikan, edit dilakukan di level per register (inventory item).');
                    }
                } else {
                    // Untuk PERSEDIAAN/FARMASI: cek apakah id_gudang mengarah ke gudang unit mereka
                    $canEdit = $gudangUnitIds->contains($dataInventory->id_gudang);
                }
                
                if (!$canEdit) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengedit inventory dari gudang unit Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }
        // GUDANG PUSAT: Bisa edit semua (tidak perlu validasi khusus)
        
        $dataBarangs = MasterDataBarang::all();
        
        // Filter gudang berdasarkan role
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Hanya tampilkan gudang UNIT yang terkait dengan unit kerja mereka
                $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->get();
            } else {
                $gudangs = collect([]);
            }
        } else {
            // GUDANG PUSAT: Hanya bisa edit inventory di gudang PUSAT
            $gudangs = MasterGudang::where('jenis_gudang', 'PUSAT')->get();
        }
        
        $sumberAnggarans = MasterSumberAnggaran::all();
        $subKegiatans = MasterSubKegiatan::all();
        $satuans = MasterSatuan::all();
        $unitKerjas = MasterUnitKerja::all();

        return view('inventory.data-inventory.edit', compact(
            'dataInventory', 'dataBarangs', 'gudangs', 'sumberAnggarans', 'subKegiatans', 'satuans', 'unitKerjas'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $inventory = DataInventory::with('gudang')->findOrFail($id);

        // Filter berdasarkan jenis gudang untuk kepala_unit dan pegawai
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                // Pastikan inventory ini dari gudang UNIT yang terkait dengan unit kerja mereka
                if ($inventory->gudang->jenis_gudang !== 'UNIT' || 
                    $inventory->gudang->id_unit_kerja !== $pegawai->id_unit_kerja) {
                    abort(403, 'Unauthorized - Anda hanya dapat mengupdate inventory dari gudang unit Anda sendiri');
                }
            } else {
                abort(403, 'Unauthorized - User tidak memiliki unit kerja');
            }
        }

        // Validation rules berbeda untuk admin/admin_gudang vs kepala_unit/pegawai
        $gudangRules = ['required', 'exists:master_gudang,id_gudang'];
        
        if ($user->hasAnyRole(['admin', 'admin_gudang'])) {
            // Admin dan admin_gudang hanya bisa update inventory di gudang PUSAT
            $gudangRules[] = function ($attribute, $value, $fail) {
                $gudang = MasterGudang::find($value);
                if ($gudang && $gudang->jenis_gudang !== 'PUSAT') {
                    $fail('Data inventory hanya dapat disimpan di gudang PUSAT. Gudang UNIT hanya menerima distribusi barang.');
                }
            };
        } else {
            // Kepala unit dan pegawai hanya bisa update inventory di gudang UNIT mereka
            $gudangRules[] = function ($attribute, $value, $fail) use ($user) {
                $pegawai = MasterPegawai::where('user_id', $user->id)->first();
                if ($pegawai && $pegawai->id_unit_kerja) {
                    $gudang = MasterGudang::find($value);
                    if ($gudang && ($gudang->jenis_gudang !== 'UNIT' || $gudang->id_unit_kerja !== $pegawai->id_unit_kerja)) {
                        $fail('Anda hanya dapat mengupdate inventory di gudang unit Anda sendiri.');
                    }
                } else {
                    $fail('User tidak memiliki unit kerja.');
                }
            };
        }

        $validated = $request->validate([
            'id_data_barang' => 'required|exists:master_data_barang,id_data_barang',
            'id_gudang' => $gudangRules,
            'id_anggaran' => 'required|exists:master_sumber_anggaran,id_anggaran',
            'id_sub_kegiatan' => 'required|exists:master_sub_kegiatan,id_sub_kegiatan',
            'jenis_inventory' => 'required|in:ASET,PERSEDIAAN,FARMASI',
            'jenis_barang' => 'nullable|string|max:50',
            'tahun_anggaran' => 'required|integer|min:2000|max:2100',
            'qty_input' => 'required|numeric|min:1',
            'id_satuan' => 'required|exists:master_satuan,id_satuan',
            'harga_satuan' => 'required|numeric|min:0',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'spesifikasi' => 'nullable|string',
            'tahun_produksi' => 'nullable|integer',
            'nama_penyedia' => 'nullable|string|max:255',
            'no_seri' => 'nullable|string|max:255',
            'no_batch' => 'nullable|string|max:255',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'status_inventory' => 'required|in:DRAFT,AKTIF,DISTRIBUSI,HABIS',
            'upload_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'upload_dokumen' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // Untuk jenis inventory FARMASI, No Batch dan Tanggal Kedaluwarsa wajib
        if (($validated['jenis_inventory'] ?? '') === 'FARMASI') {
            $request->validate([
                'no_batch' => 'required|string|max:255',
                'tanggal_kedaluwarsa' => 'required|date',
            ], [
                'no_batch.required' => 'Nomor batch wajib diisi untuk inventory Farmasi.',
                'tanggal_kedaluwarsa.required' => 'Tanggal kedaluwarsa wajib diisi untuk inventory Farmasi.',
            ]);
        }

        // Validasi jenis_barang sesuai jenis_inventory (update)
        $jenisBarangByJenis = [
            'ASET' => ['ALKES', 'NON ALKES'],
            'FARMASI' => ['OBAT', 'Vaksin', 'BHP', 'BMHP', 'REAGEN', 'ALKES'],
            'PERSEDIAAN' => ['ATK', 'ART', 'CETAKAN UMUM', 'CETAK KHUSUS'],
        ];
        $allowedJenisBarang = $jenisBarangByJenis[$validated['jenis_inventory']] ?? [];
        if (!empty($allowedJenisBarang)) {
            $request->validate([
                'jenis_barang' => 'required|in:' . implode(',', $allowedJenisBarang),
            ], [
                'jenis_barang.required' => 'Jenis barang wajib dipilih.',
                'jenis_barang.in' => 'Jenis barang tidak valid untuk jenis inventory yang dipilih.',
            ]);
        }

        $validated['total_harga'] = $validated['qty_input'] * $validated['harga_satuan'];

        // Handle file uploads
        if ($request->hasFile('upload_foto')) {
            // Hapus foto lama jika ada
            if ($inventory->upload_foto) {
                Storage::disk('public')->delete($inventory->upload_foto);
            }
            $validated['upload_foto'] = $request->file('upload_foto')->store('foto-inventory', 'public');
        }

        if ($request->hasFile('upload_dokumen')) {
            // Hapus dokumen lama jika ada
            if ($inventory->upload_dokumen) {
                Storage::disk('public')->delete($inventory->upload_dokumen);
            }
            $validated['upload_dokumen'] = $request->file('upload_dokumen')->store('dokumen-inventory', 'public');
        }

        DB::beginTransaction();
        try {
            $oldJenis = $inventory->jenis_inventory;
            $oldQty = $inventory->qty_input;
            
            $inventory->update($validated);
            
            // Jika jenis inventory berubah, perlu update DataStock dan InventoryItem
            if ($oldJenis !== $validated['jenis_inventory']) {
                // Hapus data lama berdasarkan jenis lama
                if ($oldJenis === 'ASET') {
                    // Jika sebelumnya ASET, hapus InventoryItem yang terkait
                    // (Tidak dihapus, hanya update jika perlu)
                } elseif (in_array($oldJenis, ['PERSEDIAAN', 'FARMASI'])) {
                    // Jika sebelumnya PERSEDIAAN/FARMASI, kurangi DataStock
                    $oldStock = \App\Models\DataStock::where('id_data_barang', $inventory->id_data_barang)
                        ->where('id_gudang', $inventory->id_gudang)
                        ->first();
                    if ($oldStock) {
                        $oldStock->qty_masuk -= $oldQty;
                        $oldStock->qty_akhir -= $oldQty;
                        if ($oldStock->qty_akhir <= 0) {
                            $oldStock->delete();
                        } else {
                            $oldStock->save();
                        }
                    }
                }
                
                // Buat data baru berdasarkan jenis baru
                // CATATAN: Pembuatan InventoryItem untuk ASET dilakukan oleh DataInventoryObserver
                // yang otomatis dipanggil saat DataInventory::update() berhasil
                if ($validated['jenis_inventory'] === 'ASET') {
                    // ASET: InventoryItem akan dibuat otomatis oleh DataInventoryObserver jika qty_input > 0
                    // CATATAN: RegisterAset TIDAK dibuat otomatis di sini
                    // RegisterAset dibuat secara manual oleh user melalui form "Tambah Register Aset"
                } elseif (in_array($validated['jenis_inventory'], ['PERSEDIAAN', 'FARMASI'])) {
                    // PERSEDIAAN/FARMASI: Update atau create data_stock
                    $this->updateStock($inventory, $validated);
                }
            } else {
                // Jika jenis tidak berubah, update sesuai jenis
                if ($validated['jenis_inventory'] === 'ASET') {
                    // Untuk ASET, update InventoryItem jika qty berubah
                    // (Logika ini bisa dikembangkan lebih lanjut jika diperlukan)
                } elseif (in_array($validated['jenis_inventory'], ['PERSEDIAAN', 'FARMASI'])) {
                    // Update DataStock dengan selisih qty
                    $stock = \App\Models\DataStock::where('id_data_barang', $inventory->id_data_barang)
                        ->where('id_gudang', $inventory->id_gudang)
                        ->first();
                    
                    if ($stock) {
                        $selisihQty = $validated['qty_input'] - $oldQty;
                        $stock->qty_masuk += $selisihQty;
                        $stock->qty_akhir += $selisihQty;
                        $stock->last_updated = now();
                        $stock->save();
                    } else {
                        // Jika stock belum ada, buat baru
                        $this->updateStock($inventory, $validated);
                    }
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating DataInventory: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }

        return redirect()->route('inventory.data-inventory.index')
            ->with('success', 'Data Inventory berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        // GUDANG UNIT: Tidak bisa menghapus data inventory
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized - Anda tidak memiliki izin untuk menghapus data inventory.');
        }
        
        // GUDANG PUSAT: Bisa menghapus data inventory
        $inventory = DataInventory::findOrFail($id);
        $inventory->delete();

        return redirect()->route('inventory.data-inventory.index')
            ->with('success', 'Data Inventory berhasil dihapus.');
    }
}
