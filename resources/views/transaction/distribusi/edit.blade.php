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

@php
    $intentProses = $intentProses ?? false;
@endphp
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
            {{ $intentProses ? 'Proses Distribusi — Isi Pegawai Pengirim' : 'Edit Distribusi Barang (SBBK)' }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">No. SBBK: <span class="font-semibold">{{ $distribusi->no_sbbk }}</span></p>
        @if($intentProses)
            <p class="mt-2 text-sm text-blue-800 bg-blue-50 border border-indigo-100 rounded-md px-3 py-2">
                Pilih <strong>Pegawai Pengirim</strong> lalu simpan untuk memproses SBBK. Setelah itu Anda dapat mengirim distribusi.
            </p>
        @endif
    </div>
    
    <form action="{{ route('transaction.distribusi.update', $distribusi->id_distribusi) }}" method="POST" class="p-6" id="formDistribusi">
        @csrf
        @method('PUT')
        @if($intentProses)
            <input type="hidden" name="intent" value="proses">
        @endif
        
        <div class="space-y-6">
            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permintaan Barang (acuan proses)</label>
                        <input
                            type="text"
                            readonly
                            value="{{ $selectedPermintaan?->no_permintaan ? $selectedPermintaan->no_permintaan.' - '.($selectedPermintaan->unitKerja->nama_unit_kerja ?? '-') : '-' }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 sm:text-sm"
                        >
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
                            class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_asal') border-red-500 @enderror"
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
                            class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_gudang_tujuan') border-red-500 @enderror"
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

                    <div class="{{ $intentProses ? 'sm:col-span-2 rounded-lg border border-blue-200 bg-blue-50/50 p-4' : '' }}">
                        <label for="id_pegawai_pengirim" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Pengirim <span class="text-red-500">*</span>
                            @if($intentProses)
                                <span class="ml-1 text-xs font-normal text-blue-700">(wajib untuk memproses)</span>
                            @endif
                        </label>
                        <select 
                            id="id_pegawai_pengirim" 
                            name="id_pegawai_pengirim" 
                            required
                            @if($intentProses) autofocus @endif
                            class="select-searchable block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pengirim') border-red-500 @enderror {{ $intentProses ? 'border-blue-300 ring-1 ring-indigo-200' : '' }}"
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

            @if($selectedPermintaan && $selectedPermintaan->detailPermintaan->count() > 0)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Permintaan (hasil proses)</h3>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 table-no-enhance">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diminta</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Disetujui</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($selectedPermintaan->detailPermintaan as $detailPermintaan)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detailPermintaan->dataBarang->nama_barang ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format((float) ($detailPermintaan->qty_diminta_awal ?? $detailPermintaan->qty_diminta), 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format((float) ($detailPermintaan->qty_disetujui ?? $detailPermintaan->qty_diminta), 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detailPermintaan->satuan->nama_satuan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

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
                            @foreach(old('detail', $distribusi->detailDistribusi) as $index => $detail)
                            @php
                                $selectedInvId = old("detail.{$index}.id_inventory", is_object($detail) ? $detail->id_inventory : ($detail['id_inventory'] ?? ''));
                                $selectedInv = is_object($detail) ? $detail->inventory : null;
                            @endphp
                            <tr class="item-row">
                                <td class="px-3 py-2 align-top">
                                    <select 
                                        name="detail[{{ $index }}][id_inventory]" 
                                        required
                                        data-selected-inventory="{{ $selectedInvId }}"
                                        class="select-inventory select-searchable block w-full min-w-[14rem] px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="updateHargaSatuan(this)"
                                    >
                                        <option value="">Pilih Inventory</option>
                                        @if($selectedInvId)
                                            <option value="{{ $selectedInvId }}" selected>
                                                @if($selectedInv)
                                                    {{ $selectedInv->dataBarang->nama_barang ?? '-' }}{{ $selectedInv->dataBarang?->kode_data_barang ? ' ('.$selectedInv->dataBarang->kode_data_barang.')' : '' }}
                                                    - Merk: {{ $selectedInv->merk ?: '-' }}
                                                    - Tipe: {{ $selectedInv->tipe ?: '-' }}
                                                @else
                                                    Inventory #{{ $selectedInvId }}
                                                @endif
                                            </option>
                                        @endif
                                    </select>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input 
                                        type="number" 
                                        name="detail[{{ $index }}][qty_distribusi]" 
                                        required
                                        min="0.01"
                                        step="0.01"
                                        value="{{ old("detail.{$index}.qty_distribusi", is_object($detail) ? $detail->qty_distribusi : ($detail['qty_distribusi'] ?? '')) }}"
                                        placeholder="0"
                                        class="qty-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="calculateSubtotal(this)"
                                    >
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <select 
                                        name="detail[{{ $index }}][id_satuan]" 
                                        required
                                        data-searchable="false"
                                        class="select-satuan block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Pilih</option>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->id_satuan }}" 
                                                {{ old("detail.{$index}.id_satuan", is_object($detail) ? $detail->id_satuan : ($detail['id_satuan'] ?? '')) == $satuan->id_satuan ? 'selected' : '' }}>
                                                {{ $satuan->nama_satuan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input 
                                        type="number" 
                                        name="detail[{{ $index }}][harga_satuan]" 
                                        required
                                        min="0"
                                        step="0.01"
                                        value="{{ old("detail.{$index}.harga_satuan", is_object($detail) ? $detail->harga_satuan : ($detail['harga_satuan'] ?? '')) }}"
                                        placeholder="0"
                                        class="harga-satuan-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        onchange="calculateSubtotal(this)"
                                    >
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <input 
                                        type="text" 
                                        name="detail[{{ $index }}][keterangan]" 
                                        value="{{ old("detail.{$index}.keterangan", is_object($detail) ? $detail->keterangan : ($detail['keterangan'] ?? '')) }}"
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
                            @endforeach
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
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $intentProses ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
            >
                {{ $intentProses ? 'Simpan & Proses' : 'Simpan' }}
            </button>
        </div>
    </form>
</div>

<!-- Template untuk item detail baru (hidden) -->
<template id="itemTemplate">
    <tr class="item-row">
        <td class="px-3 py-2 align-top">
            <select 
                name="detail[INDEX][id_inventory]" 
                required
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
let itemIndex = {{ count(old('detail', $distribusi->detailDistribusi)) }};
let inventoryData = {};
const currentDistribusiId = @json((int) $distribusi->id_distribusi);

function getSelectedInventoryIds() {
    return Array.from(document.querySelectorAll('.select-inventory'))
        .map((el) => {
            const fromValue = parseInt(el.value, 10);
            if (!Number.isNaN(fromValue) && fromValue > 0) {
                return fromValue;
            }
            const fromAttr = parseInt(el.getAttribute('data-selected-inventory') || '', 10);
            return (!Number.isNaN(fromAttr) && fromAttr > 0) ? fromAttr : 0;
        })
        .filter((id) => id > 0);
}

function refreshSearchableSelect(selectElement) {
    if (!selectElement || selectElement.tagName !== 'SELECT') {
        return;
    }
    if (selectElement.getAttribute('data-searchable') === 'false') {
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

function setSelectValue(selectEl, value) {
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

function setSatuanSelectValue(selectEl, value) {
    setSelectValue(selectEl, value);
}

function inventoryLabel(inv) {
    const kodeText = inv.kode_barang ? ` (${inv.kode_barang})` : '';
    const merkText = inv.merk && inv.merk !== '-' ? inv.merk : '-';
    const tipeText = inv.tipe && inv.tipe !== '-' ? inv.tipe : '-';
    return `${inv.nama_barang}${kodeText} - Merk: ${merkText} - Tipe: ${tipeText} - Stok: ${inv.qty_available}`;
}

function populateInventoryOptions(selectElement, inventoryList, preferredValue) {
    if (!selectElement) {
        return;
    }
    const currentValue = preferredValue != null
        ? String(preferredValue)
        : String(selectElement.value || selectElement.getAttribute('data-selected-inventory') || '');

    selectElement.innerHTML = '<option value="">Pilih Inventory</option>';
    (inventoryList || []).forEach(inv => {
        const option = document.createElement('option');
        option.value = inv.id_inventory;
        option.textContent = inventoryLabel(inv);
        option.setAttribute('data-harga', inv.harga_satuan);
        option.setAttribute('data-satuan', inv.id_satuan);
        selectElement.appendChild(option);
    });

    if (currentValue && Array.from(selectElement.options).some(opt => String(opt.value) === currentValue)) {
        selectElement.value = currentValue;
        selectElement.setAttribute('data-selected-inventory', currentValue);
        updateHargaSatuan(selectElement);
    }

    refreshSearchableSelect(selectElement);
}

function loadInventoryFromGudang(gudangId) {
    if (!gudangId) {
        return;
    }

    const includeIds = getSelectedInventoryIds();
    const params = new URLSearchParams();
    includeIds.forEach((id) => params.append('include_ids[]', String(id)));
    if (currentDistribusiId > 0) {
        params.set('exclude_distribusi_id', String(currentDistribusiId));
    }
    const query = params.toString() ? `?${params.toString()}` : '';

    fetch(`{{ route('api.gudang.inventory', ['id' => '__ID__']) }}`.replace('__ID__', encodeURIComponent(gudangId)) + query)
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat inventory gudang');
            }
            return response.json();
        })
        .then(data => {
            inventoryData = {};
            (data.inventory || []).forEach(inv => {
                inventoryData[inv.id_inventory] = inv;
            });

            document.querySelectorAll('.select-inventory').forEach(select => {
                const preferred = select.value || select.getAttribute('data-selected-inventory') || '';
                populateInventoryOptions(select, data.inventory, preferred);
            });
        })
        .catch(error => console.error('Error loading inventory:', error));
}

function updateHargaSatuan(select) {
    const row = select.closest('.item-row');
    if (!row) {
        return;
    }
    const hargaInput = row.querySelector('.harga-satuan-input');
    const satuanSelect = row.querySelector('.select-satuan');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        select.setAttribute('data-selected-inventory', selectedOption.value);
        const harga = selectedOption.getAttribute('data-harga');
        const satuanId = selectedOption.getAttribute('data-satuan');
        if (harga && hargaInput) {
            hargaInput.value = harga;
        }
        if (satuanId) {
            setSatuanSelectValue(satuanSelect, satuanId);
        }
    }
}

function calculateSubtotal(input) {
    // Subtotal dihitung di backend
}

document.getElementById('btnTambahItem').addEventListener('click', function() {
    const template = document.getElementById('itemTemplate');
    const container = document.getElementById('detailContainer');
    const tempTbody = document.createElement('tbody');
    tempTbody.innerHTML = template.innerHTML.replace(/INDEX/g, itemIndex);
    const newItem = tempTbody.firstElementChild;
    if (!newItem) {
        return;
    }
    container.appendChild(newItem);

    const inventorySelect = newItem.querySelector('.select-inventory');
    if (inventorySelect) {
        inventorySelect.addEventListener('change', function () {
            updateHargaSatuan(this);
        });
        if (window.jQuery) {
            window.jQuery(inventorySelect).on('select2:select select2:clear', function () {
                updateHargaSatuan(this);
            });
        }
        if (Object.keys(inventoryData).length > 0) {
            populateInventoryOptions(inventorySelect, Object.values(inventoryData));
        } else {
            const gudangAsal = document.getElementById('id_gudang_asal').value;
            if (gudangAsal) {
                loadInventoryFromGudang(gudangAsal);
            } else {
                refreshSearchableSelect(inventorySelect);
            }
        }
    }

    const btnHapus = newItem.querySelector('.btnHapusItem');
    if (btnHapus) {
        btnHapus.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    }

    itemIndex++;
});

document.addEventListener('DOMContentLoaded', function() {
    ['id_gudang_asal', 'id_gudang_tujuan', 'id_pegawai_pengirim'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            refreshSearchableSelect(el);
        }
    });

    document.querySelectorAll('.btnHapusItem').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    });

    document.querySelectorAll('.select-inventory').forEach((select) => {
        select.addEventListener('change', function () {
            updateHargaSatuan(this);
        });
        if (window.jQuery) {
            window.jQuery(select).on('select2:select select2:clear', function () {
                updateHargaSatuan(this);
            });
        }
    });

    const gudangAsal = document.getElementById('id_gudang_asal').value;
    if (gudangAsal) {
        loadInventoryFromGudang(gudangAsal);
    } else {
        document.querySelectorAll('.select-inventory').forEach((select) => refreshSearchableSelect(select));
    }

    const formDistribusi = document.getElementById('formDistribusi');
    if (formDistribusi) {
        formDistribusi.addEventListener('submit', function (e) {
            const detailRows = document.querySelectorAll('#detailContainer .item-row');
            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Minimal harus ada 1 item distribusi.');
                return false;
            }

            let isValid = true;
            const emptyFields = [];
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
                if (!hargaSatuan || !hargaSatuan.value || parseFloat(hargaSatuan.value) < 0) {
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

