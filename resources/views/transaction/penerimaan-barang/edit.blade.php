@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.penerimaan-barang.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Penerimaan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Penerimaan Barang</h2>
        <p class="text-sm text-gray-600 mt-1">No. Penerimaan: <span class="font-semibold">{{ $penerimaan->no_penerimaan }}</span></p>
    </div>
    
    <form action="{{ route('transaction.penerimaan-barang.update', $penerimaan->id_penerimaan) }}" method="POST" class="p-6" id="formPenerimaan">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Penerimaan</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_penerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Penerimaan <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_penerimaan" 
                            name="tanggal_penerimaan" 
                            required
                            value="{{ old('tanggal_penerimaan', $penerimaan->tanggal_penerimaan->format('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_penerimaan') border-red-500 @enderror"
                        >
                        @error('tanggal_penerimaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

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
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $penerimaan->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pegawai_penerima" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Penerima <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_penerima" 
                            name="id_pegawai_penerima" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_penerima') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Penerima</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_penerima', $penerimaan->id_pegawai_penerima) == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_penerima')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_penerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Penerimaan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="status_penerimaan" 
                            name="status_penerimaan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status_penerimaan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Status</option>
                            <option value="DITERIMA" {{ old('status_penerimaan', $penerimaan->status_penerimaan) == 'DITERIMA' ? 'selected' : '' }}>Diterima</option>
                            <option value="DITOLAK" {{ old('status_penerimaan', $penerimaan->status_penerimaan) == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        @error('status_penerimaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan penerimaan"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan', $penerimaan->keterangan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Detail Penerimaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Penerimaan</h3>
                <div id="detailContainer" class="space-y-4">
                    @foreach(old('detail', $penerimaan->detailPenerimaan) as $index => $detail)
                    <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                            <div class="sm:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Barang</label>
                                <input 
                                    type="text" 
                                    value="{{ is_object($detail) ? ($detail->inventory->dataBarang->nama_barang ?? '-') : '-' }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                                    readonly
                                >
                                <input type="hidden" name="detail[{{ $index }}][id_inventory]" value="{{ is_object($detail) ? $detail->id_inventory : ($detail['id_inventory'] ?? '') }}">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Qty Diterima <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="detail[{{ $index }}][qty_diterima]" 
                                    required
                                    min="0"
                                    step="0.01"
                                    value="{{ old("detail.{$index}.qty_diterima", is_object($detail) ? $detail->qty_diterima : ($detail['qty_diterima'] ?? '')) }}"
                                    placeholder="0"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="detail[{{ $index }}][id_satuan]" 
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id_satuan }}" 
                                            {{ old("detail.{$index}.id_satuan", is_object($detail) ? $detail->id_satuan : ($detail['id_satuan'] ?? '')) == $satuan->id_satuan ? 'selected' : '' }}>
                                            {{ $satuan->nama_satuan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <input 
                                    type="text" 
                                    name="detail[{{ $index }}][keterangan]" 
                                    value="{{ old("detail.{$index}.keterangan", is_object($detail) ? $detail->keterangan : ($detail['keterangan'] ?? '')) }}"
                                    placeholder="Opsional"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @error('detail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.penerimaan-barang.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection

