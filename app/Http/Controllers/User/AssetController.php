<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $assets = InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })
        ->with(['inventory.dataBarang', 'ruangan'])
        ->latest()
        ->paginate($perPage)->appends($request->query());

        return view('user.assets.index', compact('assets'));
    }

    public function show($id)
    {
        $asset = InventoryItem::whereHas('inventory', function ($query) {
            $query->where('jenis_inventory', 'ASET');
        })
        ->with(['inventory.dataBarang', 'ruangan'])
        ->findOrFail($id);

        return view('user.assets.show', compact('asset'));
    }
}

