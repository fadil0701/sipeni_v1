@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.retur-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Retur
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Retur Barang</h2>
        <p class="text-sm text-gray-600 mt-1">No. Retur: <span class="font-semibold">{{ $retur->no_retur }}</span></p>
    </div>
    
    <form action="{{ route('transaction.retur-barang.update', $retur->id_retur) }}" method="POST" class="p-6" id="formRetur">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Retur -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Retur</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_penerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Penerimaan Barang
                        </label>
                        <select 
                            id="id_penerimaan" 
                            name="id_penerimaan" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_penerimaan') border-red-500 @enderror"
                            onchange="loadPenerimaanDetail(this.value)"
                        >
                            <option value="">Pilih Penerimaan Barang</option>
                            @foreach($penerimaans as $penerimaan)
                                <option value="{{ $penerimaan->id_penerimaan }}" {{ old('id_penerimaan', $retur->id_penerimaan) == $penerimaan->id_penerimaan ? 'selected' : '' }}>
                                    {{ $penerimaan->no_penerimaan }} - {{ $penerimaan->unitKerja->nama_unit_kerja ?? '-' }} ({{ $penerimaan->tanggal_penerimaan->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_penerimaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_retur" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Retur <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_retur" 
                            name="tanggal_retur" 
                            required
                            value="{{ old('tanggal_retur', $retur->tanggal_retur->format('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_retur') border-red-500 @enderror"
                        >
                        @error('tanggal_retur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_unit_kerja" 
                            name="id_unit_kerja" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_unit_kerja') border-red-500 @enderror"
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $retur->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_asal" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Asal (Unit) <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_asal" 
                            name="id_gudang_asal" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_asal') border-red-500 @enderror"
                        >
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_asal', $retur->id_gudang_asal) == $gudang->id_gudang ? 'selected' : '' }}>
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
                            Gudang Tujuan (Pusat) <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_gudang_tujuan" 
                            name="id_gudang_tujuan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_tujuan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($gudangs as $gudang)
                                @if($gudang->jenis_gudang == 'PUSAT')
                                    <option value="{{ $gudang->id_gudang }}" {{ old('id_gudang_tujuan', $retur->id_gudang_tujuan) == $gudang->id_gudang ? 'selected' : '' }}>
                                        {{ $gudang->nama_gudang }}
                                    </option>
                                @endif
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
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pengirim', $retur->id_pegawai_pengirim) == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_pengirim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_retur" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Retur <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_retur" 
                            name="status_retur" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_retur') border-red-500 @enderror"
                        >
                            <option value="">Pilih Status</option>
                            <option value="DRAFT" {{ old('status_retur', $retur->status_retur) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="DIAJUKAN" {{ old('status_retur', $retur->status_retur) == 'DIAJUKAN' ? 'selected' : '' }}>Diajukan</option>
                        </select>
                        @error('status_retur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="alasan_retur" class="block text-sm font-medium text-gray-700 mb-2">Alasan Retur</label>
                        <textarea 
                            id="alasan_retur" 
                            name="alasan_retur" 
                            rows="3"
                            placeholder="Masukkan alasan retur barang"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('alasan_retur', $retur->alasan_retur) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan tambahan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan', $retur->keterangan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Penerimaan (untuk referensi) -->
            <div id="penerimaanDetail" style="display: none;">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penerimaan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div id="penerimaanContent"></div>
                </div>
            </div>

            <!-- Detail Retur -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Retur</h3>
                <div id="detailContainer" class="space-y-4">
                    <!-- Item akan ditambahkan di sini via JavaScript atau dari existing data -->
                </div>

                @error('detail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.retur-barang.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Update
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
                    Barang
                </label>
                <input 
                    type="text" 
                    class="nama-barang block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
                <input type="hidden" name="detail[INDEX][id_inventory]" class="id-inventory-input">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Diterima
                </label>
                <input 
                    type="text" 
                    class="qty-diterima block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Retur <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][qty_retur]" 
                    required
                    min="0"
                    step="0.01"
                    placeholder="0"
                    class="qty-retur-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Retur Item</label>
                <input 
                    type="text" 
                    name="detail[INDEX][alasan_retur_item]" 
                    placeholder="Opsional"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let penerimaanDetails = [];

// Existing detail retur data
const existingDetails = @json($retur->detailRetur->map(function($detail) {
    return [
        'id_detail_retur' => $detail->id_detail_retur,
        'id_inventory' => $detail->id_inventory,
        'nama_barang' => ($detail->inventory->dataBarang->nama_barang ?? '-'),
        'qty_retur' => $detail->qty_retur,
        'id_satuan' => $detail->id_satuan,
        'alasan_retur_item' => ($detail->alasan_retur_item ?? ''),
        'keterangan' => ($detail->keterangan ?? ''),
        'qty_diterima' => null, // Will be loaded from penerimaan
    ];
}));

// Load detail penerimaan
function loadPenerimaanDetail(penerimaanId) {
    if (!penerimaanId) {
        document.getElementById('penerimaanDetail').style.display = 'none';
        // Load existing detail if no penerimaan selected
        if (existingDetails && existingDetails.length > 0) {
            loadExistingDetails();
        }
        return;
    }

    fetch(`{{ url('/') }}/transaction/retur-barang/penerimaan/${penerimaanId}/detail`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.details && data.details.length > 0) {
                penerimaanDetails = data.details;
                
                // Tampilkan info penerimaan
                let html = `<p class="text-sm text-gray-700 mb-2"><strong>No Penerimaan:</strong> ${data.penerimaan.no_penerimaan}</p>`;
                html += `<p class="text-sm text-gray-700 mb-4"><strong>Unit Kerja:</strong> ${data.penerimaan.unit_kerja ? 'Auto-filled' : '-'}</p>`;
                
                html += '<table class="min-w-full divide-y divide-gray-200">';
                html += '<thead class="bg-gray-50"><tr>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Diterima</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>';
                html += '</tr></thead><tbody>';
                
                data.details.forEach(detail => {
                    html += '<tr>';
                    html += `<td>${detail.nama_barang}</td>`;
                    html += `<td>${detail.qty_diterima}</td>`;
                    html += `<td>${detail.nama_satuan}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                document.getElementById('penerimaanContent').innerHTML = html;
                document.getElementById('penerimaanDetail').style.display = 'block';

                // Auto-set unit kerja
                if (data.penerimaan.unit_kerja) {
                    document.getElementById('id_unit_kerja').value = data.penerimaan.unit_kerja;
                }

                // Set id_distribusi jika ada
                if (data.penerimaan.id_distribusi) {
                    let distribusiInput = document.querySelector('input[name="id_distribusi"]');
                    if (!distribusiInput) {
                        distribusiInput = document.createElement('input');
                        distribusiInput.type = 'hidden';
                        distribusiInput.name = 'id_distribusi';
                        document.getElementById('formRetur').appendChild(distribusiInput);
                    }
                    distribusiInput.value = data.penerimaan.id_distribusi;
                }

                // Merge existing detail dengan penerimaan detail
                loadDetailRetur(data.details, existingDetails);
            } else {
                // Load existing detail if no penerimaan details found
                if (existingDetails && existingDetails.length > 0) {
                    loadExistingDetails();
                }
            }
        })
        .catch(error => {
            console.error('Error loading penerimaan detail:', error);
            // Load existing detail on error
            if (existingDetails && existingDetails.length > 0) {
                loadExistingDetails();
            }
        });
}

// Load existing details without penerimaan reference
function loadExistingDetails() {
    const container = document.getElementById('detailContainer');
    container.innerHTML = '';
    
    if (!existingDetails || existingDetails.length === 0) {
        return;
    }
    
    let index = 0;
    existingDetails.forEach(detail => {
        const template = document.getElementById('itemTemplate');
        if (!template) {
            return;
        }
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, index);
        const itemElement = tempDiv.firstElementChild;
        
        if (!itemElement) {
            return;
        }
        
        // Set values from existing detail
        const namaBarangInput = itemElement.querySelector('.nama-barang');
        const idInventoryInput = itemElement.querySelector('.id-inventory-input');
        const qtyDiterimaInput = itemElement.querySelector('.qty-diterima');
        const qtyReturInput = itemElement.querySelector('.qty-retur-input');
        const satuanSelect = itemElement.querySelector('.select-satuan');
        const alasanInput = itemElement.querySelector('[name*="[alasan_retur_item]"]');
        
        if (namaBarangInput) namaBarangInput.value = detail.nama_barang || '';
        if (idInventoryInput) idInventoryInput.value = detail.id_inventory || '';
        if (qtyDiterimaInput) qtyDiterimaInput.value = detail.qty_diterima || detail.qty_retur || '0';
        if (qtyReturInput) {
            qtyReturInput.value = detail.qty_retur || '0';
            if (detail.qty_diterima) {
                qtyReturInput.max = detail.qty_diterima;
            }
        }
        if (satuanSelect) satuanSelect.value = detail.id_satuan || '';
        if (alasanInput) alasanInput.value = detail.alasan_retur_item || '';
        
        container.appendChild(itemElement);
        index++;
    });
}

// Load detail retur berdasarkan detail penerimaan dengan merge existing
function loadDetailRetur(details, existing) {
    const container = document.getElementById('detailContainer');
    container.innerHTML = '';
    
    if (!details || details.length === 0) {
        if (existing && existing.length > 0) {
            loadExistingDetails();
        }
        return;
    }
    
    // Create a map of existing details by id_inventory
    const existingMap = {};
    if (existing && existing.length > 0) {
        existing.forEach(item => {
            existingMap[item.id_inventory] = item;
        });
    }
    
    let index = 0;
    details.forEach(detail => {
        const template = document.getElementById('itemTemplate');
        if (!template) {
            return;
        }
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, index);
        const itemElement = tempDiv.firstElementChild;
        
        if (!itemElement) {
            return;
        }
        
        // Check if we have existing data for this inventory
        const existingDetail = existingMap[detail.id_inventory];
        
        // Set values - prefer existing, fallback to penerimaan detail
        const namaBarangInput = itemElement.querySelector('.nama-barang');
        const idInventoryInput = itemElement.querySelector('.id-inventory-input');
        const qtyDiterimaInput = itemElement.querySelector('.qty-diterima');
        const qtyReturInput = itemElement.querySelector('.qty-retur-input');
        const satuanSelect = itemElement.querySelector('.select-satuan');
        const alasanInput = itemElement.querySelector('[name*="[alasan_retur_item]"]');
        
        if (namaBarangInput) namaBarangInput.value = detail.nama_barang || '';
        if (idInventoryInput) idInventoryInput.value = detail.id_inventory || '';
        if (qtyDiterimaInput) qtyDiterimaInput.value = detail.qty_diterima || '0';
        if (qtyReturInput) {
            qtyReturInput.value = existingDetail ? (existingDetail.qty_retur || '0') : '0';
            qtyReturInput.max = detail.qty_diterima || '0';
        }
        if (satuanSelect) satuanSelect.value = existingDetail ? (existingDetail.id_satuan || detail.id_satuan) : (detail.id_satuan || '');
        if (alasanInput) alasanInput.value = existingDetail ? (existingDetail.alasan_retur_item || '') : '';
        
        container.appendChild(itemElement);
        index++;
    });
}

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    const penerimaanId = document.getElementById('id_penerimaan').value;
    if (penerimaanId) {
        loadPenerimaanDetail(penerimaanId);
    } else if (existingDetails && existingDetails.length > 0) {
        loadExistingDetails();
    }
    
    // Validasi form sebelum submit
    const formRetur = document.getElementById('formRetur');
    if (formRetur) {
        formRetur.addEventListener('submit', function(e) {
            const detailContainer = document.getElementById('detailContainer');
            const detailRows = detailContainer.querySelectorAll('.item-row');
            
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Detail retur tidak ditemukan. Silakan pilih penerimaan barang terlebih dahulu.');
                return false;
            }
            
            // Validasi setiap item
            let isValid = true;
            let emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyRetur = row.querySelector('[name*="[qty_retur]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const qtyDiterima = parseFloat(row.querySelector('.qty-diterima').value || 0);
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Inventory`);
                }
                if (!qtyRetur || !qtyRetur.value || parseFloat(qtyRetur.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur`);
                } else if (parseFloat(qtyRetur.value) > qtyDiterima) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur tidak boleh lebih dari Qty Diterima (${qtyDiterima})`);
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

