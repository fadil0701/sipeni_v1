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
        <h2 class="text-xl font-semibold text-gray-900">Tambah Distribusi Barang (SBBK)</h2>
    </div>
    
    <form action="{{ route('transaction.distribusi.store') }}" method="POST" class="p-6" id="formDistribusi">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_permintaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Permintaan Barang <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_permintaan" 
                            name="id_permintaan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_permintaan') border-red-500 @enderror"
                            onchange="loadPermintaanDetail(this.value)"
                        >
                            <option value="">Pilih Permintaan Barang</option>
                            @foreach($permintaans as $permintaan)
                                <option value="{{ $permintaan->id_permintaan }}" {{ old('id_permintaan') == $permintaan->id_permintaan ? 'selected' : '' }}>
                                    {{ $permintaan->no_permintaan }} - {{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }} ({{ $permintaan->tanggal_permintaan->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_permintaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_distribusi" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Distribusi <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="tanggal_distribusi" 
                            name="tanggal_distribusi" 
                            required
                            value="{{ old('tanggal_distribusi', date('Y-m-d\TH:i')) }}"
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
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_asal') == $gudang->id_gudang ? 'selected' : '' }}>
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
                            data-old-value="{{ old('id_gudang_tujuan') }}"
                        >
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_tujuan') == $gudang->id_gudang ? 'selected' : '' }}>
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
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pengirim') == $pegawai->id ? 'selected' : '' }}>
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
                        >{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Permintaan (untuk referensi) -->
            <div id="permintaanDetail" style="display: none;">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Permintaan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div id="permintaanContent"></div>
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
                    <!-- Item akan ditambahkan di sini via JavaScript -->
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

<!-- Template untuk item detail (hidden) -->
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
let itemIndex = 0;
let inventoryData = {};

// Simpan data gudang untuk fallback
const allGudangs = [
    @foreach($gudangs as $gudang)
    {
        id_gudang: {{ $gudang->id_gudang }},
        nama_gudang: '{{ $gudang->nama_gudang }}',
        jenis_gudang: '{{ $gudang->jenis_gudang }}',
        kategori_gudang: '{{ $gudang->kategori_gudang }}'
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
];

// Load detail permintaan dan set gudang tujuan
function loadPermintaanDetail(permintaanId) {
    if (!permintaanId) {
        document.getElementById('permintaanDetail').style.display = 'none';
        // Reset gudang tujuan
        const gudangTujuanSelect = document.getElementById('id_gudang_tujuan');
        gudangTujuanSelect.innerHTML = '<option value="">Pilih Gudang Tujuan</option>';
        return;
    }

    // Load gudang tujuan berdasarkan permintaan
    fetch(`{{ route('transaction.distribusi.api.gudang-tujuan', ':id') }}`.replace(':id', permintaanId))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.gudang.length > 0) {
                const gudangTujuanSelect = document.getElementById('id_gudang_tujuan');
                gudangTujuanSelect.innerHTML = '<option value="">Pilih Gudang Tujuan</option>';
                
                data.gudang.forEach(gudang => {
                    const option = document.createElement('option');
                    option.value = gudang.id_gudang;
                    option.textContent = `${gudang.nama_gudang} (${gudang.jenis_gudang})`;
                    gudangTujuanSelect.appendChild(option);
                });
                
                // Auto-select jika hanya ada 1 gudang atau jika ada old value
                const oldValue = gudangTujuanSelect.getAttribute('data-old-value');
                if (oldValue && data.gudang.some(g => g.id_gudang == oldValue)) {
                    gudangTujuanSelect.value = oldValue;
                } else if (data.gudang.length === 1) {
                    gudangTujuanSelect.value = data.gudang[0].id_gudang;
                }
            } else {
                // Jika tidak ada gudang unit, tampilkan semua gudang
                const gudangTujuanSelect = document.getElementById('id_gudang_tujuan');
                gudangTujuanSelect.innerHTML = '<option value="">Pilih Gudang Tujuan</option>';
                
                allGudangs.forEach(gudang => {
                    const option = document.createElement('option');
                    option.value = gudang.id_gudang;
                    option.textContent = `${gudang.nama_gudang} (${gudang.jenis_gudang})`;
                    gudangTujuanSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading gudang tujuan:', error);
        });

    // Load detail permintaan
    fetch(`/api/permintaan/${permintaanId}/detail`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<table class="min-w-full divide-y divide-gray-200">';
                html += '<thead class="bg-gray-50"><tr>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>';
                html += '</tr></thead><tbody>';
                
                data.details.forEach(detail => {
                    html += '<tr>';
                    html += `<td>${detail.nama_barang}</td>`;
                    html += `<td>${detail.qty_diminta}</td>`;
                    html += `<td>${detail.satuan}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                document.getElementById('permintaanContent').innerHTML = html;
                document.getElementById('permintaanDetail').style.display = 'block';
            }
        })
        .catch(error => console.error('Error loading permintaan detail:', error));
}

// Auto-load gudang tujuan jika permintaan sudah dipilih saat halaman dimuat
// (Dipindahkan ke DOMContentLoaded di bawah)

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
                    id_inventory: inv.id_inventory,
                    nama_barang: inv.nama_barang,
                    kode_barang: inv.kode_barang || '',
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
                    const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
                    option.textContent = `${inv.nama_barang}${kodeText} - Stok: ${inv.qty_available}`;
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
    
    // Subtotal akan dihitung di backend, tapi bisa ditampilkan di sini jika perlu
}

// Load inventory ke select
function loadInventoryToSelect(selectElement, gudangId) {
    if (!gudangId) {
        return;
    }
    
    fetch(`/api/gudang/${gudangId}/inventory`)
        .then(response => response.json())
        .then(data => {
            selectElement.innerHTML = '<option value="">Pilih Inventory</option>';
            data.inventory.forEach(inv => {
                const option = document.createElement('option');
                option.value = inv.id_inventory;
                const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
                option.textContent = `${inv.nama_barang}${kodeText} - Stok: ${inv.qty_available}`;
                option.setAttribute('data-harga', inv.harga_satuan);
                option.setAttribute('data-satuan', inv.id_satuan);
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading inventory:', error);
        });
}

// Tambah item
function addItemRow() {
    const template = document.getElementById('itemTemplate');
    const container = document.getElementById('detailContainer');
    
    if (!template || !container) {
        console.error('Template or container not found');
        alert('Terjadi kesalahan saat menambahkan item. Silakan refresh halaman.');
        return;
    }
    
    // Clone template content dan replace INDEX
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, itemIndex);
    const newItem = tempDiv.firstElementChild;
    
    if (!newItem) {
        console.error('Failed to clone template');
        alert('Terjadi kesalahan saat menambahkan item. Silakan refresh halaman.');
        return;
    }
    
    container.appendChild(newItem);
    
    const newRow = container.lastElementChild;
    const inventorySelect = newRow.querySelector('.select-inventory');
    
    if (!inventorySelect) {
        console.error('Inventory select not found in new row');
        alert('Terjadi kesalahan saat menambahkan item. Silakan refresh halaman.');
        return;
    }
    
    // Attach event handler untuk update harga satuan
    inventorySelect.addEventListener('change', function() {
        updateHargaSatuan(this);
    });
    
    // Load inventory ke select baru
    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        // Jika inventoryData sudah ada, langsung isi
        if (Object.keys(inventoryData).length > 0) {
            inventorySelect.innerHTML = '<option value="">Pilih Inventory</option>';
            Object.values(inventoryData).forEach(inv => {
                const option = document.createElement('option');
                option.value = inv.id_inventory;
                const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
                option.textContent = `${inv.nama_barang}${kodeText} - Stok: ${inv.qty_available}`;
                option.setAttribute('data-harga', inv.harga_satuan);
                option.setAttribute('data-satuan', inv.id_satuan);
                inventorySelect.appendChild(option);
            });
        } else {
            // Jika belum ada, load dari API
            loadInventoryToSelect(inventorySelect, gudangAsal);
        }
    }
    
    // Hapus item
    const btnHapus = newRow.querySelector('.btnHapusItem');
    if (btnHapus) {
        btnHapus.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    }
    
    itemIndex++;
    console.log('Item row added successfully, current index:', itemIndex);
}

// Event listener untuk tombol tambah item dan initialization
document.addEventListener('DOMContentLoaded', function() {
    // Setup tombol tambah item
    const btnTambahItem = document.getElementById('btnTambahItem');
    if (btnTambahItem) {
        btnTambahItem.addEventListener('click', function(e) {
            e.preventDefault();
            addItemRow();
        });
    }
    
    // Load permintaan detail jika sudah dipilih
    const permintaanId = document.getElementById('id_permintaan').value;
    if (permintaanId) {
        loadPermintaanDetail(permintaanId);
    }
    
    // Load inventory jika gudang asal sudah dipilih
    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        loadInventoryFromGudang(gudangAsal);
    }
    
    // Tambah item pertama jika belum ada
    const detailContainer = document.getElementById('detailContainer');
    if (detailContainer && detailContainer.children.length === 0) {
        addItemRow();
    }
    
    // Validasi form sebelum submit
    const formDistribusi = document.getElementById('formDistribusi');
    if (formDistribusi) {
        formDistribusi.addEventListener('submit', function(e) {
            const detailRows = detailContainer.querySelectorAll('.item-row');
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Minimal harus ada 1 item distribusi. Silakan klik tombol "Tambah Item" terlebih dahulu.');
                return false;
            }
            
            // Validasi setiap item
            let isValid = true;
            let emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyDistribusi = row.querySelector('[name*="[qty_distribusi]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const hargaSatuan = row.querySelector('[name*="[harga_satuan]"]');
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Inventory`);
                }
                if (!qtyDistribusi || !qtyDistribusi.value || parseFloat(qtyDistribusi.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Distribusi`);
                }
                if (!idSatuan || !idSatuan.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Satuan`);
                }
                if (!hargaSatuan || !hargaSatuan.value || parseFloat(hargaSatuan.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Harga Satuan`);
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

