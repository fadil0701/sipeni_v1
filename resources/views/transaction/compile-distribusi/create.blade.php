@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('transaction.compile-distribusi.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Compile SBBK
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Compile SBBK dari Draft Detail</h2>
        <p class="mt-1 text-sm text-gray-600">No. Permintaan: {{ $permintaan->no_permintaan }}</p>
    </div>
    
    <form action="{{ route('transaction.compile-distribusi.store') }}" method="POST" class="p-6" id="formCompileDistribusi">
        @csrf
        <input type="hidden" name="id_permintaan" value="{{ $permintaan->id_permintaan }}">
        
        <div class="space-y-6">
            <!-- Informasi Permintaan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Permintaan</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $permintaan->unitKerja->nama_unit_kerja ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pemohon</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $permintaan->pemohon->nama_pegawai ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Permintaan</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $permintaan->tanggal_permintaan->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Permintaan</label>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @if(is_array($permintaan->jenis_permintaan))
                                    @foreach($permintaan->jenis_permintaan as $jenis)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $jenis }}</span>
                                    @endforeach
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $permintaan->jenis_permintaan }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Draft yang akan di-compile -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Draft Distribusi</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gudang Asal</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    @if($draftDetails->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0)
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Batch</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Exp Date</th>
                                    @endif
                                    @if($draftDetails->where('kategori_gudang', 'ASET')->count() > 0)
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Seri</th>
                                    @endif
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($draftDetails as $draft)
                                    @php
                                        $inventory = $draft->inventory;
                                        $isAset = $draft->kategori_gudang === 'ASET';
                                        $isFarmasiPersediaan = in_array($draft->kategori_gudang, ['FARMASI', 'PERSEDIAAN']);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                {{ $draft->kategori_gudang }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $inventory->dataBarang->nama_barang ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->gudangAsal->nama_gudang ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($draft->qty_distribusi, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $draft->satuan->nama_satuan ?? '-' }}</td>
                                        @php
                                            $hasFarmasiPersediaan = $draftDetails->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0;
                                            $hasAset = $draftDetails->where('kategori_gudang', 'ASET')->count() > 0;
                                        @endphp
                                        @if($hasFarmasiPersediaan)
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            @if($isFarmasiPersediaan)
                                                {{ $inventory->no_batch ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            @if($isFarmasiPersediaan && $inventory->tanggal_kedaluwarsa)
                                                {{ \Carbon\Carbon::parse($inventory->tanggal_kedaluwarsa)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @endif
                                        @if($hasAset)
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            @if($isAset)
                                                @php
                                                    // Ambil nomor seri dari inventory_item yang akan didistribusikan
                                                    $inventoryItems = \App\Models\InventoryItem::where('id_inventory', $inventory->id_inventory)
                                                        ->where('id_gudang', $draft->id_gudang_asal)
                                                        ->where('status_item', 'AKTIF')
                                                        ->limit((int)$draft->qty_distribusi)
                                                        ->get();
                                                    
                                                    $noSeriList = $inventoryItems->pluck('no_seri')->filter()->unique()->values();
                                                @endphp
                                                @if($noSeriList->count() > 0)
                                                    @if($noSeriList->count() <= 3)
                                                        {{ $noSeriList->join(', ') }}
                                                    @else
                                                        {{ $noSeriList->take(3)->join(', ') }}<br>
                                                        <span class="text-xs text-gray-500">+{{ $noSeriList->count() - 3 }} lainnya</span>
                                                    @endif
                                                @else
                                                    {{ $inventory->no_seri ?? '-' }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @endif
                                        <td class="px-4 py-2 text-sm text-gray-900">Rp {{ number_format($draft->harga_satuan, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 font-medium">Rp {{ number_format($draft->subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    @php
                                        $colspan = 5; // Kategori, Barang, Gudang Asal, Qty, Satuan
                                        if($draftDetails->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])->count() > 0) {
                                            $colspan += 2; // No. Batch, Exp Date
                                        }
                                        if($draftDetails->where('kategori_gudang', 'ASET')->count() > 0) {
                                            $colspan += 1; // No. Seri
                                        }
                                        $colspan += 1; // Harga Satuan
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="px-4 py-2 text-sm text-gray-900 text-right">Total:</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        Rp {{ number_format($draftDetails->sum('subtotal'), 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Informasi Distribusi -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Distribusi</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_distribusi" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Distribusi <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="tanggal_distribusi" 
                            name="tanggal_distribusi" 
                            required
                            value="{{ old('tanggal_distribusi', date('Y-m-d')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_distribusi') border-red-500 @enderror"
                        >
                        @error('tanggal_distribusi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_gudang_tujuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Gudang Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="id_gudang_tujuan" 
                            name="id_gudang_tujuan_display"
                            value="{{ $gudangTujuan->nama_gudang ?? '-' }}"
                            readonly
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm"
                        >
                        <input type="hidden" name="id_gudang_tujuan" value="{{ $gudangTujuan->id_gudang ?? '' }}">
                        @error('id_gudang_tujuan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_pegawai_pengirim" class="block text-sm font-medium text-gray-700 mb-2">
                            Pegawai Pengirim <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_pegawai_pengirim" 
                            name="id_pegawai_pengirim" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_pegawai_pengirim') border-red-500 @enderror"
                        >
                            <option value="">Pilih Pegawai Pengirim</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ old('id_pegawai_pengirim') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pegawai_pengirim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="3"
                            placeholder="Masukkan keterangan distribusi"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('transaction.compile-distribusi.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Compile & Buat SBBK
            </button>
        </div>
    </form>
</div>
@endsection






