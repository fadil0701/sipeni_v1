@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.data-inventory.show', $inventoryItem->id_inventory) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail Data Inventory
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Inventory Item</h2>
        <p class="text-sm text-gray-600 mt-1">Kode Register: <span class="font-semibold">{{ $inventoryItem->kode_register }}</span></p>
    </div>
    
    <form action="{{ route('inventory.inventory-item.update', $inventoryItem->id_item) }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Dasar -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Register</label>
                        <input type="text" value="{{ $inventoryItem->kode_register }}" disabled class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">Kode register tidak dapat diubah</p>
                    </div>

                    <div>
                        <label for="no_seri" class="block text-sm font-medium text-gray-700 mb-2">No Seri</label>
                        <input 
                            type="text" 
                            id="no_seri" 
                            name="no_seri" 
                            value="{{ old('no_seri', $inventoryItem->no_seri) }}"
                            placeholder="Masukkan nomor seri"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('no_seri') border-red-500 @enderror"
                        >
                        @error('no_seri')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kondisi_item" class="block text-sm font-medium text-gray-700 mb-2">
                            Kondisi Item <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="kondisi_item" 
                            name="kondisi_item" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kondisi_item') border-red-500 @enderror"
                        >
                            <option value="">Pilih Kondisi</option>
                            <option value="BAIK" {{ old('kondisi_item', $inventoryItem->kondisi_item) == 'BAIK' ? 'selected' : '' }}>Baik</option>
                            <option value="RUSAK_RINGAN" {{ old('kondisi_item', $inventoryItem->kondisi_item) == 'RUSAK_RINGAN' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="RUSAK_BERAT" {{ old('kondisi_item', $inventoryItem->kondisi_item) == 'RUSAK_BERAT' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        @error('kondisi_item')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_item" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Item <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_item" 
                            name="status_item" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_item') border-red-500 @enderror"
                        >
                            <option value="">Pilih Status</option>
                            <option value="AKTIF" {{ old('status_item', $inventoryItem->status_item) == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                            <option value="DISTRIBUSI" {{ old('status_item', $inventoryItem->status_item) == 'DISTRIBUSI' ? 'selected' : '' }}>Distribusi</option>
                            <option value="NONAKTIF" {{ old('status_item', $inventoryItem->status_item) == 'NONAKTIF' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status_item')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Lokasi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lokasi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang" 
                            name="id_gudang" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang') border-red-500 @enderror"
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang', $inventoryItem->id_gudang) == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }} ({{ $gudang->jenis_gudang }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_gudang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_ruangan" class="block text-sm font-medium text-gray-700 mb-2">Ruangan</label>
                        <select 
                            id="id_ruangan" 
                            name="id_ruangan" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Ruangan (Opsional)</option>
                            @foreach($ruangans as $ruangan)
                                <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan', $inventoryItem->id_ruangan) == $ruangan->id_ruangan ? 'selected' : '' }}>
                                    {{ $ruangan->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Foto Barang -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Foto Barang</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="foto_barang_file" class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                        <input
                            type="file"
                            id="foto_barang_file"
                            name="foto_barang_file"
                            accept="image/png,image/jpeg,image/jpg"
                            class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 @error('foto_barang_file') border-red-500 @enderror"
                        >
                        @error('foto_barang_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG, maksimal 10MB.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ambil dari Kamera</label>
                        <div class="flex gap-2">
                            <button type="button" id="start-camera-btn" class="px-3 py-2 border border-blue-300 rounded-md text-sm text-blue-700 bg-blue-50 hover:bg-blue-100">Buka Kamera</button>
                            <button type="button" id="capture-photo-btn" class="px-3 py-2 border border-green-300 rounded-md text-sm text-green-700 bg-green-50 hover:bg-green-100" disabled>Ambil Foto</button>
                        </div>
                        <input type="hidden" id="foto_barang_capture" name="foto_barang_capture" value="">
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-gray-200 rounded-md p-3 bg-gray-50">
                        <p class="text-xs font-medium text-gray-600 mb-2">Preview Kamera</p>
                        <video id="camera-preview" autoplay playsinline class="w-full rounded-md bg-black hidden"></video>
                        <p id="camera-help" class="text-xs text-gray-500">Klik "Buka Kamera" untuk mulai memotret.</p>
                    </div>
                    <div class="border border-gray-200 rounded-md p-3 bg-gray-50">
                        <p class="text-xs font-medium text-gray-600 mb-2">Foto Tersimpan / Hasil Capture</p>
                        <img
                            id="foto-preview"
                            src="{{ $inventoryItem->fotoBarangPublicUrl() ?? '#' }}"
                            alt="Preview Foto Barang"
                            class="w-full max-h-64 object-contain rounded-md {{ $inventoryItem->fotoBarangPublicUrl() ? '' : 'hidden' }}"
                        >
                        <p id="foto-empty-text" class="text-xs text-gray-500 {{ $inventoryItem->fotoBarangPublicUrl() ? 'hidden' : '' }}">Belum ada foto barang.</p>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($inventoryItem->qr_code)
            @php($qrUrl = $inventoryItem->qrCodePublicUrl())
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code</h3>
                <div class="flex items-center space-x-4">
                    @if($qrUrl)
                    <img src="{{ $qrUrl }}" alt="QR Code" class="h-32 w-32 border border-gray-300 rounded-md">
                    @else
                    <div class="h-32 w-32 border border-dashed border-amber-400 rounded-md flex items-center justify-center bg-amber-50 px-2 text-center">
                        <span class="text-xs text-amber-800">Berkas QR tidak ada di server (path: {{ $inventoryItem->qr_code }})</span>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-600">Kode Register: <span class="font-semibold">{{ $inventoryItem->kode_register }}</span></p>
                        <a href="{{ route('inventory.inventory-item.template-qr', $inventoryItem->id_item) }}" target="_blank" class="mt-2 inline-flex items-center px-3 py-2 border border-indigo-300 shadow-sm text-sm font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                            Lihat Template QR
                        </a>
                        @if($qrUrl)
                        {{-- <a href="{{ route('inventory.inventory-item.template-qr', $inventoryItem->id_item) }}" target="_blank" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Template QR (PNG/JPEG)
                        </a> --}}
                        @else
                        <p class="mt-2 text-xs text-gray-600">Jalankan di server: <code class="bg-gray-100 px-1 rounded">php artisan inventory:regenerate-qr-codes</code> lalu pastikan <code class="bg-gray-100 px-1 rounded">php artisan storage:link</code> aktif.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('inventory.data-inventory.show', $inventoryItem->id_inventory) }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    let cameraStream = null;
    const startCameraBtn = document.getElementById('start-camera-btn');
    const capturePhotoBtn = document.getElementById('capture-photo-btn');
    const cameraPreview = document.getElementById('camera-preview');
    const cameraHelp = document.getElementById('camera-help');
    const fotoCaptureInput = document.getElementById('foto_barang_capture');
    const fotoPreview = document.getElementById('foto-preview');
    const fotoEmptyText = document.getElementById('foto-empty-text');
    const fotoFileInput = document.getElementById('foto_barang_file');

    startCameraBtn?.addEventListener('click', async function () {
        try {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            cameraPreview.srcObject = cameraStream;
            cameraPreview.classList.remove('hidden');
            capturePhotoBtn.disabled = false;
            cameraHelp.textContent = 'Kamera aktif. Klik "Ambil Foto".';
        } catch (error) {
            cameraHelp.textContent = 'Gagal mengakses kamera. Pastikan izin kamera sudah diaktifkan.';
        }
    });

    capturePhotoBtn?.addEventListener('click', function () {
        if (!cameraStream) return;
        const canvas = document.createElement('canvas');
        canvas.width = cameraPreview.videoWidth || 1280;
        canvas.height = cameraPreview.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        fotoCaptureInput.value = dataUrl;
        fotoPreview.src = dataUrl;
        fotoPreview.classList.remove('hidden');
        fotoEmptyText.classList.add('hidden');
        cameraHelp.textContent = 'Foto berhasil di-capture. Klik Simpan untuk menyimpan.';
        // Prioritaskan hasil capture, kosongkan file upload manual.
        if (fotoFileInput) {
            fotoFileInput.value = '';
        }
    });

    fotoFileInput?.addEventListener('change', function (event) {
        const file = event.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            fotoPreview.src = e.target?.result;
            fotoPreview.classList.remove('hidden');
            fotoEmptyText.classList.add('hidden');
            // Jika user pilih upload file, kosongkan capture base64 agar tidak konflik.
            fotoCaptureInput.value = '';
        };
        reader.readAsDataURL(file);
    });

    window.addEventListener('beforeunload', function () {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
        }
    });

    document.getElementById('id_gudang').addEventListener('change', function() {
        const gudangId = this.value;
        const ruanganSelect = document.getElementById('id_ruangan');
        
        // Reset ruangan options
        ruanganSelect.innerHTML = '<option value="">Memuat ruangan...</option>';
        
        // Load ruangans based on selected gudang via AJAX
        if (gudangId) {
            fetch(`/api/gudang/${gudangId}/ruangans`)
                .then(response => response.json())
                .then(data => {
                    ruanganSelect.innerHTML = '<option value="">Pilih Ruangan (Opsional)</option>';
                    data.ruangans.forEach(ruangan => {
                        const option = document.createElement('option');
                        option.value = ruangan.id_ruangan;
                        option.textContent = ruangan.nama_ruangan;
                        ruanganSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading ruangans:', error);
                    ruanganSelect.innerHTML = '<option value="">Pilih Ruangan (Opsional)</option>';
                });
        } else {
            ruanganSelect.innerHTML = '<option value="">Pilih Ruangan (Opsional)</option>';
        }
    });
</script>
@endsection

