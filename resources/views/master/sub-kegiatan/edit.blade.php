@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master.sub-kegiatan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Sub Kegiatan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Sub Kegiatan</h2>
    </div>
    
    <form action="{{ route('master.sub-kegiatan.update', $subKegiatan->id_sub_kegiatan) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="id_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Kegiatan <span class="text-red-500">*</span>
                </label>
                <select id="id_kegiatan" name="id_kegiatan" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_kegiatan') border-red-500 @enderror">
                    <option value="">Pilih Kegiatan</option>
                    @foreach($kegiatans as $kegiatan)
                        <option value="{{ $kegiatan->id_kegiatan }}" {{ old('id_kegiatan', $subKegiatan->id_kegiatan) == $kegiatan->id_kegiatan ? 'selected' : '' }}>
                            {{ $kegiatan->program->nama_program ?? '' }} - {{ $kegiatan->nama_kegiatan }}
                        </option>
                    @endforeach
                </select>
                @error('id_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kode_sub_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Sub Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="kode_sub_kegiatan" name="kode_sub_kegiatan" required value="{{ old('kode_sub_kegiatan', $subKegiatan->kode_sub_kegiatan) }}" placeholder="Masukkan kode sub kegiatan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kode_sub_kegiatan') border-red-500 @enderror">
                @error('kode_sub_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="nama_sub_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Sub Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_sub_kegiatan" name="nama_sub_kegiatan" required value="{{ old('nama_sub_kegiatan', $subKegiatan->nama_sub_kegiatan) }}" placeholder="Masukkan nama sub kegiatan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_sub_kegiatan') border-red-500 @enderror">
                @error('nama_sub_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master.sub-kegiatan.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>
@endsection

