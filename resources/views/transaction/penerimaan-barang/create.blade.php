@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.penerimaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Penerimaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Penerimaan Barang</h2>
    </div>
    
    <form action="{{ route('transaction.penerimaan-barang.store') }}" method="POST" class="p-6" id="formPenerimaan">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Penerimaan</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="id_distribusi" class="block text-sm font-medium text-gray-700 mb-2">
                            Distribusi (SBBK) <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_distribusi" 
                            name="id_distribusi" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_distribusi') border-red-500 @enderror"
                            onchange="loadDistribusiDetail(this.value)"
                        >
                            <option value="">Pilih Distribusi (SBBK)</option>
                            @foreach($distribusis as $distribusi)
                                <option value="{{ $distribusi->id_distribusi }}" {{ old('id_distribusi') == $distribusi->id_distribusi ? 'selected' : '' }}>
                                    {{ $distribusi->no_sbbk }} - {{ $distribusi->gudangTujuan->nama_gudang ?? '-' }} ({{ $distribusi->tanggal_distribusi->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_distribusi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_penerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Penerimaan <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_penerimaan" 
                            name="tanggal_penerimaan" 
                            required
                            value="{{ old('tanggal_penerimaan', date('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_penerimaan') border-red-500 @enderror"
                        >
                        @error('tanggal_penerimaan')
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
                        <label for="id_pegawai_penerima" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Penerima <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_penerima" 
                            name="id_pegawai_penerima" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_penerima') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Penerima</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_penerima') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_penerima')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_penerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Penerimaan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_penerimaan" 
                            name="status_penerimaan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_penerimaan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Status</option>
                            <option value="DITERIMA" {{ old('status_penerimaan') == 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                            <option value="DITOLAK" {{ old('status_penerimaan') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        @error('status_penerimaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan penerimaan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Distribusi (untuk referensi) -->
            <div id="distribusiDetail" style="display: none;">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Distribusi</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div id="distribusiContent"></div>
                </div>
            </div>

            <!-- Detail Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penerimaan</h3>
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
                href="{{ route('transaction.penerimaan-barang.index') }}" 
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
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Barang
                </label>
                <input 
                    type="text" 
                    class="nama-barang block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
                <input type="hidden" name="detail[INDEX][id_inventory]" class="id-inventory-input">
                <input type="hidden" class="kategori-gudang-input">
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Distribusi
                </label>
                <input 
                    type="text" 
                    class="qty-distribusi block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty Diterima <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][qty_diterima]" 
                    required
                    min="0"
                    step="0.01"
                    placeholder="0"
                    class="qty-diterima-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>

            <div class="sm:col-span-1">
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

            <div class="sm:col-span-2 batch-exp-date-container" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    No. Batch
                </label>
                <input 
                    type="text" 
                    class="no-batch-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-2 batch-exp-date-container" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Exp Date
                </label>
                <input 
                    type="text" 
                    class="exp-date-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-2 no-seri-container" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    No. Seri
                </label>
                <input 
                    type="text" 
                    class="no-seri-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                    readonly
                >
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <input 
                    type="text" 
                    name="detail[INDEX][keterangan]" 
                    placeholder="Opsional"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let distribusiDetails = [];

// Load detail distribusi
function loadDistribusiDetail(distribusiId) {
    if (!distribusiId) {
        document.getElementById('distribusiDetail').style.display = 'none';
        document.getElementById('detailContainer').innerHTML = '';
        return;
    }

    fetch(`/api/distribusi/${distribusiId}/detail`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.details && data.details.length > 0) {
                distribusiDetails = data.details;
                
                // Tampilkan info distribusi
                let html = `<p class="text-sm text-gray-700 mb-2"><strong>No SBBK:</strong> ${data.distribusi.no_sbbk}</p>`;
                html += `<p class="text-sm text-gray-700 mb-4"><strong>Gudang Tujuan:</strong> ${data.distribusi.gudang_tujuan}</p>`;
                
                // Cek apakah ada farmasi/persediaan atau aset
                const hasFarmasiPersediaan = data.details.some(d => ['FARMASI', 'PERSEDIAAN'].includes(d.kategori_gudang));
                const hasAset = data.details.some(d => d.kategori_gudang === 'ASET');
                
                html += '<table class="min-w-full divide-y divide-gray-200">';
                html += '<thead class="bg-gray-50"><tr>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Distribusi</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>';
                if (hasFarmasiPersediaan) {
                    html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>';
                    html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Exp Date</th>';
                }
                if (hasAset) {
                    html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>';
                }
                html += '</tr></thead><tbody>';
                
                data.details.forEach(detail => {
                    const isFarmasiPersediaan = ['FARMASI', 'PERSEDIAAN'].includes(detail.kategori_gudang);
                    const isAset = detail.kategori_gudang === 'ASET';
                    
                    html += '<tr>';
                    html += `<td>${detail.nama_barang}</td>`;
                    html += `<td>${detail.qty_distribusi}</td>`;
                    html += `<td>${detail.nama_satuan}</td>`;
                    if (hasFarmasiPersediaan) {
                        html += `<td>${isFarmasiPersediaan ? (detail.no_batch || '-') : '-'}</td>`;
                        html += `<td>${isFarmasiPersediaan ? (detail.tanggal_kedaluwarsa || '-') : '-'}</td>`;
                    }
                    if (hasAset) {
                        if (isAset) {
                            if (detail.no_seri) {
                                if (Array.isArray(detail.no_seri)) {
                                    if (detail.no_seri.length <= 3) {
                                        html += `<td>${detail.no_seri.join(', ')}</td>`;
                                    } else {
                                        html += `<td>${detail.no_seri.slice(0, 3).join(', ')}<br><span class="text-xs text-gray-500">+${detail.no_seri.length - 3} lainnya</span></td>`;
                                    }
                                } else {
                                    html += `<td>${detail.no_seri}</td>`;
                                }
                            } else {
                                html += '<td>-</td>';
                            }
                        } else {
                            html += '<td>-</td>';
                        }
                    }
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                document.getElementById('distribusiContent').innerHTML = html;
                document.getElementById('distribusiDetail').style.display = 'block';

                // Auto-set unit kerja dari gudang tujuan
                if (data.distribusi.unit_kerja) {
                    document.getElementById('id_unit_kerja').value = data.distribusi.unit_kerja;
                }

                // Load detail penerimaan
                console.log('Loading detail penerimaan with', data.details.length, 'items');
                loadDetailPenerimaan(data.details);
            } else {
                console.error('No details found in response:', data);
                alert('Detail distribusi tidak ditemukan. Silakan pilih distribusi lain.');
            }
        })
        .catch(error => {
            console.error('Error loading distribusi detail:', error);
            alert('Terjadi kesalahan saat memuat detail distribusi. Silakan coba lagi.');
        });
}

// Load detail penerimaan berdasarkan detail distribusi
function loadDetailPenerimaan(details) {
    const container = document.getElementById('detailContainer');
    container.innerHTML = '';
    
    if (!details || details.length === 0) {
        console.error('No details provided');
        return;
    }
    
    let index = 0;
    details.forEach(detail => {
        const template = document.getElementById('itemTemplate');
        if (!template) {
            console.error('Template not found');
            return;
        }
        
        // Clone template dengan cara yang benar
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, index);
        const itemElement = tempDiv.firstElementChild;
        
        if (!itemElement) {
            console.error('Failed to clone template');
            return;
        }
        
        // Set values
        const namaBarangInput = itemElement.querySelector('.nama-barang');
        const idInventoryInput = itemElement.querySelector('.id-inventory-input');
        const kategoriGudangInput = itemElement.querySelector('.kategori-gudang-input');
        const qtyDistribusiInput = itemElement.querySelector('.qty-distribusi');
        const qtyDiterimaInput = itemElement.querySelector('.qty-diterima-input');
        const satuanSelect = itemElement.querySelector('.select-satuan');
        const noBatchInput = itemElement.querySelector('.no-batch-input');
        const expDateInput = itemElement.querySelector('.exp-date-input');
        const noSeriInput = itemElement.querySelector('.no-seri-input');
        const batchExpDateContainers = itemElement.querySelectorAll('.batch-exp-date-container'); // Gunakan querySelectorAll untuk mendapatkan semua container
        const noSeriContainer = itemElement.querySelector('.no-seri-container');
        
        if (namaBarangInput) namaBarangInput.value = detail.nama_barang || '';
        if (idInventoryInput) idInventoryInput.value = detail.id_inventory || '';
        if (kategoriGudangInput) kategoriGudangInput.value = detail.kategori_gudang || '';
        if (qtyDistribusiInput) qtyDistribusiInput.value = detail.qty_distribusi || '0';
        if (qtyDiterimaInput) qtyDiterimaInput.value = detail.qty_distribusi || '0'; // Default sama dengan qty distribusi
        if (satuanSelect) satuanSelect.value = detail.id_satuan || '';
        
        // Tampilkan/sembunyikan kolom berdasarkan kategori gudang
        const isFarmasiPersediaan = ['FARMASI', 'PERSEDIAAN'].includes(detail.kategori_gudang);
        const isAset = detail.kategori_gudang === 'ASET';
        
        // Tampilkan/sembunyikan semua container batch-exp-date (ada 2: No. Batch dan Exp Date)
        if (batchExpDateContainers && batchExpDateContainers.length > 0) {
            batchExpDateContainers.forEach(container => {
                if (isFarmasiPersediaan) {
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                }
            });
            
            // Set nilai untuk No. Batch dan Exp Date jika Farmasi/Persediaan
            if (isFarmasiPersediaan) {
                if (noBatchInput) noBatchInput.value = detail.no_batch || '-';
                if (expDateInput) expDateInput.value = detail.tanggal_kedaluwarsa || '-';
            }
        }
        
        if (noSeriContainer) {
            if (isAset) {
                noSeriContainer.style.display = 'block';
                if (noSeriInput) {
                    if (detail.no_seri) {
                        if (Array.isArray(detail.no_seri)) {
                            if (detail.no_seri.length <= 3) {
                                noSeriInput.value = detail.no_seri.join(', ');
                            } else {
                                noSeriInput.value = detail.no_seri.slice(0, 3).join(', ') + ' (+' + (detail.no_seri.length - 3) + ' lainnya)';
                            }
                        } else {
                            noSeriInput.value = detail.no_seri;
                        }
                    } else {
                        noSeriInput.value = '-';
                    }
                }
            } else {
                noSeriContainer.style.display = 'none';
            }
        }
        
        container.appendChild(itemElement);
        index++;
    });
    
    console.log('Detail penerimaan loaded:', index, 'items');
}

// Load distribusi detail jika sudah dipilih
document.addEventListener('DOMContentLoaded', function() {
    const distribusiId = document.getElementById('id_distribusi').value;
    if (distribusiId) {
        loadDistribusiDetail(distribusiId);
    }
    
    // Validasi form sebelum submit
    const formPenerimaan = document.getElementById('formPenerimaan');
    if (formPenerimaan) {
        formPenerimaan.addEventListener('submit', function(e) {
            const detailContainer = document.getElementById('detailContainer');
            const detailRows = detailContainer.querySelectorAll('.item-row');
            
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Detail penerimaan tidak ditemukan. Silakan pilih distribusi (SBBK) terlebih dahulu.');
                return false;
            }
            
            // Validasi setiap item
            let isValid = true;
            let emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyDiterima = row.querySelector('[name*="[qty_diterima]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                
                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Inventory`);
                }
                if (!qtyDiterima || !qtyDiterima.value || parseFloat(qtyDiterima.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Diterima`);
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

