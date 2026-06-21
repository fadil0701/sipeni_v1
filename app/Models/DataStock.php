<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataStock extends Model
{
    protected $table = 'data_stock';
    protected $primaryKey = 'id_stock';
    public $timestamps = true;

    protected $fillable = [
        'id_data_barang',
        'id_gudang',
        'qty_awal',
        'qty_masuk',
        'qty_keluar',
        'qty_akhir',
        'id_satuan',
        'last_updated',
    ];

    protected $casts = [
        'qty_awal' => 'decimal:2',
        'qty_masuk' => 'decimal:2',
        'qty_keluar' => 'decimal:2',
        'qty_akhir' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    // Relationships
    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang', 'id_gudang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'id_stock', 'id_stock');
    }

    /**
     * Snapshot stok banyak barang sekaligus (hindari N+1 query per barang).
     *
     * @param  list<int>  $barangIds
     * @param  list<int|string>  $stockPersediaanIds
     * @param  list<int|string>  $stockFarmasiIds
     * @return array<string, array{total: float, stock_gudang_pusat_persediaan: float, stock_gudang_pusat_farmasi: float, per_gudang: \Illuminate\Support\Collection<int, array<string, mixed>>}>
     */
    public static function buildBulkStockSnapshot(array $barangIds, array $stockPersediaanIds, array $stockFarmasiIds): array
    {
        $barangIds = array_values(array_unique(array_map('intval', array_filter($barangIds))));
        if ($barangIds === []) {
            return [];
        }

        $gudangPusat = \App\Models\MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])
            ->get(['id_gudang', 'kategori_gudang', 'nama_gudang'])
            ->keyBy('kategori_gudang');

        $idGudangPersediaan = (int) ($gudangPusat->get('PERSEDIAAN')?->id_gudang ?? 0);
        $idGudangFarmasi = (int) ($gudangPusat->get('FARMASI')?->id_gudang ?? 0);
        $pusatIds = array_values(array_filter([$idGudangPersediaan, $idGudangFarmasi]));

        $stocks = self::query()
            ->whereIn('id_data_barang', $barangIds)
            ->get(['id_data_barang', 'id_gudang', 'qty_akhir', 'id_satuan']);

        $gudangNames = $pusatIds !== []
            ? \App\Models\MasterGudang::query()->whereIn('id_gudang', $pusatIds)->pluck('nama_gudang', 'id_gudang')
            : collect();

        $satuanIds = $stocks->pluck('id_satuan')->filter()->unique()->values()->all();
        $satuans = $satuanIds !== []
            ? \App\Models\MasterSatuan::query()->whereIn('id_satuan', $satuanIds)->pluck('nama_satuan', 'id_satuan')
            : collect();

        $persediaanSet = array_flip(array_map('intval', $stockPersediaanIds));
        $farmasiSet = array_flip(array_map('intval', $stockFarmasiIds));
        $grouped = $stocks->groupBy('id_data_barang');

        $result = [];
        foreach ($barangIds as $idBarang) {
            $key = (string) $idBarang;
            $rows = $grouped->get($idBarang, collect());

            $perGudang = $rows
                ->filter(fn ($stock) => in_array((int) $stock->id_gudang, $pusatIds, true))
                ->map(function ($stock) use ($gudangNames, $satuans) {
                    return [
                        'id_gudang' => $stock->id_gudang,
                        'nama_gudang' => $gudangNames[$stock->id_gudang] ?? '-',
                        'qty_akhir' => $stock->qty_akhir,
                        'satuan' => $satuans[$stock->id_satuan] ?? '-',
                    ];
                })
                ->values();

            $result[$key] = [
                'total' => (float) $rows->sum('qty_akhir'),
                'stock_gudang_pusat_persediaan' => isset($persediaanSet[$idBarang]) && $idGudangPersediaan
                    ? (float) $rows->where('id_gudang', $idGudangPersediaan)->sum('qty_akhir')
                    : 0.0,
                'stock_gudang_pusat_farmasi' => isset($farmasiSet[$idBarang]) && $idGudangFarmasi
                    ? (float) $rows->where('id_gudang', $idGudangFarmasi)->sum('qty_akhir')
                    : 0.0,
                'per_gudang' => $perGudang,
            ];
        }

        return $result;
    }

    /**
     * Get total stock available for a barang across all gudang
     */
    public static function getTotalStock($idDataBarang): float
    {
        return self::where('id_data_barang', $idDataBarang)
            ->sum('qty_akhir') ?? 0;
    }

    /**
     * Get stock di gudang pusat saja (untuk PERSEDIAAN atau FARMASI)
     * @param int $idDataBarang
     * @param string $kategoriGudang 'PERSEDIAAN' atau 'FARMASI'
     * @return float
     */
    public static function getStockGudangPusat($idDataBarang, string $kategoriGudang): float
    {
        $idGudangPusat = \App\Models\MasterGudang::where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', $kategoriGudang)
            ->value('id_gudang');

        if (!$idGudangPusat) {
            return 0;
        }

        return (float) self::where('id_data_barang', $idDataBarang)
            ->where('id_gudang', $idGudangPusat)
            ->sum('qty_akhir');
    }

    /**
     * Get stock per gudang for a barang
     */
    public static function getStockPerGudang($idDataBarang): \Illuminate\Support\Collection
    {
        return self::where('id_data_barang', $idDataBarang)
            ->with('gudang', 'satuan')
            ->get()
            ->map(function($stock) {
                return [
                    'id_gudang' => $stock->id_gudang,
                    'nama_gudang' => $stock->gudang->nama_gudang ?? '-',
                    'qty_akhir' => $stock->qty_akhir,
                    'satuan' => $stock->satuan->nama_satuan ?? '-',
                ];
            });
    }

    /**
     * Get stock per gudang pusat saja (untuk Farmasi/Persediaan)
     * Hanya mengambil stock dari gudang dengan jenis_gudang = 'PUSAT' dan kategori_gudang = 'FARMASI' atau 'PERSEDIAAN'
     */
    public static function getStockPerGudangPusat($idDataBarang): \Illuminate\Support\Collection
    {
        // Ambil ID gudang pusat untuk Farmasi dan Persediaan
        $gudangPusatIds = \App\Models\MasterGudang::where('jenis_gudang', 'PUSAT')
            ->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])
            ->pluck('id_gudang')
            ->toArray();

        if (empty($gudangPusatIds)) {
            return collect([]);
        }

        return self::where('id_data_barang', $idDataBarang)
            ->whereIn('id_gudang', $gudangPusatIds)
            ->with('gudang', 'satuan')
            ->get()
            ->map(function($stock) {
                return [
                    'id_gudang' => $stock->id_gudang,
                    'nama_gudang' => $stock->gudang->nama_gudang ?? '-',
                    'qty_akhir' => $stock->qty_akhir,
                    'satuan' => $stock->satuan->nama_satuan ?? '-',
                ];
            });
    }
}
