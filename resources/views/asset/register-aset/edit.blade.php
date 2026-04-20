@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('asset.register-aset.show', $registerAset->id_register_aset) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Register Aset</h2>
        <p class="mt-1 text-sm text-gray-600">Ubah informasi register aset</p>
    </div>
    
    <form action="{{ route('asset.register-aset.update', $registerAset->id_register_aset) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Register & Barang (Read-only, sama seperti di Detail) -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Register</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nomor Register</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registerAset->nomor_register ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gudang Unit</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Barang</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Barang</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registerAset->inventory->dataBarang->nama_barang ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kode Barang</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->dataBarang->kode_data_barang ?? '-' }}</dd>
                        </div>
                        @if($registerAset->inventory->jenis_barang ?? null)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jenis Barang</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->jenis_barang }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Merk</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->merk ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tipe</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $registerAset->inventory->tipe ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Qty (Jumlah ter-register)</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                1 {{ $registerAset->inventory->satuan->nama_satuan ?? 'Unit' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Harga Satuan</dt>
                            <dd class="mt-1 text-sm text-gray-900">Rp {{ number_format($registerAset->inventory->harga_satuan ?? 0, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Harga</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">Rp {{ number_format($registerAset->inventory->harga_satuan ?? 0, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="kondisi_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Kondisi Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="kondisi_aset" 
                        name="kondisi_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('kondisi_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Kondisi</option>
                        <option value="BAIK" {{ old('kondisi_aset', $registerAset->kondisi_aset) == 'BAIK' ? 'selected' : '' }}>Baik</option>
                        <option value="RUSAK_RINGAN" {{ old('kondisi_aset', $registerAset->kondisi_aset) == 'RUSAK_RINGAN' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="RUSAK_BERAT" {{ old('kondisi_aset', $registerAset->kondisi_aset) == 'RUSAK_BERAT' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status_aset" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Aset <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status_aset" 
                        name="status_aset" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_aset') border-red-500 @enderror"
                    >
                        <option value="">Pilih Status</option>
                        <option value="AKTIF" {{ old('status_aset', $registerAset->status_aset) == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                        <option value="NONAKTIF" {{ old('status_aset', $registerAset->status_aset) == 'NONAKTIF' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status_aset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_ruangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Ruangan
                    </label>
                    <select 
                        id="id_ruangan" 
                        name="id_ruangan" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_ruangan') border-red-500 @enderror"
                    >
                        <option value="">Pilih Ruangan (Opsional)</option>
                        @foreach($ruangans ?? [] as $ruangan)
                            <option value="{{ $ruangan->id_ruangan }}" {{ old('id_ruangan', $registerAset->id_ruangan) == $ruangan->id_ruangan ? 'selected' : '' }}>
                                {{ $ruangan->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih ruangan tempat aset ditempatkan (jika ada)</p>
                </div>

                <div>
                    <label for="id_penanggung_jawab" class="block text-sm font-medium text-gray-700 mb-2">
                        Penanggung Jawab
                    </label>
                    <select 
                        id="id_penanggung_jawab" 
                        name="id_penanggung_jawab" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_penanggung_jawab') border-red-500 @enderror"
                    >
                        <option value="">Pilih Penanggung Jawab (Opsional)</option>
                        @foreach($pegawais ?? [] as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ old('id_penanggung_jawab', $kir->id_penanggung_jawab ?? null) == $pegawai->id ? 'selected' : '' }}>
                                {{ $pegawai->nama_pegawai }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_penanggung_jawab')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Pilih penanggung jawab aset (jika sudah ditempatkan di ruangan)</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="tanggal_perolehan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Perolehan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_perolehan" 
                        name="tanggal_perolehan" 
                        value="{{ old('tanggal_perolehan', $registerAset->tanggal_perolehan ? $registerAset->tanggal_perolehan->format('Y-m-d') : '') }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_perolehan') border-red-500 @enderror"
                    >
                    @error('tanggal_perolehan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Tanggal ketika aset diperoleh</p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('asset.register-aset.show', $registerAset->id_register_aset) }}" 
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

