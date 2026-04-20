@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master.gudang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Gudang
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Gudang</h2>
    </div>
    
    <form action="{{ route('master.gudang.update', $gudang->id_gudang) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
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
                        <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $gudang->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                            {{ $unitKerja->nama_unit_kerja }}
                        </option>
                    @endforeach
                </select>
                @error('id_unit_kerja')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nama_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Gudang <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama_gudang" 
                    name="nama_gudang" 
                    required
                    value="{{ old('nama_gudang', $gudang->nama_gudang) }}"
                    placeholder="Masukkan nama gudang"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_gudang') border-red-500 @enderror"
                >
                @error('nama_gudang')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jenis_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Gudang <span class="text-red-500">*</span>
                </label>
                <select 
                    id="jenis_gudang" 
                    name="jenis_gudang" 
                    required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_gudang') border-red-500 @enderror"
                >
                    <option value="">Pilih Jenis</option>
                    <option value="PUSAT" {{ old('jenis_gudang', $gudang->jenis_gudang) == 'PUSAT' ? 'selected' : '' }}>Pusat</option>
                    <option value="UNIT" {{ old('jenis_gudang', $gudang->jenis_gudang) == 'UNIT' ? 'selected' : '' }}>Unit</option>
                </select>
                @error('jenis_gudang')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="kategori_gudang_field" style="display: {{ old('jenis_gudang', $gudang->jenis_gudang) == 'PUSAT' ? 'block' : 'none' }};">
                <label for="kategori_gudang" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori Gudang <span class="text-red-500">*</span>
                </label>
                <select 
                    id="kategori_gudang" 
                    name="kategori_gudang" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kategori_gudang') border-red-500 @enderror"
                >
                    <option value="">Pilih Kategori</option>
                    <option value="ASET" {{ old('kategori_gudang', $gudang->kategori_gudang) == 'ASET' ? 'selected' : '' }}>ASET</option>
                    <option value="PERSEDIAAN" {{ old('kategori_gudang', $gudang->kategori_gudang) == 'PERSEDIAAN' ? 'selected' : '' }}>PERSEDIAAN</option>
                    <option value="FARMASI" {{ old('kategori_gudang', $gudang->kategori_gudang) == 'FARMASI' ? 'selected' : '' }}>FARMASI</option>
                </select>
                @error('kategori_gudang')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a href="{{ route('master.gudang.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisGudangSelect = document.getElementById('jenis_gudang');
        const kategoriGudangField = document.getElementById('kategori_gudang_field');
        const kategoriGudangSelect = document.getElementById('kategori_gudang');
        
        function toggleKategoriField() {
            if (jenisGudangSelect.value === 'PUSAT') {
                kategoriGudangField.style.display = 'block';
                kategoriGudangSelect.setAttribute('required', 'required');
            } else {
                kategoriGudangField.style.display = 'none';
                kategoriGudangSelect.removeAttribute('required');
                // Clear value jika bukan PUSAT
                if (jenisGudangSelect.value !== 'PUSAT') {
                    kategoriGudangSelect.value = '';
                }
            }
        }
        
        // Set initial state
        toggleKategoriField();
        
        // Listen for changes
        jenisGudangSelect.addEventListener('change', toggleKategoriField);
    });
</script>
@endsection

