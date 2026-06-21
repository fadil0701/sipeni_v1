<?php

namespace App\Http\Controllers\Inventory;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Helpers\PaginationHelper;
use App\Helpers\StockWarehouseSummaryViewHelper;
use App\Http\Controllers\Controller;
use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Services\DataStockSyncService;
use App\Services\StockMerkBreakdownService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DataStockController extends Controller
{
    private function applyStockEligibleInventoryFilter($query): void
    {
        StockMerkBreakdownService::applyStockEligibleInventoryFilter($query);
    }

    /**
     * Rincian stok per merk (satu barang + gudang dapat punya banyak inventory dengan merk berbeda).
     */
    public function merkBreakdown(Request $request)
    {
        $validated = $request->validate([
            'id_data_barang' => 'required|integer|exists:master_data_barang,id_data_barang',
            'id_gudang' => 'required|integer|exists:master_gudang,id_gudang',
        ]);

        $user = Auth::user();
        $this->assertUserCanViewStockForGudang($user, (int) $validated['id_gudang']);

        $stock = DataStock::with(['dataBarang', 'gudang', 'satuan'])
            ->where('id_data_barang', $validated['id_data_barang'])
            ->where('id_gudang', $validated['id_gudang'])
            ->first();

        if (! $stock) {
            abort(404, 'Data stok tidak ditemukan untuk kombinasi barang dan gudang ini.');
        }

        $breakdownRows = StockMerkBreakdownService::breakdownByMerk(
            (int) $validated['id_data_barang'],
            (int) $validated['id_gudang']
        );
        $sumInventory = StockMerkBreakdownService::sumBreakdownQty($breakdownRows);

        $backUrl = route('inventory.data-stock.index', $request->only(['gudang', 'jenis', 'merk', 'no_batch', 'search', 'tampilan']));

        return view('inventory.data-stock.merk-breakdown', compact(
            'stock',
            'breakdownRows',
            'sumInventory',
            'backUrl'
        ));
    }

    private function assertUserCanViewStockForGudang($user, int $idGudang): void
    {
        if (UserScope::canViewCrossUnitData($user)) {
            return;
        }

        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if (! $pegawai || ! $pegawai->id_unit_kerja) {
                abort(403);
            }
            $allowed = MasterGudang::query()
                ->where('jenis_gudang', 'UNIT')
                ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                ->where('id_gudang', $idGudang)
                ->exists();
            if (! $allowed) {
                abort(403, 'Anda tidak memiliki akses ke gudang ini.');
            }

            return;
        }

        // Role lain (admin_gudang*, kasubbag_tu, dll.) mengikuti tampilan index: akses global.
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        DataStockSyncService::syncFromInventory();

        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangs = MasterGudang::where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->orderBy('nama_gudang')
                    ->get();
            } else {
                $gudangs = collect([]);
            }
        } else {
            $gudangs = MasterGudang::query()->orderBy('nama_gudang')->get();
        }

        $baseQuery = $this->newStockIndexBaseQuery($user, false);
        $this->applyStockIndexFilters($baseQuery, $request, false);

        $gudangIds = $gudangs->pluck('id_gudang')->filter()->values();
        $aggregatesByGudang = collect();
        if ($gudangIds->isNotEmpty()) {
            $aggregatesByGudang = (clone $baseQuery)
                ->whereIn('id_gudang', $gudangIds->all())
                ->selectRaw('id_gudang, COUNT(*) as sku_count, COALESCE(SUM(qty_akhir), 0) as qty_sum')
                ->groupBy('id_gudang')
                ->get()
                ->keyBy('id_gudang');
        }

        $gudangCards = $gudangs->map(function ($gudang) use ($aggregatesByGudang) {
            $row = $aggregatesByGudang->get($gudang->id_gudang);

            return [
                'gudang' => $gudang,
                'sku_count' => $row ? (int) $row->sku_count : 0,
                'qty_sum' => $row ? (float) $row->qty_sum : 0.0,
            ];
        });

        $tampilan = $request->get('tampilan', 'tabel');
        if (! in_array($tampilan, ['tabel', 'cards'], true)) {
            $tampilan = 'tabel';
        }
        $showWarehouseSummaryCards = StockWarehouseSummaryViewHelper::canAccessSummaryCards($user);
        if (! $showWarehouseSummaryCards) {
            $tampilan = 'tabel';
        }

        $query = $this->newStockIndexBaseQuery($user, true);
        $this->applyStockIndexFilters($query, $request, true);

        $perPage = PaginationHelper::getPerPage($request, 10);

        if ($tampilan === 'cards') {
            $stocks = new LengthAwarePaginator(
                collect(),
                0,
                max(1, (int) $perPage),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $stocks->appends(array_merge($request->query(), ['tampilan' => $tampilan]));
        } else {
            $stocks = $query->orderBy('id_gudang')
                ->orderBy('id_data_barang')
                ->latest('last_updated')
                ->paginate($perPage)
                ->appends(array_merge($request->query(), ['tampilan' => $tampilan]));
        }

        $jenisBarangMap = DataInventory::query()
            ->where(function ($q) use ($user) {
                $this->applyStockEligibleInventoryFilter($q);
                if (StockWarehouseSummaryViewHelper::shouldLimitStockViewsToPersediaanFarmasiForUnit($user)) {
                    $q->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI']);
                }
            })
            ->select(['id_data_barang', 'id_gudang', 'jenis_inventory'])
            ->get()
            ->keyBy(function ($item) {
                return $item->id_data_barang.'_'.$item->id_gudang;
            })
            ->map(function ($item) {
                return (object) ['jenis_barang' => $item->jenis_inventory];
            });

        return view('inventory.data-stock.index', compact(
            'stocks',
            'gudangs',
            'jenisBarangMap',
            'gudangCards',
            'tampilan',
            'showWarehouseSummaryCards'
        ));
    }

    /**
     * Query dasar Data Stok (tanpa filter gudang/jenis/merk dari request).
     */
    private function newStockIndexBaseQuery($user, bool $withRelations = true): Builder
    {
        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $q = DataStock::query()
                    ->whereHas('gudang', function ($g) use ($pegawai) {
                        $g->where('jenis_gudang', 'UNIT')
                            ->where('id_unit_kerja', $pegawai->id_unit_kerja);
                    })
                    ->whereHas('dataBarang', function ($b) use ($user) {
                        $b->whereHas('dataInventory', function ($invQ) use ($user) {
                            $this->applyStockIndexInventoryEligibility($invQ, $user);
                        });
                    });
                if ($withRelations) {
                    $q->with(['dataBarang', 'gudang', 'satuan']);
                }

                return $q;
            }

            return DataStock::query()->whereRaw('1 = 0');
        }

        $q = DataStock::query()
            ->whereHas('dataBarang', function ($b) use ($user) {
                $b->whereHas('dataInventory', function ($invQ) use ($user) {
                    $this->applyStockIndexInventoryEligibility($invQ, $user);
                });
            });
        if ($withRelations) {
            $q->with(['dataBarang', 'gudang', 'satuan']);
        }

        return $q;
    }

    private function applyStockIndexInventoryEligibility(Builder $invQ, $user): void
    {
        $this->applyStockEligibleInventoryFilter($invQ);
        if (StockWarehouseSummaryViewHelper::shouldLimitStockViewsToPersediaanFarmasiForUnit($user)) {
            $invQ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI']);
        }
    }

    private function applyStockIndexFilters(Builder $query, Request $request, bool $applyGudangFromRequest): void
    {
        if ($applyGudangFromRequest && $request->filled('gudang')) {
            $query->where('id_gudang', $request->gudang);
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
    }
}
