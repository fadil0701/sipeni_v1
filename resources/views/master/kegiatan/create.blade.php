@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master.kegiatan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Kegiatan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Kegiatan</h2>
    </div>
    
    <form action="{{ route('master.kegiatan.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="max-w-2xl space-y-6">
            <div>
                <label for="id_program" class="block text-sm font-medium text-gray-700 mb-2">
                    Program <span class="text-red-500">*</span>
                </label>
                <select id="id_program" name="id_program" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm @error('id_program') border-red-500 @enderror">
                    <option value="">Pilih Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id_program }}" {{ old('id_program') == $program->id_program ? 'selected' : '' }}>{{ $program->kode_program ? $program->kode_program.' — ' : '' }}{{ $program->nama_program }}</option>
                    @endforeach
                </select>
                @error('id_program')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kode_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="kode_kegiatan" name="kode_kegiatan" required value="{{ old('kode_kegiatan') }}" autocomplete="off" placeholder="Contoh: 1.02.03.1.01" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm font-mono @error('kode_kegiatan') border-red-500 @enderror">
                @error('kode_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="nama_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_kegiatan" name="nama_kegiatan" required value="{{ old('nama_kegiatan') }}" placeholder="Masukkan nama kegiatan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm @error('nama_kegiatan') border-red-500 @enderror">
                @error('nama_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master.kegiatan.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">Simpan</button>
        </div>
    </form>
</div>
@endsection

