<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\PaginationHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\StockWarehouseSummaryViewHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FarmasiExpiryReminderService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FarmasiKedaluwarsaController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = $this->userWithPermission('inventory.farmasi-kedaluwarsa.index');

        $today = \call_user_func('\\now')->startOfDay();
        $filters = [
            'gudang' => $request->input('gudang'),
            'search' => $request->input('search'),
            'prioritas' => $request->input('prioritas'),
            'include_expired' => $request->boolean('include_expired'),
        ];

        $gudangs = FarmasiExpiryReminderService::gudangFilterOptions($user);

        $filtersAgg = $filters;
        unset($filtersAgg['gudang']);
        $aggBase = FarmasiExpiryReminderService::baseFarmasiExpiryQuery($user);
        FarmasiExpiryReminderService::applyListingFilters($aggBase, $filtersAgg, $today);
        $gudangIds = $gudangs->pluck('id_gudang')->filter()->values();
        $aggregatesByGudang = collect();
        if ($gudangIds->isNotEmpty()) {
            $aggregatesByGudang = (clone $aggBase)
                ->whereIn('id_gudang', $gudangIds->all())
                ->selectRaw('id_gudang, COUNT(*) as sku_count, COALESCE(SUM(qty_input), 0) as qty_sum')
                ->groupBy('id_gudang')
                ->get()
                ->keyBy('id_gudang');
        }
        $gudangCards = $gudangs->map(function ($gudang) use ($aggregatesByGudang) {
            $gudangId = data_get($gudang, 'id_gudang');
            $row = $aggregatesByGudang->get($gudangId);

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

        $query = FarmasiExpiryReminderService::baseFarmasiExpiryQuery($user);
        FarmasiExpiryReminderService::applyListingFilters($query, $filters, $today);
        $query->with(['dataBarang', 'gudang', 'satuan'])
            ->orderBy('tanggal_kedaluwarsa');

        $perPage = PaginationHelper::getPerPage($request, 25);
        $appendQuery = array_merge($request->query(), ['tampilan' => $tampilan]);

        if ($tampilan === 'cards') {
            $paginatorClass = '\\Illuminate\\Pagination\\LengthAwarePaginator';
            $rows = new $paginatorClass(
                collect(),
                0,
                max(1, (int) $perPage),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $rows->appends($appendQuery);
        } else {
            $rows = $query->paginate($perPage)->appends($appendQuery);
        }

        $kpis = FarmasiExpiryReminderService::kpiCounts($user);

        return \call_user_func('\\view', 'inventory.farmasi-kedaluwarsa.index', compact(
            'rows',
            'kpis',
            'gudangs',
            'today',
            'gudangCards',
            'tampilan',
            'showWarehouseSummaryCards'
        ));
    }

    public function export(\Illuminate\Http\Request $request): StreamedResponse
    {
        $user = $this->userWithPermission('inventory.farmasi-kedaluwarsa.export');

        $today = \call_user_func('\\now')->startOfDay();
        $filters = [
            'gudang' => $request->input('gudang'),
            'search' => $request->input('search'),
            'prioritas' => $request->input('prioritas'),
            'include_expired' => $request->boolean('include_expired'),
        ];

        $query = FarmasiExpiryReminderService::baseFarmasiExpiryQuery($user);
        FarmasiExpiryReminderService::applyListingFilters($query, $filters, $today);
        $items = $query->with(['dataBarang', 'gudang', 'satuan'])
            ->orderBy('tanggal_kedaluwarsa')
            ->get();

        $filename = 'reminder-kedaluwarsa-farmasi-'.\call_user_func('\\now')->format('Ymd_His').'.csv';

        return \call_user_func('\\response')->streamDownload(function () use ($items, $today): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Jenis Inventory',
                'Kode Barang',
                'Nama Barang',
                'No Batch',
                'Tanggal Kedaluwarsa',
                'Sisa Hari',
                'Prioritas',
                'Gudang',
                'Qty',
                'Satuan',
                'Merk',
                'Lokasi Rak',
            ]);
            foreach ($items as $row) {
                $meta = FarmasiExpiryReminderService::decorateRowForView($row, $today);
                fputcsv($out, [
                    (string) ($row->jenis_inventory ?? ''),
                    $row->dataBarang->kode_data_barang ?? '',
                    $row->dataBarang->nama_barang ?? '',
                    (string) ($row->no_batch ?? ''),
                    $row->tanggal_kedaluwarsa ? $row->tanggal_kedaluwarsa->format('Y-m-d') : '',
                    (string) $meta['sisa_hari'],
                    $meta['prioritas_label'],
                    $row->gudang->nama_gudang ?? '',
                    (string) $row->qty_input,
                    $row->satuan->nama_satuan ?? '',
                    (string) ($row->merk ?? ''),
                    '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function userWithPermission(string $permission): User
    {
        $user = \call_user_func('\\auth')->user();
        if (! ($user instanceof User && PermissionHelper::canAccess($user, $permission))) {
            \call_user_func('\\abort', 403);
        }

        return $user;
    }
}
