<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryQrScanController extends Controller
{
    public function __invoke(Request $request)
    {
        $kodeRegister = (string) $request->query('kode_register', '');

        if ($kodeRegister === '') {
            return response()->view('inventory.inventory-item.scan', [
                'inventoryItem' => null,
                'kodeRegister' => null,
                'error' => 'Kode registrasi tidak ditemukan pada QR code.',
            ], 400);
        }

        $inventoryItem = InventoryItem::with([
            'inventory.dataBarang',
            'inventory.sumberAnggaran',
            'gudang.unitKerja',
            'ruangan',
        ])
            ->where('kode_register', $kodeRegister)
            ->first();

        if (! $inventoryItem) {
            return response()->view('inventory.inventory-item.scan', [
                'inventoryItem' => null,
                'kodeRegister' => $kodeRegister,
                'error' => 'Data inventaris tidak ditemukan untuk kode registrasi tersebut.',
            ], 404);
        }

        $fotoUrl = null;
        $itemFotoUrl = $inventoryItem->fotoBarangPublicUrl();
        if ($itemFotoUrl) {
            $fotoUrl = $itemFotoUrl;
        } else {
            $uploadFoto = $inventoryItem->inventory->upload_foto ?? null;
            if ($uploadFoto && Storage::disk('public')->exists($uploadFoto)) {
                $fotoUrl = Storage::url($uploadFoto);
            }
        }

        return view('inventory.inventory-item.scan', [
            'inventoryItem' => $inventoryItem,
            'kodeRegister' => $kodeRegister,
            'error' => null,
            'qtyByKodeRegister' => 1,
            'fotoUrl' => $fotoUrl,
        ]);
    }
}

