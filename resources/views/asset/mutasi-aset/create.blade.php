@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.mutasi-aset.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Mutasi
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah Mutasi Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Pindahkan aset dari satu ruangan ke ruangan lain</p>
    </div>
    
    <form action="{{ route('asset.mutasi-aset.store') }}" method="POST" class="p-6">
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
                            @php
                                $kir = $registerAset->kartuInventarisRuangan->first();
                            @endphp
                            <option 
                                value="{{ $registerAset->id_register_aset }}" 
                                data-ruangan-asal="{{ $kir ? $kir->id_ruangan : '' }}"
                                {{ old('id_register_aset') == $registerAset->id_register_aset ? 'selected' : '' }}
                            >
                                {{ $registerAset->nomor_register }} - {{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}
                                @if($kir)
                                    ({{ $kir->ruangan->nama_ruangan ?? '-' }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('id_register_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih register aset yang akan dimutasi (harus sudah punya KIR)</p>
                </div>
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
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan_asal') == $ruangan->id_ruangan ? 'selected' : '' }}>
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
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan_tujuan') == $ruangan->id_ruangan ? 'selected' : '' }}>
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
                        value="{{ old('tanggal_mutasi', date('Y-m-d')) }}"
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
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.mutasi-aset.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Mutasi
            </button>
        </div>
    </form>
</div>

<script>
    // Auto-fill ruangan asal berdasarkan register aset yang dipilih
    document.getElementById('id_register_aset').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const ruanganAsal = selectedOption.getAttribute('data-ruangan-asal');
        if (ruanganAsal) {
            document.getElementById('id_ruangan_asal').value = ruanganAsal;
        }
    });
</script>
@endsection
