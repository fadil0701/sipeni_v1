@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Scan QR Code Inventory</h2>
            <p class="text-sm text-gray-600 mt-1">Arahkan kamera ke QR code. Sistem akan otomatis membuka halaman data inventaris terkait.</p>
        </div>

        <div class="p-6">
            <div id="qr-reader" class="w-full max-w-xl mx-auto"></div>
            <div id="scan-status" class="mt-4 text-sm text-gray-600 text-center">Menunggu kamera aktif...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        const statusEl = document.getElementById('scan-status');
        const scanEndpoint = @json(route('inventory-item.scan'));
        let redirected = false;

        function setStatus(message, isError = false) {
            statusEl.textContent = message;
            statusEl.className = 'mt-4 text-sm text-center ' + (isError ? 'text-red-600' : 'text-gray-600');
        }

        function resolveScanResult(rawText) {
            const text = String(rawText || '').trim();
            if (!text) return null;

            // Format QR baru: URL lengkap yang berisi kode_register
            try {
                const url = new URL(text);
                const kode = url.searchParams.get('kode_register');
                if (kode) {
                    return `${scanEndpoint}?kode_register=${encodeURIComponent(kode)}`;
                }
            } catch (_) {
                // Bukan URL penuh, lanjut ke fallback.
            }

            // Format QR lama: hanya berisi kode register mentah.
            return `${scanEndpoint}?kode_register=${encodeURIComponent(text)}`;
        }

        const scanner = new Html5QrcodeScanner('qr-reader', {
            fps: 10,
            qrbox: { width: 260, height: 260 },
            rememberLastUsedCamera: true,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
        }, false);

        scanner.render((decodedText) => {
            if (redirected) return;

            const target = resolveScanResult(decodedText);
            if (!target) {
                setStatus('QR code tidak valid.', true);
                return;
            }

            redirected = true;
            setStatus('QR terdeteksi. Mengalihkan ke data inventaris...');
            window.location.href = target;
        }, () => {
            // ignore scan errors/noise
        });

        setStatus('Kamera aktif. Silakan scan QR code.');
    })();
</script>
@endpush

