<?php

namespace App\Services;

use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;

use App\Models\DataInventory;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FarmasiExpiryReminderService
{
    public const PRIORITAS_KRITIS = 'KRITIS';

    public const PRIORITAS_TINGGI = 'TINGGI';

    public const PRIORITAS_SEDANG = 'SEDANG';

    public const PRIORITAS_RENDAH = 'RENDAH';

    /**
     * Query dasar: baris inventory PERSEDIAAN/FARMASI yang punya tanggal kedaluwarsa, qty > 0,
     * memenuhi filter stok yang sama dengan Data Stock / rincian merk (StockMerkBreakdownService).
     * Cakupan gudang:
     * - Pegawai / Kepala Unit / Admin Gudang Unit: hanya gudang UNIT milik unit kerja (sama seperti Data Stok unit).
     * - Admin Gudang Farmasi: hanya FARMASI di gudang berkategori FARMASI (pusat).
     * - Admin, Admin Gudang umum, Kasubbag TU, dll.: semua gudang sesuai filter di atas.
     */
    public static function baseFarmasiExpiryQuery(User $user): Builder
    {
        $query = DataInventory::query()
            ->where('status_inventory', 'AKTIF')
            ->whereNotNull('tanggal_kedaluwarsa')
            ->where('qty_input', '>', 0)
            ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI']);

        StockMerkBreakdownService::applyStockEligibleInventoryFilter($query);

        if (UserScope::mustScopeToUnitKerja($user)) {
            $pegawai = MasterPegawai::query()->where('user_id', $user->id)->first();
            if ($pegawai && $pegawai->id_unit_kerja) {
                $gudangUnitIds = MasterGudang::query()
                    ->where('jenis_gudang', 'UNIT')
                    ->where('id_unit_kerja', $pegawai->id_unit_kerja)
                    ->pluck('id_gudang');
                if ($gudangUnitIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id_gudang', $gudangUnitIds);
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('admin_gudang_farmasi')) {
            $query->where('jenis_inventory', 'FARMASI')
                ->whereHas('gudang', fn ($g) => $g->where('kategori_gudang', 'FARMASI'));
        }

        return $query;
    }

    /**
     * @return Collection<int, MasterGudang>
     */
    public static function gudangFilterOptions(User $user): Collection
    {
        $base = self::baseFarmasiExpiryQuery($user);
        $ids = (clone $base)->distinct()->pluck('id_gudang')->filter()->values();

        return MasterGudang::query()
            ->whereIn('id_gudang', $ids)
            ->orderBy('nama_gudang')
            ->get();
    }

    public static function sisaHari(?Carbon $expiry, Carbon $today): int
    {
        if (! $expiry) {
            return 99999;
        }

        return (int) $today->copy()->startOfDay()->diffInDays($expiry->copy()->startOfDay(), false);
    }

    public static function prioritasFromSisaHari(int $sisaHari): string
    {
        if ($sisaHari < 0 || $sisaHari <= 7) {
            return self::PRIORITAS_KRITIS;
        }
        if ($sisaHari <= 30) {
            return self::PRIORITAS_TINGGI;
        }
        if ($sisaHari <= 90) {
            return self::PRIORITAS_SEDANG;
        }
        if ($sisaHari <= 180) {
            return self::PRIORITAS_RENDAH;
        }

        return 'LUAR_JENDELA';
    }

    public static function labelPrioritas(string $kode): string
    {
        return match ($kode) {
            self::PRIORITAS_KRITIS => 'Kritis',
            self::PRIORITAS_TINGGI => 'Tinggi',
            self::PRIORITAS_SEDANG => 'Sedang',
            self::PRIORITAS_RENDAH => 'Rendah',
            default => $kode,
        };
    }

    /**
     * @return array{kritis_tinggi: int, le_90: int, range_91_180: int, expired: int}
     */
    public static function kpiCounts(User $user): array
    {
        $today = now()->startOfDay();
        $d30 = $today->copy()->addDays(30)->toDateString();
        $d90 = $today->copy()->addDays(90)->toDateString();
        $d180 = $today->copy()->addDays(180)->toDateString();
        $todayStr = $today->toDateString();

        $base = self::baseFarmasiExpiryQuery($user);

        $kritisTinggi = (clone $base)
            ->whereDate('tanggal_kedaluwarsa', '<=', $d30)
            ->count();

        $le90 = (clone $base)
            ->whereDate('tanggal_kedaluwarsa', '>=', $todayStr)
            ->whereDate('tanggal_kedaluwarsa', '<=', $d90)
            ->count();

        $r91_180 = (clone $base)
            ->whereDate('tanggal_kedaluwarsa', '>=', $today->copy()->addDays(91)->toDateString())
            ->whereDate('tanggal_kedaluwarsa', '<=', $d180)
            ->count();

        $expired = (clone $base)
            ->whereDate('tanggal_kedaluwarsa', '<', $todayStr)
            ->count();

        return [
            'kritis_tinggi' => $kritisTinggi,
            'le_90' => $le90,
            'range_91_180' => $r91_180,
            'expired' => $expired,
        ];
    }

    /**
     * Baris paling mendesak untuk pratinjau dashboard.
     *
     * @return Collection<int, DataInventory>
     */
    public static function previewRows(User $user, int $limit = 8): Collection
    {
        $today = now()->startOfDay();

        return self::baseFarmasiExpiryQuery($user)
            ->with(['dataBarang', 'gudang', 'satuan'])
            ->where(function ($q) use ($today) {
                $q->whereBetween('tanggal_kedaluwarsa', [
                    $today->toDateString(),
                    $today->copy()->addDays(180)->toDateString(),
                ])->orWhereDate('tanggal_kedaluwarsa', '<', $today->toDateString());
            })
            ->orderBy('tanggal_kedaluwarsa')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{gudang?: string|null, search?: string|null, prioritas?: string|null, include_expired?: bool|string|null}  $filters
     */
    public static function applyListingFilters(Builder $query, array $filters, Carbon $today): Builder
    {
        if (! empty($filters['gudang'])) {
            $query->where('id_gudang', (int) $filters['gudang']);
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->whereHas('dataBarang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_data_barang', 'like', "%{$search}%");
            });
        }

        $includeExpired = filter_var($filters['include_expired'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $prioritas = (string) ($filters['prioritas'] ?? '');

        if ($prioritas === 'kritis_tinggi') {
            $query->whereDate('tanggal_kedaluwarsa', '<=', $today->copy()->addDays(30)->toDateString());
        } elseif ($prioritas === '0_90') {
            $query->whereDate('tanggal_kedaluwarsa', '>=', $today->toDateString())
                ->whereDate('tanggal_kedaluwarsa', '<=', $today->copy()->addDays(90)->toDateString());
        } elseif ($prioritas === '91_180') {
            $query->whereDate('tanggal_kedaluwarsa', '>=', $today->copy()->addDays(91)->toDateString())
                ->whereDate('tanggal_kedaluwarsa', '<=', $today->copy()->addDays(180)->toDateString());
        } elseif ($prioritas === 'expired') {
            $query->whereDate('tanggal_kedaluwarsa', '<', $today->toDateString());
        } else {
            $query->where(function ($w) use ($today, $includeExpired) {
                $w->whereBetween('tanggal_kedaluwarsa', [
                    $today->toDateString(),
                    $today->copy()->addDays(180)->toDateString(),
                ]);
                if ($includeExpired) {
                    $w->orWhereDate('tanggal_kedaluwarsa', '<', $today->toDateString());
                }
            });
        }

        return $query;
    }

    public static function decorateRowForView(DataInventory $row, Carbon $today): array
    {
        $exp = $row->tanggal_kedaluwarsa instanceof Carbon
            ? $row->tanggal_kedaluwarsa->copy()->startOfDay()
            : Carbon::parse($row->tanggal_kedaluwarsa)->startOfDay();
        $sisa = self::sisaHari($exp, $today);
        $prio = self::prioritasFromSisaHari($sisa);

        return [
            'inventory' => $row,
            'sisa_hari' => $sisa,
            'prioritas' => $prio,
            'prioritas_label' => self::labelPrioritas($prio),
        ];
    }
}
