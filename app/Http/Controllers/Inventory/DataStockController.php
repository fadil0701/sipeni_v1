<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataStock;
use App\Models\DataInventory;
use App\Models\MasterGudang;
use App\Models\MasterDataBarang;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Auth;

class DataStockController extends Controller
{
    private function applyStockEligibleInventoryFilter($query): void
    {
        $query->where(function ($invQ) {
            $invQ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
                ->orWhere(function ($asetQ) {
                    $asetQ->where('jenis_inventory', 'ASET')
                        ->where(function ($regQ) {
                            $regQ->whereDoesntHave('registerAset')
                                ->orWhereHas('registerAset', function ($r) {
                                    $r->whereNull('nomor_register')
                                        ->orWhere('nomor_register', '');
                                });
                        });
                });
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // GUDANG PUSAT (Admin/Admin Gudang): Melihat SEMUA data stock (global view)
        // GUDANG UNIT (Kepala Unit/Admin Unit): Hanya melihat stock di unitnya saja (local view)
        
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            // GUDANG UNIT: Hanya melihat stock yang ada di gudang UNIT mereka
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $query = DataStock::with(['dataBarang', 'gudang', 'satuan'])
                    ->whereHas('gudang', function ($q) use ($pegawai) {
                        $q->where('jenis_gudang', 'UNIT')
                          ->where('id_unit_kerja', $pegawai->id_unit_kerja);
                    })
                    ->whereHas('dataBarang', function($q) {
                        $q->whereHas('dataInventory', function($invQ) {
                            $this->applyStockEligibleInventoryFilter($invQ);
                        });
                    });
            } else {
                // Jika user tidak memiliki pegawai atau unit kerja, tidak tampilkan data
                $query = DataStock::whereRaw('1 = 0');
            }
        } else {
            // GUDANG PUSAT: Melihat SEMUA data stock (tidak ada filter)
            // Tampilkan data stock untuk:
            // - PERSEDIAAN/FARMASI
            // - ASET yang belum memiliki nomor register
            $query = DataStock::with(['dataBarang', 'gudang', 'satuan'])
                ->whereHas('dataBarang', function($q) {
                    $q->whereHas('dataInventory', function($invQ) {
                        $this->applyStockEligibleInventoryFilter($invQ);
                    });
                });
        }

        // Filters
        if ($request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
        }

        if ($request->filled('sub_kategori')) {
            // Filter by sub kategori through data_barang
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_data_barang', 'like', "%{$search}%");
            });
        }

        if ($request->filled('merk')) {
            $query->whereHas('dataBarang', function ($q) use ($request) {
                $q->whereHas('dataInventory', function ($invQ) use ($request) {
                    $invQ->where('merk', 'like', "%{$request->merk}%");
                });
            });
        }

        if ($request->filled('no_batch')) {
            $query->whereHas('dataBarang', function ($q) use ($request) {
                $q->whereHas('dataInventory', function ($invQ) use ($request) {
                    $invQ->where('no_batch', 'like', "%{$request->no_batch}%");
                });
            });
        }

        if ($request->filled('jenis')) {
            $query->whereHas('dataBarang', function ($q) use ($request) {
                $q->whereHas('dataInventory', function ($invQ) use ($request) {
                    if ($request->jenis === 'ASET') {
                        $invQ->where('jenis_inventory', 'ASET')
                            ->where(function ($regQ) {
                                $regQ->whereDoesntHave('registerAset')
                                    ->orWhereHas('registerAset', function ($r) {
                                        $r->whereNull('nomor_register')
                                            ->orWhere('nomor_register', '');
                                    });
                            });
                    } else {
                        $invQ->where('jenis_inventory', $request->jenis);
                    }
                });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        
        // Sinkronkan DataStock dengan DataInventory eligible untuk stok:
        // PERSEDIAAN/FARMASI + ASET tanpa nomor register.
        // Hitung total qty_input dari DataInventory per gudang per barang
        $this->syncStockFromInventory();
        
        // Urutkan berdasarkan gudang, kemudian nama barang
        $stocks = $query->orderBy('id_gudang')
            ->orderBy('id_data_barang')
            ->latest('last_updated')
            ->paginate($perPage)
            ->appends($request->query());
        
        // Filter gudang yang ditampilkan di dropdown berdasarkan role
        if ($user->hasAnyRole(['kepala_unit', 'pegawai']) && !$user->hasRole('admin')) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->get();
            } else {
                $gudangs = collect([]);
            }
        } else {
            $gudangs = MasterGudang::all();
        }

        // Map jenis barang per kombinasi barang+gudang untuk kebutuhan tampilan tabel.
        // Selalu inisialisasi agar aman saat di-compact pada semua role/filter branch.
        $jenisBarangMap = DataInventory::query()
            ->where(function ($q) {
                $this->applyStockEligibleInventoryFilter($q);
            })
            ->select(['id_data_barang', 'id_gudang', 'jenis_inventory'])
            ->get()
            ->keyBy(function ($item) {
                return $item->id_data_barang . '_' . $item->id_gudang;
            })
            ->map(function ($item) {
                return (object) ['jenis_barang' => $item->jenis_inventory];
            });

        return view('inventory.data-stock.index', compact('stocks', 'gudangs', 'jenisBarangMap'));
    }
    
    /**
     * Sinkronkan DataStock dengan DataInventory eligible untuk stok:
     * PERSEDIAAN/FARMASI + ASET yang belum memiliki nomor register.
     * Hitung total qty_input dari DataInventory per gudang per barang
     * qty_akhir di DataStock = total qty_input dari semua DataInventory untuk barang dan gudang yang sama
     */
    private function syncStockFromInventory()
    {
        // Ambil semua inventory eligible yang aktif
        $inventories = \App\Models\DataInventory::query()
            ->where(function ($q) {
                $this->applyStockEligibleInventoryFilter($q);
            })
            ->where('status_inventory', 'AKTIF')
            ->with(['dataBarang', 'gudang', 'satuan'])
            ->get();
        
        // Group by id_data_barang dan id_gudang, lalu sum qty_input
        $stockData = $inventories->groupBy(function($inv) {
            return $inv->id_data_barang . '_' . $inv->id_gudang;
        })->map(function($group) {
            $first = $group->first();
            return [
                'id_data_barang' => $first->id_data_barang,
                'id_gudang' => $first->id_gudang,
                'qty_total' => $group->sum('qty_input'),
                'id_satuan' => $first->id_satuan,
            ];
        });
        
        // Update atau create DataStock
        foreach ($stockData as $data) {
            $stock = DataStock::firstOrNew([
                'id_data_barang' => $data['id_data_barang'],
                'id_gudang' => $data['id_gudang'],
            ]);
            
            // Simpan qty_keluar yang sudah ada (dari distribusi sebelumnya)
            $existingQtyKeluar = $stock->exists ? $stock->qty_keluar : 0;
            
            // qty_akhir = total qty_input dari semua inventory record untuk barang dan gudang ini
            $stock->qty_akhir = $data['qty_total'];
            
            // Jika stock baru, set qty_awal dan qty_masuk
            if (!$stock->exists) {
                $stock->qty_awal = 0;
                $stock->qty_masuk = $data['qty_total'];
                $stock->qty_keluar = 0;
                $stock->id_satuan = $data['id_satuan'];
            } else {
                // Jika stock sudah ada, update qty_masuk berdasarkan qty_akhir dan qty_keluar
                // qty_masuk = qty_akhir + qty_keluar (karena qty_akhir = qty_masuk - qty_keluar)
                $stock->qty_masuk = $stock->qty_akhir + $existingQtyKeluar;
                // Pastikan qty_keluar tetap sama (tidak diubah)
                $stock->qty_keluar = $existingQtyKeluar;
            }
            
            $stock->last_updated = now();
            $stock->save();
        }
        
        // Hapus DataStock yang tidak memiliki inventory aktif eligible untuk stok.
        if ($stockData->count() > 0) {
            $activeBarangGudang = $stockData->map(function($data) {
                return $data['id_data_barang'] . '_' . $data['id_gudang'];
            })->toArray();
            
            // Hapus stock yang tidak ada di stockData (tidak ada inventory aktif)
            DataStock::whereHas('dataBarang', function($q) {
                $q->whereHas('dataInventory', function($invQ) {
                    $this->applyStockEligibleInventoryFilter($invQ);
                });
            })->get()->each(function($stock) use ($activeBarangGudang) {
                $key = $stock->id_data_barang . '_' . $stock->id_gudang;
                if (!in_array($key, $activeBarangGudang)) {
                    // Hanya hapus jika tidak ada inventory aktif untuk kombinasi barang dan gudang ini
                    $hasActiveInventory = \App\Models\DataInventory::where('id_data_barang', $stock->id_data_barang)
                        ->where('id_gudang', $stock->id_gudang)
                        ->where(function ($q) {
                            $this->applyStockEligibleInventoryFilter($q);
                        })
                        ->where('status_inventory', 'AKTIF')
                        ->exists();
                    
                    if (!$hasActiveInventory) {
                        $stock->delete();
                    }
                }
            });
        }
    }
}

