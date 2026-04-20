@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.mutasi-aset.show', $mutasiAset->id_mutasi) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Mutasi Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Ubah informasi mutasi aset</p>
    </div>
    
    <form action="{{ route('asset.mutasi-aset.update', $mutasiAset->id_mutasi) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Register (Read-only) -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register Aset</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $mutasiAset->registerAset->nomor_register ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $mutasiAset->registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                            @if($mutasiAset->registerAset->inventory->jenis_barang)
                                <span class="text-gray-500 text-sm block">Jenis Barang: {{ $mutasiAset->registerAset->inventory->jenis_barang }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="id_ruangan_asal" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Asal <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_ruangan_asal" 
                        name="id_ruangan_asal" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_asal') border-red-500 @enderror"
                    >
                        <option value="">Pilih Ruangan Asal</option>
                        @foreach($ruangans as $ruangan)
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan_asal', $mutasiAset->id_ruangan_asal) == $ruangan->id_ruangan ? 'selected' : '' }}>
                                {{ $ruangan->nama_ruangan }} ({{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruangan_asal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_ruangan_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_ruangan_tujuan" 
                        name="id_ruangan_tujuan" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan_tujuan') border-red-500 @enderror"
                    >
                        <option value="">Pilih Ruangan Tujuan</option>
                        @foreach($ruangans as $ruangan)
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan_tujuan', $mutasiAset->id_ruangan_tujuan) == $ruangan->id_ruangan ? 'selected' : '' }}>
                                {{ $ruangan->nama_ruangan }} ({{ $ruangan->unitKerja->nama_unit_kerja ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruangan_tujuan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_mutasi" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mutasi <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_mutasi" 
                        name="tanggal_mutasi" 
                        value="{{ old('tanggal_mutasi', $mutasiAset->tanggal_mutasi ? $mutasiAset->tanggal_mutasi->format('Y-m-d') : '') }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_mutasi') border-red-500 @enderror"
                    >
                    @error('tanggal_mutasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan
                    </label>
                    <textarea 
                        id="keterangan" 
                        name="keterangan" 
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan', $mutasiAset->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.mutasi-aset.show', $mutasiAset->id_mutasi) }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
