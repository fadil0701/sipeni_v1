@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.draft-distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Disposisi
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Proses Disposisi - {{ $kategoriGudang }}</h2>
        <p class="mt-1 text-sm text-gray-600">No. Permintaan: {{ $approvalLog->permintaan->no_permintaan }}</p>
    </div>
    
    <!-- Error Messages -->
    @if($errors->any())
        <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan saat menyimpan data:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
    
    <form action="{{ route('transaction.draft-distribusi.store') }}" method="POST" class="p-6" id="formDraftDistribusi">
        @csrf
        <input type="hidden" name="id_permintaan" value="{{ $approvalLog->permintaan->id_permintaan }}">
        <input type="hidden" name="kategori_gudang" value="{{ $kategoriGudang ?? 'ASET' }}" id="hiddenKategoriGudang">
        
        <div class="space-y-6">
            <!-- Informasi Permintaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permintaan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->unitKerja->nama_unit_kerja ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pemohon</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->pemohon->nama_pegawai ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Permintaan</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $approvalLog->permintaan->tanggal_permintaan->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Permintaan</label>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @if(is_array($approvalLog->permintaan->jenis_permintaan))
                                    @foreach($approvalLog->permintaan->jenis_permintaan as $jenis)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $jenis }}</span>
                                    @endforeach
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $approvalLog->permintaan->jenis_permintaan }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Permintaan untuk Kategori {{ $kategoriGudang }} -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Permintaan - {{ $kategoriGudang }}</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Disetujui</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($detailPermintaan as $detail)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $detail->dataBarang->nama_barang ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($detail->qty_diminta, 2) }}</td>
                                    <td class="px-4 py-2 text-sm font-semibold text-blue-900">{{ number_format($detail->qty_diminta, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Distribusi - {{ $kategoriGudang }}</h3>
                
                <div class="mb-4 flex justify-end">
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
                href="{{ route('transaction.draft-distribusi.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan & Siapkan untuk Distribusi
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
                    Inventory <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_inventory]" 
                    required
                    class="select-inventory block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="updateInventoryDetails(this)"
                >
                    <option value="">Pilih Inventory</option>
                </select>
                <input type="hidden" class="inventory-jenis-input" value="">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Gudang Asal <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_gudang_asal]" 
                    required
                    class="select-gudang block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="loadInventoryByGudang(this)"
                >
                    <option value="">Pilih Gudang</option>
                    @foreach($gudangs as $gudang)
                        <option value="{{ $gudang->id_gudang }}">{{ $gudang->nama_gudang }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Disetujui
                </label>
                <input 
                    type="text" 
                    class="qty-disetujui-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
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
        </div>
        
        <!-- Field untuk Farmasi/Persediaan: Exp Date dan Nomor Batch -->
        <div class="mt-2 farmasi-persediaan-fields" style="display: none;">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Batch</label>
                    <input 
                        type="text" 
                        class="no-batch-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                        readonly
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exp Date</label>
                    <input 
                        type="text" 
                        class="exp-date-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                        readonly
                    >
                </div>
            </div>
        </div>
        
        <!-- Field untuk ASET: Nomor Seri dan Kode Register -->
        <div class="mt-2 aset-fields" style="display: none;">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Seri</label>
                    <input 
                        type="text" 
                        class="no-seri-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                        readonly
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Register</label>
                    <input 
                        type="text" 
                        class="kode-register-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                        readonly
                    >
                </div>
            </div>
        </div>
        
        <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Satuan <span class="text-red-500">*</span></label>
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <input 
                    type="text" 
                    name="detail[INDEX][keterangan]" 
                    placeholder="Opsional"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
            <div class="flex items-end">
                <button 
                    type="button" 
                    class="btnHapusItem w-full px-3 py-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Hapus
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = 0;
let inventoryData = {};
const kategoriGudang = '{{ $kategoriGudang }}';

// Pre-populate inventory data dari controller
@php
    $inventoriesData = $inventories->map(function($inv) use ($detailPermintaan) {
        // Cari qty disetujui dari detail permintaan berdasarkan id_data_barang
        $qtyDisetujui = 0;
        $detailPermintaanItem = $detailPermintaan->firstWhere('id_data_barang', $inv->id_data_barang);
        if ($detailPermintaanItem) {
            $qtyDisetujui = (float)$detailPermintaanItem->qty_diminta;
        }
        
        // Untuk ASET, ambil kode register dan nomor seri dari inventory_item
        $kodeRegisterList = [];
        $noSeriList = [];
        if ($inv->jenis_inventory === 'ASET' && $inv->inventoryItems && $inv->inventoryItems->count() > 0) {
            $kodeRegisterList = $inv->inventoryItems->pluck('kode_register')->filter()->values()->toArray();
            $noSeriList = $inv->inventoryItems->pluck('no_seri')->filter()->unique()->values()->toArray();
            // Jika tidak ada di inventory_item, gunakan dari data_inventory
            if (empty($noSeriList) && $inv->no_seri) {
                $noSeriList = [$inv->no_seri];
            }
        }
        
        return [
            'id_inventory' => $inv->id_inventory,
            'id_data_barang' => $inv->id_data_barang,
            'id_gudang' => $inv->id_gudang,
            'nama_barang' => ($inv->dataBarang->nama_barang ?? '-'),
            'kode_barang' => ($inv->dataBarang->kode_data_barang ?? ''),
            'jenis_inventory' => $inv->jenis_inventory,
            'jenis_barang' => $inv->jenis_barang ?? null,
            'harga_satuan' => (float)($inv->harga_satuan ?? 0),
            'id_satuan' => $inv->id_satuan,
            'qty_input' => (float)($inv->qty_input ?? 0),
            'qty_disetujui' => $qtyDisetujui,
            'no_batch' => $inv->no_batch ?? null,
            'tanggal_kedaluwarsa' => $inv->tanggal_kedaluwarsa ? \Carbon\Carbon::parse($inv->tanggal_kedaluwarsa)->format('d/m/Y') : null,
            'no_seri_list' => $noSeriList,
            'kode_register_list' => $kodeRegisterList,
        ];
    })->values()->toArray();
@endphp
const inventoriesFromController = @json($inventoriesData);

// Load inventory berdasarkan gudang (untuk backward compatibility)
function loadInventoryByGudang(select) {
    const gudangId = select.value;
    const row = select.closest('.item-row');
    const inventorySelect = row.querySelector('.select-inventory');
    
    // Gunakan fungsi filter yang baru
    filterInventoryByGudang(inventorySelect, gudangId);
}

// Update semua detail inventory saat inventory dipilih
function updateInventoryDetails(select) {
    const row = select.closest('.item-row');
    const hargaInput = row.querySelector('.harga-satuan-input');
    const satuanSelect = row.querySelector('.select-satuan');
    const qtyDisetujuiInput = row.querySelector('.qty-disetujui-input');
    const noBatchInput = row.querySelector('.no-batch-input');
    const expDateInput = row.querySelector('.exp-date-input');
    const noSeriInput = row.querySelector('.no-seri-input');
    const kodeRegisterInput = row.querySelector('.kode-register-input');
    const farmasiPersediaanFields = row.querySelector('.farmasi-persediaan-fields');
    const asetFields = row.querySelector('.aset-fields');
    const inventoryJenisInput = row.querySelector('.inventory-jenis-input');
    
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const inventoryId = selectedOption.value;
        const inventory = inventoriesFromController.find(inv => inv.id_inventory == inventoryId);
        
        if (inventory) {
            // Update harga dan satuan
            const harga = inventory.harga_satuan;
            const satuanId = inventory.id_satuan;
            
            if (harga) {
                hargaInput.value = harga;
            }
            if (satuanId) {
                satuanSelect.value = satuanId;
            }
            
            // Update qty disetujui
            if (qtyDisetujuiInput) {
                qtyDisetujuiInput.value = inventory.qty_disetujui ? inventory.qty_disetujui.toFixed(2) : '0.00';
            }
            
            // Update jenis inventory untuk menentukan field mana yang ditampilkan
            if (inventoryJenisInput) {
                inventoryJenisInput.value = inventory.jenis_inventory;
            }
            
            // Tampilkan/sembunyikan field berdasarkan jenis inventory
            const isFarmasiPersediaan = ['FARMASI', 'PERSEDIAAN'].includes(inventory.jenis_inventory);
            const isAset = inventory.jenis_inventory === 'ASET';
            
            if (isFarmasiPersediaan && farmasiPersediaanFields) {
                farmasiPersediaanFields.style.display = 'block';
                if (noBatchInput) noBatchInput.value = inventory.no_batch || '-';
                if (expDateInput) expDateInput.value = inventory.tanggal_kedaluwarsa || '-';
            } else {
                if (farmasiPersediaanFields) farmasiPersediaanFields.style.display = 'none';
            }
            
            if (isAset && asetFields) {
                asetFields.style.display = 'block';
                // Untuk ASET, tampilkan list kode register dan nomor seri
                const noSeriList = inventory.no_seri_list || [];
                const kodeRegisterList = inventory.kode_register_list || [];
                if (noSeriInput) {
                    noSeriInput.value = noSeriList.length > 0 ? noSeriList.join(', ') : '-';
                }
                if (kodeRegisterInput) {
                    kodeRegisterInput.value = kodeRegisterList.length > 0 ? kodeRegisterList.join(', ') : '-';
                }
            } else {
                if (asetFields) asetFields.style.display = 'none';
            }
            
            calculateSubtotal(hargaInput);
        }
    } else {
        // Reset semua field jika tidak ada inventory yang dipilih
        if (qtyDisetujuiInput) qtyDisetujuiInput.value = '';
        if (noBatchInput) noBatchInput.value = '';
        if (expDateInput) expDateInput.value = '';
        if (noSeriInput) noSeriInput.value = '';
        if (kodeRegisterInput) kodeRegisterInput.value = '';
        if (farmasiPersediaanFields) farmasiPersediaanFields.style.display = 'none';
        if (asetFields) asetFields.style.display = 'none';
    }
}

// Calculate subtotal
function calculateSubtotal(input) {
    const row = input.closest('.item-row');
    const qtyInput = row.querySelector('.qty-input');
    const hargaInput = row.querySelector('.harga-satuan-input');
    
    // Subtotal akan dihitung di backend
}

// Pre-populate inventory untuk semua gudang sesuai kategori
function populateInventoryForAllGudangs(inventorySelect) {
    if (!inventorySelect) return;
    
    // Filter inventory sesuai kategori dari semua gudang
    const allInventory = inventoriesFromController.filter(inv => 
        inv.jenis_inventory === kategoriGudang
    );
    
    console.log('Populating inventory for all gudangs, kategori:', kategoriGudang);
    console.log('Available inventory:', allInventory.length, 'items');
    
    inventorySelect.innerHTML = '<option value="">Pilih Inventory</option>';
    
    if (allInventory.length > 0) {
        allInventory.forEach(inv => {
            const option = document.createElement('option');
            option.value = inv.id_inventory;
            const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
            option.textContent = `${inv.nama_barang}${kodeText} - Stok: ${inv.qty_input}`;
            option.setAttribute('data-harga', inv.harga_satuan);
            option.setAttribute('data-satuan', inv.id_satuan);
            option.setAttribute('data-gudang', inv.id_gudang);
            option.setAttribute('data-jenis', inv.jenis_inventory);
            option.setAttribute('data-qty-disetujui', inv.qty_disetujui || 0);
            option.setAttribute('data-no-batch', inv.no_batch || '');
            option.setAttribute('data-exp-date', inv.tanggal_kedaluwarsa || '');
            option.setAttribute('data-no-seri-list', JSON.stringify(inv.no_seri_list || []));
            option.setAttribute('data-kode-register-list', JSON.stringify(inv.kode_register_list || []));
            inventorySelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Tidak ada inventory tersedia';
        option.disabled = true;
        inventorySelect.appendChild(option);
    }
}

// Filter inventory berdasarkan gudang yang dipilih
function filterInventoryByGudang(inventorySelect, gudangId) {
    if (!inventorySelect || !gudangId) {
        // Jika gudang tidak dipilih, tampilkan semua inventory
        populateInventoryForAllGudangs(inventorySelect);
        return;
    }
    
    // Filter hanya inventory dari gudang yang dipilih
    const filteredInventory = inventoriesFromController.filter(inv => 
        inv.id_gudang == gudangId && inv.jenis_inventory === kategoriGudang
    );
    
    inventorySelect.innerHTML = '<option value="">Pilih Inventory</option>';
    
    if (filteredInventory.length > 0) {
        filteredInventory.forEach(inv => {
            const option = document.createElement('option');
            option.value = inv.id_inventory;
            const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
            option.textContent = `${inv.nama_barang}${kodeText} - Stok: ${inv.qty_input}`;
            option.setAttribute('data-harga', inv.harga_satuan);
            option.setAttribute('data-satuan', inv.id_satuan);
            option.setAttribute('data-gudang', inv.id_gudang);
            option.setAttribute('data-jenis', inv.jenis_inventory);
            option.setAttribute('data-qty-disetujui', inv.qty_disetujui || 0);
            option.setAttribute('data-no-batch', inv.no_batch || '');
            option.setAttribute('data-exp-date', inv.tanggal_kedaluwarsa || '');
            option.setAttribute('data-no-seri-list', JSON.stringify(inv.no_seri_list || []));
            option.setAttribute('data-kode-register-list', JSON.stringify(inv.kode_register_list || []));
            inventorySelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Tidak ada inventory tersedia untuk gudang ini';
        option.disabled = true;
        inventorySelect.appendChild(option);
    }
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
    const gudangSelect = newRow.querySelector('.select-gudang');
    
    if (!inventorySelect || !gudangSelect) {
        console.error('Select elements not found in new row');
        return;
    }
    
    // Pre-populate inventory dengan semua inventory sesuai kategori
    populateInventoryForAllGudangs(inventorySelect);
    
    // Attach event handler untuk update semua detail inventory
    inventorySelect.addEventListener('change', function() {
        updateInventoryDetails(this);
        
        // Auto-select gudang berdasarkan inventory yang dipilih
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value && selectedOption.getAttribute('data-gudang')) {
            const gudangId = selectedOption.getAttribute('data-gudang');
            gudangSelect.value = gudangId;
        }
    });
    
    // Attach event handler untuk filter inventory saat gudang dipilih
    gudangSelect.addEventListener('change', function() {
        filterInventoryByGudang(inventorySelect, this.value);
    });
    
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
    
    // Tambah item pertama jika belum ada
    const detailContainer = document.getElementById('detailContainer');
    if (detailContainer && detailContainer.children.length === 0) {
        addItemRow();
    }
    
    // Pastikan kategori_gudang selalu ada sebelum submit
    const formDraftDistribusi = document.getElementById('formDraftDistribusi');
    if (formDraftDistribusi) {
        // Pastikan hidden field kategori_gudang terisi
        const hiddenKategoriField = document.getElementById('hiddenKategoriGudang');
        if (hiddenKategoriField && !hiddenKategoriField.value) {
            hiddenKategoriField.value = kategoriGudang || 'ASET';
        }
        
        formDraftDistribusi.addEventListener('submit', function(e) {
            console.log('Form submit triggered');
            
            // Pastikan kategori_gudang terisi
            const hiddenKategoriField = document.getElementById('hiddenKategoriGudang');
            if (hiddenKategoriField && !hiddenKategoriField.value) {
                hiddenKategoriField.value = kategoriGudang || 'ASET';
            }
            
            const detailRows = detailContainer.querySelectorAll('.item-row');
            console.log('Detail rows found:', detailRows.length);
            
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
                const idGudangAsal = row.querySelector('[name*="[id_gudang_asal]"]');
                const qtyDistribusi = row.querySelector('[name*="[qty_distribusi]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const hargaSatuan = row.querySelector('[name*="[harga_satuan]"]');
                
                console.log(`Validating row ${index + 1}:`, {
                    inventory: idInventory?.value,
                    gudang: idGudangAsal?.value,
                    qty: qtyDistribusi?.value,
                    satuan: idSatuan?.value,
                    harga: hargaSatuan?.value
                });
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Inventory`);
                }
                if (!idGudangAsal || !idGudangAsal.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Gudang Asal`);
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
            
            console.log('Validation passed, submitting form...');
        });
    }
});
</script>
@endpush
@endsection


