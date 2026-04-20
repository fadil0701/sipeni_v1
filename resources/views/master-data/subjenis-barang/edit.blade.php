@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-data.subjenis-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Subjenis Barang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Subjenis Barang</h2>
    </div>
    
    <form action="{{ route('master-data.subjenis-barang.update', $subjenisBarang->id_subjenis_barang) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="id_jenis_barang" class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Barang <span class="text-red-500">*</span>
                </label>
                <select id="id_jenis_barang" name="id_jenis_barang" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_jenis_barang') border-red-500 @enderror">
                    <option value="">Pilih Jenis Barang</option>
                    @foreach($jenisBarangs as $jenisBarang)
                        <option value="{{ $jenisBarang->id_jenis_barang }}" {{ old('id_jenis_barang', $subjenisBarang->id_jenis_barang) == $jenisBarang->id_jenis_barang ? 'selected' : '' }}>
                            {{ $jenisBarang->kategoriBarang->nama_kategori_barang ?? '' }} - {{ $jenisBarang->nama_jenis_barang }}
                        </option>
                    @endforeach
                </select>
                @error('id_jenis_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kode_subjenis_barang" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Subjenis Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" id="kode_subjenis_barang" name="kode_subjenis_barang" required value="{{ old('kode_subjenis_barang', $subjenisBarang->kode_subjenis_barang) }}" placeholder="Masukkan kode subjenis" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kode_subjenis_barang') border-red-500 @enderror">
                @error('kode_subjenis_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="nama_subjenis_barang" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Subjenis Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_subjenis_barang" name="nama_subjenis_barang" required value="{{ old('nama_subjenis_barang', $subjenisBarang->nama_subjenis_barang) }}" placeholder="Masukkan nama subjenis" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_subjenis_barang') border-red-500 @enderror">
                @error('nama_subjenis_barang')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master-data.subjenis-barang.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>
@endsection

