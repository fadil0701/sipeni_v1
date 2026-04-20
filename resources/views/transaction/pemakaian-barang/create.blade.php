@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.pemakaian-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Pemakaian
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Pemakaian Barang</h2>
    </div>
    
    <form action="{{ route('transaction.pemakaian-barang.store') }}" method="POST" class="p-6" id="formPemakaian">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Pemakaian -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pemakaian</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_unit_kerja" 
                            name="id_unit_kerja" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_unit_kerja') border-red-500 @enderror"
                            onchange="loadGudangByUnit(this.value)"
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja') == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang" 
                            name="id_gudang" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang') border-red-500 @enderror"
                            onchange="loadInventoryByGudang(this.value)"
                        >
                            <option value="">Pilih Gudang</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang') == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }} ({{ $gudang->jenis_gudang }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_gudang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pegawai_pemakai" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Pemakai <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_pemakai" 
                            name="id_pegawai_pemakai" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pemakai') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Pemakai</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pemakai') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_pemakai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_pemakaian" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Pemakaian <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_pemakaian" 
                            name="tanggal_pemakaian" 
                            required
                            value="{{ old('tanggal_pemakaian', date('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_pemakaian') border-red-500 @enderror"
                        >
                        @error('tanggal_pemakaian')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_pemakaian" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_pemakaian" 
                            name="status_pemakaian" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_pemakaian') border-red-500 @enderror"
                        >
                            <option value="DRAFT" {{ old('status_pemakaian', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="DIAJUKAN" {{ old('status_pemakaian') == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                        </select>
                        @error('status_pemakaian')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="alasan_pemakaian" class="block text-sm font-medium text-gray-700 mb-2">Alasan Pemakaian</label>
                        <textarea 
                            id="alasan_pemakaian" 
                            name="alasan_pemakaian" 
                            rows="3"
                            placeholder="Masukkan alasan pemakaian barang"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('alasan_pemakaian') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan tambahan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Pemakaian -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detail Pemakaian</h3>
                    <button 
                        type="button" 
                        onclick="addDetailRow()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item
                    </button>
                </div>
                <div id="detailContainer" class="space-y-4">
                    <!-- Item akan ditambahkan di sini via JavaScript -->
                </div>

                @error('detail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.pemakaian-barang.index') }}" 
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

<!-- Template untuk item detail (hidden) -->
<template id="itemTemplate">
    <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
            <div class="sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Barang <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_inventory]" 
                    required
                    class="select-inventory block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="updateInventoryInfo(this)"
                >
                    <option value="">Pilih Barang</option>
                    @foreach($inventories as $inventory)
                        <option 
                            value="{{ $inventory->id_inventory }}" 
                            data-nama="{{ $inventory->dataBarang->nama_barang ?? '-' }}"
                            data-qty="{{ $inventory->qty_input }}"
                            data-satuan="{{ $inventory->satuan->id_satuan ?? '' }}"
                            data-nama-satuan="{{ $inventory->satuan->nama_satuan ?? '-' }}"
                        >
                            {{ $inventory->dataBarang->nama_barang ?? '-' }} (Stok: {{ $inventory->qty_input }} {{ $inventory->satuan->nama_satuan ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Tersedia
                </label>
                <input 
                    type="text" 
                    class="qty-tersedia block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Pemakaian <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][qty_pemakaian]" 
                    required
                    min="0.01"
                    step="0.01"
                    placeholder="0"
                    class="qty-pemakaian-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Satuan <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_satuan]" 
                    required
                    class="select-satuan block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                    <option value="">Pilih Satuan</option>
                    @foreach($satuans as $satuan)
                        <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pemakaian Item</label>
                <input 
                    type="text" 
                    name="detail[INDEX][alasan_pemakaian_item]" 
                    placeholder="Opsional"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>
        <div class="mt-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan Item</label>
            <textarea 
                name="detail[INDEX][keterangan]" 
                rows="2"
                placeholder="Keterangan tambahan (opsional)"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            ></textarea>
        </div>
        <div class="mt-2 flex justify-end">
            <button 
                type="button" 
                onclick="removeDetailRow(this)"
                class="text-red-600 hover:text-red-900 text-sm font-medium"
            >
                Hapus Item
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
let detailIndex = 0;
let inventories = @json($inventories);

function loadGudangByUnit(unitId) {
    // Filter gudang berdasarkan unit kerja
    const gudangSelect = document.getElementById('id_gudang');
    const options = gudangSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
        } else {
            // Logic untuk filter gudang berdasarkan unit kerja bisa ditambahkan di sini
            option.style.display = 'block';
        }
    });
}

function loadInventoryByGudang(gudangId) {
    // Filter inventory berdasarkan gudang
    const inventorySelects = document.querySelectorAll('.select-inventory');
    inventorySelects.forEach(select => {
        const options = select.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                // Logic untuk filter inventory berdasarkan gudang bisa ditambahkan di sini
                option.style.display = 'block';
            }
        });
    });
}

function addDetailRow() {
    const template = document.getElementById('itemTemplate');
    const container = document.getElementById('detailContainer');
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, detailIndex);
    const itemElement = tempDiv.firstElementChild;
    
    container.appendChild(itemElement);
    detailIndex++;
}

function removeDetailRow(button) {
    const row = button.closest('.item-row');
    row.remove();
}

function updateInventoryInfo(select) {
    const row = select.closest('.item-row');
    const option = select.options[select.selectedIndex];
    
    if (option && option.value) {
        const qtyTersedia = row.querySelector('.qty-tersedia');
        const qtyPemakaian = row.querySelector('.qty-pemakaian-input');
        const satuanSelect = row.querySelector('.select-satuan');
        
        if (qtyTersedia) {
            qtyTersedia.value = option.dataset.qty || '0';
        }
        
        if (qtyPemakaian) {
            qtyPemakaian.max = option.dataset.qty || '0';
        }
        
        if (satuanSelect && option.dataset.satuan) {
            satuanSelect.value = option.dataset.satuan;
        }
    }
}

// Tambah satu baris saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    addDetailRow();
    
    // Validasi form sebelum submit
    const formPemakaian = document.getElementById('formPemakaian');
    if (formPemakaian) {
        formPemakaian.addEventListener('submit', function(e) {
            const detailContainer = document.getElementById('detailContainer');
            const detailRows = detailContainer.querySelectorAll('.item-row');
            
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Detail pemakaian tidak boleh kosong. Silakan tambahkan minimal satu item.');
                return false;
            }
            
            // Validasi setiap item
            let isValid = true;
            let emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyPemakaian = row.querySelector('[name*="[qty_pemakaian]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const qtyTersedia = parseFloat(row.querySelector('.qty-tersedia').value || 0);
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Barang`);
                }
                if (!qtyPemakaian || !qtyPemakaian.value || parseFloat(qtyPemakaian.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Pemakaian`);
                } else if (parseFloat(qtyPemakaian.value) > qtyTersedia) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Pemakaian tidak boleh lebih dari Qty Tersedia (${qtyTersedia})`);
                }
                if (!idSatuan || !idSatuan.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Satuan`);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi:\n' + emptyFields.join('\n'));
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection

