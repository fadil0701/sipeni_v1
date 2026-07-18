<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\MasterGudang;
use App\Models\MasterRuangan;
use App\Support\Storage\PrivateStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InventoryItemController extends Controller
{
    public function scanQrPage()
    {
        return view('inventory.inventory-item.scan-qr');
    }

    public function edit($id)
    {
        $inventoryItem = InventoryItem::with(['inventory', 'gudang.unitKerja', 'ruangan'])->findOrFail($id);
        $gudangs = MasterGudang::all();

        $unitKerjaId = $inventoryItem->gudang->id_unit_kerja ?? null;
        $ruangans = $unitKerjaId ? MasterRuangan::where('id_unit_kerja', $unitKerjaId)->get() : collect();

        return view('inventory.inventory-item.edit', compact('inventoryItem', 'gudangs', 'ruangans'));
    }

    public function update(Request $request, $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'no_seri' => 'nullable|string|max:255',
            'kondisi_item' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT',
            'status_item' => 'required|in:AKTIF,DISTRIBUSI,NONAKTIF',
            'id_gudang' => 'required|exists:master_gudang,id_gudang',
            'id_ruangan' => 'nullable|exists:master_ruangan,id_ruangan',
            'foto_barang_file' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'foto_barang_capture' => 'nullable|string',
        ]);

        if ($request->hasFile('foto_barang_file')) {
            PrivateStorage::delete($inventoryItem->foto_barang);
            $validated['foto_barang'] = PrivateStorage::storeUploadedFile($request->file('foto_barang_file'), 'foto-inventory-item');
        } elseif ($request->filled('foto_barang_capture')) {
            $capture = trim((string) $request->input('foto_barang_capture'));
            if (preg_match('/^data:image\/(png|jpe?g);base64,/i', $capture, $matches)) {
                $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                $commaPos = strpos($capture, ',');
                $binary = $commaPos !== false ? base64_decode(substr($capture, $commaPos + 1), true) : false;
                if ($binary !== false && $binary !== '') {
                    PrivateStorage::delete($inventoryItem->foto_barang);
                    $path = 'foto-inventory-item/'.uniqid('item_'.$inventoryItem->id_item.'_', true).'.'.$ext;
                    PrivateStorage::put($path, $binary);
                    $validated['foto_barang'] = $path;
                }
            }
        }

        unset($validated['foto_barang_file'], $validated['foto_barang_capture']);

        $inventoryItem->update($validated);

        return redirect()->route('inventory.data-inventory.show', $inventoryItem->id_inventory)
            ->with('success', 'Data Inventory Item berhasil diperbarui.');
    }

    public function qrImage($id): Response
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        $qrPath = $inventoryItem->resolveQrCodeStoragePath();

        if ($qrPath === null) {
            abort(404, 'File QR code tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($qrPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/svg+xml',
        };

        return response(Storage::disk('public')->get($qrPath), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function templateQr($id)
    {
        $inventoryItem = InventoryItem::with(['inventory.dataBarang', 'gudang', 'ruangan'])->findOrFail($id);

        $parts = explode('/', (string) $inventoryItem->kode_register);
        $tahun = $parts[count($parts) - 2] ?? ($inventoryItem->inventory?->tahun_anggaran ?? date('Y'));
        $qrPublicUrl = $inventoryItem->qrCodePublicUrl();
        $scanTargetUrl = rtrim((string) config('app.url'), '/')
            .'/scan/inventory-item?kode_register='.urlencode((string) $inventoryItem->kode_register);

        $payload = [
            'qrPublicUrl' => $qrPublicUrl,
            'scanTargetUrl' => $scanTargetUrl,
            'tahun' => (string) $tahun,
            'kodeReg' => (string) $inventoryItem->kode_register,
            'namaBarang' => (string) ($inventoryItem->inventory?->dataBarang?->nama_barang ?? 'Item Inventory'),
        ];
        foreach (['scanTargetUrl', 'kodeReg', 'namaBarang'] as $key) {
            $v = $payload[$key];
            if (function_exists('mb_scrub')) {
                $payload[$key] = mb_scrub($v, 'UTF-8');
            } else {
                $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                $payload[$key] = $clean !== false ? $clean : $v;
            }
        }

        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
            | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);
        $templateQrPayloadJson = json_encode($payload, $jsonFlags, 512);
        if ($templateQrPayloadJson === false) {
            $templateQrPayloadJson = '{}';
        }

        return view('inventory.inventory-item.template-qr', compact('inventoryItem', 'tahun', 'qrPublicUrl', 'templateQrPayloadJson'));
    }

    public function downloadTemplateQr($id)
    {
        $inventoryItem = InventoryItem::with(['inventory.dataBarang'])->findOrFail($id);

        $parts = explode('/', (string) $inventoryItem->kode_register);
        $tahun = $parts[count($parts) - 2] ?? ($inventoryItem->inventory?->tahun_anggaran ?? date('Y'));

        $qrPath = $inventoryItem->resolveQrCodeStoragePath();

        if ($qrPath === null) {
            return back()->with('error', 'File QR code tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($qrPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/svg+xml',
        };
        $qrBinary = Storage::disk('public')->get($qrPath);
        $qrDataUri = 'data:'.$mime.';base64,'.base64_encode($qrBinary);

        $title = 'Inventarisasi BMD';
        $subtitle = 'Puspelkes DKI Jakarta';
        $kodeReg = 'Kode Reg : '.$inventoryItem->kode_register;

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="1300" viewBox="0 0 900 1300">
  <rect x="8" y="8" width="884" height="1284" fill="#ffffff" stroke="#111111" stroke-width="6"/>
  <text x="450" y="150" text-anchor="middle" font-family="Arial, sans-serif" font-size="70" font-weight="700" fill="#111111">{$title}</text>
  <text x="450" y="230" text-anchor="middle" font-family="Arial, sans-serif" font-size="58" font-weight="700" fill="#111111">{$subtitle}</text>
  <image x="220" y="300" width="460" height="460" href="{$qrDataUri}"/>
  <text x="450" y="940" text-anchor="middle" font-family="Arial, sans-serif" font-size="74" font-weight="800" fill="#111111">Tahun {$tahun}</text>
  <text x="450" y="1040" text-anchor="middle" font-family="Arial, sans-serif" font-size="46" font-weight="700" fill="#111111">{$kodeReg}</text>
</svg>
SVG;

        $filename = str_replace(['/', '\\', ' '], '_', (string) $inventoryItem->kode_register).'.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
