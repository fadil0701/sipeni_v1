@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.permintaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Permintaan Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Permintaan Barang</h2>
        <p class="text-sm text-gray-600 mt-1">No. Permintaan: <span class="font-semibold">{{ $permintaan->no_permintaan }}</span></p>
    </div>
    
    <form action="{{ route('transaction.permintaan-barang.update', $permintaan->id_permintaan) }}" method="POST" class="p-6" id="formPermintaan">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Permintaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permintaan</h3>
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
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $permintaan->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pemohon" class="block text-sm font-medium text-gray-700 mb-2">
                            Pemohon <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pemohon" 
                            name="id_pemohon" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pemohon') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pemohon</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pemohon', $permintaan->id_pemohon) == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pemohon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_permintaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Permintaan <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_permintaan" 
                            name="tanggal_permintaan" 
                            required
                            value="{{ old('tanggal_permintaan', $permintaan->tanggal_permintaan->format('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_permintaan') border-red-500 @enderror"
                        >
                        @error('tanggal_permintaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Permintaan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="tipe_rutin_edit" 
                                    name="tipe_permintaan" 
                                    value="RUTIN"
                                    {{ old('tipe_permintaan', $permintaan->tipe_permintaan) == 'RUTIN' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    onchange="updateSubJenis()"
                                >
                                <label for="tipe_rutin_edit" class="ml-2 block text-sm text-gray-700">
                                    Rutin
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="tipe_cito_edit" 
                                    name="tipe_permintaan" 
                                    value="CITO"
                                    {{ old('tipe_permintaan', $permintaan->tipe_permintaan) == 'CITO' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    onchange="updateSubJenis()"
                                >
                                <label for="tipe_cito_edit" class="ml-2 block text-sm text-gray-700">
                                    CITO (Penting)
                                </label>
                            </div>
                        </div>
                        @error('tipe_permintaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="subJenisContainer" class="mt-4 {{ old('tipe_permintaan', $permintaan->tipe_permintaan) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sub Jenis Permintaan <span class="text-red-500">*</span>
                        </label>
                        <div id="subJenisOptions" class="space-y-2">
                            @php
                                $rawJenis = is_array($permintaan->jenis_permintaan) ? $permintaan->jenis_permintaan : (is_string($permintaan->jenis_permintaan) ? json_decode($permintaan->jenis_permintaan, true) : []);
                                $jenisPermintaan = array_values(array_intersect((array) old('jenis_permintaan', $rawJenis), ['PERSEDIAAN', 'FARMASI']));
                            @endphp
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="subjenis_persediaan" 
                                    name="jenis_permintaan[]" 
                                    value="PERSEDIAAN"
                                    {{ in_array('PERSEDIAAN', $jenisPermintaan) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label for="subjenis_persediaan" class="ml-2 block text-sm text-gray-700">
                                    Persediaan
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="subjenis_farmasi" 
                                    name="jenis_permintaan[]" 
                                    value="FARMASI"
                                    {{ in_array('FARMASI', $jenisPermintaan) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label for="subjenis_farmasi" class="ml-2 block text-sm text-gray-700">
                                    Farmasi
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Satu SPB bisa ke satu gudang atau ke semua gudang (Persediaan + Farmasi). Aset tidak masuk permintaan rutin/cito.</p>
                        </div>
                        @error('jenis_permintaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('jenis_permintaan.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan permintaan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan', $permintaan->keterangan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Permintaan -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detail Permintaan</h3>
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
                    @foreach(old('detail', $permintaan->detailPermintaan) as $index => $detail)
                    <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 items-end">
                            <div class="sm:col-span-4 flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Data Barang / Permintaan lainnya <span class="text-red-500">*</span>
                                </label>
                                @php
                                    $useLainnyaEdit = !empty(trim((string) (old("detail.{$index}.deskripsi_barang") ?? (is_object($detail) ? $detail->deskripsi_barang : ($detail['deskripsi_barang'] ?? '')))));
                                @endphp
                                <div class="flex gap-4 mb-2">
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" name="detail[{{ $index }}][tipe_barang]" value="master" class="tipe-barang-radio mr-1" {{ !$useLainnyaEdit ? 'checked' : '' }}>
                                        Dari master
                                    </label>
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" name="detail[{{ $index }}][tipe_barang]" value="lainnya" class="tipe-barang-radio mr-1" {{ $useLainnyaEdit ? 'checked' : '' }}>
                                        Permintaan lainnya (freetext)
                                    </label>
                                </div>
                                <div class="min-h-[38px] w-full min-w-0">
                                    <div class="wrap-master w-full min-w-0" style="{{ $useLainnyaEdit ? 'display:none' : '' }}">
                                        <select 
                                            name="detail[{{ $index }}][id_data_barang]" 
                                            class="select-data-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error("detail.{$index}.id_data_barang") border-red-500 @enderror"
                                        >
                                            <option value="">Pilih Data Barang</option>
                                            @foreach($dataBarangs as $dataBarang)
                                                <option value="{{ $dataBarang->id_data_barang }}" 
                                                    data-satuan="{{ $dataBarang->id_satuan }}"
                                                    {{ old("detail.{$index}.id_data_barang", is_object($detail) ? $detail->id_data_barang : ($detail['id_data_barang'] ?? '')) == $dataBarang->id_data_barang ? 'selected' : '' }}>
                                                    {{ $dataBarang->kode_data_barang }} - {{ $dataBarang->nama_barang }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="wrap-lainnya w-full min-w-0" style="{{ $useLainnyaEdit ? '' : 'display:none' }}">
                                        <input type="text" 
                                            name="detail[{{ $index }}][deskripsi_barang]" 
                                            value="{{ old("detail.{$index}.deskripsi_barang", is_object($detail) ? $detail->deskripsi_barang : ($detail['deskripsi_barang'] ?? '')) }}"
                                            placeholder="Ketik deskripsi barang (tidak masuk master/stock)"
                                            maxlength="500"
                                            class="input-deskripsi-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        >
                                    </div>
                                </div>
                                @error("detail.{$index}.id_data_barang")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Qty <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="detail[{{ $index }}][qty_diminta]" 
                                    required
                                    min="0.01"
                                    step="0.01"
                                    value="{{ old("detail.{$index}.qty_diminta", is_object($detail) ? $detail->qty_diminta : ($detail['qty_diminta'] ?? '')) }}"
                                    placeholder="0"
                                    max=""
                                    class="qty-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error("detail.{$index}.qty_diminta") border-red-500 @enderror"
                                >
                                @error("detail.{$index}.qty_diminta")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="detail[{{ $index }}][id_satuan]" 
                                    required
                                    class="select-satuan block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error("detail.{$index}.id_satuan") border-red-500 @enderror"
                                >
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id_satuan }}" 
                                            {{ old("detail.{$index}.id_satuan", is_object($detail) ? $detail->id_satuan : ($detail['id_satuan'] ?? '')) == $satuan->id_satuan ? 'selected' : '' }}>
                                            {{ $satuan->nama_satuan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("detail.{$index}.id_satuan")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Tersedia
                                </label>
                                <div class="stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm font-semibold text-gray-700 text-center">
                                    -
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <input 
                                    type="text" 
                                    name="detail[{{ $index }}][keterangan]" 
                                    value="{{ old("detail.{$index}.keterangan", is_object($detail) ? $detail->keterangan : ($detail['keterangan'] ?? '')) }}"
                                    placeholder="Opsional"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>

                            <div class="sm:col-span-1 flex items-center justify-center pb-0.5">
                                <button 
                                    type="button" 
                                    class="btnHapusItem p-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center"
                                    title="Hapus Item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
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
                href="{{ route('transaction.permintaan-barang.index') }}" 
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 items-end">
            <div class="sm:col-span-4 flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Data Barang / Permintaan lainnya <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4 mb-2">
                    <label class="inline-flex items-center text-sm">
                        <input type="radio" name="detail[INDEX][tipe_barang]" value="master" class="tipe-barang-radio mr-1" checked>
                        Dari master
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="radio" name="detail[INDEX][tipe_barang]" value="lainnya" class="tipe-barang-radio mr-1">
                        Permintaan lainnya (freetext)
                    </label>
                </div>
                <div class="min-h-[38px] w-full min-w-0">
                    <div class="wrap-master w-full min-w-0">
                        <select 
                            name="detail[INDEX][id_data_barang]" 
                            class="select-data-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Pilih Data Barang</option>
                            @foreach($dataBarangs as $dataBarang)
                                <option value="{{ $dataBarang->id_data_barang }}" data-satuan="{{ $dataBarang->id_satuan }}">
                                    {{ $dataBarang->kode_data_barang }} - {{ $dataBarang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="wrap-lainnya w-full min-w-0" style="display:none">
                        <input type="text" 
                            name="detail[INDEX][deskripsi_barang]" 
                            placeholder="Ketik deskripsi barang (tidak masuk master/stock)"
                            maxlength="500"
                            class="input-deskripsi-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                </div>
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qty <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="detail[INDEX][qty_diminta]" 
                    required
                    min="0.01"
                    step="0.01"
                    placeholder="0"
                    max=""
                    class="qty-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Satuan <span class="text-red-500">*</span>
                </label>
                <select 
                    name="detail[INDEX][id_satuan]" 
                    required
                    class="select-satuan block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                    <option value="">Pilih Satuan</option>
                    @foreach($satuans as $satuan)
                        <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Stock Tersedia
                </label>
                <div class="stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm font-semibold text-gray-700 text-center">
                    -
                </div>
            </div>

            <div class="sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <input 
                    type="text" 
                    name="detail[INDEX][keterangan]" 
                    placeholder="Opsional"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
            </div>

            <div class="sm:col-span-1 flex items-center justify-center pb-0.5">
                <button 
                    type="button" 
                    class="btnHapusItem p-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center"
                    title="Hapus Item"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = {{ count(old('detail', $permintaan->detailPermintaan)) }};
const stockData = @json($stockData ?? []);
const stockPersediaanIds = @json(array_map('intval', $stockPersediaanIds ?? []));
const stockFarmasiIds = @json(array_map('intval', $stockFarmasiIds ?? []));

// Helper function untuk format number
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    const n = !isFinite(+number) ? 0 : +number;
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    const sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
    const dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
    let s = '';
    const toFixedFix = function(n, prec) {
        const k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
    };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

// Helper: lookup stock
function getStockForBarang(barangId) {
    if (!barangId) return null;
    const id = String(barangId);
    const num = parseInt(barangId, 10);
    return stockData[id] || stockData[num] || null;
}

// Helper: nilai stock yang ditampilkan (hanya Persediaan & Farmasi, stock gudang pusat).
function getDisplayStock(barangId) {
    const info = getStockForBarang(barangId);
    if (!info) return null;
    const checkedJenis = Array.from(document.querySelectorAll('input[name="jenis_permintaan[]"]:checked')).map(cb => cb.value);
    const id = parseInt(barangId, 10);
    const inFarmasiIds = (stockFarmasiIds || []).map(Number).includes(id);
    const inPersediaanIds = (stockPersediaanIds || []).map(Number).includes(id);
    if (inFarmasiIds && checkedJenis.includes('FARMASI') && info.stock_gudang_pusat_farmasi !== undefined) {
        return parseFloat(info.stock_gudang_pusat_farmasi) || 0;
    }
    if (inPersediaanIds && checkedJenis.includes('PERSEDIAAN') && info.stock_gudang_pusat_persediaan !== undefined) {
        return parseFloat(info.stock_gudang_pusat_persediaan) || 0;
    }
    return parseFloat(info.total) || 0;
}

// Batasi Qty dengan stock gudang pusat untuk PERSEDIAAN/FARMASI
function shouldEnforceMaxStock(barangId) {
    const checkedJenis = Array.from(document.querySelectorAll('input[name="jenis_permintaan[]"]:checked')).map(cb => cb.value);
    const id = parseInt(barangId, 10);
    const inFarmasiIds = (stockFarmasiIds || []).map(Number).includes(id);
    const inPersediaanIds = (stockPersediaanIds || []).map(Number).includes(id);
    if (checkedJenis.includes('FARMASI') && inFarmasiIds) return true;
    if (checkedJenis.includes('PERSEDIAAN') && inPersediaanIds) return true;
    return false;
}

// Toggle "Dari master" vs "Permintaan lainnya" per baris
function setupTipeBarangToggle(row) {
    if (!row) return;
    const masterWrap = row.querySelector('.wrap-master');
    const lainnyaWrap = row.querySelector('.wrap-lainnya');
    const selectBarang = row.querySelector('.select-data-barang');
    const inputDeskripsi = row.querySelector('.input-deskripsi-barang');
    const stockDisplay = row.querySelector('.stock-display');
    const qtyInput = row.querySelector('.qty-input');
    const radios = row.querySelectorAll('.tipe-barang-radio');
    if (!radios.length || !masterWrap || !lainnyaWrap) return;
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const isLainnya = this.value === 'lainnya';
            masterWrap.style.display = isLainnya ? 'none' : '';
            lainnyaWrap.style.display = isLainnya ? '' : 'none';
            if (isLainnya) {
                if (selectBarang) selectBarang.value = '';
                if (stockDisplay) { stockDisplay.textContent = '-'; stockDisplay.className = 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm font-semibold text-gray-700 text-center'; }
                if (qtyInput) { qtyInput.removeAttribute('max'); qtyInput.setCustomValidity(''); }
            } else {
                if (inputDeskripsi) inputDeskripsi.value = '';
                if (selectBarang && selectBarang.value) selectBarang.dispatchEvent(new Event('change'));
            }
        });
    });
}

// Pesan validasi qty (max) dalam bahasa Indonesia
function updateQtyValidity(input) {
    if (!input) return;
    // Cek apakah ini input qty (bisa pakai class atau name attribute)
    if (!input.classList.contains('qty-input') && (!input.name || !input.name.includes('[qty_diminta]'))) return;
    const max = input.getAttribute('max');
    if (!max) {
        input.setCustomValidity('');
        return;
    }
    const val = parseFloat(input.value);
    const maxNum = parseFloat(max);
    if (isNaN(val) || val <= 0) {
        input.setCustomValidity('');
        return;
    }
    if (val > maxNum) {
        const maxFormatted = number_format(maxNum, 2, ',', '.');
        input.setCustomValidity('Nilai harus kurang dari atau sama dengan ' + maxFormatted + '.');
    } else {
        input.setCustomValidity('');
    }
}

// Fungsi untuk menambahkan item baru
function tambahItem() {
    const template = document.getElementById('itemTemplate');
    const container = document.getElementById('detailContainer');
    
    if (!template || !container) {
        console.error('Template atau container tidak ditemukan');
        return;
    }
    
    const newItem = template.content.cloneNode(true);
    
    // Replace INDEX dengan itemIndex
    const tempDiv = document.createElement('div');
    tempDiv.appendChild(newItem);
    let htmlContent = tempDiv.innerHTML;
    htmlContent = htmlContent.replace(/INDEX/g, itemIndex);
    tempDiv.innerHTML = htmlContent;
    
    const finalItem = tempDiv.firstElementChild;
    container.appendChild(finalItem);
    itemIndex++;
    
    setupTipeBarangToggle(finalItem);
    // Auto-set satuan dan tampilkan stock ketika data barang dipilih
    const selectBarang = finalItem.querySelector('.select-data-barang');
    const selectSatuan = finalItem.querySelector('.select-satuan');
    const qtyInput = finalItem.querySelector('input[name*="[qty_diminta]"]');
    const stockDisplay = finalItem.querySelector('.stock-display');
    
    if (selectBarang && selectSatuan) {
        selectBarang.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuanId = selectedOption.getAttribute('data-satuan');
            const barangId = this.value;
            
            if (satuanId) {
                selectSatuan.value = satuanId;
            }
            
            // Tampilkan stock tersedia
            const displayQty = getDisplayStock(barangId);
            if (barangId && displayQty !== null && stockDisplay) {
                const totalStock = displayQty;
                stockDisplay.textContent = totalStock > 0 ? number_format(totalStock, 2, ',', '.') : '0';
                stockDisplay.className = totalStock > 0 
                    ? 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-green-50 text-sm font-semibold text-green-700 text-center' 
                    : 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-red-50 text-sm font-semibold text-red-700 text-center';
                if (qtyInput) {
                    if (shouldEnforceMaxStock(barangId)) {
                        qtyInput.setAttribute('max', totalStock);
                        if (parseFloat(qtyInput.value) > totalStock) qtyInput.value = totalStock;
                        updateQtyValidity(qtyInput);
                    } else {
                        qtyInput.removeAttribute('max');
                        qtyInput.setCustomValidity('');
                    }
                }
            } else {
                if (stockDisplay) {
                    stockDisplay.textContent = '-';
                    stockDisplay.className = 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm font-semibold text-gray-700 text-center';
                }
                if (qtyInput) {
                    qtyInput.removeAttribute('max');
                    qtyInput.setCustomValidity('');
                }
            }
        });
    }
    
    // Hapus item
    const btnHapus = finalItem.querySelector('.btnHapusItem');
    if (btnHapus) {
        btnHapus.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    }
}

// Update sub jenis berdasarkan tipe permintaan yang dipilih
window.updateSubJenis = function() {
    const tipePermintaan = document.querySelector('input[name="tipe_permintaan"]:checked');
    const subJenisContainer = document.getElementById('subJenisContainer');
    
    if (!tipePermintaan || !subJenisContainer) return;
    
    subJenisContainer.classList.remove('hidden');
};

// Event listener untuk button tambah item
document.addEventListener('DOMContentLoaded', function() {
    const btnTambahItem = document.getElementById('btnTambahItem');
    if (btnTambahItem) {
        btnTambahItem.addEventListener('click', function(e) {
            e.preventDefault();
            tambahItem();
        });
    }
    
    // Hapus item untuk item yang sudah ada
    document.querySelectorAll('.btnHapusItem').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    });
    
    // Setup toggle master/lainnya dan auto-set satuan/stock untuk item yang sudah ada
    document.querySelectorAll('.item-row').forEach(row => setupTipeBarangToggle(row));
    document.querySelectorAll('.select-data-barang').forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuanId = selectedOption.getAttribute('data-satuan');
            const barangId = this.value;
            const row = this.closest('.item-row');
            const selectSatuan = row.querySelector('.select-satuan');
            const qtyInput = row.querySelector('input[name*="[qty_diminta]"]');
            const stockDisplay = row.querySelector('.stock-display');
            
            if (satuanId && selectSatuan) {
                selectSatuan.value = satuanId;
            }
            
            // Tampilkan stock tersedia
            const displayQty = getDisplayStock(barangId);
            if (barangId && displayQty !== null && stockDisplay) {
                const totalStock = displayQty;
                stockDisplay.textContent = totalStock > 0 ? number_format(totalStock, 2, ',', '.') : '0';
                stockDisplay.className = totalStock > 0 
                    ? 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-green-50 text-sm font-semibold text-green-700 text-center' 
                    : 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-red-50 text-sm font-semibold text-red-700 text-center';
                if (qtyInput) {
                    if (shouldEnforceMaxStock(barangId)) {
                        qtyInput.setAttribute('max', totalStock);
                        if (parseFloat(qtyInput.value) > totalStock) qtyInput.value = totalStock;
                        updateQtyValidity(qtyInput);
                    } else {
                        qtyInput.removeAttribute('max');
                        qtyInput.setCustomValidity('');
                    }
                }
            } else {
                if (stockDisplay) {
                    stockDisplay.textContent = '-';
                    stockDisplay.className = 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm font-semibold text-gray-700 text-center';
                }
                if (qtyInput) {
                    qtyInput.removeAttribute('max');
                    qtyInput.setCustomValidity('');
                }
            }
        });
        
        // Trigger change untuk item yang sudah terpilih (untuk menampilkan stock)
        if (select.value) {
            setTimeout(() => select.dispatchEvent(new Event('change')), 100);
        }
    });
    
    // Pesan validasi qty (max) dalam bahasa Indonesia saat user mengetik
    const formPermintaan = document.getElementById('formPermintaan');
    if (formPermintaan) {
        formPermintaan.addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('[qty_diminta]')) updateQtyValidity(e.target);
        });
        formPermintaan.addEventListener('change', function(e) {
            if (e.target.name && e.target.name.includes('[qty_diminta]')) updateQtyValidity(e.target);
        });
    }
    
    // Initialize sub jenis saat halaman dimuat
    const tipePermintaan = document.querySelector('input[name="tipe_permintaan"]:checked');
    if (tipePermintaan) {
        updateSubJenis();
    }
    
    // Update stock display saat sub jenis checkbox berubah
    document.querySelectorAll('input[name="jenis_permintaan[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Trigger change pada semua select data barang untuk update stock display
            document.querySelectorAll('.select-data-barang').forEach(select => {
                if (select.value) {
                    setTimeout(() => select.dispatchEvent(new Event('change')), 50);
                }
            });
        });
    });
});
</script>
@endpush
@endsection

