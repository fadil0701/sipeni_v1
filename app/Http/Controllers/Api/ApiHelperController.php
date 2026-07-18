<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataStock;
use App\Models\MasterGudang;
use App\Models\MasterRuangan;
use App\Services\GeocodeService;
use App\Support\Rbac\UserScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiHelperController extends Controller
{
    public function gudangRuangans(int $id): JsonResponse
    {
        $gudang = MasterGudang::with('unitKerja')->findOrFail($id);
        UserScope::assertCanAccessUnitKerjaData(request()->user(), (int) $gudang->id_unit_kerja);

        $ruangans = MasterRuangan::where('id_unit_kerja', $gudang->id_unit_kerja)->get();

        return response()->json(['ruangans' => $ruangans]);
    }

    public function stockDetail(int $id): JsonResponse
    {
        $stock = DataStock::with('gudang')->findOrFail($id);
        $gudang = $stock->gudang;
        if ($gudang) {
            UserScope::assertCanAccessUnitKerjaData(request()->user(), (int) $gudang->id_unit_kerja);
        }

        return response()->json([
            'qty_awal' => $stock->qty_awal,
            'qty_masuk' => $stock->qty_masuk,
            'qty_keluar' => $stock->qty_keluar,
            'qty_akhir' => $stock->qty_akhir,
        ]);
    }

    public function reverseGeocode(Request $request, GeocodeService $geocode): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $alamat = $geocode->reverse((float) $validated['lat'], (float) $validated['lng']);

        return response()->json([
            'success' => filled($alamat),
            'alamat' => $alamat,
        ]);
    }
}
