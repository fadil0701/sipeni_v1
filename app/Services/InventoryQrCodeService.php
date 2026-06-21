<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

            $qrCodePath = 'qrcodes/inventory_item/'.str_replace('\\', '/', $kodeRegister).'.'.$format;
            $scanUrl = rtrim((string) config('app.url'), '/').'/scan/inventory-item?kode_register='.urlencode($kodeRegister);
            $qrContent = QrCode::format($format)->size(200)->generate($scanUrl);
            $binary = (string) $qrContent;

            $disk = Storage::disk('public');
            $directory = dirname($qrCodePath);
            if ($directory !== '.' && $directory !== '') {
                $disk->makeDirectory($directory, 0755, true);
            }

            if (! $disk->put($qrCodePath, $binary)) {
                Log::error('QR Code: gagal menulis berkas (cek izin folder storage/app/public).', [
                    'kode_register' => $kodeRegister,
                    'path' => $qrCodePath,
                ]);

                return null;
            }

            return $qrCodePath;
        } catch (\Throwable $e) {
            Log::error('QR Code generation failed: '.$e->getMessage(), [
                'kode_register' => $kodeRegister,
                'error' => $e->getMessage(),
                'hint' => 'Pastikan direktori storage/app/public dan turunannya dapat ditulis oleh user web server (Linux: chmod/chown; Laragon: jalankan web server dengan user yang punya akses tulis ke folder proyek).',
            ]);

            return null;
        }
    }
}
