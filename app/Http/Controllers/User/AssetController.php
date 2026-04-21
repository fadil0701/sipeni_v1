<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $query = $this->portalAssetBaseQuery();
        $query = $this->scopePortalAssetsForUser($query);

        $assets = $query->latest()->paginate($perPage)->appends($request->query());

        return view('user.assets.index', compact('assets'));
    }

    public function show($id)
    {
        $query = $this->portalAssetBaseQuery();
        $query = $this->scopePortalAssetsForUser($query);

        $asset = $query->findOrFail($id);

        return view('user.assets.show', compact('asset'));
    }

    /**
     * Aset fisik (inventory item) berjenis ASET — untuk portal pegawai dibatasi ke unit kerja.
     */
    private function portalAssetBaseQuery(): Builder
    {
        return InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })->with(['inventory.dataBarang', 'ruangan', 'gudang']);
    }

    private function scopePortalAssetsForUser(Builder $query): Builder
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return $query;
        }

        $pegawai = MasterPegawai::where('user_id', $user->id)->first();
        if (! $pegawai?->id_unit_kerja) {
            return $query->whereRaw('1 = 0');
        }

        $unitId = (int) $pegawai->id_unit_kerja;

        return $query->where(function ($q) use ($unitId) {
            $q->whereHas('ruangan', fn ($r) => $r->where('id_unit_kerja', $unitId))
                ->orWhereHas('gudang', fn ($g) => $g->where('id_unit_kerja', $unitId));
        });
    }
}
