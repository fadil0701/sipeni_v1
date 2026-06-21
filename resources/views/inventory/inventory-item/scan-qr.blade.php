@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200 text-center sm:text-left">
            <h2 class="text-xl font-semibold text-gray-900">Scan QR Code Inventory</h2>
            <p class="text-sm text-gray-600 mt-1">Arahkan kamera ke QR code. Sistem akan otomatis membuka halaman data inventaris terkait.</p>
        </div>

        <div class="p-6">
            <div class="sipeni-qr-scan">
                <div class="sipeni-qr-scan__viewport">
                    <div id="qr-placeholder" class="sipeni-qr-scan__placeholder">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h4V3M17 3h4v4M21 17h-4v4M7 21H3v-4M7 8h3v3H7V8zm10 0h-3v3h3V8zM7 13h3v3H7v-3zm7 0h-3v3h3v-3z"/>
                        </svg>
                        <p class="text-sm">Klik tombol di bawah untuk mengaktifkan kamera.</p>
                    </div>
                    <div id="qr-reader" class="sipeni-qr-scan__reader"></div>
                </div>

                <div class="sipeni-qr-scan__actions">
                    <button type="button" id="qr-start-btn" class="btn-primary-ui">Buka Kamera</button>
                    <button type="button" id="qr-stop-btn" class="btn-secondary-ui hidden">Matikan Kamera</button>
                </div>

                <p id="scan-status" class="sipeni-qr-scan__status">Menunggu izin kamera...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        const statusEl = document.getElementById('scan-status');
        const startBtn = document.getElementById('qr-start-btn');
        const stopBtn = document.getElementById('qr-stop-btn');
        const placeholderEl = document.getElementById('qr-placeholder');
        const scanEndpoint = @json(route('inventory-item.scan'));
        let redirected = false;
        let scanner = null;
        let active = false;

        function setStatus(message, isError) {
            statusEl.textContent = message;
            statusEl.classList.toggle('is-error', Boolean(isError));
        }

        function hidePlaceholder() {
            if (placeholderEl) {
                placeholderEl.classList.add('hidden');
            }
        }

        function showPlaceholder() {
            if (placeholderEl) {
                placeholderEl.classList.remove('hidden');
            }
        }

        function toggleControls(running) {
            active = running;
            startBtn.classList.toggle('hidden', running);
            stopBtn.classList.toggle('hidden', !running);
        }

        function resolveScanResult(rawText) {
            const text = String(rawText || '').trim();
            if (!text) return null;

            try {
                const url = new URL(text);
                const kode = url.searchParams.get('kode_register');
                if (kode) {
                    return scanEndpoint + '?kode_register=' + encodeURIComponent(kode);
                }
            } catch (_) {}

            return scanEndpoint + '?kode_register=' + encodeURIComponent(text);
        }

        function onScanSuccess(decodedText) {
            if (redirected) return;

            const target = resolveScanResult(decodedText);
            if (!target) {
                setStatus('QR code tidak valid.', true);
                return;
            }

            redirected = true;
            setStatus('QR terdeteksi. Mengalihkan ke data inventaris...');
            window.location.href = target;
        }

        function pickCameraConfig() {
            return { facingMode: 'environment' };
        }

        async function startScanner() {
            if (active || redirected) return;

            if (typeof Html5Qrcode === 'undefined') {
                setStatus('Library scanner gagal dimuat. Periksa koneksi internet.', true);
                return;
            }

            setStatus('Meminta izin kamera...');
            startBtn.disabled = true;

            try {
                scanner = new Html5Qrcode('qr-reader', { verbose: false });
                hidePlaceholder();

                await scanner.start(
                    pickCameraConfig(),
                    {
                        fps: 10,
                        qrbox: function (viewWidth, viewHeight) {
                            const size = Math.min(viewWidth, viewHeight, 280) * 0.75;
                            return { width: Math.floor(size), height: Math.floor(size) };
                        },
                        aspectRatio: 1.0,
                    },
                    onScanSuccess,
                    function () {}
                );

                toggleControls(true);
                setStatus('Kamera aktif. Arahkan ke QR code inventaris.');
            } catch (error) {
                showPlaceholder();
                toggleControls(false);
                const msg = String(error && error.message ? error.message : error);
                if (/notallowed|permission/i.test(msg)) {
                    setStatus('Izin kamera ditolak. Aktifkan izin kamera di browser lalu coba lagi.', true);
                } else if (/notfound|devices/i.test(msg)) {
                    setStatus('Kamera tidak ditemukan pada perangkat ini.', true);
                } else {
                    setStatus('Gagal membuka kamera: ' + msg, true);
                }
            } finally {
                startBtn.disabled = false;
            }
        }

        async function stopScanner() {
            if (!scanner || !active) return;

            try {
                await scanner.stop();
                await scanner.clear();
            } catch (_) {}

            scanner = null;
            toggleControls(false);
            showPlaceholder();
            setStatus('Kamera dimatikan.');
        }

        startBtn.addEventListener('click', startScanner);
        stopBtn.addEventListener('click', stopScanner);

        window.addEventListener('beforeunload', function () {
            if (scanner && active) {
                scanner.stop().catch(function () {});
            }
        });
    })();
</script>
@endpush
