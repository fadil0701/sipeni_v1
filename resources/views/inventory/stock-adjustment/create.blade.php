@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.stock-adjustment.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Stock Adjustment
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Stock Adjustment</h2>
        <p class="mt-1 text-sm text-gray-600">Lakukan penyesuaian stock barang</p>
    </div>
    
    <form action="{{ route('inventory.stock-adjustment.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Pilih Stock -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Stock</h3>
                <div>
                    <label for="id_stock" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Stock <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_stock" 
                        name="id_stock" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_stock') border-red-500 @enderror"
                    >
                        <option value="">Pilih Stock</option>
                        @foreach($stocks as $stock)
                            <option 
                                value="{{ $stock->id_stock }}" 
                                data-qty-akhir="{{ $stock->qty_akhir }}"
                                {{ old('id_stock') == $stock->id_stock ? 'selected' : '' }}
                            >
                                {{ $stock->dataBarang->nama_barang ?? '-' }} 
                                ({{ $stock->gudang->nama_gudang ?? '-' }})
                                - Stock: {{ number_format($stock->qty_akhir, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_stock')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih stock yang akan disesuaikan</p>
                </div>
            </div>

            <!-- Informasi Stock Saat Ini -->
            <div id="stock-info" class="bg-blue-50 rounded-lg p-6 border border-blue-200 hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Stock Saat Ini</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Awal</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold" id="qty-awal">-</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Masuk</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold" id="qty-masuk">-</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Keluar</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold" id="qty-keluar">-</dd>
                    </div>
                    <div class="sm:col-span-3">
                        <dt class="text-sm font-medium text-gray-500">Qty Akhir (Saat Ini)</dt>
                        <dd class="mt-1 text-lg text-blue-600 font-bold" id="qty-akhir">-</dd>
                    </div>
                </dl>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tanggal_adjustment" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Adjustment <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_adjustment" 
                        name="tanggal_adjustment" 
                        value="{{ old('tanggal_adjustment', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_adjustment') border-red-500 @enderror"
                    >
                    @error('tanggal_adjustment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="qty_sesudah" class="block text-sm font-medium text-gray-700 mb-2">
                        Qty Sesudah Adjustment <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="qty_sesudah" 
                        name="qty_sesudah" 
                        step="0.01"
                        min="0"
                        value="{{ old('qty_sesudah') }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('qty_sesudah') border-red-500 @enderror"
                    >
                    @error('qty_sesudah')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan jumlah stock setelah adjustment</p>
                </div>

                <div>
                    <label for="jenis_adjustment" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Adjustment <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="jenis_adjustment" 
                        name="jenis_adjustment" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_adjustment') border-red-500 @enderror"
                    >
                        <option value="">Pilih Jenis</option>
                        <option value="PENAMBAHAN" {{ old('jenis_adjustment') == 'PENAMBAHAN' ? 'selected' : '' }}>Penambahan</option>
                        <option value="PENGURANGAN" {{ old('jenis_adjustment') == 'PENGURANGAN' ? 'selected' : '' }}>Pengurangan</option>
                        <option value="KOREKSI" {{ old('jenis_adjustment', 'KOREKSI') == 'KOREKSI' ? 'selected' : '' }}>Koreksi</option>
                        <option value="OPNAME" {{ old('jenis_adjustment') == 'OPNAME' ? 'selected' : '' }}>Opname</option>
                    </select>
                    @error('jenis_adjustment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status" 
                        name="status" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-500 @enderror"
                    >
                        <option value="DRAFT" {{ old('status', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="DIAJUKAN" {{ old('status') == 'DIAJUKAN' ? 'selected' : '' }}>Ajukan</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan
                    </label>
                    <input 
                        type="text" 
                        id="alasan" 
                        name="alasan" 
                        value="{{ old('alasan') }}"
                        placeholder="Alasan adjustment..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('alasan') border-red-500 @enderror"
                    >
                    @error('alasan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan
                    </label>
                    <textarea 
                        id="keterangan" 
                        name="keterangan" 
                        rows="3"
                        placeholder="Keterangan tambahan..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('inventory.stock-adjustment.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Adjustment
            </button>
        </div>
    </form>
</div>

<script>
    // Load stock info saat stock dipilih
    document.getElementById('id_stock').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockId = this.value;
        
        if (stockId) {
            // Fetch stock data via AJAX
            fetch(`/api/stock/${stockId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('qty-awal').textContent = parseFloat(data.qty_awal || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    document.getElementById('qty-masuk').textContent = parseFloat(data.qty_masuk || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    document.getElementById('qty-keluar').textContent = parseFloat(data.qty_keluar || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    document.getElementById('qty-akhir').textContent = parseFloat(data.qty_akhir || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    document.getElementById('stock-info').classList.remove('hidden');
                    
                    // Set default qty_sesudah = qty_akhir
                    document.getElementById('qty_sesudah').value = data.qty_akhir || 0;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        } else {
            document.getElementById('stock-info').classList.add('hidden');
        }
    });
</script>
@endsection
