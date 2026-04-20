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
        <h2 class="text-xl font-semibold text-gray-900">Tambah Permintaan Barang</h2>
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
    
    <form action="{{ route('transaction.permintaan-barang.store') }}" method="POST" class="p-6" id="formPermintaan" onsubmit="return validateForm()">
        @csrf
        
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
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', optional(auth()->user()->pegawai)->id_unit_kerja ?? '') == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
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
                                <option value="{{ $pegawai->id }}" {{ old('id_pemohon', optional(auth()->user()->pegawai)->id ?? '') == $pegawai->id ? 'selected' : '' }}>
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
                            value="{{ old('tanggal_permintaan', date('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_permintaan') border-red-500 @enderror"
                        >
                        @error('tanggal_permintaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Permintaan <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Permintaan</label>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input 
                                            type="radio" 
                                            id="tipe_rutin" 
                                            name="tipe_permintaan" 
                                            value="RUTIN"
                                            {{ old('tipe_permintaan', 'RUTIN') == 'RUTIN' ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                            onchange="updateSubJenis()"
                                        >
                                        <label for="tipe_rutin" class="ml-2 block text-sm text-gray-700">
                                            Rutin
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input 
                                            type="radio" 
                                            id="tipe_cito" 
                                            name="tipe_permintaan" 
                                            value="CITO"
                                            {{ old('tipe_permintaan') == 'CITO' ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                            onchange="updateSubJenis()"
                                        >
                                        <label for="tipe_cito" class="ml-2 block text-sm text-gray-700">
                                            CITO (Penting)
                                        </label>
                                    </div>
                                </div>
                                @error('tipe_permintaan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div id="subJenisContainer" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sub Jenis Permintaan <span class="text-red-500">*</span>
                                </label>
                                <div id="subJenisOptions" class="space-y-2">
                                    @php
                                        $jenisPermintaanOld = array_intersect((array) old('jenis_permintaan', []), ['PERSEDIAAN', 'FARMASI']);
                                    @endphp
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            id="subjenis_persediaan" 
                                            name="jenis_permintaan[]" 
                                            value="PERSEDIAAN"
                                            {{ in_array('PERSEDIAAN', $jenisPermintaanOld) ? 'checked' : '' }}
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
                                            {{ in_array('FARMASI', $jenisPermintaanOld) ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        >
                                        <label for="subjenis_farmasi" class="ml-2 block text-sm text-gray-700">
                                            Farmasi
                                        </label>
                                    </div>
                                    <!-- <p class="text-xs text-gray-500 mt-1">Satu SPB bisa ke satu gudang atau ke semua gudang (Persediaan + Farmasi). Aset tidak masuk permintaan rutin/cito.</p> -->
                                </div>
                                @error('jenis_permintaan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('jenis_permintaan.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan permintaan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan') }}</textarea>
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
                    <!-- Item akan ditambahkan di sini via JavaScript -->
                    @if(old('detail'))
                        @foreach(old('detail') as $index => $detail)
                            <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 items-end">
                                    <div class="sm:col-span-4 flex flex-col">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Data Barang / Permintaan lainnya <span class="text-red-500">*</span>
                                        </label>
                                        @php
                                            $useLainnya = !empty(trim((string) old('detail.'.$index.'.deskripsi_barang')));
                                        @endphp
                                        <div class="flex gap-4 mb-2">
                                            <label class="inline-flex items-center text-sm">
                                                <input type="radio" name="detail[{{ $index }}][tipe_barang]" value="master" class="tipe-barang-radio mr-1" {{ !$useLainnya ? 'checked' : '' }}>
                                                Dari master
                                            </label>
                                            <label class="inline-flex items-center text-sm">
                                                <input type="radio" name="detail[{{ $index }}][tipe_barang]" value="lainnya" class="tipe-barang-radio mr-1" {{ $useLainnya ? 'checked' : '' }}>
                                                Permintaan lainnya (freetext)
                                            </label>
                                        </div>
                                        <div class="min-h-[38px] w-full min-w-0">
                                            <div class="wrap-master w-full min-w-0" style="{{ $useLainnya ? 'display:none' : '' }}">
                                                <select 
                                                    name="detail[{{ $index }}][id_data_barang]" 
                                                    class="select-data-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('detail.'.$index.'.id_data_barang') border-red-500 @enderror"
                                                >
                                                    <option value="">Pilih Data Barang</option>
                                                    @foreach($dataBarangs as $dataBarang)
                                                        <option value="{{ $dataBarang->id_data_barang }}" 
                                                            data-satuan="{{ $dataBarang->id_satuan }}"
                                                            {{ old('detail.'.$index.'.id_data_barang') == $dataBarang->id_data_barang ? 'selected' : '' }}>
                                                            {{ $dataBarang->kode_data_barang }} - {{ $dataBarang->nama_barang }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="wrap-lainnya w-full min-w-0" style="{{ $useLainnya ? '' : 'display:none' }}">
                                                <input type="text" 
                                                    name="detail[{{ $index }}][deskripsi_barang]" 
                                                    value="{{ old('detail.'.$index.'.deskripsi_barang') }}"
                                                    placeholder="Ketik deskripsi barang (tidak masuk master/stock)"
                                                    maxlength="500"
                                                    class="input-deskripsi-barang w-full min-w-0 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                >
                                            </div>
                                        </div>
                                        @error('detail.'.$index.'.id_data_barang')
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
                                            value="{{ old('detail.'.$index.'.qty_diminta') }}"
                                            placeholder="0"
                                            max=""
                                            class="qty-input block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('detail.'.$index.'.qty_diminta') border-red-500 @enderror"
                                        >
                                        @error('detail.'.$index.'.qty_diminta')
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
                                            class="select-satuan block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('detail.'.$index.'.id_satuan') border-red-500 @enderror"
                                        >
                                            <option value="">Pilih Satuan</option>
                                            @foreach($satuans as $satuan)
                                                <option value="{{ $satuan->id_satuan }}" {{ old('detail.'.$index.'.id_satuan') == $satuan->id_satuan ? 'selected' : '' }}>{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                        @error('detail.'.$index.'.id_satuan')
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
                                            value="{{ old('detail.'.$index.'.keterangan') }}"
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
                    @endif
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

<!-- Template untuk item detail (hidden) -->
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
let itemIndex = {{ old('detail') ? count(old('detail')) : 0 }};
const stockData = @json($stockData ?? []);
const stockPersediaanIds = @json(array_map('intval', $stockPersediaanIds ?? []));
const stockFarmasiIds = @json(array_map('intval', $stockFarmasiIds ?? []));

// Helper: lookup stock (kunci di JSON bisa string atau number)
function getStockForBarang(barangId) {
    if (!barangId) return null;
    const id = String(barangId);
    const num = parseInt(barangId, 10);
    return stockData[id] || stockData[num] || null;
}

// Pesan validasi qty (max) dalam bahasa Indonesia (menggantikan pesan bawaan browser)
function updateQtyValidity(input) {
    if (!input || !input.classList.contains('qty-input')) return;
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

// Filter dropdown barang berdasarkan sub jenis permintaan (hanya Persediaan & Farmasi)
function filterDataBarangByJenisPermintaan(targetSelect = null) {
    const checkedJenis = Array.from(document.querySelectorAll('input[name="jenis_permintaan[]"]:checked'))
        .map(cb => cb.value);
    const stockFarmasiIdsNum = (stockFarmasiIds || []).map(id => parseInt(id));
    const stockPersediaanIdsNum = (stockPersediaanIds || []).map(id => parseInt(id));
    
    // Jika targetSelect diberikan, hanya filter select tersebut
    // Jika tidak, filter semua select
    // Pastikan allSelects selalu berupa array yang bisa di-iterasi
    let allSelects;
    if (targetSelect) {
        allSelects = [targetSelect];
    } else {
        const nodeList = document.querySelectorAll('.select-data-barang');
        allSelects = Array.from(nodeList);
    }
    
    console.log('=== FILTER DATA BARANG ===');
    console.log('Checked jenis:', checkedJenis);
    console.log('Farmasi IDs count:', stockFarmasiIdsNum.length);
    console.log('Persediaan IDs count:', stockPersediaanIdsNum.length);
    console.log('Total selects to filter:', allSelects.length);
    console.log('Target select:', targetSelect ? 'specific' : 'all');

    // Simpan nilai yang sudah dipilih di semua select sebelum restore
    const selectedValues = new Map();
    if (allSelects && allSelects.length > 0) {
        allSelects.forEach(select => {
            if (select && select.value) {
                selectedValues.set(select, select.value);
            }
        });
    }
    
    if (allSelects && Array.isArray(allSelects) && allSelects.length > 0) {
        allSelects.forEach(function(select) {
            // Pastikan select masih valid
            if (!select || select.tagName !== 'SELECT') {
                console.warn('Invalid select element, skipping');
                return;
            }
            
            const currentValue = selectedValues.get(select) || select.value;
            
            // Simpan instance Choices.js jika ada untuk di-destroy sebelum restore
            const hadChoicesInstance = select.choicesInstance ? true : false;
            if (hadChoicesInstance) {
                try {
                    select.choicesInstance.destroy();
                    select.choicesInstance = null;
                } catch (e) {
                    console.warn('Error destroying Choices instance:', e);
                }
            }
            
            // Simpan semua opsi asli sebelum filter pertama kali (untuk restore nanti)
            if (!select._allOriginalOptions) {
                select._allOriginalOptions = Array.from(select.options).map(opt => {
                    const cloned = opt.cloneNode(true);
                    cloned.removeAttribute('data-hidden-by-filter');
                    cloned.style.display = '';
                    return cloned;
                });
                console.log('Saved original options:', select._allOriginalOptions.length);
            }
            
            // Restore semua opsi asli sebelum filter baru diterapkan
            // Hanya restore jika checkbox berubah atau jika ini adalah select baru yang belum punya opsi
            const needsRestore = !select._lastCheckedJenis || 
                                JSON.stringify(select._lastCheckedJenis.sort()) !== JSON.stringify(checkedJenis.sort()) ||
                                select.options.length <= 1; // Hanya placeholder
            
            if (needsRestore && select._allOriginalOptions && select._allOriginalOptions.length > 0) {
                // Simpan placeholder jika ada
                const existingPlaceholder = select.querySelector('option[value=""]');
                const placeholderText = existingPlaceholder ? existingPlaceholder.textContent : 'Pilih...';
                
                // Hapus semua opsi saat ini (termasuk placeholder, kita akan restore semuanya)
                while (select.firstChild) {
                    select.removeChild(select.firstChild);
                }
                
                // Restore semua opsi asli (termasuk placeholder)
                select._allOriginalOptions.forEach(originalOpt => {
                    const cloned = originalOpt.cloneNode(true);
                    cloned.removeAttribute('data-hidden-by-filter');
                    cloned.style.display = '';
                    // Pastikan placeholder di posisi pertama
                    if (cloned.value === '') {
                        select.insertBefore(cloned, select.firstChild);
                    } else {
                        select.appendChild(cloned);
                    }
                });
                
                // Pastikan placeholder ada di posisi pertama
                const placeholder = select.querySelector('option[value=""]');
                if (placeholder && select.firstChild !== placeholder) {
                    select.insertBefore(placeholder, select.firstChild);
                }
                
                console.log('Restored all original options:', select.options.length, 'checkedJenis:', checkedJenis, 'needsRestore:', needsRestore);
            }
            
            // Simpan checkedJenis terakhir untuk perbandingan di filter berikutnya
            select._lastCheckedJenis = [...checkedJenis];
            
            const options = Array.from(select.options);
            
            // Simpan opsi yang akan dihapus untuk bisa di-restore nanti jika perlu
            const optionsToRemove = [];
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = '';
                    option.removeAttribute('data-hidden-by-filter');
                    return;
                }
                const barangId = parseInt(option.value);
                let shouldShow = false;
                
                const isFarmasi = stockFarmasiIdsNum.includes(barangId);
                const isPersediaan = stockPersediaanIdsNum.includes(barangId);
                
                // Debug log untuk melihat klasifikasi barang
                if (option.textContent && (option.textContent.includes('Paracetamol') || option.textContent.includes('Farmasi'))) {
                    console.log('Filtering item:', {
                        text: option.textContent.substring(0, 30),
                        barangId: barangId,
                        isFarmasi: isFarmasi,
                        isPersediaan: isPersediaan,
                        checkedJenis: checkedJenis
                    });
                }
                
                if (checkedJenis.length === 0) {
                    // Tidak ada checkbox ter-check, sembunyikan semua
                    shouldShow = false;
                } else if (checkedJenis.includes('FARMASI') && checkedJenis.includes('PERSEDIAAN')) {
                    // Keduanya ter-check: tampilkan Farmasi ATAU Persediaan
                    shouldShow = isFarmasi || isPersediaan;
                } else if (checkedJenis.includes('FARMASI')) {
                    // Hanya Farmasi ter-check: tampilkan HANYA yang Farmasi
                    shouldShow = isFarmasi;
                } else if (checkedJenis.includes('PERSEDIAAN')) {
                    // Hanya Persediaan ter-check: tampilkan HANYA yang Persediaan
                    shouldShow = isPersediaan;
                }
                
                // Update log untuk item yang relevan
                if (option.textContent && (option.textContent.includes('Paracetamol') || option.textContent.includes('Farmasi'))) {
                    console.log('Item shouldShow:', shouldShow, 'for', option.textContent.substring(0, 30));
                }
                
                if (shouldShow) {
                    option.style.display = '';
                    option.removeAttribute('data-hidden-by-filter');
                } else {
                    option.style.display = 'none';
                    option.setAttribute('data-hidden-by-filter', 'true');
                    // Simpan opsi yang akan dihapus dari DOM sebelum Choices.js membaca
                    optionsToRemove.push(option);
                }
            });
            
            // Hapus opsi yang tidak terlihat dari DOM sebelum Choices.js membaca
            // Ini penting karena Choices.js membaca semua option dari DOM, tidak peduli CSS display
            // Opsi yang dihapus akan di-restore dari _allOriginalOptions saat filter berubah
            optionsToRemove.forEach(option => {
                if (option.parentNode === select) {
                    option.remove();
                }
            });
            
            const remainingOptions = Array.from(select.options).filter(opt => opt.value !== '');
            console.log('Filter applied:', {
                removedCount: optionsToRemove.length,
                remainingCount: remainingOptions.length,
                checkedJenis: checkedJenis,
                remainingOptions: remainingOptions.slice(0, 5).map(opt => opt.textContent).join(', ') + '...'
            });

            // Set kembali nilai yang sudah dipilih sebelum restore (jika masih ada di DOM setelah filter)
            if (currentValue) {
                const selectedOption = select.querySelector(`option[value="${currentValue}"]`);
                if (selectedOption) {
                    // Nilai yang dipilih masih ada di DOM setelah filter, set kembali
                    select.value = currentValue;
                    select.dispatchEvent(new Event('change'));
                } else {
                    // Nilai yang dipilih sudah dihapus oleh filter, reset
                    select.value = '';
                }
            }
            
            // Re-initialize Choices.js setelah filter jika sebelumnya sudah ada instance
            // Atau jika ini adalah inisialisasi pertama, pastikan filter diterapkan dulu
            if (typeof window.initChoicesForSelect === 'function') {
                // Simpan referensi select untuk digunakan di setTimeout
                const selectRef = select;
                const savedValueRef = currentValue;
                const checkedJenisRef = [...checkedJenis]; // Copy array untuk menghindari closure issues
                
                setTimeout(function() {
                    // Pastikan select masih valid
                    if (!selectRef || selectRef.tagName !== 'SELECT') {
                        console.warn('Select element invalid in timeout, skipping');
                        return;
                    }
                    
                    // Simpan nilai yang dipilih sebelum re-initialize Choices.js
                    const savedValue = savedValueRef;
                    
                    // Pastikan tidak ada instance Choices.js yang tersisa
                    if (selectRef.choicesInstance) {
                        try {
                            selectRef.choicesInstance.destroy();
                        } catch (e) {
                            console.warn('Error destroying Choices instance in timeout:', e);
                        }
                        selectRef.choicesInstance = null;
                    }
                    
                    // Hitung opsi yang masih ada di DOM (setelah filter)
                    const remainingOptions = selectRef.options ? Array.from(selectRef.options).filter(opt => opt && opt.value !== '') : [];
                    const visibleCount = remainingOptions.length;
                    
                    console.log('Re-initializing Choices.js after filter:', {
                        selectId: selectRef.id || 'unnamed',
                        visibleCount: visibleCount,
                        checkedJenis: checkedJenisRef,
                        savedValue: savedValue,
                        totalOptionsInDOM: selectRef.options ? selectRef.options.length : 0,
                        firstFewOptions: remainingOptions.slice(0, 5).map(opt => opt ? opt.textContent : '').join(', ')
                    });
                    
                    if (visibleCount > 0) {
                        // Pastikan select element masih valid sebelum re-initialize
                        if (selectRef && selectRef.tagName === 'SELECT' && selectRef.options && selectRef.options.length > 0) {
                            // Re-initialize dengan opsi yang sudah di-filter (hanya yang masih ada di DOM)
                            console.log('Calling initChoicesForSelect for:', selectRef.id || selectRef.className || 'unnamed');
                            window.initChoicesForSelect(selectRef, 0); // Threshold 0 untuk selalu initialize
                            
                            // Set kembali nilai yang dipilih setelah Choices.js di-initialize
                            if (savedValue) {
                                setTimeout(function() {
                                    if (selectRef && selectRef.querySelector) {
                                        const selectedOption = selectRef.querySelector(`option[value="${savedValue}"]`);
                                        if (selectedOption) {
                                            selectRef.value = savedValue;
                                            // Trigger change event untuk update satuan dan stock
                                            selectRef.dispatchEvent(new Event('change', { bubbles: true }));
                                            console.log('Restored selected value after Choices.js init:', savedValue);
                                        }
                                    }
                                }, 150);
                            }
                        } else {
                            console.warn('Select element invalid or has no options, skipping Choices.js initialization');
                        }
                    } else {
                        console.warn('No visible options after filter, skipping Choices.js initialization');
                    }
                }, 250);
            }
        }); // End forEach // End forEach
    } else {
        console.warn('No selects found to filter');
    }
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
    
    // Filter hanya untuk select baru yang ditambahkan, bukan semua select
    // Ini mencegah nilai yang sudah dipilih di item lain menjadi hilang
    const newSelectBarang = finalItem.querySelector('.select-data-barang');
    if (newSelectBarang) {
        // Filter hanya untuk select baru ini
        filterDataBarangByJenisPermintaan(newSelectBarang);
    }
    
    // Initialize Choices.js untuk select yang baru ditambahkan
    const selectBarang = finalItem.querySelector('.select-data-barang');
    const selectSatuan = finalItem.querySelector('.select-satuan');
    
    // Auto-set satuan dan tampilkan stock ketika data barang dipilih
    const qtyInput = finalItem.querySelector('.qty-input');
    const stockDisplay = finalItem.querySelector('.stock-display');
    
    // Function untuk handle perubahan data barang
    const handleDataBarangChange = function(barangId) {
        // Cari option yang dipilih untuk mendapatkan data-satuan
        const selectElement = selectBarang.tagName === 'SELECT' ? selectBarang : (selectBarang.closest('.choices') ? selectBarang.closest('.choices').querySelector('select') : selectBarang);
        if (selectElement && selectElement.tagName === 'SELECT') {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const satuanId = selectedOption ? selectedOption.getAttribute('data-satuan') : null;
            
            if (satuanId && selectSatuan) {
                // Set satuan menggunakan Choices.js jika sudah diinisialisasi
                if (selectSatuan.choicesInstance) {
                    selectSatuan.choicesInstance.setChoiceByValue(satuanId);
                } else {
                    selectSatuan.value = satuanId;
                }
            }
        }
        
        // Tampilkan stock tersedia (stock gudang pusat Persediaan/Farmasi)
        const displayQty = getDisplayStock(barangId);
        if (barangId && displayQty !== null) {
            const totalStock = displayQty;
            if (stockDisplay) {
                stockDisplay.textContent = totalStock > 0 ? number_format(totalStock, 2, ',', '.') : '0';
                stockDisplay.className = totalStock > 0 
                    ? 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-green-50 text-sm font-semibold text-green-700 text-center' 
                    : 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-red-50 text-sm font-semibold text-red-700 text-center';
            }
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
    };
    
    if (selectBarang && selectSatuan) {
        // Event listener untuk select standar
        selectBarang.addEventListener('change', function() {
            handleDataBarangChange(this.value);
        });
        
        // Inisialisasi Choices.js untuk select-data-barang dan select-satuan
        if (typeof window.initChoicesForSelect === 'function') {
            setTimeout(function() {
                // Inisialisasi untuk Data Barang
                if (selectBarang && selectBarang.tagName === 'SELECT') {
                    const optionCount = Array.from(selectBarang.options).filter(opt => opt.value !== '').length;
                    console.log('Initializing Choices.js for select-data-barang, options:', optionCount);
                    
                    // Untuk select-data-barang, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0) {
                        window.initChoicesForSelect(selectBarang, 0); // Threshold 0 untuk selalu initialize
                        
                        // Event listener untuk Choices.js setelah diinisialisasi
                        setTimeout(function() {
                            if (selectBarang.choicesInstance) {
                                selectBarang.containerOuter.element.addEventListener('choice', function(event) {
                                    const choice = event.detail.choice;
                                    if (choice && choice.value) {
                                        handleDataBarangChange(choice.value);
                                    }
                                });
                            }
                        }, 150);
                    } else {
                        console.warn('select-data-barang has insufficient options:', optionCount);
                    }
                }
                
                // Inisialisasi untuk Satuan dengan styling khusus untuk dropdown kecil
                if (selectSatuan && selectSatuan.tagName === 'SELECT') {
                    const optionCount = Array.from(selectSatuan.options).filter(opt => opt.value !== '').length;
                    console.log('Initializing Choices.js for select-satuan, options:', optionCount);
                    
                    // Untuk select-satuan, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0) {
                        // Inisialisasi dengan konfigurasi khusus untuk dropdown kecil
                        if (typeof Choices !== 'undefined') {
                            try {
                                // Destroy jika sudah ada instance
                                if (selectSatuan.choicesInstance) {
                                    try {
                                        selectSatuan.choicesInstance.destroy();
                                    } catch (e) {}
                                    selectSatuan.choicesInstance = null;
                                }
                                
                                // Pastikan semua option memiliki textContent yang benar sebelum inisialisasi
                                Array.from(selectSatuan.options).forEach(function(option) {
                                    // Jika textContent kosong atau tidak valid, gunakan innerText atau value
                                    if (!option.textContent || option.textContent.trim() === '') {
                                        option.textContent = option.innerText || option.getAttribute('label') || option.value;
                                    }
                                    // Pastikan textContent tidak mengandung karakter yang menyebabkan masalah
                                    const text = option.textContent.trim();
                                    if (text && text.length > 0) {
                                        option.textContent = text;
                                    }
                                });
                                
                                const choicesInstance = new Choices(selectSatuan, {
                                    searchEnabled: true,
                                    searchChoices: true,
                                    itemSelectText: '',
                                    placeholder: true,
                                    placeholderValue: 'Pilih Satuan',
                                    searchPlaceholderValue: 'Ketik minimal 2 karakter...',
                                    shouldSort: true,
                                    fuseOptions: {
                                        threshold: 0.3,
                                        distance: 100
                                    },
                                    shouldSortItems: true,
                                    removeItemButton: false
                                });
                                
                                console.log('Choices.js initialized for select-satuan in tambahItem');
                                
                                selectSatuan.choicesInstance = choicesInstance;
                                
                                // Custom search filter untuk satuan juga
                                setTimeout(function() {
                                    const searchInput = choicesInstance.input.element;
                                    const containerOuter = choicesInstance.containerOuter.element;
                                    
                                    if (searchInput && containerOuter) {
                                        searchInput.addEventListener('input', function(e) {
                                            const searchValue = e.target.value.trim();
                                            
                                            setTimeout(function() {
                                                const dropdown = containerOuter.querySelector('.choices__list--dropdown');
                                                if (!dropdown) return;
                                                
                                                if (searchValue.length < 2) {
                                                    const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                                    items.forEach(item => {
                                                        item.style.display = 'none';
                                                    });
                                                    
                                                    let noResults = dropdown.querySelector('.choices__item--no-results');
                                                    if (!noResults || !noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                                        if (noResults) noResults.remove();
                                                        noResults = document.createElement('div');
                                                        noResults.className = 'choices__item choices__item--no-results';
                                                        noResults.setAttribute('data-select-text', 'Tekan untuk memilih');
                                                        noResults.setAttribute('data-choice', '');
                                                        noResults.setAttribute('data-choice-selectable', '');
                                                        noResults.textContent = 'Ketik minimal 2 karakter...';
                                                        dropdown.appendChild(noResults);
                                                    }
                                                    dropdown.classList.remove('is-hidden');
                                                } else {
                                                    const noResults = dropdown.querySelector('.choices__item--no-results');
                                                    if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                                        noResults.remove();
                                                    }
                                                    const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                                    items.forEach(item => {
                                                        item.style.display = '';
                                                    });
                                                }
                                            }, 10);
                                        });
                                    }
                                }, 100);
                                
                                console.log('Choices.js initialized for select-satuan');
                            } catch (error) {
                                console.error('Error initializing Choices.js for select-satuan:', error);
                            }
                        }
                    }
                }
            }, 150);
        }
    }
    
    // Hapus item
    const btnHapus = finalItem.querySelector('.btnHapusItem');
    if (btnHapus) {
        btnHapus.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    }
}

// Event listener untuk button tambah item
document.addEventListener('DOMContentLoaded', function() {
    const btnTambahItem = document.getElementById('btnTambahItem');
    if (btnTambahItem) {
        btnTambahItem.addEventListener('click', function(e) {
            e.preventDefault();
            tambahItem();
        });
    }
    
    // Inisialisasi Choices.js untuk field yang sudah ada di halaman
    function initializeExistingFields() {
        console.log('Checking for Choices.js initialization...');
        console.log('Choices available:', typeof Choices !== 'undefined');
        console.log('initChoicesForSelect available:', typeof window.initChoicesForSelect === 'function');
        
        if (typeof Choices === 'undefined') {
            console.log('Choices.js not loaded yet, retrying...');
            setTimeout(initializeExistingFields, 100);
            return;
        }
        
        // Pastikan filter sudah berjalan sebelum inisialisasi
        if (typeof filterDataBarangByJenisPermintaan === 'function') {
            filterDataBarangByJenisPermintaan();
        }
        
        if (typeof Choices !== 'undefined') {
            // Inisialisasi untuk semua select-data-barang yang sudah ada
            document.querySelectorAll('.select-data-barang').forEach(function(selectBarang) {
                if (selectBarang.tagName === 'SELECT' && !selectBarang.choicesInstance) {
                    const optionCount = Array.from(selectBarang.options).filter(opt => opt.value !== '').length;
                    console.log('Found select-data-barang, options:', optionCount, 'element:', selectBarang);
                    
                    // Untuk select-data-barang, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0) {
                        if (typeof window.initChoicesForSelect === 'function') {
                            console.log('Initializing via initChoicesForSelect...');
                            window.initChoicesForSelect(selectBarang, 0); // Threshold 0 untuk selalu initialize
                        } else {
                            console.log('initChoicesForSelect not available, initializing directly...');
                            // Inisialisasi langsung jika fungsi helper tidak tersedia
                            try {
                                const choicesInstance = new Choices(selectBarang, {
                                    searchEnabled: true,
                                    searchChoices: true,
                                    itemSelectText: '',
                                    placeholder: true,
                                    placeholderValue: 'Pilih Data Barang',
                                    searchPlaceholderValue: 'Ketik minimal 2 karakter untuk mencari...',
                                    shouldSort: true,
                                    fuseOptions: {
                                        threshold: 0.3,
                                        distance: 100
                                    },
                                    shouldSortItems: true,
                                    removeItemButton: false
                                });
                                selectBarang.choicesInstance = choicesInstance;
                                console.log('Choices.js initialized directly for select-data-barang');
                            } catch (error) {
                                console.error('Error initializing Choices.js directly:', error);
                            }
                        }
                    }
                }
            });
            
            // Inisialisasi untuk semua select-satuan yang sudah ada dengan styling khusus
            document.querySelectorAll('.select-satuan').forEach(function(selectSatuan) {
                if (selectSatuan.tagName === 'SELECT' && !selectSatuan.choicesInstance) {
                    const optionCount = Array.from(selectSatuan.options).filter(opt => opt.value !== '').length;
                    console.log('Initializing Choices.js for existing select-satuan, options:', optionCount);
                    
                    // Untuk select-satuan, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0 && typeof Choices !== 'undefined') {
                        try {
                            // Pastikan semua option memiliki textContent yang benar sebelum inisialisasi
                            Array.from(selectSatuan.options).forEach(function(option) {
                                // Jika textContent kosong atau tidak valid, gunakan innerText atau value
                                if (!option.textContent || option.textContent.trim() === '') {
                                    option.textContent = option.innerText || option.getAttribute('label') || option.value;
                                }
                                // Pastikan textContent tidak mengandung karakter yang menyebabkan masalah
                                const text = option.textContent.trim();
                                if (text && text.length > 0) {
                                    option.textContent = text;
                                }
                            });
                            
                            const choicesInstance = new Choices(selectSatuan, {
                                searchEnabled: true,
                                searchChoices: true,
                                itemSelectText: '',
                                placeholder: true,
                                placeholderValue: 'Pilih Satuan',
                                searchPlaceholderValue: 'Ketik minimal 2 karakter...',
                                shouldSort: true,
                                fuseOptions: {
                                    threshold: 0.3,
                                    distance: 100
                                },
                                shouldSortItems: true,
                                removeItemButton: false
                            });
                            
                            selectSatuan.choicesInstance = choicesInstance;
                            console.log('Choices.js initialized successfully for select-satuan');
                            
                            // Custom search filter untuk satuan
                            setTimeout(function() {
                                const searchInput = choicesInstance.input.element;
                                const containerOuter = choicesInstance.containerOuter.element;
                                
                                if (searchInput && containerOuter) {
                                    searchInput.addEventListener('input', function(e) {
                                        const searchValue = e.target.value.trim();
                                        
                                        setTimeout(function() {
                                            const dropdown = containerOuter.querySelector('.choices__list--dropdown');
                                            if (!dropdown) return;
                                            
                                            if (searchValue.length < 2) {
                                                const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                                items.forEach(item => {
                                                    item.style.display = 'none';
                                                });
                                                
                                                let noResults = dropdown.querySelector('.choices__item--no-results');
                                                if (!noResults || !noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                                    if (noResults) noResults.remove();
                                                    noResults = document.createElement('div');
                                                    noResults.className = 'choices__item choices__item--no-results';
                                                    noResults.setAttribute('data-select-text', 'Tekan untuk memilih');
                                                    noResults.setAttribute('data-choice', '');
                                                    noResults.setAttribute('data-choice-selectable', '');
                                                    noResults.textContent = 'Ketik minimal 2 karakter...';
                                                    dropdown.appendChild(noResults);
                                                }
                                                dropdown.classList.remove('is-hidden');
                                            } else {
                                                const noResults = dropdown.querySelector('.choices__item--no-results');
                                                if (noResults && noResults.textContent.includes('Ketik minimal 2 karakter')) {
                                                    noResults.remove();
                                                }
                                                const items = dropdown.querySelectorAll('.choices__item:not(.choices__item--no-results)');
                                                items.forEach(item => {
                                                    item.style.display = '';
                                                });
                                            }
                                        }, 10);
                                    });
                                }
                            }, 100);
                        } catch (error) {
                            console.error('Error initializing Choices.js for select-satuan:', error);
                        }
                    }
                }
            });
        }
    }
    
    // Coba initialize dengan delay yang lebih lama untuk memastikan Choices.js ter-load
    setTimeout(initializeExistingFields, 500);
    
    // Juga coba lagi setelah window load
    window.addEventListener('load', function() {
        setTimeout(initializeExistingFields, 300);
    });
    
    // Force initialize setelah semua script selesai
    setTimeout(function() {
        console.log('Force initialization check...');
        if (typeof Choices !== 'undefined') {
            // Force initialize untuk select-data-barang
            document.querySelectorAll('.select-data-barang').forEach(function(select) {
                if (select.tagName === 'SELECT' && !select.choicesInstance) {
                    const optionCount = Array.from(select.options).filter(opt => opt.value !== '').length;
                    console.log('Force init select-data-barang, options:', optionCount);
                    // Untuk select-data-barang, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0) {
                        try {
                            const instance = new Choices(select, {
                                searchEnabled: true,
                                searchChoices: true,
                                itemSelectText: '',
                                placeholder: true,
                                placeholderValue: 'Pilih Data Barang',
                                searchPlaceholderValue: 'Ketik minimal 2 karakter...',
                                shouldSort: true,
                                fuseOptions: { threshold: 0.3, distance: 100 }
                            });
                            select.choicesInstance = instance;
                            console.log('Force initialized select-data-barang');
                        } catch (e) {
                            console.error('Force init error for select-data-barang:', e);
                        }
                    }
                }
            });
            
            // Force initialize untuk select-satuan
            document.querySelectorAll('.select-satuan').forEach(function(select) {
                if (select.tagName === 'SELECT' && !select.choicesInstance) {
                    const optionCount = Array.from(select.options).filter(opt => opt.value !== '').length;
                    console.log('Force init select-satuan, options:', optionCount);
                    
                    // Pastikan semua option memiliki textContent yang benar
                    Array.from(select.options).forEach(function(option) {
                        if (!option.textContent || option.textContent.trim() === '') {
                            option.textContent = option.innerText || option.value;
                        }
                    });
                    
                    // Untuk select-satuan, selalu inisialisasi jika ada minimal 1 opsi
                    if (optionCount > 0) {
                        try {
                            const instance = new Choices(select, {
                                searchEnabled: true,
                                searchChoices: true,
                                itemSelectText: '',
                                placeholder: true,
                                placeholderValue: 'Pilih Satuan',
                                searchPlaceholderValue: 'Ketik minimal 2 karakter...',
                                shouldSort: true,
                                fuseOptions: { threshold: 0.3, distance: 100 }
                            });
                            select.choicesInstance = instance;
                            console.log('Force initialized select-satuan');
                        } catch (e) {
                            console.error('Force init error for select-satuan:', e);
                        }
                    }
                }
            });
        }
    }, 1000);

    // Pesan validasi qty (max) dalam bahasa Indonesia saat user mengetik
    const formPermintaan = document.getElementById('formPermintaan');
    if (formPermintaan) {
        formPermintaan.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input')) updateQtyValidity(e.target);
        });
        formPermintaan.addEventListener('change', function(e) {
            if (e.target.classList.contains('qty-input')) updateQtyValidity(e.target);
        });
    }
    
    // Hapus item untuk item yang sudah ada
    document.querySelectorAll('.btnHapusItem').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.item-row').remove();
        });
    });
    
    // Auto-set satuan dan tampilkan stock untuk item yang sudah ada
    document.querySelectorAll('.select-data-barang').forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuanId = selectedOption.getAttribute('data-satuan');
            const barangId = this.value;
            const row = this.closest('.item-row');
            const selectSatuan = row.querySelector('.select-satuan');
            const qtyInput = row.querySelector('.qty-input');
            const stockDisplay = row.querySelector('.stock-display');
            
            if (satuanId && selectSatuan) {
                selectSatuan.value = satuanId;
            }
            
            // Tampilkan stock tersedia (stock gudang pusat Persediaan/Farmasi)
            const displayQty = getDisplayStock(barangId);
            if (barangId && displayQty !== null) {
                const totalStock = displayQty;
                if (stockDisplay) {
                    stockDisplay.textContent = totalStock > 0 ? number_format(totalStock, 2, ',', '.') : '0';
                    stockDisplay.className = totalStock > 0 
                        ? 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-green-50 text-sm font-semibold text-green-700 text-center' 
                        : 'stock-display block w-full px-2 py-2 border border-gray-200 rounded-md bg-red-50 text-sm font-semibold text-red-700 text-center';
                }
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
        
        // Trigger change untuk item yang sudah terpilih (untuk menampilkan stock + satuan)
        if (select.value) {
            // Delay sedikit untuk memastikan DOM sudah ready
            setTimeout(() => {
                select.dispatchEvent(new Event('change'));
            }, 100);
        }
    });
    
    // Tambah item pertama jika belum ada (hanya jika tidak ada old input)
    const container = document.getElementById('detailContainer');
    if (container && container.children.length === 0) {
        tambahItem();
    }
    
    // Setup event listeners untuk detail items yang sudah ada (dari old input atau baris pertama)
    if (container) {
        container.querySelectorAll('.item-row').forEach(row => {
            setupTipeBarangToggle(row);
            const selectBarang = row.querySelector('.select-data-barang');
            const selectSatuan = row.querySelector('.select-satuan');
            const stockDisplay = row.querySelector('.stock-display');
            const qtyInput = row.querySelector('.qty-input');
            const btnHapus = row.querySelector('.btnHapusItem');
            
            // Auto-set satuan dan tampilkan stock ketika data barang dipilih
            if (selectBarang) {
                selectBarang.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const satuanId = selectedOption ? selectedOption.getAttribute('data-satuan') : null;
                    const barangId = this.value;
                    if (satuanId && selectSatuan) {
                        selectSatuan.value = satuanId;
                    }
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
                if (selectBarang.value) {
                    setTimeout(() => selectBarang.dispatchEvent(new Event('change')), 50);
                }
            }
            
            // Hapus item
            if (btnHapus) {
                btnHapus.addEventListener('click', function() {
                    this.closest('.item-row').remove();
                });
            }
        });
    }
    
    // Auto-select unit kerja dan pemohon berdasarkan user yang login
    @php
        $userPegawai = auth()->user()->pegawai;
    @endphp
    @if($userPegawai)
        const unitKerjaSelect = document.getElementById('id_unit_kerja');
        const pemohonSelect = document.getElementById('id_pemohon');
        
        if (unitKerjaSelect && !unitKerjaSelect.value) {
            const userUnitKerja = {{ $userPegawai->id_unit_kerja ?? 'null' }};
            if (userUnitKerja) {
                unitKerjaSelect.value = userUnitKerja;
            }
        }
        
        if (pemohonSelect && !pemohonSelect.value) {
            const userPegawaiId = {{ $userPegawai->id ?? 'null' }};
            if (userPegawaiId) {
                pemohonSelect.value = userPegawaiId;
            }
        }
    @endif
    
    // Update sub jenis berdasarkan tipe permintaan yang dipilih
    window.updateSubJenis = function() {
        const tipePermintaan = document.querySelector('input[name="tipe_permintaan"]:checked');
        const subJenisContainer = document.getElementById('subJenisContainer');
        const subJenisOptions = document.getElementById('subJenisOptions');
        
        if (!subJenisContainer || !subJenisOptions) {
            console.error('subJenisContainer atau subJenisOptions tidak ditemukan');
            return;
        }
        
        if (!tipePermintaan) {
            subJenisContainer.classList.add('hidden');
            subJenisOptions.innerHTML = '';
            return;
        }
        
        // Tampilkan container
        subJenisContainer.classList.remove('hidden');
        
        // Cek apakah sudah ada checkbox (dari old input atau render sebelumnya)
        const existingCheckboxes = subJenisOptions.querySelectorAll('input[type="checkbox"][name="jenis_permintaan[]"]');
        if (existingCheckboxes.length > 0) {
            // Jika sudah ada, hanya setup event listener
            setTimeout(() => {
                existingCheckboxes.forEach(cb => {
                    // Hapus listener lama jika ada
                    const newCb = cb.cloneNode(true);
                    cb.parentNode.replaceChild(newCb, cb);
                    newCb.addEventListener('change', filterDataBarangByJenisPermintaan);
                });
                filterDataBarangByJenisPermintaan();
            }, 50);
            return;
        }
        
        // Sub jenis permintaan rutin/cito: hanya Persediaan & Farmasi (Aset tidak masuk)
        const subJenisList = [
            { value: 'PERSEDIAAN', label: 'Persediaan' },
            { value: 'FARMASI', label: 'Farmasi' }
        ];
        
        // Get old values untuk pre-select
        const oldJenisPermintaan = @json(old('jenis_permintaan', []));
        
        let html = '';
        subJenisList.forEach(subJenis => {
            const isChecked = oldJenisPermintaan.includes(subJenis.value);
            html += `
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="subjenis_${subJenis.value.toLowerCase()}" 
                        name="jenis_permintaan[]" 
                        value="${subJenis.value}"
                        ${isChecked ? 'checked' : ''}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="subjenis_${subJenis.value.toLowerCase()}" class="ml-2 block text-sm text-gray-700">
                        ${subJenis.label}
                    </label>
                </div>
            `;
        });
        
        subJenisOptions.innerHTML = html;
        
        // Setup event listener untuk checkbox sub jenis
        setTimeout(() => {
            document.querySelectorAll('input[name="jenis_permintaan[]"]').forEach(cb => {
                cb.addEventListener('change', filterDataBarangByJenisPermintaan);
            });
            // Filter awal setelah sub jenis di-render
            filterDataBarangByJenisPermintaan();
            
            // Re-initialize Choices.js setelah filter selesai
            // Pastikan filter sudah diterapkan sebelum Choices.js membaca opsi
            setTimeout(function() {
                if (typeof window.initChoicesForSelect === 'function') {
                    document.querySelectorAll('.select-data-barang').forEach(function(select) {
                        if (select.tagName === 'SELECT') {
                            // Pastikan filter sudah diterapkan
                            const checkedJenis = Array.from(document.querySelectorAll('input[name="jenis_permintaan[]"]:checked'))
                                .map(cb => cb.value);
                            const stockFarmasiIdsNum = (stockFarmasiIds || []).map(id => parseInt(id));
                            const stockPersediaanIdsNum = (stockPersediaanIds || []).map(id => parseInt(id));
                            
                            // Terapkan filter lagi untuk memastikan
                            Array.from(select.options).forEach(function(option) {
                                if (option.value === '') {
                                    option.style.display = '';
                                    return;
                                }
                                const barangId = parseInt(option.value);
                                const isFarmasi = stockFarmasiIdsNum.includes(barangId);
                                const isPersediaan = stockPersediaanIdsNum.includes(barangId);
                                
                                let shouldShow = false;
                                if (checkedJenis.length === 0) {
                                    shouldShow = false;
                                } else if (checkedJenis.includes('FARMASI') && checkedJenis.includes('PERSEDIAAN')) {
                                    shouldShow = isFarmasi || isPersediaan;
                                } else if (checkedJenis.includes('FARMASI')) {
                                    shouldShow = isFarmasi;
                                } else if (checkedJenis.includes('PERSEDIAAN')) {
                                    shouldShow = isPersediaan;
                                }
                                
                                option.style.display = shouldShow ? '' : 'none';
                                if (!shouldShow) {
                                    option.setAttribute('data-hidden-by-filter', 'true');
                                } else {
                                    option.removeAttribute('data-hidden-by-filter');
                                }
                            });
                            
                            const visibleCount = Array.from(select.options).filter(opt => {
                                if (opt.value === '') return false;
                                const style = window.getComputedStyle(opt);
                                return style.display !== 'none' && style.visibility !== 'hidden';
                            }).length;
                            
                            if (visibleCount > 0) {
                                // Destroy instance lama jika ada
                                if (select.choicesInstance) {
                                    try {
                                        select.choicesInstance.destroy();
                                    } catch (e) {}
                                    select.choicesInstance = null;
                                }
                                
                                console.log('Re-initializing select-data-barang after filter, visible options:', visibleCount, 'checkedJenis:', checkedJenis);
                                window.initChoicesForSelect(select, 0);
                            }
                        }
                    });
                }
            }, 300);
        }, 100);
    };
    
    // Setup event listener untuk checkbox sub jenis yang sudah ada (dari old input)
    // Panggil updateSubJenis segera saat script dimuat (tidak perlu menunggu DOMContentLoaded)
    // Karena "Rutin" sudah checked secara default, sub jenis akan langsung muncul
    setTimeout(function() {
        if (typeof window.updateSubJenis === 'function') {
            window.updateSubJenis();
        }
    }, 50);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Pastikan updateSubJenis dipanggil saat DOM ready
        setTimeout(function() {
            if (typeof window.updateSubJenis === 'function') {
                window.updateSubJenis();
                
                // Setup event listener untuk checkbox sub jenis setelah di-render
                setTimeout(function() {
                    document.querySelectorAll('input[name="jenis_permintaan[]"]').forEach(cb => {
                        // Hapus listener lama jika ada untuk menghindari duplikasi
                        const newCb = cb.cloneNode(true);
                        cb.parentNode.replaceChild(newCb, cb);
                        newCb.addEventListener('change', filterDataBarangByJenisPermintaan);
                    });
                    // Filter dropdown berdasarkan old input jika ada
                    const oldJenis = @json(old('jenis_permintaan', []));
                    if (oldJenis.length > 0) {
                        filterDataBarangByJenisPermintaan();
                    }
                }, 150);
            }
        }, 100);
    });
    
    // Form validation sebelum submit
    window.validateForm = function() {
        const form = document.getElementById('formPermintaan');
        const tipePermintaan = form.querySelector('input[name="tipe_permintaan"]:checked');
        const jenisPermintaan = form.querySelectorAll('input[name="jenis_permintaan[]"]:checked');
        const detailItems = form.querySelectorAll('.item-row');
        
        // Validasi tipe permintaan
        if (!tipePermintaan) {
            alert('Tipe permintaan harus dipilih (Rutin atau CITO (Penting)).');
            return false;
        }
        
        // Validasi jenis permintaan (sub jenis)
        if (jenisPermintaan.length === 0) {
            alert('Sub jenis permintaan harus dipilih minimal satu (Persediaan atau Farmasi).');
            return false;
        }
        
        // Validasi detail items
        if (detailItems.length === 0) {
            alert('Detail permintaan harus diisi minimal satu item.');
            return false;
        }
        
        // Validasi setiap detail item: wajib salah satu — dari master (id_data_barang) atau permintaan lainnya (deskripsi_barang)
        let isValid = true;
        detailItems.forEach((item, index) => {
            const idDataBarang = item.querySelector('select[name*="[id_data_barang]"]');
            const deskripsiBarang = item.querySelector('input[name*="[deskripsi_barang]"]');
            const qtyDiminta = item.querySelector('input[name*="[qty_diminta]"]');
            const idSatuan = item.querySelector('select[name*="[id_satuan]"]');
            const hasMaster = idDataBarang && idDataBarang.value && idDataBarang.value.trim() !== '';
            const hasLainnya = deskripsiBarang && deskripsiBarang.value && deskripsiBarang.value.trim() !== '';
            if (!hasMaster && !hasLainnya) {
                alert(`Item ${index + 1}: pilih data barang dari master atau isi deskripsi permintaan lainnya.`);
                isValid = false;
                return false;
            }
            if (hasMaster && hasLainnya) {
                alert(`Item ${index + 1}: pilih salah satu — dari master ATAU permintaan lainnya, jangan keduanya.`);
                isValid = false;
                return false;
            }
            
            if (!qtyDiminta || !qtyDiminta.value || parseFloat(qtyDiminta.value) <= 0) {
                alert(`Jumlah yang diminta pada item ${index + 1} harus diisi dan lebih dari 0.`);
                isValid = false;
                return false;
            }
            
            if (!idSatuan || !idSatuan.value) {
                alert(`Satuan pada item ${index + 1} harus dipilih.`);
                isValid = false;
                return false;
            }
        });
        
        return isValid;
    };
});
</script>
@endpush
@push('styles')
<style>
    /* Styling khusus untuk Choices.js pada field satuan yang lebih kecil */
    .select-satuan + .choices,
    .select-satuan ~ .choices {
        width: 100% !important;
    }
    
    /* Perbaiki tampilan dropdown Choices.js secara umum */
    .choices__list--dropdown {
        z-index: 9999 !important;
        max-height: 200px !important;
        overflow-y: auto !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        background: white !important;
    }
    
    .choices__list--dropdown .choices__item {
        padding: 8px 12px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    
    .choices__list--dropdown .choices__item:last-child {
        border-bottom: none !important;
    }
    
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }
    
    .choices__list--dropdown .choices__item--no-results {
        white-space: normal !important;
        word-wrap: break-word !important;
        padding: 12px !important;
        text-align: center !important;
        line-height: 1.5 !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        border-bottom: none !important;
    }
    
    /* Perbaiki untuk field satuan yang lebih kecil */
    .select-satuan + .choices .choices__list--dropdown,
    .select-satuan ~ .choices .choices__list--dropdown {
        max-height: 180px !important;
        font-size: 13px !important;
    }
    
    .select-satuan + .choices .choices__item,
    .select-satuan ~ .choices .choices__item {
        padding: 6px 10px !important;
        font-size: 13px !important;
    }
    
    .select-satuan + .choices .choices__item--no-results,
    .select-satuan ~ .choices .choices__item--no-results {
        padding: 10px !important;
        font-size: 12px !important;
    }
    
    /* Pastikan dropdown tidak terpotong */
    .choices.is-open .choices__list--dropdown {
        position: absolute !important;
        width: 100% !important;
        box-sizing: border-box !important;
        margin-top: 4px !important;
    }
    
    /* Perbaiki input search */
    .choices__input {
        font-size: 14px !important;
        padding: 6px 8px !important;
    }
    
    .select-satuan + .choices .choices__input,
    .select-satuan ~ .choices .choices__input {
        font-size: 13px !important;
        padding: 4px 8px !important;
    }
    
    /* Perbaiki inner container - pastikan height sama dengan input Qty (38px = py-2) */
    .choices__inner {
        min-height: 38px !important;
        padding: 0 8px !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
    }
    
    /* Pastikan field Satuan memiliki height yang sama dengan Qty */
    .select-satuan + .choices,
    .select-satuan ~ .choices {
        height: 38px !important;
    }
    
    .select-satuan + .choices .choices__inner,
    .select-satuan ~ .choices .choices__inner {
        min-height: 38px !important;
        height: 38px !important;
        padding: 0 8px !important;
        display: flex !important;
        align-items: center !important;
        line-height: 1.5 !important;
    }
    
    /* Pastikan placeholder text sejajar vertikal */
    .select-satuan + .choices .choices__inner .choices__placeholder,
    .select-satuan ~ .choices .choices__inner .choices__placeholder {
        line-height: 1.5 !important;
        padding: 0 !important;
        margin: 0 !important;
        color: #9ca3af !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
    }
    
    /* Pastikan selected item sejajar vertikal */
    .select-satuan + .choices .choices__inner .choices__item--selectable,
    .select-satuan ~ .choices .choices__inner .choices__item--selectable {
        line-height: 1.5 !important;
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
    }
    
    /* Pastikan dropdown arrow sejajar vertikal dan center */
    .select-satuan + .choices .choices__inner .choices__arrow,
    .select-satuan ~ .choices .choices__inner .choices__arrow {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 4px 0 8px !important;
        margin-left: auto !important;
    }
    
    /* Pastikan input search juga sejajar */
    .select-satuan + .choices .choices__inner .choices__input,
    .select-satuan ~ .choices .choices__inner .choices__input {
        line-height: 1.5 !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
@endpush
@endsection

