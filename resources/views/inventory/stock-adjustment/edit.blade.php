@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('inventory.stock-adjustment.show', $adjustment->id_adjustment) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Detail
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Stock Adjustment</h2>
        <p class="mt-1 text-sm text-gray-600">Ubah informasi stock adjustment</p>
    </div>
    
    <form action="{{ route('inventory.stock-adjustment.update', $adjustment->id_adjustment) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Stock (Read-only) -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Stock</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Barang</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ $adjustment->dataBarang->nama_barang ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gudang</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $adjustment->gudang->nama_gudang ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Qty Sebelum</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            {{ number_format($adjustment->qty_sebelum, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Form Fields -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tanggal_adjustment" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Adjustment <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="tanggal_adjustment" 
                        name="tanggal_adjustment" 
                        value="{{ old('tanggal_adjustment', $adjustment->tanggal_adjustment ? $adjustment->tanggal_adjustment->format('Y-m-d') : '') }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_adjustment') border-red-500 @enderror"
                    >
                    @error('tanggal_adjustment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="qty_sesudah" class="block text-sm font-medium text-gray-700 mb-2">
                        Qty Sesudah Adjustment <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="qty_sesudah" 
                        name="qty_sesudah" 
                        step="0.01"
                        min="0"
                        value="{{ old('qty_sesudah', $adjustment->qty_sesudah) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('qty_sesudah') border-red-500 @enderror"
                    >
                    @error('qty_sesudah')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_adjustment" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Adjustment <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="jenis_adjustment" 
                        name="jenis_adjustment" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('jenis_adjustment') border-red-500 @enderror"
                    >
                        <option value="">Pilih Jenis</option>
                        <option value="PENAMBAHAN" {{ old('jenis_adjustment', $adjustment->jenis_adjustment) == 'PENAMBAHAN' ? 'selected' : '' }}>Penambahan</option>
                        <option value="PENGURANGAN" {{ old('jenis_adjustment', $adjustment->jenis_adjustment) == 'PENGURANGAN' ? 'selected' : '' }}>Pengurangan</option>
                        <option value="KOREKSI" {{ old('jenis_adjustment', $adjustment->jenis_adjustment) == 'KOREKSI' ? 'selected' : '' }}>Koreksi</option>
                        <option value="OPNAME" {{ old('jenis_adjustment', $adjustment->jenis_adjustment) == 'OPNAME' ? 'selected' : '' }}>Opname</option>
                    </select>
                    @error('jenis_adjustment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status" 
                        name="status" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-500 @enderror"
                    >
                        <option value="DRAFT" {{ old('status', $adjustment->status) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="DIAJUKAN" {{ old('status', $adjustment->status) == 'DIAJUKAN' ? 'selected' : '' }}>Ajukan</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan
                    </label>
                    <input 
                        type="text" 
                        id="alasan" 
                        name="alasan" 
                        value="{{ old('alasan', $adjustment->alasan) }}"
                        placeholder="Alasan adjustment..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('alasan') border-red-500 @enderror"
                    >
                    @error('alasan')
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
                        placeholder="Keterangan tambahan..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan', $adjustment->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('inventory.stock-adjustment.show', $adjustment->id_adjustment) }}" 
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
