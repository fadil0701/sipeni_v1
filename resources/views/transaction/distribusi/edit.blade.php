@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Distribusi
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Distribusi Barang (SBBK)</h2>
        <p class="text-sm text-gray-600 mt-1">No. SBBK: <span class="font-semibold">{{ $distribusi->no_sbbk }}</span></p>
    </div>
    
    <form action="{{ route('transaction.distribusi.update', $distribusi->id_distribusi) }}" method="POST" class="p-6" id="formDistribusi">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_distribusi" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Distribusi <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="tanggal_distribusi" 
                            name="tanggal_distribusi" 
                            required
                            value="{{ old('tanggal_distribusi', $distribusi->tanggal_distribusi->format('Y-m-d\TH:i')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_distribusi') border-red-500 @enderror"
                        >
                        @error('tanggal_distribusi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_asal" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Asal <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_asal" 
                            name="id_gudang_asal" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_asal') border-red-500 @enderror"
                            onchange="loadInventoryFromGudang(this.value)"
                        >
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_asal', $distribusi->id_gudang_asal) == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }} ({{ $gudang->jenis_gudang }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_gudang_asal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Tujuan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_tujuan" 
                            name="id_gudang_tujuan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_tujuan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_tujuan', $distribusi->id_gudang_tujuan) == $gudang->id_gudang ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }} ({{ $gudang->jenis_gudang }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_gudang_tujuan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pegawai_pengirim" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Pengirim <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_pengirim" 
                            name="id_pegawai_pengirim" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pengirim') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Pengirim</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pengirim', $distribusi->id_pegawai_pengirim) == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_pengirim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan distribusi"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan', $distribusi->keterangan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Distribusi -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detail Distribusi</h3>
                    <button 
                        type="button" 
                        id="btnTambahItem"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item
                    </button>
                </div>

                <div id="detailContainer" class="space-y-4">
                    @foreach(old('detail', $distribusi->detailDistribusi) as $index => $detail)
                    <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Inventory <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="detail[{{ $index }}][id_inventory]" 
                                    required
                                    class="select-inventory block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    onchange="updateHargaSatuan(this)"
                                >
                                    <option value="">Pilih Inventory</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Qty Distribusi <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="detail[{{ $index }}][qty_distribusi]" 
                                    required
                                    min="0.01"
                                    step="0.01"
                                    value="{{ old("detail.{$index}.qty_distribusi", is_object($detail) ? $detail->qty_distribusi : ($detail['qty_distribusi'] ?? '')) }}"
                                    placeholder="0"
                                    class="qty-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    onchange="calculateSubtotal(this)"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="detail[{{ $index }}][id_satuan]" 
                                    required
                                    class="select-satuan block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id_satuan }}" 
                                            {{ old("detail.{$index}.id_satuan", is_object($detail) ? $detail->id_satuan : ($detail['id_satuan'] ?? '')) == $satuan->id_satuan ? 'selected' : '' }}>
                                            {{ $satuan->nama_satuan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Satuan <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="detail[{{ $index }}][harga_satuan]" 
                                    required
                                    min="0"
                                    step="0.01"
                                    value="{{ old("detail.{$index}.harga_satuan", is_object($detail) ? $detail->harga_satuan : ($detail['harga_satuan'] ?? '')) }}"
                                    placeholder="0"
                                    class="harga-satuan-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    onchange="calculateSubtotal(this)"
                                >
                            </div>

                            <div class="sm:col-span-1 flex items-end">
                                <button 
                                    type="button" 
                                    class="btnHapusItem w-full px-3 py-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input 
                                type="text" 
                                name="detail[{{ $index }}][keterangan]" 
                                value="{{ old("detail.{$index}.keterangan", is_object($detail) ? $detail->keterangan : ($detail['keterangan'] ?? '')) }}"
                                placeholder="Opsional"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>
                    </div>
                    @endforeach
                </div>

                @error('detail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.distribusi.index') }}" 
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

<!-- Template untuk item detail baru (hidden) -->
<template id="itemTemplate">
    <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
            <div class="sm:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Inventory <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_inventory]" 
                    required
                    class="select-inventory block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="updateHargaSatuan(this)"
                >
                    <option value="">Pilih Inventory</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Distribusi <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][qty_distribusi]" 
                    required
                    min="0.01"
                    step="0.01"
                    placeholder="0"
                    class="qty-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="calculateSubtotal(this)"
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
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Harga Satuan <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][harga_satuan]" 
                    required
                    min="0"
                    step="0.01"
                    placeholder="0"
                    class="harga-satuan-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="calculateSubtotal(this)"
                >
            </div>

            <div class="sm:col-span-1 flex items-end">
                <button 
                    type="button" 
                    class="btnHapusItem w-full px-3 py-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Hapus
                </button>
            </div>
        </div>
        <div class="mt-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
            <input 
                type="text" 
                name="detail[INDEX][keterangan]" 
                placeholder="Opsional"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = {{ count(old('detail', $distribusi->detailDistribusi)) }};
let inventoryData = {};

// Load inventory dari gudang
function loadInventoryFromGudang(gudangId) {
    if (!gudangId) {
        return;
    }

    fetch(`/api/gudang/${gudangId}/inventory`)
        .then(response => response.json())
        .then(data => {
            inventoryData = {};
            data.inventory.forEach(inv => {
                inventoryData[inv.id_inventory] = {
                    nama_barang: inv.nama_barang,
                    harga_satuan: inv.harga_satuan,
                    id_satuan: inv.id_satuan,
                    qty_available: inv.qty_available
                };
            });

            // Update semua select inventory
            document.querySelectorAll('.select-inventory').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Pilih Inventory</option>';
                
                data.inventory.forEach(inv => {
                    const option = document.createElement('option');
                    option.value = inv.id_inventory;
                    option.textContent = `${inv.nama_barang} (Stok: ${inv.qty_available})`;
                    option.setAttribute('data-harga', inv.harga_satuan);
                    option.setAttribute('data-satuan', inv.id_satuan);
                    select.appendChild(option);
                });

                if (currentValue) {
                    select.value = currentValue;
                }
            });
        })
        .catch(error => console.error('Error loading inventory:', error));
}

// Update harga satuan saat inventory dipilih
function updateHargaSatuan(select) {
    const row = select.closest('.item-row');
    const hargaInput = row.querySelector('.harga-satuan-input');
    const satuanSelect = row.querySelector('.select-satuan');
    
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const harga = selectedOption.getAttribute('data-harga');
        const satuanId = selectedOption.getAttribute('data-satuan');
        
        if (harga) {
            hargaInput.value = harga;
        }
        if (satuanId) {
            satuanSelect.value = satuanId;
        }
        
        calculateSubtotal(hargaInput);
    }
}

// Calculate subtotal
function calculateSubtotal(input) {
    const row = input.closest('.item-row');
    const qtyInput = row.querySelector('.qty-input');
    const hargaInput = row.querySelector('.harga-satuan-input');
    
    // Subtotal akan dihitung di backend
}

// Tambah item
document.getElementById('btnTambahItem').addEventListener('click', function() {
    const template = document.getElementById('itemTemplate');
    const container = document.getElementById('detailContainer');
    const newItem = template.content.cloneNode(true);
    
    newItem.innerHTML = newItem.innerHTML.replace(/INDEX/g, itemIndex);
    container.appendChild(newItem);
    
    // Load inventory ke select baru
    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        loadInventoryFromGudang(gudangAsal);
    }
    
    // Hapus item
    container.lastElementChild.querySelector('.btnHapusItem').addEventListener('click', function() {
        this.closest('.item-row').remove();
    });
    
    itemIndex++;
});

// Hapus item
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btnHapusItem').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    });
    
    // Load inventory jika gudang asal sudah dipilih
    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        loadInventoryFromGudang(gudangAsal);
    }
    
    // Set nilai inventory yang sudah ada untuk item yang sudah ada
    @foreach($distribusi->detailDistribusi as $index => $detail)
        const select{{ $index }} = document.querySelector('select[name="detail[{{ $index }}][id_inventory]"]');
        if (select{{ $index }}) {
            setTimeout(() => {
                select{{ $index }}.value = {{ $detail->id_inventory }};
            }, 500);
        }
    @endforeach
});
</script>
@endpush
@endsection

