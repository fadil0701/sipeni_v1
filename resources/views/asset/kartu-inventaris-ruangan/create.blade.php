@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.kartu-inventaris-ruangan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar KIR
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Kartu Inventaris Ruangan</h2>
        <p class="mt-1 text-sm text-gray-600">Tempatkan aset ke ruangan tertentu</p>
    </div>
    
    <form action="{{ route('asset.kartu-inventaris-ruangan.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Informasi Register Aset -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Register Aset</h3>
                <div>
                    <label for="id_register_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Register Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_register_aset" 
                        name="id_register_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_register_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Register Aset</option>
                        @foreach($registerAsets as $registerAset)
                            <option value="{{ $registerAset->id_register_aset }}" {{ old('id_register_aset') == $registerAset->id_register_aset ? 'selected' : '' }}>
                                {{ $registerAset->nomor_register }} - {{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_register_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih register aset yang akan ditempatkan di ruangan</p>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="id_ruangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_ruangan" 
                        name="id_ruangan" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan') border-red-500 @enderror"
                    >
                        <option value="">Pilih Ruangan</option>
                        @foreach($ruangans as $ruangan)
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan') == $ruangan->id_ruangan ? 'selected' : '' }}>
                                {{ $ruangan->nama_ruangan }} ({{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_penanggung_jawab" class="block text-sm font-medium text-gray-700 mb-2">
                        Penanggung Jawab <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_penanggung_jawab" 
                        name="id_penanggung_jawab" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_penanggung_jawab') border-red-500 @enderror"
                    >
                        <option value="">Pilih Penanggung Jawab</option>
                        @foreach($pegawais as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ old('id_penanggung_jawab') == $pegawai->id ? 'selected' : '' }}>
                                {{ $pegawai->nama_pegawai }} ({{ $pegawai->unitKerja->nama_unit_kerja ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_penanggung_jawab')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_penempatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Penempatan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_penempatan" 
                        name="tanggal_penempatan" 
                        value="{{ old('tanggal_penempatan', date('Y-m-d')) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_penempatan') border-red-500 @enderror"
                    >
                    @error('tanggal_penempatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.kartu-inventaris-ruangan.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan KIR
            </button>
        </div>
    </form>
</div>
@endsection
