@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-data.kode-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Kode Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Kode Barang</h2>
    </div>
    
    <form action="{{ route('master-data.kode-barang.update', $kodeBarang->id_kode_barang) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="id_aset" class="block text-sm font-medium text-gray-700 mb-2">
                    Aset <span class="text-red-500">*</span>
                </label>
                <select id="id_aset" name="id_aset" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_aset') border-red-500 @enderror">
                    <option value="">Pilih Aset</option>
                    @foreach($asets as $aset)
                        <option value="{{ $aset->id_aset }}" {{ old('id_aset', $kodeBarang->id_aset) == $aset->id_aset ? 'selected' : '' }}>{{ $aset->nama_aset }}</option>
                    @endforeach
                </select>
                @error('id_aset')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kode_barang" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" id="kode_barang" name="kode_barang" required value="{{ old('kode_barang', $kodeBarang->kode_barang) }}" placeholder="Masukkan kode barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kode_barang') border-red-500 @enderror">
                @error('kode_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="nama_kode_barang" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kode Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_kode_barang" name="nama_kode_barang" required value="{{ old('nama_kode_barang', $kodeBarang->nama_kode_barang) }}" placeholder="Masukkan nama kode barang" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_kode_barang') border-red-500 @enderror">
                @error('nama_kode_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master-data.kode-barang.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>
@endsection

