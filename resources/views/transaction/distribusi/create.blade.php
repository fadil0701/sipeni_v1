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
        @php
            $isProsesMode = ($flowMode ?? 'distribusi') === 'proses';
        @endphp
        <h2 class="text-xl font-semibold text-gray-900">
            {{ $isProsesMode ? 'Proses Daftar Permintaan ke SBBK' : 'Tambah Distribusi Barang (SBBK)' }}
        </h2>
        @if($isProsesMode)
            <p class="text-sm text-gray-600 mt-1">Tahap ini untuk menyusun SBBK dari permintaan yang sudah di-approve. Pegawai pengirim ditentukan pada tahap Distribusi.</p>
        @else
            <p class="text-sm text-gray-600 mt-1">Pengiriman langsung tanpa melalui permintaan barang. Pilih gudang asal/tujuan dan isi detail item manual.</p>
        @endif
    </div>
    
    <form action="{{ route('transaction.distribusi.store') }}" method="POST" class="p-6" id="formDistribusi">
        @csrf
        <input type="hidden" name="flow_mode" value="{{ $flowMode ?? 'distribusi' }}">
        @if(!empty($approvalLogId))
            <input type="hidden" name="approval_log_id" value="{{ $approvalLogId }}">
        @endif
        
        <div class="space-y-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @if($isProsesMode)
                        <div>
                            <label for="id_permintaan" class="block text-sm font-medium text-gray-700 mb-2">
                                Permintaan Barang <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="id_permintaan" 
                                name="id_permintaan" 
                                required
                                class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_permintaan') border-red-500 @enderror"
                                onchange="loadPermintaanDetail(this.value)"
                            >
                                <option value="">Pilih Permintaan Barang</option>
                                @foreach($permintaans as $permintaan)
                                    <option value="{{ $permintaan->id_permintaan }}" {{ old('id_permintaan', $selectedPermintaan?->id_permintaan) == $permintaan->id_permintaan ? 'selected' : '' }}>
                                        {{ $permintaan->no_permintaan }} - {{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }} ({{ $permintaan->tanggal_permintaan->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_permintaan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

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
                            class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_asal') border-red-500 @enderror"
                            onchange="loadInventoryFromGudang(this.value)"
                            data-old-value="{{ old('id_gudang_asal') }}"
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
                            class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_tujuan') border-red-500 @enderror"
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

                    @if(!$isProsesMode)
                        <div>
                            <label for="id_pegawai_pengirim" class="block text-sm font-medium text-gray-700 mb-2">
                                Pegawai Pengirim <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="id_pegawai_pengirim" 
                                name="id_pegawai_pengirim" 
                                required
                                class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pengirim') border-red-500 @enderror"
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
                    @endif

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

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 table-no-enhance">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[32%]">Inventory <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Qty <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">Satuan <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[14%]">Harga <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[24%]">Keterangan</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[8%]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detailContainer" class="bg-white divide-y divide-gray-200">
                            <!-- Item akan ditambahkan di sini via JavaScript -->
                        </tbody>
                    </table>
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
                {{ $isProsesMode ? 'Simpan SBBK (Masuk Tahap Distribusi)' : 'Simpan' }}
            </button>
        </div>
    </form>
</div>

<!-- Template untuk item detail (hidden) -->
<template id="itemTemplate">
    <tr class="item-row">
        <td class="px-3 py-2 align-top">
            <select 
                name="detail[INDEX][id_inventory]" 
                required
                data-searchable="true"
                class="select-inventory select-searchable block w-full min-w-[14rem] px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                onchange="updateHargaSatuan(this)"
            >
                <option value="">Pilih Inventory</option>
            </select>
        </td>
        <td class="px-3 py-2 align-top">
            <input 
                type="number" 
                name="detail[INDEX][qty_distribusi]" 
                required
                min="0.01"
                step="0.01"
                placeholder="0"
                class="qty-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                onchange="calculateSubtotal(this)"
            >
        </td>
        <td class="px-3 py-2 align-top">
            <select 
                name="detail[INDEX][id_satuan]" 
                required
                data-searchable="false"
                class="select-satuan block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Pilih</option>
                @foreach($satuans as $satuan)
                    <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2 align-top">
            <input 
                type="number" 
                name="detail[INDEX][harga_satuan]" 
                required
                min="0"
                step="0.01"
                placeholder="0"
                class="harga-satuan-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                onchange="calculateSubtotal(this)"
            >
        </td>
        <td class="px-3 py-2 align-top">
            <input 
                type="text" 
                name="detail[INDEX][keterangan]" 
                placeholder="Opsional"
                class="block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
        </td>
        <td class="px-3 py-2 text-center align-top">
            <button 
                type="button" 
                class="btnHapusItem inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                title="Hapus baris"
                aria-label="Hapus baris"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let itemIndex = 0;
let inventoryData = {};
const selectedApprovalLogId = @json(request('approval_log'));
const isProsesMode = @json(($flowMode ?? 'distribusi') === 'proses');

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

function inventoryApiUrl(gudangId) {
    let url = `{{ route('api.gudang.inventory', ['id' => '__ID__']) }}`.replace('__ID__', encodeURIComponent(gudangId));
    const params = new URLSearchParams();
    const permintaanSelect = document.getElementById('id_permintaan');
    const permintaanId = permintaanSelect ? permintaanSelect.value : '';
    if (isProsesMode && permintaanId) {
        params.set('permintaan_id', permintaanId);
    }
    Array.from(document.querySelectorAll('.select-inventory'))
        .map((el) => parseInt(el.value || el.getAttribute('data-selected-inventory') || '', 10))
        .filter((id) => !Number.isNaN(id) && id > 0)
        .forEach((id) => params.append('include_ids[]', String(id)));
    const qs = params.toString();
    if (qs) {
        url += (url.includes('?') ? '&' : '?') + qs;
    }
    return url;
}

/** Destroy + re-init Select2 setelah opsi diganti via innerHTML (baris dinamis). */
function refreshSearchableSelect(selectElement) {
    if (!selectElement || selectElement.tagName !== 'SELECT') {
        return;
    }
    if (!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function')) {
        return;
    }

    const $el = window.jQuery(selectElement);
    if (selectElement.choicesInstance && typeof selectElement.choicesInstance.destroy === 'function') {
        try { selectElement.choicesInstance.destroy(); } catch (e) {}
        selectElement.choicesInstance = null;
    }
    if ($el.hasClass('select2-hidden-accessible')) {
        try { $el.select2('destroy'); } catch (e) {}
    }
    delete selectElement.dataset.sipeniSelect2Init;

    if (typeof window.initChoicesForSelect === 'function') {
        window.initChoicesForSelect(selectElement, 0);
        return;
    }

    const placeholderOption = selectElement.querySelector('option[value=""]');
    const placeholderText = ((placeholderOption && placeholderOption.textContent) || 'Pilih...').trim();
    $el.select2(Object.assign({}, (typeof window.sipeniSelect2BaseOptions === 'function' ? window.sipeniSelect2BaseOptions() : { width: '100%', minimumResultsForSearch: 0 }), {
        placeholder: placeholderText || 'Pilih...',
        allowClear: !!placeholderOption,
    }));
    selectElement.dataset.sipeniSelect2Init = '1';
}

function populateInventoryOptions(selectElement, inventoryList, preferredValue) {
    if (!selectElement) {
        return;
    }
    const currentValue = preferredValue != null ? String(preferredValue) : String(selectElement.value || selectElement.getAttribute('data-selected-inventory') || '');
    selectElement.innerHTML = '<option value="">Pilih Inventory</option>';

    (inventoryList || []).forEach(inv => {
        const option = document.createElement('option');
        option.value = inv.id_inventory;
        const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
        const merkText = inv.merk && inv.merk !== '-' ? inv.merk : '-';
        const tipeText = inv.tipe && inv.tipe !== '-' ? inv.tipe : '-';
        option.textContent = `${inv.nama_barang}${kodeText} - Merk: ${merkText} - Tipe: ${tipeText} - Stok: ${inv.qty_available}`;
        option.setAttribute('data-harga', inv.harga_satuan);
        option.setAttribute('data-satuan', inv.id_satuan);
        selectElement.appendChild(option);
    });

    if (currentValue && Array.from(selectElement.options).some(opt => String(opt.value) === currentValue)) {
        selectElement.value = currentValue;
        selectElement.setAttribute('data-selected-inventory', currentValue);
        updateHargaSatuan(selectElement);
    } else if ((inventoryList || []).length === 1) {
        selectElement.value = String(inventoryList[0].id_inventory);
        selectElement.setAttribute('data-selected-inventory', String(inventoryList[0].id_inventory));
        updateHargaSatuan(selectElement);
    }

    refreshSearchableSelect(selectElement);
}

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
    const endpoint = `{{ route('transaction.distribusi.api.gudang-tujuan', ':id') }}`.replace(':id', permintaanId);
    const url = selectedApprovalLogId ? `${endpoint}?approval_log=${selectedApprovalLogId}` : endpoint;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const gudangTujuanSelect = document.getElementById('id_gudang_tujuan');
            gudangTujuanSelect.innerHTML = '<option value="">Pilih Gudang Tujuan</option>';

            if (data.success && Array.isArray(data.gudang) && data.gudang.length > 0) {
                data.gudang.forEach(gudang => {
                    const option = document.createElement('option');
                    option.value = gudang.id_gudang;
                    option.textContent = `${gudang.nama_gudang} (${gudang.jenis_gudang})`;
                    gudangTujuanSelect.appendChild(option);
                });

                const oldValue = gudangTujuanSelect.getAttribute('data-old-value');
                if (oldValue && data.gudang.some(g => String(g.id_gudang) === String(oldValue))) {
                    gudangTujuanSelect.value = oldValue;
                } else if (data.gudang.length === 1) {
                    gudangTujuanSelect.value = data.gudang[0].id_gudang;
                }
                refreshSearchableSelect(gudangTujuanSelect);
            } else {
                gudangTujuanSelect.innerHTML = '<option value="">Gudang UNIT tujuan tidak ditemukan</option>';
                refreshSearchableSelect(gudangTujuanSelect);
            }

            // Auto-set gudang asal (pusat) sesuai kategori disposisi/permintaan.
            const gudangAsalSelect = document.getElementById('id_gudang_asal');
            gudangAsalSelect.innerHTML = '<option value="">Pilih Gudang Asal</option>';
            const asalList = Array.isArray(data.gudang_asal) ? data.gudang_asal : [];

            if (asalList.length > 0) {
                asalList.forEach(gudang => {
                    const option = document.createElement('option');
                    option.value = gudang.id_gudang;
                    option.textContent = `${gudang.nama_gudang} (${gudang.kategori_gudang || gudang.jenis_gudang})`;
                    gudangAsalSelect.appendChild(option);
                });

                const oldAsal = gudangAsalSelect.getAttribute('data-old-value');
                if (oldAsal && asalList.some(g => String(g.id_gudang) === String(oldAsal))) {
                    gudangAsalSelect.value = oldAsal;
                } else if (asalList.length === 1) {
                    gudangAsalSelect.value = asalList[0].id_gudang;
                }
                refreshSearchableSelect(gudangAsalSelect);

                if (gudangAsalSelect.value) {
                    loadInventoryFromGudang(gudangAsalSelect.value);
                }
            } else {
                gudangAsalSelect.innerHTML = '<option value="">Gudang PUSAT asal tidak ditemukan</option>';
                refreshSearchableSelect(gudangAsalSelect);
            }
        })
        .catch(error => {
            console.error('Error loading gudang tujuan:', error);
        });

    // Load detail permintaan (pakai route() agar subpath /demo-simantik ikut)
    fetch(`{{ route('api.permintaan.detail', ['id' => '__ID__']) }}`.replace('__ID__', encodeURIComponent(permintaanId)))
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat detail permintaan');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let html = '<table class="min-w-full divide-y divide-gray-200">';
                html += '<thead class="bg-gray-50"><tr>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty Disetujui</th>';
                html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>';
                html += '</tr></thead><tbody>';
                
                data.details.forEach(detail => {
                    html += '<tr>';
                    html += `<td>${detail.nama_barang}</td>`;
                    html += `<td>${detail.qty_diminta}</td>`;
                    html += `<td>${detail.qty_disetujui}</td>`;
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

    fetch(inventoryApiUrl(gudangId))
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat inventory gudang');
            }
            return response.json();
        })
        .then(data => {
            inventoryData = {};
            data.inventory.forEach(inv => {
                inventoryData[inv.id_inventory] = {
                    id_inventory: inv.id_inventory,
                    nama_barang: inv.nama_barang,
                    kode_barang: inv.kode_barang || '',
                    merk: inv.merk || '-',
                    tipe: inv.tipe || '-',
                    harga_satuan: inv.harga_satuan,
                    id_satuan: inv.id_satuan,
                    qty_available: inv.qty_available
                };
            });

            // Update semua select inventory
            document.querySelectorAll('.select-inventory').forEach(select => {
                populateInventoryOptions(select, data.inventory, select.value);
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
            setSatuanSelectValue(satuanSelect, satuanId);
        }
        
        calculateSubtotal(hargaInput);
    }
}

function syncDerivedFieldsFromInventory(row) {
    if (!row) return;
    const inventorySelect = row.querySelector('.select-inventory');
    const hargaInput = row.querySelector('.harga-satuan-input');
    const satuanSelect = row.querySelector('.select-satuan');
    if (!inventorySelect || !hargaInput || !satuanSelect) return;
    if (!inventorySelect.value) return;

    const selectedOption = inventorySelect.options[inventorySelect.selectedIndex];
    if (!selectedOption) return;

    const harga = selectedOption.getAttribute('data-harga');
    const satuanId = selectedOption.getAttribute('data-satuan');

    if (harga && (!hargaInput.value || parseFloat(hargaInput.value) <= 0)) {
        hargaInput.value = harga;
    }
    if (satuanId && !satuanSelect.value) {
        setSatuanSelectValue(satuanSelect, satuanId);
    }
}

/** Set nilai satuan: native + Select2 bila elemen sudah di-wrap (layout). */
function setSatuanSelectValue(selectEl, value) {
    if (!selectEl) {
        return;
    }
    const v = value != null ? String(value) : '';
    selectEl.value = v;
    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function' &&
        window.jQuery(selectEl).hasClass('select2-hidden-accessible')) {
        window.jQuery(selectEl).val(v).trigger('change');
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
    
    fetch(inventoryApiUrl(gudangId))
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat inventory gudang');
            }
            return response.json();
        })
        .then(data => {
            populateInventoryOptions(selectElement, data.inventory);
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
    
    // Clone template content dan replace INDEX (gunakan tbody agar <tr> tidak di-strip browser)
    const tempTbody = document.createElement('tbody');
    tempTbody.innerHTML = template.innerHTML.replace(/INDEX/g, itemIndex);
    const newItem = tempTbody.firstElementChild;
    
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
    
    // Attach event handler untuk update harga satuan (native + Select2)
    inventorySelect.addEventListener('change', function() {
        updateHargaSatuan(this);
    });
    if (window.jQuery) {
        window.jQuery(inventorySelect).on('select2:select select2:clear', function () {
            updateHargaSatuan(this);
        });
    }
    
    // Load inventory ke select baru
    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        // Jika inventoryData sudah ada, langsung isi
        if (Object.keys(inventoryData).length > 0) {
            populateInventoryOptions(inventorySelect, Object.values(inventoryData));
        } else {
            // Jika belum ada, load dari API
            loadInventoryToSelect(inventorySelect, gudangAsal);
        }
    } else {
        refreshSearchableSelect(inventorySelect);
    }

    const satuanSelect = newRow.querySelector('.select-satuan');
    if (satuanSelect && satuanSelect.getAttribute('data-searchable') !== 'false') {
        refreshSearchableSelect(satuanSelect);
    }
    
    // Hapus item
    const btnHapus = newRow.querySelector('.btnHapusItem');
    if (btnHapus) {
        btnHapus.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    }
    
    itemIndex++;
}

// Event listener untuk tombol tambah item dan initialization
document.addEventListener('DOMContentLoaded', function() {
    ['id_permintaan', 'id_gudang_asal', 'id_gudang_tujuan', 'id_pegawai_pengirim'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            refreshSearchableSelect(el);
        }
    });

    // Setup tombol tambah item
    const btnTambahItem = document.getElementById('btnTambahItem');
    if (btnTambahItem) {
        btnTambahItem.addEventListener('click', function(e) {
            e.preventDefault();
            addItemRow();
        });
    }
    
    // Load permintaan detail jika sudah dipilih (hanya mode proses dari approval)
    const permintaanSelect = document.getElementById('id_permintaan');
    if (permintaanSelect && permintaanSelect.value) {
        loadPermintaanDetail(permintaanSelect.value);
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
                // Sinkronkan ulang field turunan dari inventory sebelum validasi.
                syncDerivedFieldsFromInventory(row);

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
                    emptyFields.push(`Item ${index + 1}: Satuan (pilih ulang inventory atau pilih satuan manual)`);
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
