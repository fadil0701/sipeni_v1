@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-manajemen.master-jabatan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Jabatan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Master Jabatan</h2>
    </div>
    
    <form action="{{ route('master-manajemen.master-jabatan.update', $jabatan->id_jabatan) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="nama_jabatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nama_jabatan" 
                        name="nama_jabatan" 
                        required
                        value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_jabatan') border-red-500 @enderror"
                        placeholder="Masukkan nama jabatan"
                    >
                    @error('nama_jabatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="urutan" class="block text-sm font-medium text-gray-700 mb-2">
                        Urutan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="urutan" 
                        name="urutan" 
                        required
                        min="1"
                        value="{{ old('urutan', $jabatan->urutan) }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('urutan') border-red-500 @enderror"
                        placeholder="Masukkan urutan jabatan"
                    >
                    @error('urutan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Urutan menentukan hierarki jabatan (1 = tertinggi)</p>
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Role User
                    </label>
                    <select 
                        id="role_id" 
                        name="role_id" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role_id') border-red-500 @enderror"
                    >
                        <option value="">Pilih Role (Opsional)</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $jabatan->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} ({{ $role->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Role yang akan diberikan kepada user dengan jabatan ini</p>
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea 
                        id="deskripsi" 
                        name="deskripsi" 
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('deskripsi') border-red-500 @enderror"
                        placeholder="Masukkan deskripsi jabatan"
                    >{{ old('deskripsi', $jabatan->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('master-manajemen.master-jabatan.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Update
            </button>
        </div>
    </form>
</div>
@endsection






