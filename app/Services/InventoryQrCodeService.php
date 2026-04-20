<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventoryQrCodeService
{
    /**
     * Generate file QR (SVG) dan kembalikan path relatif terhadap disk "public"
     * (contoh: qrcodes/inventory_item/kode/tahun/0001.svg).
     */
    public function generateForKodeRegister(string $kodeRegister): ?string
    {
        try {
            $pathParts = explode('/', $kodeRegister);
            $baseDir = storage_path('app/public/qrcodes/inventory_item');

            $currentDir = $baseDir;
            for ($i = 0; $i < count($pathParts) - 1; $i++) {
                $currentDir .= DIRECTORY_SEPARATOR.$pathParts[$i];
                if (! file_exists($currentDir)) {
                    if (! mkdir($currentDir, 0755, true) && ! is_dir($currentDir)) {
                        Log::error('QR Code directory tidak dapat dibuat: '.$currentDir);

                        return null;
                    }
                }
            }

            // simple-qrcode mendukung svg/png/eps.
            // PNG membutuhkan ekstensi imagick. Jika tidak ada, fallback ke SVG agar proses tidak gagal.
            $format = strtolower((string) config('app.qr_code_format', env('QR_CODE_FORMAT', 'png')));
            if (! in_array($format, ['png', 'svg', 'eps'], true)) {
                $format = 'png';
            }
            if ($format === 'png' && ! extension_loaded('imagick')) {
                Log::warning('QR_CODE_FORMAT=png tetapi imagick tidak tersedia. Fallback ke svg.');
                $format = 'svg';
            }

            $qrCodeFileName = end($pathParts).'.'.$format;
            $qrCodePath = 'qrcodes/inventory_item/'.str_replace('\\', '/', $kodeRegister).'.'.$format;
            $fullPath = $currentDir.DIRECTORY_SEPARATOR.$qrCodeFileName;

            $scanUrl = rtrim((string) config('app.url'), '/').'/scan/inventory-item?kode_register='.urlencode($kodeRegister);

            QrCode::format($format)->size(200)->generate($scanUrl, $fullPath);

            return $qrCodePath;
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: '.$e->getMessage(), [
                'kode_register' => $kodeRegister,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
